<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PILLO - Medikamentenhilfe</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #EAFFE6;
            margin: 0;
            padding: 0;
            color: #00005D;
            font-size: 18pt;
        }

        h1 {
            font-family: 'Roboto', sans-serif;
            font-size: 32pt;
            font-weight: bold;
        }

        h2 {
            font-family: 'Roboto', sans-serif;
            font-size: 26pt;
            font-weight: bold;
        }

        h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 22pt;
            font-weight: bold;
        }

        header {
            background-color: #533DF6;
            height: 197px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        main.container {
            display: center;
            flex-direction: column;
            align-items: center;
            padding: 128px 10px 38px 10px;
            margin: 0 auto;
            max-width: 900px;
        }

        .intro {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .intro-text {
            max-width: 60%;
        }

        .intro-text h2 {
            margin-top: 0;
        }

        .intro ol {
            padding-left: 1.5rem;
        }

        .intro ol li {
            margin-bottom: 0.5rem;
        }

        .highlight-green {
            color: #3CF417;
            font-weight: bold;
        }

        .intro img {
            max-width: 35%;
            height: auto;
        }

        .btn-primary {
            background-color: #3CF417;
            border: none;
            padding: 1rem 2rem;
            font-size: 18pt;
            border-radius: 12px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #533DF6;
            color: white;
        }

        .status-box, .history-box {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .next-intake {
            background-color: #533DF6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: bold;
            font-size: 18pt;
            text-align: center;
            margin-bottom: 1rem;
        }

        .fach-status {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            text-align: center;
        }

        .fach {
            border: 3px solid #533DF6;
            border-radius: 12px;
            padding: 1rem;
            flex: 1;
            font-weight: bold;
        }

        .fach.green { color: #3CF417; }
        .fach.orange { color: #00005D; }
        .fach.red { color: red; }

        table {
            width: 100%;
            border-spacing: 10px;
            text-align: center;
        }

        td, th {
            padding: 0.5rem;
        }

        .green { background-color: #3CF417; }
        .yellow { background-color: #FFD700; }
        .red { background-color: red; }

        .legend span {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 4px;
            vertical-align: middle;
            border-radius: 50%;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #533DF6;
            color: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        .popup input, .popup select {
            display: block;
            width: 100%;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 8px;
            border: none;
        }

        .close-btn {
            background: #3CF417;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
        }

        footer {
            background-color: #533DF6;
            color: #3CF417;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <header>
        <img src="Logo.png" alt="Logo von Pillo" style="height: 57px; border-radius: 16px;">
    </header>

    <main class="container">
    <div class="container">
        <div class="intro">
            <div class="intro-text">
                <h2>Deine tägliche Medikamentenhilfe – Zuverlässig & einfach.</h2>
                <p><strong>Die smarte Medikamentenbox mit Web-Erinnerung & Notfallfunktion</strong></p>
                <ol>
                    <li><strong>Medikamente eintragen</strong><br> Uhrzeit, Fach und Wochentage festlegen – zum Beispiel, ob ein Medikament täglich oder nur montags genommen werden soll.</li>
                    <li><strong>Display zeigt, was als Nächstes dran ist</strong><br> Beispiel: 12:00 – Fach 2 – Blutdruckmedikament</li>
                    <li><strong>Fach öffnen – Einnahme erfolgt</strong><br> Du öffnest ein bestimmtes Fach, sobald das Medikament genommen hast.</li>
                    <li><strong>Vergessen? Du wirst erinnert</strong><br> Vergisst du eine Einnahme, wird eine Notfallnummer benachrichtigt.</li>
                    <li><strong>Alles im Blick behalten</strong><br> Mit einem Blick siehst du übersichtlich, was eingenommen hast – farblich <span class="highlight-green">markiert</span>.</li>
                </ol>
                <button class="btn-primary" onclick="openPopup()">Medikamente eintragen</button>
            </div>
            <img src="Gadget.png" alt="Medikamentenbox">
        </div>

        <div class="status-box">
            <h3>Nächste Einnahme</h3>
            <div class="next-intake">12:00 – Fach 2 – Blutdruckmedikament</div>

            <h3>Einnahmestatus</h3>
            <div class="fach-status">
                <div class="fach green">
                    <div>Fach 1</div>
                    <div>Eingenommen</div>
                    <div>08:00 – Eingenommen</div>
                </div>
                <div class="fach orange">
                    <div>Fach 2</div>
                    <div>Bald</div>
                    <div>12:00 – Erinnerung</div>
                </div>
                <div class="fach red">
                    <div>Fach 3</div>
                    <div>Überfällig</div>
                    <div>20:00 – 6h verspätet</div>
                </div>
            </div>
        </div>

        <div class="history-box">
            <h3>Einnahmeverlauf</h3>
            <table>
                <tr>
                    <th></th>
                    <th>MO</th><th>DI</th><th>MI</th><th>DO</th><th>FR</th><th>SA</th><th>SO</th>
                </tr>
                <tr>
                    <td>Fach 1</td>
                    <td class="green"></td><td class="red"></td><td class="green"></td><td class="green"></td><td class="yellow"></td><td class="green"></td><td class="yellow"></td>
                </tr>
                <tr>
                    <td>Fach 2</td>
                    <td class="yellow"></td><td class="green"></td><td class="green"></td><td class="yellow"></td><td class="green"></td><td class="red"></td><td class="yellow"></td>
                </tr>
                <tr>
                    <td>Fach 3</td>
                    <td class="green"></td><td class="yellow"></td><td class="green"></td><td class="green"></td><td class="red"></td><td class="green"></td><td class="yellow"></td>
                </tr>
            </table>
            <div class="legend">
                <p><span class="green"></span> pünktlich <span class="yellow"></span> verspätet <span class="red"></span> nicht eingenommen</p>
            </div>
        </div>
    </div>
    </main>

   <!-- Pop-up Formular -->
    <div class="popup" id="popupForm">
        <form action="submit_medikament.php" method="post">
            <label>Fach:</label>
            <select name="fach" required>
                <option value="Fach 1">Fach 1</option>
                <option value="Fach 2">Fach 2</option>
                <option value="Fach 3">Fach 3</option>
            </select>

            <label>Medikamentenname:</label>
            <input type="text" name="medi_name" required>

            <label>Wochentage (z.B. MO,DI,MI):</label>
            <input type="text" name="wochentage" required>

            <label>Uhrzeit:</label>
            <input type="time" name="uhrzeit" required>

            <label>Notfallnummer:</label>
            <input type="text" name="notfall_nummer">

            <button class="btn-primary" type="submit">Änderungen speichern</button>
            <button class="close-btn" type="button" onclick="closePopup()">Abbrechen</button>
        </form>
    </div>

    <footer>
        Copyright 2025, Luca Gemini
    </footer>

    <script>
        function openPopup() {
            document.getElementById('popupForm').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popupForm').style.display = 'none';
        }
    </script>
</body>
</html>
