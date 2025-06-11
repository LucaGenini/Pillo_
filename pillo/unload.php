<?php
header('Content-Type: application/json');
require_once 'config.php';

date_default_timezone_set("Europe/Zurich");

function mapDayToDE($dayEN) {
    return [
        'MON' => 'MO', 'TUE' => 'DI', 'WED' => 'MI',
        'THU' => 'DO', 'FRI' => 'FR', 'SAT' => 'SA', 'SUN' => 'SO'
    ][$dayEN] ?? $dayEN;
}

function getCurrentWeekDates() {
    $start = new DateTime();
    $start->modify('monday this week');
    $week = [];
    for ($i = 0; $i < 7; $i++) {
        $date = clone $start;
        $date->modify("+$i days");
        $week[] = ['tag' => mapDayToDE(strtoupper($date->format('D'))), 'datum' => $date->format('Y-m-d')];
    }
    return $week;
}

function getHistory($pdo) {
    $tage = getCurrentWeekDates();
    $result = [];

    foreach ([1, 2] as $fach) {
        $statusArray = [];

        foreach ($tage as $tagInfo) {
            $tag = $tagInfo['tag'];
            $datum = $tagInfo['datum'];
            $jetzt = new DateTime();

            $stmt = $pdo->prepare("
                SELECT id, uhrzeit, created_at
                FROM medication_schedule
                WHERE fach_nr = :fach AND wochentag = :tag AND status = 'voll'
            ");
            $stmt->execute(['fach' => $fach, 'tag' => $tag]);
            $plaene = $stmt->fetchAll();

            if (!$plaene) {
                $statusArray[] = "no-med";
                continue;
            }

            $color = "future";

            foreach ($plaene as $plan) {
                $soll = new DateTime($datum . ' ' . $plan['uhrzeit']);
                $created = new DateTime($plan['created_at']);
                if ($created > $soll) {
                    $color = "no-med";
                    continue;
                }

                if ($soll > $jetzt) {
                    $color = "future";
                    continue;
                }

                $stmt2 = $pdo->prepare("
                    SELECT timestamp FROM medication_log
                    WHERE fach_nr = :fach AND plan_id = :pid AND DATE(timestamp) = :datum
                ");
                $stmt2->execute([
                    'fach' => $fach,
                    'pid' => $plan['id'],
                    'datum' => $datum
                ]);
                $log = $stmt2->fetch();

                if ($log) {
                    $ist = new DateTime($log['timestamp']);
                    $diff = abs($ist->getTimestamp() - $soll->getTimestamp());

                    if ($diff <= 600) $color = "green";
                    elseif ($diff <= 3600) $color = "yellow";
                    else $color = "red";
                } elseif ($jetzt->getTimestamp() > $soll->getTimestamp() + 3600) {
                    $color = "red";
                }
            }

            $statusArray[] = $color;
        }

        $result[] = [
            'fach' => "Fach $fach",
            'wochentage' => $statusArray
        ];
    }

    return $result;
}

function getNextIntake($pdo) {
    $now = new DateTime();
    $cutoff = $now->modify('-30 seconds')->format('Y-m-d H:i:s');

    for ($i = 0; $i <= 6; $i++) {
        $dayObj = new DateTime("+$i days");
        $tag = mapDayToDE(strtoupper($dayObj->format('D')));
        $zeit = ($i === 0) ? date('H:i:s') : '00:00:00';

        $stmt = $pdo->prepare("
            SELECT * FROM medication_schedule
            WHERE wochentag = :tag
              AND uhrzeit >= :zeit
              AND status = 'voll'
              AND (last_taken IS NULL OR last_taken < :cutoff)
            ORDER BY uhrzeit ASC
            LIMIT 1
        ");
        $stmt->execute([
            'tag' => $tag,
            'zeit' => $zeit,
            'cutoff' => $cutoff
        ]);

        if ($row = $stmt->fetch()) {
            return [
                'fach' => "Fach " . $row['fach_nr'],
                'uhrzeit' => substr($row['uhrzeit'], 0, 5),
                'medikament' => $row['med_name'],
                'wochentag' => $tag
            ];
        }
    }

    return null;
}

function getFachStatus($pdo) {
    $result = [];
    $tage = ['MO','DI','MI','DO','FR','SA','SO'];

    foreach ([1, 2] as $fach) {
        $stmt = $pdo->prepare("
            SELECT * FROM medication_schedule
            WHERE fach_nr = :fach AND status = 'voll'
        ");
        $stmt->execute(['fach' => $fach]);

        $rows = $stmt->fetchAll();
        $found = null;
        $jetzt = new DateTime();

        foreach ($rows as $row) {
            $tagIndex = array_search($row['wochentag'], $tage);
            if ($tagIndex === false) continue;
            $planDate = new DateTime("last Sunday +$tagIndex days " . $row['uhrzeit']);
            if (empty($row['last_taken']) || new DateTime($row['last_taken']) < $planDate) {
                $found = $row;
                break;
            }
        }

        if ($found) {
            $status = "Geplant";
            $farbe = "grey";
            $zeit = "{$found['wochentag']}, " . substr($found['uhrzeit'], 0, 5) . " – {$found['med_name']}";

            if (!empty($found['last_taken'])) {
                $last = new DateTime($found['last_taken']);
                if ($jetzt->getTimestamp() - $last->getTimestamp() < 15) {
                    $status = "Eingenommen";
                    $farbe = "green";
                    $zeit = $last->format("H:i") . " – Eingenommen";
                }
            }
        } else {
            $status = "Nicht geplant";
            $farbe = "grey";
            $zeit = "Keine Einnahme geplant";
        }

        $result[] = [
            'fach' => "Fach $fach",
            'status' => $status,
            'zeit' => $zeit,
            'statusFarbe' => $farbe
        ];
    }

    return $result;
}

function getMonthlyStats($pdo) {
    $result = [];
    for ($i = 11; $i >= 0; $i--) {
        $start = (new DateTime("first day of -$i month"))->format("Y-m-01");
        $end = (new DateTime("last day of -$i month"))->format("Y-m-t");
        $label = (new DateTime($start))->format("M Y");

        $entry = ['monat' => $label];
        foreach ([1, 2] as $fach) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN korrekt = 1 THEN 1 ELSE 0 END) as korrekt
                FROM medication_log
                WHERE fach_nr = :fach AND DATE(timestamp) BETWEEN :start AND :end
            ");
            $stmt->execute(['fach' => $fach, 'start' => $start, 'end' => $end]);
            $row = $stmt->fetch();
            $entry["fach$fach"] = ($row['total'] > 0) ? round($row['korrekt'] / $row['total'] * 100) : 0;
        }

        $result[] = $entry;
    }

    return $result;
}

function getWeeklyStats($pdo) {
    $result = [];
    $today = new DateTime();

    for ($i = 3; $i >= 0; $i--) {
        $start = clone $today;
        $start->modify("-$i week")->modify('monday this week');
        $end = clone $start;
        $end->modify('sunday this week');

        $label = "KW " . $start->format("W");
        $entry = ['kw' => $label];

        foreach ([1, 2] as $fach) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN korrekt = 1 THEN 1 ELSE 0 END) as korrekt
                FROM medication_log
                WHERE fach_nr = :fach
                  AND DATE(timestamp) BETWEEN :start AND :end
            ");
            $stmt->execute([
                'fach' => $fach,
                'start' => $start->format("Y-m-d"),
                'end' => $end->format("Y-m-d")
            ]);
            $row = $stmt->fetch();

            // DEBUG
            error_log("📊 $label | Fach $fach | {$start->format('Y-m-d')} - {$end->format('Y-m-d')} | Total: {$row['total']} | Korrekt: {$row['korrekt']}");

            $entry["fach$fach"] = ($row['total'] > 0) ? round($row['korrekt'] / $row['total'] * 100) : 0;
        }

        $result[] = $entry;
    }

    return $result;
}

