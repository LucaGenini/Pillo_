# 💊 Pillo-Box: Intelligenter Medikamentenspender mit ESP32-C6

> Ein interaktives IoT-System zur Verwaltung, Erinnerung und Protokollierung der Medikamenteneinnahme.

---

## 📦 Inhaltsverzeichnis

- [📝 Projektbeschreibung](#projektbeschreibung)
- [🔁 Reproduzierbarkeit](#reproduzierbarkeit)
- [🔄 Flussdiagramm](#flussdiagramm)
- [🔧 Komponentenplan](#komponentenplan)
- [🧩 Steckschema](#steckschema)
- [🖼️ Screenshots / Medien](#screenshots--medien)
- [⚙️ Umsetzungsprozess](#umsetzungsprozess)
  - [🔧 Entwicklungsprozess](#entwicklungsprozess)
  - [🗺️ Inspirationen & Ziele](#inspirationen--ziele)
  - [🎨 Designentscheidungen](#designentscheidungen)
  - [🚫 Verworfene Lösungsansätze](#verworfene-lösungsansätze)
  - [🔄 Fehlschläge und Umplanung](#fehlschläge-und-umplanung)
  - [🧱 Planung & Aufgabenverteilung](#planung--aufgabenverteilung)
  - [🧠 Lerneffekt](#lerneffekt)
  - [🐞 Known Bugs](#known-bugs)
  - [🧰 Hilfsmittel & Tools](#hilfsmittel--tools)
- [🎥 Video-Dokumentation](#video-dokumentation)
- [📚 Fazit](#fazit)


---

## 📝Projektbeschreibung

Pillo ist eine Website, bei der man seine Medikamenteneinnahme tracken kann.

Das Projekt wurde konzipiert und umgesetzt im Modul Interaktive Medien IV an der Fachhochschule Graubünden. Es soll Ü50+ oder auch jüngeren Menschen, welche regelmässig Medikamente einnehmen müssen, eine Hilfe sein, nachzuverfolgen, wie oft in den vergangenen Wochen oder Monaten ein Medikament vergessen wurde. Vor allem bei schwerwiegenden Medikamenten ist es teilweise unerlässlich, eine regelmässige Einnahme festzustellen, da das Auslassen dieser Medikamente sich negativ auf die Gesundheit einer Person auswirken kann oder beim Versäumnis eines überlebenswichtigen Medikamentes sogar lebensgefährlich sein kann.

Die Motivation für dieses Projekt resultierte aus folgendem Gedanken:
In jüngeren Jahren musste ich selbst regelmässig ein Medikament konsumieren, jedoch war ich mit der Einnahme nie besonders konsistent, womit ich am Ende einer Woche/Monats nicht feststellen konnte, wie oft ich das Medikament tatsächlich konsumiert habe. Gerade bei meinem näheren und vor allem älteren Bekanntenkreis fällt es mir auf, dass viele regelmässig Medikamente konsumieren, aber keine Möglichkeit haben, die Einnahmeregelmässigkeit zu überprüfen. Mein Projekt soll helfen, dieses Handlungsmuster zu brechen durch eine effektive Visualisierung der Versäumnisse.

Die Versäumnisse werden mittels einer Grafik angezeigt, bei der ersichtlich ist, wie viel Prozent ein Medikament effektiv eingenommen wurde innert eines Zeitraumes.

Link zur Website: www.lucagenini.com/Pillo

Innert der aktuellen Woche sieht man auch immer den aktuellen Plan, welches Medikament zu welcher Uhrzeit eingenommen werden muss und je nach Einnahme oder Versäumnis registriert.
<img width="646" alt="Bildschirmfoto 2025-06-15 um 23 56 21" src="https://github.com/user-attachments/assets/8bbc0354-200c-4111-a4a9-1e2a5984246a" />

---

## 🔁 Reproduzierbarkeit

Diese Schritt-für-Schritt-Anleitung hilft dir, das Projekt vollständig nachzubauen – vom Hardwareaufbau bis zur Webvisualisierung.

---

### 1. 🧰 Hardware beschaffen

Für den Bau der Pillo-Box wird folgende Hardware benötigt:

- **1× ESP32-C6** (mit WiFi-Funktion)
- **2× VL6180X Distanzsensoren** (für Objektentnahme-Erkennung)
- **2× GP541-B Magnetsensoren** (zur Fachöffnungserkennung)
- **1× OLED-Display (I2C)** (zur Anzeige der nächsten Einnahme)
- **1× ganzes Breadboard** (für Hauptverkabelung)
- **1× kleineres Breadboard oder Erweiterung** (für Sensorhalterungen)
- Diverse **Jumper-Kabel**, **Widerstände**, ggf. **3D-gedrucktes Gehäuse** (optional)

Hinweis: Der Abstand der Distanzsensoren zur Fachöffnung muss korrekt abgestimmt sein (siehe Abschnitt [Steckschema](#steckschema)).

---

### 2. 🔌 Schaltplan und Aufbau

- Baue den Schaltkreis gemäss dem Steckschema auf. Achte dabei besonders auf:
  - **I2C-Kommunikation** der Sensoren (gemeinsame SDA/SCL-Leitungen)
  - **XSHUT-Pins**, um die Sensoren mit individuellen I2C-Adressen anzusprechen
  - **Reedkontakte** zur Erkennung von Fachöffnungen
- Fixiere die Sensoren in richtiger Position im Gehäuse (ca. 3 cm vom Fach entfernt), um korrekte Distanzmessungen zu ermöglichen.

→ Details findest du im Abschnitt [🧩 Steckschema](#steckschema)

---

### 3. ⚙️ Firmware flashen

- Öffne die Datei `mc.ino` in der **Arduino IDE** oder **PlatformIO**
- Trage deine **WLAN-Zugangsdaten** im Sketch ein (z. B. `ssid`, `pass`)
- Installiere nötige Bibliotheken:
  - `Adafruit_VL6180X`
  - `Adafruit_SSD1306`
  - `RTClib`
  - `WiFi` (ESP32-kompatibel)
  - `Arduino_JSON`
- Schliess den ESP32 an und flashe die Firmware via USB

Nach dem Flashen zeigt das OLED-Display die nächste geplante Einnahme an und der ESP32 sendet Signale an den Server, wenn Sensoren ausgelöst werden.

---

### 4. 🌐 Server aufsetzen (lokal oder Raspberry Pi)

- Installiere einen Webserver mit **PHP 8+** und **MySQL/MariaDB**
  - z. B. über XAMPP (lokal) oder LAMP-Stack auf dem Raspberry Pi
- Lege eine Datenbank an (z. B. `pillo_db`) und importiere die SQL-Datei mit den Tabellenstrukturen `medication_schedule` und `medication_log`
- Lade folgende PHP-Skripte in dein Serververzeichnis:
  - `load.php` → stellt alle Daten bereit (JSON)
  - `submit_medikament.php` → verarbeitet Einnahmelogs
  - `get_next.php` → liefert die nächste geplante Einnahme
  - `unload.php` → generiert Statistikdaten
  - `config.php` → enthält Zugangsdaten zur DB

> Achte darauf, dass der ESP32 die IP-Adresse deines Servers erreichen kann (Port 80, lokal oder via WLAN).

---

### 5. 🖥️ Webinterface starten

- Kopiere die Dateien `index.html`, `style.css` und `loading1.js` in das gleiche Verzeichnis wie die PHP-Skripte
- Rufe die Webseite über den Browser auf (z. B. `http://192.168.1.XX/pillo/index.html`)
- Über das Interface kannst du:
  - die geplanten Einnahmen prüfen
  - Einnahmeverläufe der letzten Wochen/Monate einsehen
  - Live-Visualisierungen nutzen (z. B. Matrix- und Barcharts)

---

### 6. 📈 Test & Debugging

- Öffne ein Fach → wird korrekt erkannt?
- Entnehme ein Objekt → wird die Distanzmessung registriert?
- Überprüfe auf der Website, ob Einträge im Log auftauchen
- Analysiere die Anzeige auf dem OLED (Uhrzeit, Medikament, Fach)
- Optional: Öffne `unload.php` direkt im Browser → bekommst du korrektes JSON?

---

### ✅ Systembereit

Wenn alle Module wie gewünscht funktionieren, kannst du das System im Alltag testen. Der ESP32 sendet zuverlässig alle Einnahmeereignisse an den Server, der diese speichert und visuell auswertet. So entsteht ein datengestütztes Feedbacksystem zur Medikamenteneinnahme.

---

## 🔄Flussdiagramm
In einem ersten Schritt nach der Ideenfindung habe ich mit der Erstellung vom Flussdiagramm in Figma gestartet, dieser Prozess gestaltete sich schwieriger als Anfangs gedacht.
Speziell da ich in ein Projekt in dieser Komplexität so noch nicht umgesetzt habe musste ich im nachhinein das Flussdiagramm nochmals grundlegend anpassen. Grund dafür war, dass auf dem Ursprünglichen gewisse Stränge fehlten oder die Umsetzung anderer Teile zu komplex oder gar nicht möglich waren so wie ich es mir erhofft hatte. Dennoch war die Erstellung des Flussdiagramm eine nützlich Übung, da ich so einen groben Überblick darüber bekommen konnte, wie mein Projekt funktionieren sollte.

Hier im in Anschluss mein finales Flussdiagramm:
![Screenflow](https://github.com/user-attachments/assets/68f54622-40ea-4e81-a5e2-a8ce419c0432)
*Das Diagramm zeigt die Kommunikationswege zwischen ESP32, Website und Server.*

---

## 🔧Komponentenplan
Einen Komponentenplan zu erstellen stellte sich als nicht ganz so intuitiv dar wie ich mir dies Anfangs erhofft habe. Gerade auch deswegen, da ich für dieses Projekt mit mehreren PHP Files und Tabellen gearbeitet habe, was die Verarbeitung/Schematische Darstellung verkompliziert hat.
![Komponentenplan](https://github.com/user-attachments/assets/e54be341-70cc-4e0a-91e9-0666d4949830)
*Verbindungen und Protokolle zwischen haptischen Komponenten und digitalen Modulen.*

In einem ersten Schritt war mir nicht bewusst, wieviel komplexer sich das Projekt gestalten würde, wenn ich mehrere Tabellen verwende, welche Daten aus unterschiedlichen Quellen ziehen. Da ich die Auslesung der Daten und Zusammenarbeit und Auswertung in der DB komplett selbstständig umsetzen musste, forderte dies dementsprechend viele Trial & Error Versuche, bis alles sauber miteinander kommunizierte.

Untenstehend die finalisierte Tabellenstruktur:
<img width="1058" alt="Bildschirmfoto 2025-06-15 um 21 59 47" src="https://github.com/user-attachments/assets/88e7f6ba-4d42-43eb-a86e-efd0e5f52c20" />
<img width="1104" alt="Bildschirmfoto 2025-06-15 um 21 59 54" src="https://github.com/user-attachments/assets/4b903c09-6f1a-471a-add5-d0eaf1dff29b" />

Im Zentrum des Systems stehen zwei eng miteinander verknüpfte Tabellen:

- **`medication_schedule`**: definiert den geplanten Einnahmerhythmus
- **`medication_log`**: dokumentiert tatsächliche Einnahmen

---

** 📋 `medication_schedule` (Die Planungsgrundlage)**

Diese Tabelle enthält alle **geplanten Einnahmen**, basierend auf Wochentagen und Uhrzeiten pro Medikamentenfach.

Wichtige Felder:

| Feld         | Bedeutung                                                 |
|--------------|------------------------------------------------------------|
| `fach_nr`    | Nummer des Fachs im Pillo-Spender                          |
| `med_name`   | Name des Medikaments                                       |
| `wochentag`  | Geplanter Einnahmetag (z. B. "MO")                         |
| `uhrzeit`    | Geplante Uhrzeit                                           |
| `status`     | Aktueller Ladezustand des Fachs ("voll", "leer" etc.)     |
| `last_taken` | Zeitpunkt der letzten registrierten Einnahme              |
| `created_at` | Wann wurde dieser Eintrag geplant?                         |
| `id`         | Eindeutiger Primärschlüssel für Verknüpfung mit Logs       |

**Ein Plan-Eintrag beschreibt also:**
> *„Am Montag um 19:30 Uhr soll im Fach 2 das Medikament 'Methadon' eingenommen werden.“*

---

**📦 `medication_log` (Das reale Verhalten)**

Diese Tabelle speichert jede **tatsächliche Interaktion** mit dem Spender: z. B. wenn ein Fach geöffnet wird und ein Medikament entnommen wird.

| Feld        | Bedeutung                                                      |
|-------------|-----------------------------------------------------------------|
| `timestamp` | Zeitstempel der tatsächlichen Einnahme                         |
| `korrekt`   | 1 = korrekt (geplant & eingenommen), 2 =verspätet, 0 = vergessen,   |
| `plan_id`   | Verweis auf den geplanten Eintrag (`medication_schedule.id`)   |
| `fach_nr`   | Welches Fach wurde geöffnet?                                   |
| `id`        | Primärschlüssel                                                 |

---

** 🔗 Wie die Tabellen zusammenarbeiten**


Die beiden Tabellen `medication_schedule` (Plan) und `medication_log` (Protokoll) arbeiten eng zusammen, um den gesamten Einnahmeprozess strukturiert abzubilden:

1. **Planung**  
   In `medication_schedule` wird festgelegt, **wann welches Medikament in welchem Fach eingenommen werden soll** – jeweils mit Wochentag, Uhrzeit und Medikamentenname. Jeder Planungseintrag erhält eine eindeutige `id`.

2. **Einnahmeerfassung**  
   Öffnet eine Person zur vorgesehenen Zeit ein Fach oder wird ein Sensor ausgelöst, **zeichnet das System die Einnahme automatisch auf**. Der Eintrag wird in `medication_log` gespeichert – mit Zeitstempel (`timestamp`), Fachnummer (`fach_nr`) und einem Verweis auf den zugehörigen Plan über `plan_id`.

3. **Auswertung & Statuszuweisung**  
   Anhand der `plan_id` wird die Einnahme dem entsprechenden geplanten Eintrag zugeordnet. Daraus wird automatisch der Einnahmestatus berechnet:
   - **Grün** = pünktlich eingenommen (`korrekt = 1`)
   - **Gelb** = verspätet oder früh, aber erkannt (`korrekt = 0`)
   - **Rot** = keine Einnahme (kein Log-Eintrag vorhanden)


Beispiel:  
- In `medication_schedule` steht:  
  `id = 1170`, `fach_nr = 1`, `wochentag = "MO"`, `uhrzeit = 02:15`, `med_name = "Aspirin"`  
- Dann erfolgt am Montag eine Einnahme, gespeichert in `medication_log`:  
  `timestamp = 2025-06-16 02:32`, `korrekt = 1`, `plan_id = 1170`

🔁 Dieses Tabellen Struktur ermöglicht mir:
- **Vergleich geplant vs. real** (z. B. „Wurde zu spät eingenommen?“)
- **Zählung von Einnahmen pro Woche / Monat**
- **Darstellung in Matrix- und Balkendiagrammen**

Diese strukturierte Trennung zwischen Planung (`schedule`) und tatsächlicher Ausführung (`log`) war entscheidend für eine transparente, nachvollziehbare und automatisiert auswertbare Lösung im Pillo-System.

---

## 🧩Steckschema

Das Steckschema für das Gadget sollte erst nur über ein halbes Breadboard laufen, jedoch war der Platz zu begrenzt, wodurch ich auf ein ganzes Breadboard umsteigen musste.
Ich nutzte anschliessend das zweite Breadboard als Erhöhung für meine Reed-Magnet Schalter, da die  ursprüngliche Idee mit dem Löten der Kabel sich, schwieriger anstellte als gedacht (siehe [Innenleben des Gadget](#innenleben-des-gadget)). Wenn man sich die Entfernung der Sensoren genau ansieht fragt man sich, warum sind diese so weit von der Fachöffnung entfernt? Ursprünglich waren diese direkt am Fach Entrypoint, aber da die Fächer jeweils nur eine Länge von 3cm hatten, hat das Gadget die ganze Zeit die Wand gemessen, obwohl die Definierung im mc.ino so gesetzt war das der Distanzsensor, jeweils nur bis knapp 3cm messen sollte.
Daraufhin musste ich mit einem debug feststellen, dass die effektive Distanzmessung der Sensoren verschoben ist, bzw. die gemessenen Distanzwerte um etwa 3cm verschoben waren und jeweils der 0 Punkt der Messung erst bei ca 3cm war. Somit habe ich die Distanzsensoren wie auf dem Steckschema platziert um die Distanz der Messung auszugleichen. (Dieser Bug der Sensoren war bekannt, auch von Jan Fiess schon festgestellt). Jedoch nicht weiter schlimm da die Distanzwerte keine effektive Relevanz für dieses Projekt haben.

![Steckplan] <img width="690" alt="Breadboard_PILLO" src="https://github.com/user-attachments/assets/a648950c-0470-4d46-a3ad-abfe32ca8ebb" />
*Das Breadboard-Schema zeigt den Aufbau mit ESP32 und Sensoren.*

---

## 🎥Video-Dokumentation

Hier findest du noch eine kleine Präsentation zu meinem Pillo Gadget.
-> [https://youtu.be/Q0Ffs1uNg1s](https://youtu.be/PgARDj2r46M)

---

## ⚙️Umsetzungsprozess

Der Umsetzungsprozess hat sich anfangs schwierig gestaltet. In einem ersten Schritt habe ich folgenden Bauplan für mein Gadget vorgesehen. 

<img width="1053" alt="Bildschirmfoto 2025-06-15 um 23 51 21" src="https://github.com/user-attachments/assets/2c431c8c-7d6d-43a3-a504-63704058c96a" />

Ich musste, aber schnell feststellen, das ich nicht wirklich begabt bin im Löten deswegen entschied ich mich für eine andere Lösung. Indem ich die Breadboards anreihte wie man dies auf dem Bild unterhalb erkennen kann. Für das oben referenzierte Design hätte ich keine Breadboards verwenden dürfen, da das Spacing nicht ausgereicht hätte. 

Ich musste einen Weg finden, wie ich die Umsetzung möglichst simpel und so detailgetreu wie möglich mit meinem vorhandenen Steckplan umsetzen konnte.
Folgende Sachen musste ich hierbei beachten:

- Es durfte nicht allzu komplex sein, da meine 3D Modellierung Skills noch nicht 100% perfekt sind
- Beide Breadboards mussten hineinpassen
- Das Fach muss den Magnetsensor triggern, damit das Gerät funktioniert

Mein gefundener Lösungsansatz sah wie folgt aus im Querschnitt

![IMG_0273](https://github.com/user-attachments/assets/f0d889fd-2fb6-4e72-9583-3f2498481a5a)
![IMG_0274](https://github.com/user-attachments/assets/6084aaf5-380d-418f-87d8-430ba33459b5)

In einem nächsten Schritt machte ich mich an die Umsetzung in 3D, hierfür benutzte ich Hauptsächlich Blender. Ich nutzte jeweils die Vermassungen der Breadboards, als Referenz im Innenleben um detailgetreu zu modellieren. Hier ein paar Ausschnitte des 3D Modells.

<img width="1203" alt="Bildschirmfoto 2025-06-15 um 23 58 09" src="https://github.com/user-attachments/assets/1db89306-fb1e-42b5-83c1-1187d5b2c4cb" /><img width="1214" alt="Bildschirmfoto 2025-06-15 um 23 57 56" src="https://github.com/user-attachments/assets/4ddffaff-3a52-48de-9d70-dc1b3a1e13e8" />
<img width="1216" alt="Bildschirmfoto 2025-06-15 um 23 58 03" src="https://github.com/user-attachments/assets/a46266b0-9b8a-4c4d-97de-65691c0d5f4c" />

In einem nächsten Schritt ging es an dern Druckprozess. ich habe zuhause einen 3D Drucker (Bambulab X1C), welchen ich für die Umsetzung verwendet habe.
Da ich jedoch bis anhin selten Projekte realisierte mit Überhang, musste ich meine Entwürfe einige Male über Board werfen, da diese zu viel Zeit und Material in Anspruch nahmen.

Im Endeffekt entschied ich mich für folgenden Prozess:

![Screenshot 2025-06-16 071450](https://github.com/user-attachments/assets/31550310-5710-4e75-9d0c-47f71a8ab628)

Ich entschied mich mit normalem Filament als Support zu arbeiten, welches ich bei einem nächsten mal nicht mehr machen würde, weil die Entnahme des Supprt Filament aus der Struktur im Bild unterhalb min. 1h oder mehr in Anspruch genommen hat.

![Screenshot 2025-06-16 071435](https://github.com/user-attachments/assets/70f38305-cd59-4871-8c51-07f58cb666cf)

Am Schluss musste ich dann nur noch meine Komponenten in das Gehäuse einfügen und unterhalb mit einem gedruckten Deckel verschliessen.

<img width="1344" alt="Bildschirmfoto 2025-06-16 um 16 24 50" src="https://github.com/user-attachments/assets/bcc47ec4-eafb-489a-b6f2-0160c1b68694" />
<img width="616" alt="Bildschirmfoto 2025-06-15 um 23 54 41" src="https://github.com/user-attachments/assets/083d2f01-585e-4760-b326-09b7e69bc710" />

### Innenleben des Gadget
![Innenleben des Gadget](https://github.com/user-attachments/assets/7dd518fc-1761-44ce-9278-e2bcd21f3ced)

---

### 🔧 Entwicklungsprozess

Der Entwicklungsprozess für die Pillo-Box war geprägt durch viele iterative Verbesserungen und parallele Arbeitsphasen. Ziel war es, ein funktionierendes IoT-System zu bauen, das sowohl Hardware- als auch Webtechnologien vereint und dabei zuverlässig im Alltag funktioniert. Die Umsetzung fand auf verschiedenen Ebenen statt:

---

#### 1. 🧰 Hardware & Elektronik

- Auswahl eines geeigneten Microcontrollers: Der **ESP32-C6** wurde wegen seiner WiFi-Funktionalität und GPIO-Vielfalt gewählt.
- Zwei Sensorarten wurden pro Fach verwendet:
  - **VL6180X-Distanzsensoren**, um zu erkennen, ob ein Objekt (z. B. Medikament) entfernt wurde
  - **GP541-B Magnetsensoren**, um festzustellen, ob das Fach geöffnet wurde
- Die Sensoren wurden mit einem Breadboard und zusätzlichen Halterungen korrekt positioniert. Besonders bei den Distanzsensoren war Feintuning notwendig, da die Messung erst ab ca. 3 cm zuverlässig war.

---

#### 2. 💻 Firmware (ESP32-Programmierung)

- Die Firmware wurde in C++ mittels **Arduino IDE** geschrieben.
- Die Sensorwerte wurden laufend abgefragt und per HTTP-Request an den Webserver übertragen (mittels `WiFiClient`).
- Der ESP32 sendet keine Rohdaten, sondern löst Ereignisse aus (z. B. „Fach geöffnet“) – die **Auswertung erfolgt serverseitig**.
- Debugging erfolgte über den Serial Monitor und über visuelle OLED-Ausgaben zur Statusanzeige.

---

#### 3. 🗄️ Server & Datenbank (Backend-Logik)

- Der Webserver verarbeitet eingehende Daten via **PHP-Skripte**:
  - `submit_medikament.php` → speichert Einnahmen
  - `unload.php` → gibt alle nötigen Visualisierungsdaten als JSON aus
- Die **MySQL-Datenbank** besteht aus zwei Haupttabellen:
  - `medication_schedule` (Plan)
  - `medication_log` (tatsächliche Einnahmen)
- Die Serverlogik prüft automatisch, ob eine Einnahme zur korrekten Zeit erfolgte, und klassifiziert sie entsprechend (**grün / gelb / rot**).

---

#### 4. 🌐 Frontend (Visualisierung & Benutzeroberfläche)

- Die Weboberfläche wurde mit **HTML, CSS und JavaScript** umgesetzt.
- Diagramme (Matrix & Barcharts) wurden mit **Chart.js** erstellt.
- Die Website bietet dem Benutzer:
  - eine Übersicht über den aktuellen Einnahmestatus
  - eine grafische Auswertung (Woche / Monat / Jahr)
  - die Möglichkeit, den Plan visuell zu überprüfen
- Die Gestaltung wurde gezielt auf ältere Nutzer:innen ausgerichtet (klare Farben, grosse Buttons, einfache Navigation).

---

#### 5. 🔄 Testen & Integration

- Es wurde modular entwickelt: Zuerst ein funktionierender ESP32-Prototyp, danach die serverseitige Logik, zuletzt die Webvisualisierung.
- Regelmässige Integrationstests sicherten die Kompatibilität zwischen Hardware, Server und Interface.
- Mehrere Fehlschläge (siehe [Fehlschläge und Umplanung](#fehlschläge-und-umplanung)) führten zu entscheidenden Verbesserungen im Hardwaredesign und Softwareaufbau.

---

Insgesamt entstand ein robustes System, das auch mit einfachen Mitteln (Breadboard, Low-Cost-Sensoren, Webtechnologien) einen realen Alltagsnutzen bietet. Die Entwicklung war lehrreich, fordernd und praxisnah – mit vielen Erkenntnissen in Elektronik, Webentwicklung und systemübergreifendem Denken.

---

### 🚫 Verworfene Lösungsansätze

Während der Entwicklung wurden verschiedene Ideen und Ansätze getestet, jedoch teilweise wieder verworfen, da sie in der praktischen Umsetzung zu ungenau, fehleranfällig oder zu komplex waren:

- **Nur mit Magnetsensoren arbeiten**  
  Die erste Idee war, allein über Magnetsensoren zu erfassen, ob ein Fach geöffnet wurde. Dies stellte sich als zu fehleranfällig heraus – bereits ein kurzes oder irrtümliches Öffnen hätte als Einnahme gezählt, ohne zu prüfen, ob tatsächlich ein Medikament entnommen wurde.

---

### 🔄Fehlschläge und Umplanung

Im Verlauf der Umsetzung kam es zu mehreren technischen Problemen und notwendigen Richtungsänderungen:

| Problem                                 | Erkenntnis                                                | Lösung                                                                 |
|-----------------------------------------|------------------------------------------------------------|------------------------------------------------------------------------|
| Distanzsensor misst ungenau             | Die gemessene Distanz beginnt erst ab ~3 cm                | Sensoren im Gehäuse 3 cm nach hinten versetzt                          |
| Mehrfache Einnahmen wurden registriert  | Öffnen des Fachs löste mehrere Logs aus                    | Einführung eines Schutzfensters zur Unterdrückung       |
| OLED schnitt Text ab                    | Medikament + Uhrzeit passten nicht auf eine Zeile          | Darstellung auf zwei Zeilen mit klarer Trennung                        |
| 3D-Druckstruktur zu aufwändig           | Überhänge verursachten benötigten übermässig viel Material und Druckzeit              | Modellierung überarbeitet, um Support zu reduzieren                    |
| Steckschema zu eng kalkuliert           | Ein halbes Breadboard reichte nicht aus                    | Zweites Breadboard integriert und als Halterung verwendet              |

---

### 🧱Planung & Aufgabenverteilung

Da es sich um ein Einzelprojekt handelte, lagen alle Aufgaben bei mir – von der Konzeption über die Verkabelung, Programmierung, Webentwicklung bis hin zur finalen Dokumentation. Gearbeitet wurde parallel an den Modulen, mit regelmässigen Tests auf funktionierender Hardware.

Ein Projektplan wurde grob in Phasen eingeteilt:
- Prototyp Hardware (Sensorik)
- parallele Webentwicklung (Frontend)
- Integration & Datenbankstruktur
- Auswertung, Optimierung
- Doku + Video

---

### 🧠Lerneffekt

Dieses Projekt brachte viele neue Herausforderungen, durch die ich wertvolle Erfahrungen gesammelt habe:

- **IoT-Architektur mit ESP32**, HTTP-Kommunikation und REST-API-Anbindung
- **Sensorfusion** (zwei Sensoren logisch verknüpfen)
- **MySQL-Abfragen für Zeitvergleiche, Statistik und Filterung**
- **PHP-Serverlogik mit Prepared Statements und Fehlerbehandlung**
- **Optimierung für Performance (z. B. Datenbankabfragen)**
- Umgang mit realen Hardwareproblemen (z. B. Sensorfehlfunktionen, ungenaue Trigger)

---

### 🐞Known Bugs

Trotz intensiver Tests gibt es aktuell noch einige bekannte Einschränkungen oder kleinere Fehler im System:

- **Lange Medikamentennamen**  
  Auf dem OLED-Display kann es bei langen Namen zu Darstellungsfehlern oder abgeschnittenem Text kommen.

 - **Jahreschart Auswertung**  
  Die Berechnung für das Jahresbarchart wird in der Relation her richtig angezeigt, aber hat kleine %-Abweichungen in der Rechnung.


---

### 🧰Hilfsmittel & Tools

| Tool | Zweck |
|------|-------|
| **ChatGPT** | Planung, Debugging, Code-Optimierung, README-Struktur |
| **Fritzing** | Erstellung des Steckschemas |
| **Figma** | Erstellung des Systemflussdiagramms |
| **Arduino IDE** | Microcontroller-Programmierung |
| **PHP + MySQL** | Serverseitige Skripte und Datenhaltung |
| **Markdown / GitHub** | Projektdokumentation, ReadMe, Versionierung |
| **Browser DevTools** | Debugging von HTML, CSS, JS |

---

## 📚Fazit


---

