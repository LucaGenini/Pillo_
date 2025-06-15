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

---

## 🔁Reproduzierbarkeit

Wenn du selbst Interesse hast das Projekt nachzubauen, dann findest du hier eine auführliche Anleitung: :)
https://docs.google.com/document/d/1ege301V0So02RBs3WJ28wAcD5htAfxtaAjiDvV3Bwis/edit?usp=sharing

---

## 🔄Flussdiagramm

![Screenflow](https://github.com/user-attachments/assets/68f54622-40ea-4e81-a5e2-a8ce419c0432)
*Das Diagramm zeigt die Kommunikationswege zwischen ESP32, Website und Server.*

---

## 🔧Komponentenplan

![Komponentenplan](https://github.com/user-attachments/assets/e54be341-70cc-4e0a-91e9-0666d4949830)
*Verbindungen und Protokolle zwischen haptischen Komponenten und digitalen Modulen.*

---

## 🧩Steckschema

![Steckplan](https://github.com/user-attachments/assets/b0e210c0-8eb5-4ff1-9236-5de2e835f993)
*Das Breadboard-Schema zeigt den Aufbau mit ESP32 und Sensoren.*

---

## 🖼️Screenshots / Medien

_Füge hier Screenshots oder GIFs ein, z. B.:_

- Weboberfläche (index.html)
- OLED-Anzeige mit aktueller Einnahme
- Graphen aus `unload.php`

![Weboberfläche](./assets/webui.png)  
![OLED Screenshot](./assets/oled_display.jpg)

---

## 🎥Video-Dokumentation

Hier findest du noch eine kleine Präsentation zu meinem Pillo Gadget.
-> https://youtu.be/Q0Ffs1uNg1s

---

## ⚙️Umsetzungsprozess



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

---

### 🚫Verworfene Lösungsansätze

- **Nur mit Distanzsensoren zu arbeiten**, ohne Magnetsensor: stellte sich als zu fehleranfällig heraus – Bewegungen vor dem Sensor lösten falsche Einnahmen aus.
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

