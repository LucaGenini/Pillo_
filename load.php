<?php
header('Content-Type: application/json');
require_once 'config.php';
date_default_timezone_set("Europe/Zurich");

// Eingabe empfangen & loggen
$inputJSON = file_get_contents('php://input');
file_put_contents("debug.txt", date("Y-m-d H:i:s") . " – INPUT: " . $inputJSON . PHP_EOL, FILE_APPEND);

// JSON dekodieren
$input = json_decode($inputJSON, true);
if (!is_array($input)) {
    file_put_contents("debug.txt", "⚠️ JSON konnte nicht geparst werden: $inputJSON" . PHP_EOL, FILE_APPEND);
    echo json_encode(["status" => "error", "message" => "Ungültiges JSON"]);
    exit;
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    if (!isset($input["fach_nr"])) {
        echo json_encode(["status" => "error", "message" => "Fehlendes Fach"]);
        exit;
    }

    $fach = intval($input["fach_nr"]);
    $korrekt = isset($input["korrekt"]) ? (bool)$input["korrekt"] : true;
    $timestamp = date("Y-m-d H:i:s");
    $zeit = date("H:i:s");

    $tag = strtoupper(date("D"));
    $deTag = match($tag) {
        'MON' => 'MO', 'TUE' => 'DI', 'WED' => 'MI',
        'THU' => 'DO', 'FRI' => 'FR', 'SAT' => 'SA', 'SUN' => 'SO',
        default => $tag
    };

    file_put_contents("debug.txt", "📆 Tag: $deTag, ⏰ Zeit: $zeit, 📦 Fach: $fach" . PHP_EOL, FILE_APPEND);

    // Medikamentenplan anhand von Wochentag + Zeit (±10 Minuten) suchen
    $sql = "
        SELECT id FROM medication_schedule
        WHERE fach_nr = :fach
          AND status = 'voll'
          AND wochentag = :tag
          AND uhrzeit BETWEEN SUBTIME(:zeit, '00:10:00') AND ADDTIME(:zeit, '00:10:00')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, uhrzeit, :zeit))
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'fach' => $fach,
        'tag' => $deTag,
        'zeit' => $zeit
    ]);

    $plan_id = $stmt->fetchColumn();
    file_put_contents("debug.txt", "🔎 Gefundene plan_id: " . ($plan_id ?: 'NULL') . PHP_EOL, FILE_APPEND);

    if ($plan_id) {
        // Einnahme loggen
        $stmt = $pdo->prepare("INSERT INTO medication_log (fach_nr, timestamp, korrekt, plan_id)
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$fach, $timestamp, $korrekt, $plan_id]);
        file_put_contents("debug.txt", "📥 Einnahme geloggt mit plan_id: $plan_id" . PHP_EOL, FILE_APPEND);

        if ($korrekt) {
            // last_taken setzen
            $update = $pdo->prepare("UPDATE medication_schedule SET last_taken = ? WHERE id = ?");
            $update->execute([$timestamp, $plan_id]);
            file_put_contents("debug.txt", "✅ last_taken aktualisiert für Plan $plan_id" . PHP_EOL, FILE_APPEND);
        }
    } else {
        // Kein Plan – trotzdem loggen
        $stmt = $pdo->prepare("INSERT INTO medication_log (fach_nr, timestamp, korrekt)
                               VALUES (?, ?, ?)");
        $stmt->execute([$fach, $timestamp, false]);
        file_put_contents("debug.txt", "⚠️ Kein Plan gefunden – Einnahme ohne plan_id geloggt" . PHP_EOL, FILE_APPEND);
    }

    echo json_encode([
        "status" => "ok",
        "fach" => $fach,
        "zeit" => $timestamp,
        "plan_id" => $plan_id ?: null
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    file_put_contents("debug.txt", "❌ DB-Exception: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode(["status" => "error", "message" => "DB-Fehler: " . $e->getMessage()]);
}
?>
