#include "lock_control.h"

#define LOCK_PIN 5  // Broche de contrôle de la serrure

void initLock() {
    pinMode(LOCK_PIN, OUTPUT);
    closeLock(); // Verrouille par défaut
}

void openLock() {
    digitalWrite(LOCK_PIN, HIGH);
    Serial.println("🔓 Serrure ouverte !");
    delay(5000);
    closeLock(); // Ferme automatiquement après 5s
}

void closeLock() {
    digitalWrite(LOCK_PIN, LOW);
    Serial.println("🔒 Serrure verrouillée !");
}
