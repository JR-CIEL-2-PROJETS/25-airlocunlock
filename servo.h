#include <WiFi.h>
#include <HTTPClient.h>
#include <ESP32Servo.h>  // Utiliser la bibliothèque ESP32Servo

const char* WIFI_SSID = "Bilal";
const char* WIFI_PASSWORD = "bilo77400";

// URL de l'API Mock avec l'action pour ouvrir ou fermer la serrure
const char* MOCK_SERVER_URL_OUVRIR = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/serrure?reservation=1&serurre_1=on";
const char* MOCK_SERVER_URL_FERMER = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/serrure?reservation=1&serurre_1=off";

Servo moteur;  // Créer une instance du servomoteur

void unlock_lock(); // Prototype de la fonction
void lock_lock();   // Prototype de la fonction

void connect_wifi() {
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    Serial.print("Connexion au réseau Wi-Fi...");
    
    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        Serial.print(".");
    }
    
    Serial.println("Connecté au Wi-Fi avec succès.");
    Serial.print("Adresse IP: ");
    Serial.println(WiFi.localIP());
}

void send_request(bool ouvrir) {
    HTTPClient http;
    const char* url = ouvrir ? MOCK_SERVER_URL_OUVRIR : MOCK_SERVER_URL_FERMER;
    
    http.begin(url);
    int httpCode = http.GET();
    
    Serial.println("Code HTTP de la requête: " + String(httpCode));
    
    if (httpCode > 0) {
        String payload = http.getString();
        Serial.println("Réponse du serveur:");
        Serial.println(payload);
        
        if (payload.indexOf("La serrure est ouverte") >= 0 || payload.indexOf("La serrure de la Reservation_1 est ouvert") >= 0) {
            Serial.println("Accès validé. Ouverture de la serrure...");
            unlock_lock();
        } else if (payload.indexOf("La serrure est fermée") >= 0) {
            Serial.println("Accès validé. Fermeture de la serrure...");
            lock_lock();
        } else {
            Serial.println("Accès non autorisé ou erreur de la réservation.");
        }
    } else {
        Serial.println("Erreur de requête HTTP. Code: " + String(httpCode));
    }
    http.end();
}

void unlock_lock() {
    Serial.println("Déverrouillage de la serrure...");
    
    // Commande pour faire tourner le servomoteur (par exemple, 90 degrés)
    moteur.write(90);  // Tourner le moteur à 90 degrés (ajuste selon ton besoin)
    delay(1000);       // Attendre que le moteur ait le temps de tourner
}

void lock_lock() {
    Serial.println("Verrouillage de la serrure...");
    
    // Revenir à la position initiale
    moteur.write(0);  // Revenir à la position de départ (ajuste selon ton besoin)
    delay(1000);      // Attendre que le moteur se positionne
}

void setup() {
    Serial.begin(115200);
    connect_wifi();
    
    moteur.attach(12);  // Connecter le servomoteur à la broche 9 (ajuste selon ton branchement)
    
    // Envoi de la requête pour ouvrir la serrure
    send_request(true); // true pour ouvrir la serrure
    
    // Si tu veux fermer la serrure, appelle cette fonction
    // send_request(false); // false pour fermer la serrure
}

void loop() {
}
