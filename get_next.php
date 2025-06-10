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
                'zeit' => substr($row['uhrzeit'], 0, 5),
                'med' => $row['med_name'],
                'fach' => (string)$row['fach_nr'],
                'tag' => $tag
            ];
        }
    }

    return [
        'zeit' => '-',
        'med' => '-',
        'fach' => '-',
        'tag' => '-'
    ];
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo json_encode(getNextIntake($pdo));
} catch (PDOException $e) {
    echo json_encode(['zeit' => '-', 'med' => 'DB-Fehler', 'fach' => '-', 'tag' => '-']);
}
