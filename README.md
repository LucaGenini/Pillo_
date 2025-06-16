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

## 🔁Reproduzierbarkeit

Folge dieser Schritt-für-Schritt-Anleitung**, um das Projekt nachzubauen:

1. **Hardware beschaffen**  
   - ESP32-C6  
   - VL6180X Distanzsensoren ×2  
   - GP541-B Magnetsensoren ×2  
   - OLED-Display  
   - Steckbrett, Kabel, Widerstände

2. **Schaltplan gemäß Steckschema aufbauen**  
   → siehe Abschnitt [Steckschema](#steckschema)

3. **Firmware `mc.ino` auf ESP32 flashen**  
   - via Arduino IDE oder PlatformIO  
   - WLAN-Zugangsdaten eintragen

4. **Webserver lokal oder auf Raspberry Pi einrichten**  
   - PHP & MySQL installieren  
   - `load.php`, `get_next.php`, `submit_medikament.php`, `unload.php`, `config.php` hochladen  
   - Datenbanktabellen importieren

5. **Website aufrufen (`index.html`)**  
   - Im gleichen Server-Verzeichnis hosten  
   - Medikamentenpläne erstellen und testen

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
Ich nutzte anschliessend das zweite Breadboard als Erhöhung für meine Reed-Magnet Schalter, da die  ursprüngliche Idee mit dem Löten der Kabel sich, schwieriger anstellte als gedacht. (siehe [Innenleben des Gadget](#innenleben-des-gadget))


![Steckplan] <img width="690" alt="Breadboard_PILLO" src="https://github.com/user-attachments/assets/a648950c-0470-4d46-a3ad-abfe32ca8ebb" />

*Das Breadboard-Schema zeigt den Aufbau mit ESP32 und Sensoren.*

---

## 🖼️Screenshots / Medien

![OLED Screenshot](./assets/oled_display.jpg)
![IMG_5871](https://github.com/user-attachments/assets/8f0d655c-69c2-4746-b6ff-ebb463790d7d)



---

## 🎥Video-Dokumentation

Hier findest du noch eine kleine Präsentation zu meinem Pillo Gadget.
-> https://youtu.be/Q0Ffs1uNg1s

---

## ⚙️Umsetzungsprozess

Der Umsetzungsprozess hat sich anfangs schwierig gestaltet. In einem ersten Schritt habe ich folgenden Bauplan für mein Gadget vorgesehen. 

<img width="1053" alt="Bildschirmfoto 2025-06-15 um 23 51 21" src="https://github.com/user-attachments/assets/2c431c8c-7d6d-43a3-a504-63704058c96a" />

Ich musste, aber schnell feststellen, das ich nicht wirklich begabt bin im Löten deswegen entschied ich mich für eine andere Lösung. Indem ich die Breadboards anreiht wie man dies auf dem Bild unterhalb erkennen kann.


### Innenleben des Gadget
![Innenleben des Gadget](https://github.com/user-attachments/assets/7dd518fc-1761-44ce-9278-e2bcd21f3ced)

<img width="616" alt="Bildschirmfoto 2025-06-15 um 23 54 41" src="https://github.com/user-attachments/assets/083d2f01-585e-4760-b326-09b7e69bc710" />

Wenn man das Medikamentenfach öffnet. Entfernt sich der Magnet vom Reed-Schalter, welcher den Distanzsensor freigibt.



### 🔧Entwicklungsprozess

Der Entwicklungsprozess von Pillo erfolgte iterativ – von der Idee über den Hardware-Prototyp bis hin zur vollständigen Integration von Web-Interface, Server und Datenbank. Nach einer ersten Ideensammlung folgte die Modulplanung: Welche Sensoren werden benötigt, welche Logik läuft auf dem ESP32, wie wird die Kommunikation mit dem Server umgesetzt? Parallel dazu wurde die Website als Benutzeroberfläche konzipiert und gestaltet.

Die Entwicklung erfolgte in vier zentralen Bereichen:
1. **Elektronik / Microcontroller (ESP32-C6 + Sensoren)**
2. **Firmware-Programmierung (Arduino mit WLAN und Sensorlogik)**
3. **Backend (PHP, MySQL, API-Skripte)**
4. **Frontend (HTML, CSS, JavaScript für Visualisierung)**

---

### 🗺️Inspirationen & Ziele

Die Motivation entstand aus einer persönlichen Erfahrung mit unregelmässiger Medikamenteneinnahme sowie Beobachtungen im familiären Umfeld. Viele Menschen – insbesondere ältere – nehmen Medikamente regelmässig ein, haben jedoch kein System zur Überprüfung oder Erinnerung. Genau dort soll Pillo ansetzen: als einfache, visuelle Hilfe zur Selbsterinnerung und zur Protokollierung der Einnahmen.

---

### 🎨Designentscheidungen

- **Benutzeroberfläche reduziert & klar strukturiert**, insbesondere auf Bedienbarkeit durch ältere Nutzerinnen und Nutzer ausgelegt (grosse Schriften, klare Farbcodes, einfache Navigation).
- **Zwei Sensorarten pro Fach**:  
  - *VL6180X Distanzsensoren* für Objektentnahme  
  - *GP541-B Magnetsensoren* zur Erkennung, ob das Fach geöffnet wurde
- **ESP32 als Client**: übernimmt keine Logikentscheidung, sondern sendet Zustände an den Server, welcher die Prüfung und Speicherung übernimmt.
- Die Visualisierung auf der Website zeigt klar: Wurde das Medikament eingenommen? Wurde es vergessen? Mit welchen Abweichungen?

Für das Gadget an sich wollte ich etwas schlichtes bei dem schnell erkennbar ist, welches Fach man entnehmen muss. Mit dem Pillo Logo zusammen bin ich echt zufrieden geworden. :)

<img width="1203" alt="Bildschirmfoto 2025-06-15 um 23 58 09" src="https://github.com/user-attachments/assets/1db89306-fb1e-42b5-83c1-1187d5b2c4cb" /><img width="1214" alt="Bildschirmfoto 2025-06-15 um 23 57 56" src="https://github.com/user-attachments/assets/4ddffaff-3a52-48de-9d70-dc1b3a1e13e8" />
<img width="1216" alt="Bildschirmfoto 2025-06-15 um 23 58 03" src="https://github.com/user-attachments/assets/a46266b0-9b8a-4c4d-97de-65691c0d5f4c" />



---

### 🚫Verworfene Lösungsansätze

- **Nur mit Magnetsensoren zu arbeiten**, ohne Distanzsensor: stellte sich als zu fehleranfällig heraus – Bewegungen vor dem Sensor lösten falsche Einnahmen aus.
- **Backendlose Lösung** rein auf dem ESP32 und OLED: schränkte Erweiterbarkeit massiv ein, keine Statistikauswertung möglich.
- **OLED-Einzeilige Darstellung**: führte zu abgeschnittenen Informationen – Umstieg auf Zweizeilenanzeige (Zeit + Medikament).

---

### 🔄Fehlschläge und Umplanung

| Problem | Erkenntnis | Lösung |
|--------|------------|--------|
| Distanzsensor löste zu früh/zufällig aus | Bewegungen oder Lichtverhältnisse beeinflussten die Messung | Einsatz eines **Magnetsensors zusätzlich**, nur bei *beiden Triggern gleichzeitig* wird eine Einnahme gewertet |
| Einnahmen wurden mehrfach registriert | Öffnen des Fachs löste mehrmals aus | Einführung eines Zeitfensters (±10 Minuten) in `load.php`, um doppelte Logs zu vermeiden |
| OLED schnitt Text ab | Medikamentenname + Uhrzeit passten nicht auf eine Zeile | Anzeige auf zwei Zeilen mit Trennung: *Uhrzeit + Medikament + Fach* |
| `unload.php` zu langsam bei vielen Datensätzen | viele SELECTs mit wenig Optimierung | LIMITs, gezielte WHERE-Bedingungen, weniger verschachtelte Loops |
| Synchronisation ESP32 ↔ Server ↔ Webinterface unklar | Zeitpunkt der Einnahme oft nicht eindeutig zuordenbar | `last_taken` wird gezielt verwaltet, und `get_next.php` berücksichtigt Toleranzzeit |

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

- Bei sehr schnellen Öffnungs- und Schliessaktionen **können Logs doppelt erscheinen**, falls das Zeitfenster nicht greift.
- Die OLED-Anzeige kann **bei langen Medikamentennamen** überlaufen (visuell unsauber).
- Keine Benutzerverwaltung oder Authentifizierung – nur im lokalen WLAN sicher nutzbar.
- Bei schlechtem WLAN kann die Synchronisation **zwischen ESP32 und Server stocken** (z. B. "Verbinde mit WLAN..." Dauerschleife).

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

--nfmmhmjjmjgmm

---

