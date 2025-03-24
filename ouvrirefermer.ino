#include <WiFi.h>
#include <HTTPClient.h>

const char* WIFI_SSID = "Bilal";
const char* WIFI_PASSWORD = "bilo77400";

// URL de l'API Mock avec l'action pour ouvrir ou fermer la serrure
const char* MOCK_SERVER_URL_OUVRIR = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/serrure?reservation=1&serurre_1=on";
const char* MOCK_SERVER_URL_FERMER = "https://7b053c83-308c-4225-95a8-3ae0ffd5ca21.mock.pstmn.io/serrure?reservation=1&serurre_1=off";

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
}

void lock_lock() {
    Serial.println("Verrouillage de la serrure...");
}

void setup() {
    Serial.begin(115200);
    connect_wifi();
    
    // Envoi de la requête pour ouvrir la serrure
    send_request(true); // true pour ouvrir la serrure
    
    // Si tu veux fermer la serrure, appelle cette fonction
    // send_request(false); // false pour fermer la serrure
}

void loop() {
}
