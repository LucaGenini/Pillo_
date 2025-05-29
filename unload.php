<?php
header('Content-Type: application/json');
require_once 'config.php';

date_default_timezone_set("Europe/Zurich");

function mapDayToDE($dayEN) {
    $map = [
        'MON' => 'MO', 'TUE' => 'DI', 'WED' => 'MI',
        'THU' => 'DO', 'FRI' => 'FR', 'SAT' => 'SA', 'SUN' => 'SO'
    ];
    return $map[$dayEN] ?? $dayEN;
}

function getCurrentWeekDates() {
    $monday = new DateTime();
    $monday->modify('monday this week');
    $week = [];
    for ($i = 0; $i < 7; $i++) {
        $day = clone $monday;
        $day->modify("+$i days");
        $week[] = [
            'tag' => mapDayToDE(strtoupper($day->format('D'))),
            'datum' => $day->format('Y-m-d')
        ];
    }
    return $week;
}

function getHistory($pdo) {
    $tage = getCurrentWeekDates();
    $result = [];

    foreach ([1, 2] as $fach) {
        $wochenStatus = [];

        foreach ($tage as $tagInfo) {
            $tag = $tagInfo['tag'];
            $datum = $tagInfo['datum'];

            $sqlPlan = "SELECT id, uhrzeit, created_at FROM medication_schedule 
                        WHERE fach_nr = :fach AND wochentag = :tag AND status = 'voll'";
            $stmt = $pdo->prepare($sqlPlan);
            $stmt->execute(['fach' => $fach, 'tag' => $tag]);
            $plaene = $stmt->fetchAll();

            if (count($plaene) === 0) {
                $wochenStatus[] = "no-med";
                continue;
            }

            $statusFeld = "future";
            $jetzt = new DateTime();

            foreach ($plaene as $plan) {
                $created = new DateTime($plan['created_at']);
                $soll = new DateTime($datum . ' ' . $plan['uhrzeit']);

                if ($created > $soll) {
                    $statusFeld = "no-med";
                    continue;
                }

                if ($soll > $jetzt) {
                    $statusFeld = "future";
                    continue;
                }

                $sqlLog = "SELECT timestamp FROM medication_log 
                           WHERE fach_nr = :fach AND DATE(timestamp) = :datum AND plan_id = :plan_id";
                $stmt2 = $pdo->prepare($sqlLog);
                $stmt2->execute([
                    'fach' => $fach,
                    'datum' => $datum,
                    'plan_id' => $plan['id']
                ]);
                $log = $stmt2->fetch();

                if ($log) {
                    $ist = new DateTime($log['timestamp']);
                    $diff = abs($soll->getTimestamp() - $ist->getTimestamp());

                    if ($diff <= 600) {
                        $statusFeld = "green";
                        break;
                    } elseif ($diff <= 3600 && $statusFeld !== "green") {
                        $statusFeld = "yellow";
                    }
                } else {
                    if ($jetzt->getTimestamp() > $soll->getTimestamp() + 3600) {
                        $statusFeld = "red";
                    }
                }
            }

            $wochenStatus[] = $statusFeld;
        }

        $result[] = [
            'fach' => "Fach $fach",
            'wochentage' => $wochenStatus
        ];
    }

    return $result;
}

function getNextIntake($pdo) {
    $now = new DateTime();
    $cutoff = $now->modify('-30 seconds')->format('Y-m-d H:i:s');

    for ($i = 0; $i <= 6; $i++) {
        $future = (new DateTime())->modify("+{$i} days");
        $day = mapDayToDE(strtoupper($future->format('D')));
        $time = ($i === 0) ? date('H:i:s') : '00:00:00';

        $sql = "SELECT * FROM medication_schedule 
                WHERE wochentag = :day
                  AND uhrzeit >= :time
                  AND status = 'voll'
                  AND (last_taken IS NULL OR last_taken < :date_cutoff)
                ORDER BY uhrzeit ASC
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'day' => $day,
            'time' => $time,
            'date_cutoff' => $cutoff
        ]);

        if ($row = $stmt->fetch()) {
            return [
                'fach' => "Fach " . $row['fach_nr'],
                'uhrzeit' => substr($row['uhrzeit'], 0, 5),
                'medikament' => $row['med_name']
            ];
        }
    }

    return null;
}

