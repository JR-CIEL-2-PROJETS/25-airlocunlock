#include "api_request.h"

void checkAPIRequests() {
    HTTPClient http;
    http.begin("https://api.airlockunlock.com/check_access");
    int httpResponseCode = http.GET();

    if (httpResponseCode == 200) {
        Serial.println("Accès autorisé");
        openLock();
    } else {
        Serial.println("Accès refusé");
    }
    http.end();
}
