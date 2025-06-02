#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <WebServer.h>
#include <ESP32Servo.h>

const char* ssid = "ciel2";
const char* password = "btsciel123";

const String SERIAL_AUTORISE = "TK-001-XYZ";

WebServer server(80);
Servo monServo;
const int servoPin = 15;
const int positionOn = 180;
const int positionOff = 0;
const int positionMid = 90;

WiFiClientSecure client;

const char* backendHost = "192.168.1.160";
const int backendPort = 421;
const char* backendPath = "/airlockunlock/verif/verif-tapkey.php";

bool isAuthorized() {
  if (!server.hasHeader("Authorization")) {
    Serial.println("❌ En-tête Authorization manquant");
    return false;
  }
  return true;
}

bool isSerialValid() {
  if (!server.hasArg("serial")) {
    Serial.println("❌ Numéro de série manquant dans la requête");
    return false;
  }

  String serialRecu = server.arg("serial");
  Serial.println("Numéro de série reçu : " + serialRecu);

  if (serialRecu != SERIAL_AUTORISE) {
    Serial.println("❌ Numéro de série invalide !");
    return false;
  }
  return true;
}

bool verifReservationAvecBackend(const String& token, const String& serial) {
  Serial.println("Connexion au backend HTTPS pour vérification...");

  client.setInsecure(); // ⚠️ Pour accepter un certificat auto-signé (NE PAS utiliser en production)

  if (!client.connect(backendHost, backendPort)) {
    Serial.println("❌ Impossible de se connecter au backend HTTPS");
    return false;
  }

  String url = String(backendPath) + "?serial=" + serial;

  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
               "Host: " + backendHost + "\r\n" +
               "Authorization: " + token + "\r\n" +
               "Connection: close\r\n\r\n");

  unsigned long timeout = millis();
  while (client.available() == 0) {
    if (millis() - timeout > 5000) {
      Serial.println("❌ Timeout attente réponse backend");
      client.stop();
      return false;
    }
  }

  bool isValid = false;
  while (client.available()) {
    String line = client.readStringUntil('\n');
    Serial.println(line);
    if (line.indexOf("OK") >= 0) {
      isValid = true;
    }
  }
  client.stop();

  if (isValid) {
    Serial.println("✅ Backend : réservation valide");
  } else {
    Serial.println("❌ Backend : réservation non valide");
  }
  return isValid;
}

void handleCommand(int position, const char* positionName) {
  if (!isAuthorized()) {
    server.send(401, "text/plain", "Unauthorized");
    return;
  }

  if (!isSerialValid()) {
    server.send(403, "text/plain", "Numéro de série invalide");
    return;
  }

  String authHeader = server.header("Authorization");
  String serialRecu = server.arg("serial");

  if (!verifReservationAvecBackend(authHeader, serialRecu)) {
    server.send(403, "text/plain", "Vérification réservation échouée");
    return;
  }

  Serial.printf("Commande reçue : %s (%d°)\n", positionName, position);
  monServo.attach(servoPin, 500, 2500);
  monServo.write(position);
  delay(500);
  monServo.detach();

  server.send(200, "text/plain", String("Servo tourné à ") + position + "°");
}

void setup() {
  Serial.begin(115200);

  WiFi.begin(ssid, password);
  Serial.print("Connexion au Wi-Fi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ Wi-Fi connecté !");
  Serial.print("Adresse IP : ");
  Serial.println(WiFi.localIP());

  server.on("/on", HTTP_GET, []() {
    handleCommand(positionOn, "ON");
  });

  server.on("/off", HTTP_GET, []() {
    handleCommand(positionOff, "OFF");
  });

  server.on("/mid", HTTP_GET, []() {
    handleCommand(positionMid, "MID");
  });

  server.begin();
  Serial.println("🚀 Serveur HTTP lancé");
}

void loop() {
  server.handleClient();
}
