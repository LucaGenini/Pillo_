#include <Wire.h>                         // Für I2C-Kommunikation
#include <Adafruit_VL6180X.h>             // Bibliothek für den VL6180X Distanzsensor
#include <Adafruit_SSD1306.h>             // Bibliothek für das OLED-Display
#include <Adafruit_GFX.h>                 // Grafikbibliothek für das OLED
#include <RTClib.h>                       // Für die Nutzung einer RTC (wird hier aber nicht aktiv verwendet)
#include <WiFi.h>                         // WLAN-Kommunikation
#include <HTTPClient.h>                   // HTTP Requests senden
#include <Arduino_JSON.h>                 // Zum Verarbeiten von JSON-Daten

#define SCREEN_WIDTH 128                  // Breite des OLED-Displays in Pixel
#define SCREEN_HEIGHT 64                  // Höhe des OLED-Displays in Pixel
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1); // OLED-Objekt initialisieren

#define SDA_PIN 20                        // I2C-Datenleitung
#define SCL_PIN 21                        // I2C-Taktleitung
#define XSHUT_1 10                        // Steuerpin für VL6180X Sensor 1
#define XSHUT_2 8                         // Steuerpin für VL6180X Sensor 2
#define TOF1_ADDRESS 0x30                 // I2C-Adresse Sensor 1
#define TOF2_ADDRESS 0x31                 // I2C-Adresse Sensor 2

#define REED_1 6                          // Reed-Sensor für Fach 1 (Türkontakt)
#define REED_2 7                          // Reed-Sensor für Fach 2 (Türkontakt)

const char* ssid = "...";                 // WLAN-SSID (Zugangspunkt)
const char* pass = "...b";               // WLAN-Passwort
const char* serverURL = "https://lucagenini.com/pillo/load.php"; // Ziel-URL für Logging

Adafruit_VL6180X tof1 = Adafruit_VL6180X();  // Objekt für Sensor 1
Adafruit_VL6180X tof2 = Adafruit_VL6180X();  // Objekt für Sensor 2

bool fach1Voll = false;                 // Zustand Fach 1 befüllt oder leer
bool fach2Voll = false;                 // Zustand Fach 2 befüllt oder leer

bool entnahmeErfolgt = false;           // Flag, ob eine Entnahme registriert wurde
unsigned long entnahmeZeit = 0;         // Zeitpunkt der letzten Entnahme
const unsigned long anzeigenDauer = 3000; // Dauer der Anzeige bei Entnahme (nicht aktiv verwendet)

/**
 * Senden eines JSON-Logs an den Webserver bei Entnahme oder Befüllung
 */
void sendeEntnahmeLog(int fachNr, bool korrekt) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/json");

    JSONVar payload;
    payload["fach_nr"] = fachNr;
    payload["korrekt"] = korrekt;

    String jsonString = JSON.stringify(payload);
    int httpResponseCode = http.POST(jsonString); // POST-Request senden

    if (httpResponseCode > 0) {
      Serial.print("Server Antwort: ");
      Serial.println(httpResponseCode);
    } else {
      Serial.print("Fehler bei POST: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  } else {
    Serial.println("Keine WLAN-Verbindung zum Senden");
  }
}

/**
 * Setup-Funktion – wird einmal beim Start ausgeführt
 */
void setup() {
  Serial.begin(115200);
  Wire.begin(SDA_PIN, SCL_PIN);

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("OLED nicht erkannt");
    while (true); // Endlosschleife bei Fehler
  }

  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);

  // Sensoren deaktivieren, um Adresskonflikte zu vermeiden
  pinMode(XSHUT_1, OUTPUT);
  pinMode(XSHUT_2, OUTPUT);
  digitalWrite(XSHUT_1, LOW);
  digitalWrite(XSHUT_2, LOW);
  delay(10);

  // Sensor 1 aktivieren und Adresse setzen
  digitalWrite(XSHUT_1, HIGH);
  delay(100);
  if (!tof1.begin(&Wire)) {
    Serial.println("Sensor 1 Fehler");
    while (true);
  }
  tof1.setAddress(TOF1_ADDRESS);
  delay(10);

  // Sensor 2 aktivieren und Adresse setzen
  digitalWrite(XSHUT_2, HIGH);
  delay(100);
  if (!tof2.begin(&Wire)) {
    Serial.println("Sensor 2 Fehler");
    while (true);
  }
  tof2.setAddress(TOF2_ADDRESS);

  // Reed-Sensoren als Eingänge konfigurieren
  pinMode(REED_1, INPUT_PULLUP);
  pinMode(REED_2, INPUT_PULLUP);

  // WLAN-Verbindung herstellen
  display.clearDisplay();
  display.setCursor(0, 0);
  display.println("Verbinde mit WLAN...");
  display.display();

  WiFi.begin(ssid, pass);
  int versuche = 0;
  while (WiFi.status() != WL_CONNECTED && versuche < 20) {
    delay(500);
    Serial.print(".");
    versuche++;
  }

  display.clearDisplay();
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWLAN verbunden!");
    display.setCursor(0, 0);
    display.println("WLAN verbunden!");
  } else {
    Serial.println("\nWLAN Verbindung fehlgeschlagen!");
    display.setCursor(0, 0);
    display.println("WLAN fehlgeschlagen!");
  }
  display.display();
  delay(2000);
}

