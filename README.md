# 💊 Pillo-Box: Intelligenter Medikamentenspender mit ESP32-C6

> Ein interaktives IoT-System zur Verwaltung, Erinnerung und Protokollierung der Medikamenteneinnahme.

---

## 📦 Inhaltsverzeichnis

- [📝 Projektbeschreibung](#projektbeschreibung)
- [🔁 Reproduzierbarkeit](#reproduzierbarkeit)
- [🔄 Flussdiagramm](#flussdiagramm)
- [🔧 Komponentenplan](#komponentenplan)
- [🧩 Steckschema](#steckschema)
- [⚙️ Umsetzungsprozess](#umsetzungsprozess)
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

## 🔁Reproduzierbarkeit

Diese Schritt-für-Schritt-Anleitung hilft dir, das Projekt vollständig nachzubauen – vom Hardwareaufbau bis zur Webvisualisierung inklusive lokaler Datenverarbeitung.

---

### 1. 🧰 Hardware beschaffen

Für den Bau der Pillo-Box wird folgende Hardware benötigt:

- **1× ESP32-C6** (mit WiFi-Funktion)
- **2× VL6180X Distanzsensoren** (für Objektentnahme-Erkennung)
- **2× GP541-B Magnetsensoren** (zur Fachöffnungserkennung)
- **1× OLED-Display (I2C)** (zur Anzeige der nächsten Einnahme)
- **1× ganzes Breadboard** (für Hauptverkabelung)
- **1× kleineres Breadboard oder Erweiterung** (für Sensorhalterungen)
- Diverse **Jumper-Kabel**, ggf. **3D-gedrucktes Gehäuse**

👉 Hinweis: Die VL6180X-Sensoren müssen ca. **3 cm von der Fachöffnung entfernt** montiert werden, um zuverlässige Werte zu liefern (siehe Abschnitt [🧩 Steckschema](#steckschema)).

---

### 2. 🔌 Schaltplan und Aufbau

- Baue den Schaltkreis gemäss dem Steckschema auf. Achte dabei besonders auf:
  - **I2C-Verbindung** (gemeinsames SDA/SCL für Display und Sensoren)
  - **XSHUT-Pins**, um individuelle I2C-Adressen für beide Distanzsensoren zu setzen
  - **GPIO-Pins** für die Reedkontakte (Öffnungserkennung)
- Die Sensoren und das OLED-Display werden direkt mit dem ESP32 verbunden.
- Das Gerät benötigt Strom über USB oder Netzteil.

→ Details findest du im Abschnitt [🧩 Steckschema](#steckschema)

---

### 3. ⚙️ Firmware auf den ESP32 laden

- Öffne `mc.ino` in der **Arduino IDE**
- Trage deine WLAN-Zugangsdaten im Sketch ein (`ssid`, `pass`)
- Stelle sicher, dass folgende Bibliotheken installiert sind:
  - `Adafruit_VL6180X`
  - `Adafruit_SSD1306`
  - `Adafruit_GFX`
  - `RTClib`
  - `WiFi` (ESP32-Version)
  - `Arduino_JSON`
- Flashe das Skript via USB-C auf den ESP32-C6

Nach dem Start verbindet sich der ESP32 mit dem WLAN, zeigt die **nächste geplante Einnahme** auf dem OLED an und überwacht kontinuierlich die Sensoren. Bei einer erkannten Einnahme sendet er die Daten an den Server.

---

### 4. 🌐 Server aufsetzen 

Setze folgende Komponenten auf:

- **Webserver mit PHP**
- **MySQL/MariaDB**

#### 📦 Datenbank einrichten

1. Erstelle eine neue Datenbank, z. B. `pillo_db`
2. Importiere die Tabellenstruktur für:
   - `medication_schedule` (Pläne)
   - `medication_log` (Ereignisse)

mehr Informationen zum aufsetzen der DB findest du hier -> [🔧 Komponentenplan](#komponentenplan)

#### 📂 PHP-Dateien ins Serververzeichnis legen

- `config.php` – zentrale DB-Verbindung
- `load.php` – nimmt POST-Daten vom ESP32 entgegen → `INSERT INTO medication_log`
- `get_next.php` – liefert dem ESP32 die nächste geplante Einnahme → `SELECT FROM medication_schedule`
- `submit_medikament.php` – verarbeitet neue Pläne aus dem Webinterface → `INSERT INTO medication_schedule`
- `unload.php` – stellt aggregierte Statistikdaten für die Website bereit

> 🔐 Der ESP32 muss via IP Zugriff auf den Server haben (HTTP-Port 80, WLAN), z. B. `http://192.165.1.535/pillo/load.php`

---

### 5. 🖥️ Webinterface starten

- Kopiere folgende Dateien ins gleiche Serververzeichnis:
  - `index.html` – Hauptansicht der Webanwendung
  - `style.css` – Styling
  - `loading3.js` – JavaScript-Logik (z. B. Daten laden & Diagramme rendern)
- Rufe die Website im Browser auf, z. B.:  
  `http://192.168.1.50/pillo/index.html`

#### Die Website ermöglicht:

- **Visualisierung** deiner Einnahmehistorie (Matrix, Wochen- & Monatscharts)
- **Anzeige der aktuellen Woche und nächsten Einnahme**
- **Manuelle Planänderung über Pop-up-Felder**

---

### 6. ✅ Test & Debugging

Folgende Tests helfen bei der Inbetriebnahme:

- Objekt entfernen → wird die Messung korrekt registriert und im Frontend angezeigt?
- OLED zeigt die geplante Einnahme?
- `unload.php` liefert valides JSON im Browser?
- Diagramme und Statusfarben erscheinen korrekt?
- Keine doppelten Logs bei erneutem Öffnen?

---

### 🧪 Systemstatus: Lokal & unabhängig

Das System arbeitet vollständig **lokal**, solange sich der Server, ESP32 und Browser im selben WLAN befinden. Es werden **keine externen Dienste oder Cloud-Plattformen** benötigt. Somit bleibt Pillo datenschutzfreundlich, unabhängig und portabel – ideal für den Heimgebrauch oder medizinische Prototypentests.


---

## 🔄Flussdiagramm
In einem ersten Schritt nach der Ideenfindung habe ich mit der Erstellung des Flussdiagramms in Figma begonnen. Dieser Prozess gestaltete sich schwieriger als anfangs gedacht, insbesondere da ich bisher noch kein Projekt mit einer solchen Komplexität umgesetzt hatte. Im Nachhinein musste ich das Flussdiagramm nochmals grundlegend anpassen. Der Grund dafür war, dass im ursprünglichen Entwurf gewisse Stränge fehlten oder die Umsetzung einzelner Teile zu komplex oder gar nicht realisierbar war, wie ich es mir ursprünglich vorgestellt hatte.

Dennoch war die Erstellung des Flussdiagramms eine nützliche Übung, da ich dadurch einen groben Überblick darüber gewinnen konnte, wie mein Projekt funktionieren sollte.

Hier im in Anschluss mein finales Flussdiagramm:
![Screenflow](https://github.com/user-attachments/assets/68f54622-40ea-4e81-a5e2-a8ce419c0432)
*Das Diagramm zeigt die Kommunikationswege zwischen ESP32, Website und Server.*

---

## 🔧Komponentenplan
Einen Komponentenplan zu erstellen stellte sich als weniger intuitiv heraus, als ich es mir anfangs erhofft hatte. Das lag insbesondere daran, dass ich für dieses Projekt mit mehreren PHP-Dateien und Datenbanktabellen gearbeitet habe, was die Verarbeitung und die schematische Darstellung erheblich verkomplizt hat.

![Komponentenplan](https://github.com/user-attachments/assets/e54be341-70cc-4e0a-91e9-0666d4949830)
*Verbindungen und Protokolle zwischen haptischen Komponenten und digitalen Modulen.*

Zu Beginn war mir nicht bewusst, wie viel komplexer sich das Projekt gestaltet, wenn mehrere Tabellen eingesetzt werden, die Daten aus unterschiedlichen Quellen beziehen. Da ich die Datenstruktur sowie das Zusammenspiel und die Auswertung innerhalb der Datenbank vollständig selbst umsetzen musste, waren zahlreiche Trial-and-Error-Versuche notwendig, bis alle Komponenten zuverlässig miteinander kommunizierten.

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
Ich nutzte anschliessend das zweite Breadboard als Erhöhung für meine Reed-Magnet-Schalter, da die ursprüngliche Idee mit dem Löten der Kabel sich schwieriger anstellte als gedacht (siehe [Innenleben des Gadget](#innenleben-des-gadget)). Wenn man sich die Entfernung der Sensoren genau ansieht, fragt man sich, warum diese so weit von der Fachöffnung entfernt sind. Ursprünglich waren diese direkt am Fach-Entrypoint, aber da die Fächer jeweils nur eine Länge von 3 cm hatten, hat das Gadget die ganze Zeit die Wand gemessen, obwohl die Definierung im mc.ino so gesetzt war, dass der Distanzsensor jeweils nur bis knapp 3 cm messen sollte.
Daraufhin musste ich mit einem Debug feststellen, dass die effektive Distanzmessung der Sensoren verschoben ist, bzw. die gemessenen Distanzwerte um etwa 3 cm verschoben waren und jeweils der Nullpunkt der Messung erst bei ca. 3 cm war. Somit habe ich die Distanzsensoren wie auf dem Steckschema platziert, um die Distanz der Messung auszugleichen. (Dieser Bug der Sensoren war bekannt, auch von Jan Fiess schon festgestellt.) Jedoch nicht weiter schlimm, da die Distanzwerte keine effektive Relevanz für dieses Projekt haben.

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

Ich musste aber schnell feststellen, dass ich nicht wirklich begabt bin im Löten, deswegen entschied ich mich für eine andere Lösung, indem ich die Breadboards anreihte, wie man dies auf dem Bild unterhalb erkennen kann. Für das oben referenzierte Design hätte ich keine Breadboards verwenden dürfen, da das Spacing nicht ausgereicht hätte.

Ich musste einen Weg finden, wie ich die Umsetzung möglichst simpel und so detailgetreu wie möglich mit meinem vorhandenen Steckplan umsetzen konnte.
Folgende Sachen musste ich hierbei beachten:

- Es durfte nicht allzu komplex sein, da meine 3D-Modellierungsskills noch nicht 100% perfekt sind
- Beide Breadboards mussten hineinpassen
- Das Fach muss den Magnetsensor triggern, damit das Gerät funktioniert

Mein gefundener Lösungsansatz sah wie folgt aus im Querschnitt

![IMG_0273](https://github.com/user-attachments/assets/f0d889fd-2fb6-4e72-9583-3f2498481a5a)
![IMG_0274](https://github.com/user-attachments/assets/6084aaf5-380d-418f-87d8-430ba33459b5)

In einem nächsten Schritt machte ich mich an die Umsetzung in 3D, hierfür benutzte ich Hauptsächlich Blender. Ich nutzte jeweils die Vermassungen der Breadboards, als Referenz im Innenleben um detailgetreu zu modellieren. Hier ein paar Ausschnitte des 3D Modell Gestaltungsprozess:

<img width="1203" alt="Bildschirmfoto 2025-06-15 um 23 58 09" src="https://github.com/user-attachments/assets/1db89306-fb1e-42b5-83c1-1187d5b2c4cb" /><img width="1214" alt="Bildschirmfoto 2025-06-15 um 23 57 56" src="https://github.com/user-attachments/assets/4ddffaff-3a52-48de-9d70-dc1b3a1e13e8" />
<img width="1216" alt="Bildschirmfoto 2025-06-15 um 23 58 03" src="https://github.com/user-attachments/assets/a46266b0-9b8a-4c4d-97de-65691c0d5f4c" />

In einem nächsten Schritt ging es an den Druckprozess. ich habe zuhause einen 3D Drucker (Bambulab X1C), welchen ich für die Umsetzung verwendet habe.
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

Da es sich um ein Einzelprojekt handelte, lagen alle Aufgaben bei mir (Einzelperson) – von der Konzeption über die Verkabelung, Programmierung, Webentwicklung bis hin zur finalen Dokumentation. Gearbeitet wurde parallel an den Modulen, mit regelmässigen Tests auf funktionierender Hardware.

Ein Projektplan wurde grob in Phasen eingeteilt:
- Prototyp Hardware (Sensorik)
- parallele Webentwicklung (Frontend)
- Integration & Datenbankstruktur
- Auswertung, Optimierung
- Doku + Video

---

### 🧠Lerneffekt

Durch die Arbeit an diesem Projekt habe ich in mehreren Bereichen viel dazu gelernt und mein Verständnis für komplexe Systemarchitekturen deutlich erweitert.

Ich konnte mein Wissen im Bereich **IoT-Entwicklung** vertiefen, insbesondere was die Programmierung und Anbindung eines ESP32-C6 betrifft. Ich habe gelernt, wie wichtig es ist, Sensoren zuverlässig auszulesen, korrekt zu kalibrieren und Hardware-Komponenten so zu positionieren, dass sie im Alltag zuverlässig funktionieren.

Im Bereich **Webentwicklung** habe ich sowohl auf der Client- als auch auf der Serverseite grosse Fortschritte gemacht. Besonders im Zusammenspiel zwischen PHP, Datenbank und Frontend-Logik wurde mir klar, wie zentral eine saubere Datenstruktur, konsistente Formate und effiziente Abfragen sind. Ich musste lernen, wie man unterschiedliche Datenquellen und Zeitformate so kombiniert, dass sie synchronisiert und logisch auswertbar bleiben.

Ein grosser Lerneffekt lag auch in der **Datenvisualisierung**. Ich habe mich intensiv mit Tools wie Chart.js auseinandergesetzt und gelernt, wie wichtig es ist, Daten nicht nur korrekt, sondern auch verständlich und ansprechend darzustellen – gerade für eine Zielgruppe, die auf Übersichtlichkeit angewiesen ist.

Zusätzlich habe ich gelernt, mit **Fehlfunktionen in der Hardwareumsetzung** umzugehen, Lösungen kreativ zu suchen und bei unerwarteten Problemen pragmatisch umzudenken. Das betraf sowohl die Sensorlogik als auch die Modellierung für den 3D-Druck.

Abschliessend kann ich sagen, dass Pillo nicht nur ein funktionierendes technisches System ist, sondern auch ein Lernfeld war, das mir geholfen hat, systemübergreifend zu denken, Problemstellungen zu durchdringen und ein Projekt in seiner vollen Komplexität eigenständig umzusetzen.


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

Insgesamt bin ich sehr zufrieden mit dem Ergebnis dieser Arbeit.  
Es war mit Abstand das umfangreichste Projekt, das ich bisher im Rahmen meines Studiums an der FH Graubünden umsetzen durfte. Die Arbeit umfasste sämtliche Bereiche von der Elektronik über das physische Gadget bis hin zur vollständigen Front-End- und Backend-Entwicklung.

Dass die Umsetzung anspruchsvoll werden würde, war mir von Anfang an bewusst. Wie viele Tage und Nächte mich dieses Projekt am Ende tatsächlich kosten würde, konnte ich jedoch nicht ahnen. Besonders herausfordernd war die Komplexität der Logik, da es nicht nur um die einfache Visualisierung von Sensordaten ging, sondern um den Vergleich mehrerer Tabellen, den zeitlichen Abgleich und die korrekte Darstellung im Frontend.

Was zunächst simpel wirkte, etwa das Vergleichen einer geplanten Uhrzeit mit einem tatsächlichen Einnahmezeitpunkt, entpuppte sich als vielschichtige technische Herausforderung. Daten mussten zeitlich und logisch korrekt abgeglichen werden. Unterschiedliche Formate wie MySQL-Zeitstempel und JavaScript-Zeitwerte mussten einheitlich funktionieren. Visualisierungen sollten nicht nur korrekt, sondern auch verständlich und übersichtlich gestaltet sein.

Diese Erfahrungen haben mir gezeigt, wie viel Detailarbeit und Präzision selbst in scheinbar einfachen Anwendungsfällen steckt, insbesondere wenn Hardware, Backend und Frontend nahtlos zusammenspielen sollen.

Trotz aller Hürden und Rückschläge blicke ich heute mit Stolz auf ein funktionierendes System zurück. Das Projekt hat meine Kompetenzen in den Bereichen IoT, Datenverarbeitung, Webentwicklung und Datenbanklogik stark erweitert. Ich bin gespannt, ob und wie sich die Entwicklung von *Pillo* in Zukunft fortsetzen wird. Erste Ideen für eine Weiterentwicklung sind bereits vorhanden.



---

