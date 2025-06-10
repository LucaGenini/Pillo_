# 💊 Pillo-Box: Intelligenter Medikamentenspender mit ESP32-C6

> Ein interaktives IoT-System zur Verwaltung, Erinnerung und Protokollierung der Medikamenteneinnahme.

---

## 📦 Inhaltsverzeichnis

- [Projektbeschreibung](#projektbeschreibung)
- [Reproduzierbarkeit](#reproduzierbarkeit)
- [Flussdiagramm](#flussdiagramm)
- [Komponentenplan](#komponentenplan)
- [Steckschema](#steckschema)
- [Screenshots / Medien](#screenshots--medien)
- [Umsetzungsprozess](#umsetzungsprozess)
- [Video-Dokumentation](#video-dokumentation)
- [Lernfortschritt](#lernfortschritt)

---

## 📝 Projektbeschreibung

Pillo ist eine Website, bei der man seine Medikamenteneinnahme tracken kann.

Das Projekt wurde konzipiert und umgesetzt im Modul Interaktive Medien IV an der Fachhochschule Graubünden. Es soll Ü50+ oder auch jüngeren Menschen, welche regelmässig Medikamente einnehmen müssen, eine Hilfe sein, nachzuverfolgen, wie oft in den vergangenen Wochen oder Monaten ein Medikament vergessen wurde. Vor allem bei schwerwiegenden Medikamenten ist es teilweise unerlässlich, eine regelmässige Einnahme festzustellen, da das Auslassen dieser Medikamente sich negativ auf die Gesundheit einer Person auswirken kann oder beim Versäumnis eines überlebenswichtigen Medikamentes sogar lebensgefährlich sein kann.

Die Motivation für dieses Projekt resultierte aus folgendem Gedanken:
In jüngeren Jahren musste ich selbst regelmässig ein Medikament konsumieren, jedoch war ich mit der Einnahme nie besonders konsistent, womit ich am Ende einer Woche/Monats nicht feststellen konnte, wie oft ich das Medikament tatsächlich konsumiert habe. Gerade bei meinem näheren und vor allem älteren Bekanntenkreis fällt es mir auf, dass viele regelmässig Medikamente konsumieren, aber keine Möglichkeit haben, die Einnahmeregelmässigkeit zu überprüfen. Mein Projekt soll helfen, dieses Handlungsmuster zu brechen durch eine effektive Visualisierung der Versäumnisse.

Die Versäumnisse werden mittels einer Grafik angezeigt, bei der ersichtlich ist, wie viel Prozent ein Medikament effektiv eingenommen wurde innert eines Zeitraumes.

Link zur Website: www.lucagenini.com/Pillo

Innert der aktuellen Woche sieht man auch immer den aktuellen Plan, welches Medikament zu welcher Uhrzeit eingenommen werden muss und je nach Einnahme oder Versäumnis registriert.

---

## 🔁 Reproduzierbarkeit

**Folge dieser Schritt-für-Schritt-Anleitung**, um das Projekt nachzubauen:

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

## 🔄 Flussdiagramm
[Pillo.pdf](https://github.com/user-attachments/files/20678275/Pillo.pdf)
*Das Diagramm zeigt die Kommunikationswege zwischen ESP32, Website und Server.*

---

## 🔧 Komponentenplan

![Komponentenplan](https://github.com/user-attachments/assets/e54be341-70cc-4e0a-91e9-0666d4949830)
*Verbindungen und Protokolle zwischen haptischen Komponenten und digitalen Modulen.*

---

## 🧩 Steckschema

![Steckplan](https://github.com/user-attachments/assets/b0e210c0-8eb5-4ff1-9236-5de2e835f993)
*Das Breadboard-Schema zeigt den Aufbau mit ESP32 und Sensoren.*

---

## 🖼️ Screenshots / Medien

_Füge hier Screenshots oder GIFs ein, z. B.:_

- Weboberfläche (index.html)
- OLED-Anzeige mit aktueller Einnahme
- Graphen aus `unload.php`

```markdown
![Weboberfläche](./assets/webui.png)
![OLED Screenshot](./assets/oled_display.jpg)
