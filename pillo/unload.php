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

function getVerlaufExtended($pdo, $startDate, $endDate) {
    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day')
    );

    $result = [];
    foreach ([1, 2] as $fach) {
        $tageDaten = [];

        foreach ($period as $date) {
            $tag = mapDayToDE(strtoupper($date->format('D')));
            $datumStr = $date->format('Y-m-d');
            $jetzt = new DateTime();

            $stmt = $pdo->prepare("SELECT id, uhrzeit, created_at FROM medication_schedule WHERE fach_nr = :fach AND wochentag = :tag AND status = 'voll'");
            $stmt->execute(['fach' => $fach, 'tag' => $tag]);
            $plaene = $stmt->fetchAll();

            if (!$plaene) {
                $tageDaten[] = ['datum' => $datumStr, 'status' => 'no-med'];
                continue;
            }

            $finalStatus = 'future';
            foreach ($plaene as $plan) {
                $soll = new DateTime($datumStr . ' ' . $plan['uhrzeit']);
                $created = new DateTime($plan['created_at']);

                if ($created > $soll) {
                    $finalStatus = 'no-med';
                    continue;
                }

                if ($soll > $jetzt) {
                    $finalStatus = 'future';
                    continue;
                }

                $stmt2 = $pdo->prepare("SELECT timestamp, korrekt FROM medication_log WHERE fach_nr = :fach AND plan_id = :pid AND DATE(timestamp) = :datum");
                $stmt2->execute([
                    'fach' => $fach,
                    'pid' => $plan['id'],
                    'datum' => $datumStr
                ]);
                $log = $stmt2->fetch();

                if ($log) {
                    switch ((int)$log['korrekt']) {
                        case 1:  $finalStatus = 'green'; break;
                        case 2:  $finalStatus = 'yellow'; break;
                        case 0:
                        default: $finalStatus = 'red'; break;
                    }
                } elseif ($jetzt->getTimestamp() > $soll->getTimestamp() + 3600) {
                    $finalStatus = 'red';
                }
            }

            $tageDaten[] = ['datum' => $datumStr, 'status' => $finalStatus];
        }

        $result[] = ['fach' => "Fach $fach", 'tage' => $tageDaten];
    }

    return $result;
}

function getVerlaufWeeklyMatrix($verlaufExtended) {
    $heute = new DateTime();
    $montag = (clone $heute)->modify('monday this week');
    $result = [];

    foreach ($verlaufExtended as $fachDaten) {
        $fachName = $fachDaten['fach'];
        $tage = [];

        foreach (range(0, 6) as $i) {
            $datum = (clone $montag)->modify("+$i days")->format('Y-m-d');
            $status = 'no-med';

            foreach ($fachDaten['tage'] as $eintrag) {
                if ($eintrag['datum'] === $datum) {
                    $status = $eintrag['status'];
                    break;
                }
            }

            $tage[] = ['datum' => $datum, 'status' => $status];
        }

        $result[] = [
            'fach' => $fachName,
            'tage' => $tage
        ];
    }

    return $result;
}

function groupByWeek($verlauf) {
    $result = [];
    $now = new DateTime();

    foreach (range(3, 0) as $i) {
        $start = (clone $now)->modify("-$i weeks")->modify("monday this week");
        $end = (clone $start)->modify("sunday this week");
        $label = "KW " . $start->format("W");

        $entry = ['kw' => $label];
        foreach ($verlauf as $fachDaten) {
            $gruen = $gelb = $rot = $gesamt = 0;
            foreach ($fachDaten['tage'] as $eintrag) {
                $datum = $eintrag['datum'];
                $status = $eintrag['status'];
                $d = new DateTime($datum);
                if ($d >= $start && $d <= $end) {
                    $gesamt++;
                    if ($status === 'green') $gruen++;
                    elseif ($status === 'yellow') $gelb++;
                    elseif ($status === 'red') $rot++;
                }
            }
            $key = strtolower(str_replace(" ", "", $fachDaten['fach']));
            $entry[$key] = [
                'gruen' => $gesamt ? round($gruen / $gesamt * 100) : 0,
                'gelb'  => $gesamt ? round($gelb  / $gesamt * 100) : 0,
                'rot'   => $gesamt ? round($rot   / $gesamt * 100) : 0
            ];
        }
        $result[] = $entry;
    }
    return $result;
}

function groupByMonth($verlauf) {
    $result = [];
    foreach (range(0, 11) as $i) {
        $start = new DateTime("first day of -$i month");
        $end = new DateTime("last day of -$i month");
        $label = $start->format('M Y');

        $entry = ['monat' => $label];
        foreach ($verlauf as $fachDaten) {
            $gruen = $gelb = $rot = $gesamt = 0;
            foreach ($fachDaten['tage'] as $eintrag) {
                $datum = $eintrag['datum'];
                $status = $eintrag['status'];
                $d = new DateTime($datum);
                if ($d >= $start && $d <= $end) {
                    $gesamt++;
                    if ($status === 'green') $gruen++;
                    elseif ($status === 'yellow') $gelb++;
                    elseif ($status === 'red') $rot++;
                }
            }
            $key = strtolower(str_replace(" ", "", $fachDaten['fach']));
            $entry[$key] = [
                'gruen' => $gesamt ? round($gruen / $gesamt * 100) : 0,
                'gelb'  => $gesamt ? round($gelb  / $gesamt * 100) : 0,
                'rot'   => $gesamt ? round($rot   / $gesamt * 100) : 0
            ];
        }
        $result[] = $entry;
    }
    return array_reverse($result);
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    $today = new DateTime();
    $startDate = (clone $today)->modify('-12 months')->format('Y-m-01');
    $endDate = $today->format('Y-m-d');

    $verlaufExtended = getVerlaufExtended($pdo, $startDate, $endDate);
    $verlaufWeeklyMatrix = getVerlaufWeeklyMatrix($verlaufExtended);

    echo json_encode([
        'naechste' => getNextIntake($pdo),
        'motivation' => [],
        'verlauf' => $verlaufWeeklyMatrix,
        'wochenstatistik' => groupByWeek($verlaufExtended),
        'monatsstatistik' => groupByMonth($verlaufExtended)
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