/**
 * Prüft, ob ein Objekt im Fach erkannt wurde (unter 10 mm)
 */
bool fachHatObjekt(uint8_t range) {
  return (range < 10);
}

/**
 * OLED-Display aktualisieren mit zwei Statuszeilen und zwei Fach-Zuständen
 */
void aktualisiereOLED(const char* zeile1, const char* zeile2, const char* status1, const char* status2) {
  display.clearDisplay();
  display.drawRect(0, 0, SCREEN_WIDTH, 24, WHITE);
  display.setTextSize(1);
  display.setCursor(2, 2);
  display.println(zeile1);
  display.setCursor(2, 12);
  display.println(zeile2);
  display.setCursor(0, 34);
  display.println(status1);
  display.setCursor(0, 46);
  display.println(status2);
  display.display();
}

/**
 * Hauptloop – wird wiederholt ausgeführt
 */
void loop() {
  int aktuelleStunde = 8;                  // (Manuell gesetzt – später evtl. RTC nutzen)
  int aktuelleMinute = 0;
  const char* aktuellerTag = "MI";         // Aktueller Tag (Platzhalter)

  static char status1[24];
  static char status2[24];
  strcpy(status1, fach1Voll ? "Fach 1: bereit" : "Fach 1: nachfuellen");
  strcpy(status2, fach2Voll ? "Fach 2: bereit" : "Fach 2: nachfuellen");

  // Schleife über beide Fächer
  for (int fachNr = 1; fachNr <= 2; fachNr++) {
    int reedPin = (fachNr == 1) ? REED_1 : REED_2;
    bool& statusVoll = (fachNr == 1) ? fach1Voll : fach2Voll;
    Adafruit_VL6180X& sensor = (fachNr == 1) ? tof1 : tof2;
    char* status = (fachNr == 1) ? status1 : status2;

    bool tuerGeoeffnet = digitalRead(reedPin) == HIGH; // Reed HIGH → Tür offen

    if (tuerGeoeffnet) {
      uint8_t range = sensor.readRange();
      Serial.print("Fach "); Serial.print(fachNr);
      Serial.print(" Distanz: "); Serial.print(range);
      Serial.println(" mm");

      bool objektDa = fachHatObjekt(range);
      bool korrekt = true; // Immer als korrekt angenommen

      // Entnahme erkannt
      if (statusVoll && !objektDa) {
        Serial.print("Fach "); Serial.print(fachNr); Serial.println(" entnommen");
        statusVoll = false;
        strcpy(status, "OK: Entnahme erfolgt");
        entnahmeErfolgt = true;
        entnahmeZeit = millis();
        sendeEntnahmeLog(fachNr, korrekt);
      }
      // Fach wurde neu befüllt
      else if (!statusVoll && objektDa) {
        Serial.print("Fach "); Serial.print(fachNr); Serial.println(" gefuellt");
        statusVoll = true;
        strcpy(status, fachNr == 1 ? "Fach 1: bereit" : "Fach 2: bereit");
      }
    }
  }

  // Anzeige: Nächste geplante Einnahme
  char zeile1[24];
  char zeile2[24];
  char zeile3[24];

  snprintf(zeile1, sizeof(zeile1), "Naechste Einnahme:");

  const char* getNextURL = "https://lucagenini.com/pillo/get_next.php";
  String zeile2text = "Keine Einnahme";
  String zeile3text = "";

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(getNextURL);
    int httpCode = http.GET();

    if (httpCode == 200) {
      String payload = http.getString();
      JSONVar response = JSON.parse(payload);

      if (JSON.typeof(response) == "object") {
        String fach = (const char*)response["fach"];
        String zeit = (const char*)response["zeit"];
        String tag = (const char*)response["tag"];
        String med = (const char*)response["med"];

        if (fach != "-" && zeit != "-") {
          zeile2text = tag + " " + zeit + " Uhr";
          zeile3text = med + " (Fach " + fach + ")";
        } else {
          zeile2text = "Keine Einnahme geplant";
          zeile3text = "";
        }
      } else {
        zeile2text = "Fehlerhafte Antwort";
      }
    } else {
      zeile2text = "Abruf fehlgeschlagen";
    }
    http.end();
  }

  zeile2text.toCharArray(zeile2, sizeof(zeile2));
  zeile3text.toCharArray(zeile3, sizeof(zeile3));

  // Ausgabe auf dem Display
  display.clearDisplay();
  display.drawRect(0, 0, SCREEN_WIDTH, 34, WHITE);  // Rahmen für obere Anzeige
  display.setTextSize(1);
  display.setCursor(2, 2);
  display.println(zeile1);
  display.setCursor(2, 12);
  display.println(zeile2);
  display.setCursor(2, 22);
  display.println(zeile3);
  display.setCursor(0, 38);
  display.println(status1);
  display.setCursor(0, 50);
  display.println(status2);
  display.display();

  delay(500); // Kurze Pause bis zur nächsten Schleife
}