function getMotivation($pdo) {
    $infos = [];
    foreach ([1, 2] as $fach) {
        $stmt = $pdo->prepare("
            SELECT DATE(timestamp) as datum, korrekt
            FROM medication_log
            WHERE fach_nr = :fach
            ORDER BY timestamp DESC
        ");
        $stmt->execute(['fach' => $fach]);
        $logs = $stmt->fetchAll();

        $streak = 0;
        $heute = new DateTime();
        $heute->setTime(0, 0);

        foreach ($logs as $log) {
            $logDate = new DateTime($log['datum']);
            $logDate->setTime(0, 0);

            if ($logDate == $heute && (int)$log['korrekt'] === 1) {
                $streak++;
                $heute->modify('-1 day');
            } elseif ($logDate < $heute) {
                break;
            }
        }

        $infos[] = "Fach $fach: $streak Tage ohne Aussetzer 🎯";
    }

    return $infos;
}

// === JSON-Ausgabe ===
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    echo json_encode([
        'naechste' => getNextIntake($pdo),
        'fach_status' => getFachStatus($pdo),
        'verlauf' => getHistory($pdo),
        'monatsstatistik' => getMonthlyStats($pdo),
        'wochenstatistik' => getWeeklyStats($pdo),
        'motivation' => getMotivation($pdo)
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
