#include <WiFi.h>
#include <WebServer.h>
#include <ESP32Servo.h>

const char* ssid = "ciel";
const char* password = "U7803k66";

WebServer server(80);
Servo monServo;
const int servoPin = 13; // GPIO où est branché le servo
const int positionOn = 180;   // Position à 180° pour "on" (ouvert)
const int positionOff = 0; // Position à 0° pour "off" (fermé)
const int positionMid = 90;  // Position à 90° pour "mid" (milieu)

void setup() {
  Serial.begin(115200);

  // Connexion Wi-Fi
  WiFi.begin(ssid, password);
  Serial.print("Connexion au Wi-Fi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWi-Fi connecté !");
  Serial.print("Adresse IP : ");
  Serial.println(WiFi.localIP());

  // Route pour ouvrir (180°)
  server.on("/on", HTTP_GET, []() {
    Serial.println("Commande reçue : ON (180°)");
    monServo.attach(servoPin, 500, 2500); // Plage de pulsations ajustée
    monServo.write(positionOn); // Position à 180° (ouvert)
    delay(500); // Petit temps pour atteindre la position
    monServo.detach(); // Coupe l'alimentation du servo
    server.send(200, "text/plain", "Servo tourné à 180°");
  });

  // Route pour fermer (0°)
  server.on("/off", HTTP_GET, []() {
    Serial.println("Commande reçue : OFF (0°)");
    monServo.attach(servoPin, 500, 2500); // Plage de pulsations ajustée
    monServo.write(positionOff); // Position à 0° (fermé)
    delay(500); // Laisse le temps de revenir
    monServo.detach(); // Coupe l'alimentation du servo
    server.send(200, "text/plain", "Servo retourné à 0°");
  });

  // Route pour positionner à 90°
  server.on("/mid", HTTP_GET, []() {
    Serial.println("Commande reçue : MID (90°)");
    monServo.attach(servoPin, 500, 2500); // Plage de pulsations ajustée
    monServo.write(positionMid); // Position à 90° (milieu)
    delay(500); // Laisse le temps de revenir
    monServo.detach(); // Coupe l'alimentation du servo
    server.send(200, "text/plain", "Servo tourné à 90°");
  });

  server.begin();
  Serial.println("Serveur HTTP lancé.");
}

void loop() {
  server.handleClient();
}
