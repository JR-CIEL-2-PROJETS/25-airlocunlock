#include "low_power.h"
#include <esp_sleep.h>

void enterLowPowerMode() {
    Serial.println("Passage en mode basse consommation...");
    esp_deep_sleep_start();
}
