<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Smarte Pillenbox – Startseite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 2rem;
        }

        header {
            background-color: #0078D4;
            color: white;
            padding: 1.5rem;
            text-align: center;
            border-radius: 8px;
        }

        main {
            margin-top: 2rem;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        .info-box {
            background: #e7f3ff;
            border-left: 6px solid #0078D4;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 4px;
        }

        .placeholder {
            height: 200px;
            background: #ccc;
            border-radius: 4px;
            text-align: center;
            line-height: 200px;
            color: #666;
            font-style: italic;
        }

        footer {
            text-align: center;
            margin-top: 3rem;
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body>

<header>
    <h1>Smarte Pillenbox_</h1>
    <p>Deine digitale Unterstützung für pünktliche Medikamenteneinnahme</p>
</header>

<main>
    <div class="info-box">
        <strong>Hinweis:</strong> Diese Version der Startseite zeigt noch keine Echtzeitdaten. Später werden hier Statistiken aus der Datenbank angezeigt.
    </div>

    <h2>Dein Einnahmestatus</h2>
    <div class="placeholder">
        [Hier könnte deine Statistik stehen]
    </div>

    <h2>Was macht die smarte Pillenbox?</h2>
    <ul>
        <li>Erkennt per Distanzsensor, ob ein Medikament entnommen wurde</li>
        <li>Speichert die Daten für jeden Wochentag und jedes Fach</li>
        <li>Visualisiert Einnahmen auf dieser Webseite</li>
        <li>Optional: Benachrichtigt bei verpasster Einnahme</li>
    </ul>
</main>

<footer>
    &copy; <?= date("Y") ?> Smarte Pillenbox – Projektarbeit von Luca
</footer>

</body>
</html>