function getFachStatus($pdo) {
    $faecher = [];
    $jetzt = new DateTime();

    for ($fach = 1; $fach <= 2; $fach++) {
        $sql = "SELECT * FROM medication_schedule 
                WHERE fach_nr = :fach AND status = 'voll'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fach' => $fach]);

        $found = null;
        while ($row = $stmt->fetch()) {
            $tag = $row['wochentag'];
            $zeit = $row['uhrzeit'];
            $planDate = new DateTime("last Sunday +" . array_search($tag, ['MO','DI','MI','DO','FR','SA','SO']) . " days " . $zeit);
            if (empty($row['last_taken']) || new DateTime($row['last_taken']) < $planDate) {
                $found = $row;
                break;
            }
        }

        if ($found) {
            $status = "Geplant";
            $farbe = "grey";
            $text = "Nächste Einnahme: {$found['wochentag']}, " . substr($found['uhrzeit'], 0, 5) . " – {$found['med_name']}";

            if (!empty($found['last_taken'])) {
                $lastTaken = new DateTime($found['last_taken']);
                if ((new DateTime())->getTimestamp() - $lastTaken->getTimestamp() < 15) {
                    $status = "Eingenommen";
                    $farbe = "green";
                    $text = $lastTaken->format("H:i") . " – Eingenommen";
                }
            }
        } else {
            $status = "Nicht geplant";
            $farbe = "grey";
            $text = "Keine Einnahme geplant";
        }

        $faecher[] = [
            'fach' => "Fach $fach",
            'status' => $status,
            'zeit' => $text,
            'statusFarbe' => $farbe
        ];
    }

    return $faecher;
}

function getPlanned($pdo) {
    $sql = "SELECT fach_nr AS fach, med_name AS medikament, uhrzeit, wochentag FROM medication_schedule WHERE status = 'voll'";
    return $pdo->query($sql)->fetchAll();
}

function getMonatsStatistik($pdo) {
    $fach1 = [];
    $fach2 = [];
    $monate = [];

    for ($i = 11; $i >= 0; $i--) {
        $start = new DateTime("first day of -$i months");
        $end = new DateTime("last day of -$i months");
        $monat = $start->format("M Y");
        $monate[] = $monat;

        foreach ([1, 2] as $fach) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total, 
                SUM(CASE WHEN TIMESTAMPDIFF(SECOND, s.uhrzeit, l.timestamp) <= 3600 THEN 1 ELSE 0 END) as korrekt
                FROM medication_schedule s
                LEFT JOIN medication_log l ON s.id = l.plan_id AND MONTH(l.timestamp) = :month AND YEAR(l.timestamp) = :year
                WHERE s.fach_nr = :fach AND MONTH(s.created_at) <= :month AND s.status = 'voll'");

            $stmt->execute([
                'month' => (int)$start->format('m'),
                'year' => (int)$start->format('Y'),
                'fach' => $fach
            ]);
            $row = $stmt->fetch();
            $percent = ($row['total'] > 0) ? round(($row['korrekt'] / $row['total']) * 100) : 0;

            if ($fach === 1) $fach1[] = $percent;
            else $fach2[] = $percent;
        }
    }

    return [
        'monate' => $monate,
        'fach1' => $fach1,
        'fach2' => $fach2
    ];
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    echo json_encode([
        'naechste' => getNextIntake($pdo),
        'fach_status' => getFachStatus($pdo),
        'verlauf' => getHistory($pdo),
        'geplant' => getPlanned($pdo),
        'monatsstatistik' => getMonatsStatistik($pdo)
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Fehler: ' . $e->getMessage()]);
}
