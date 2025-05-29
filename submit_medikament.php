<?php
require_once 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    // Eingabedaten absichern
    $fach = $_POST['fach'] ?? '';
    $med_name = trim($_POST['medi_name'] ?? '');
    $notfallnummer = trim($_POST['notfall_nummer'] ?? '');
    $zeiten = $_POST['zeiten'] ?? [];

    // Fachnummer extrahieren
    $fach_nr = intval(filter_var($fach, FILTER_SANITIZE_NUMBER_INT));

    // Pflichtfeld prüfen
    if (empty($med_name)) {
        header("Location: index.html?error=1");
        exit();
    }

    // Alte Einträge dieses Fachs löschen
    $stmt = $pdo->prepare("DELETE FROM medication_schedule WHERE fach_nr = ?");
    $stmt->execute([$fach_nr]);

    // Neue Einträge einfügen, wenn Zeit gesetzt ist
    $tage = ['MO', 'DI', 'MI', 'DO', 'FR', 'SA', 'SO'];
    $insert = $pdo->prepare("
        INSERT INTO medication_schedule (fach_nr, med_name, wochentag, uhrzeit, status, notfallnummer)
        VALUES (:fach_nr, :med_name, :wochentag, :uhrzeit, 'voll', :notfallnummer)
    ");

    foreach ($tage as $tag) {
        if (!empty($zeiten[$tag])) {
            $insert->execute([
                'fach_nr' => $fach_nr,
                'med_name' => $med_name,
                'wochentag' => $tag,
                'uhrzeit' => $zeiten[$tag],
                'notfallnummer' => $notfallnummer
            ]);
        }
    }

    header("Location: index.html?success=1");
    exit();

} catch (PDOException $e) {
    echo "❌ Fehler beim Speichern: " . $e->getMessage();
}
?>
