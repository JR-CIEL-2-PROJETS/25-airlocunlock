#include <BluetoothSerial.h>

BluetoothSerial ESP_BT;  // Instance pour la communication Bluetooth série

const int serrurePin = 13;  // Utilisation du pin 13 pour contrôler la serrure (peut être modifié)

void setup() {
  Serial.begin(115200);  // Démarre la communication série pour le debug
  pinMode(serrurePin, OUTPUT);  // Définit le pin comme sortie

  // Démarre le Bluetooth
  if (!ESP_BT.begin("SerrureESP32")) {
    Serial.println("Erreur lors du démarrage du Bluetooth");
    return;
  }

  Serial.println("Bluetooth prêt. Attente de la connexion...");
}

void loop() {
  if (ESP_BT.available()) {
    String command = ESP_BT.readStringUntil('\n');  // Lire la commande envoyée par l'application

    Serial.println("Commande reçue: " + command);

    if (command == "OPEN") {
      ouvrirSerrure();
    } else if (command == "CLOSE") {
      fermerSerrure();
    } else {
      Serial.println("Commande inconnue");
    }
  }
}

void ouvrirSerrure() {
  digitalWrite(serrurePin, HIGH);  // Allume la serrure (ou ouvre)
  Serial.println("Serrure ouverte");
  ESP_BT.println("Serrure ouverte");  // Envoie un message à l'application Android
}

void fermerSerrure() {
  digitalWrite(serrurePin, LOW);  // Éteint la serrure (ou ferme)
  Serial.println("Serrure fermée");
  ESP_BT.println("Serrure fermée");  // Envoie un message à l'application Android
}
