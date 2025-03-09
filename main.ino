#include "wifi_config.h"
#include "api_request.h"
#include "lock_control.h"
#include "bluetooth_handler.h"
#include "low_power.h"

void setup() {
    Serial.begin(115200);
    connectWiFi();  
    initBluetooth(); 
    initLock();      
}

void loop() {
    checkBluetoothCommands(); 
    checkAPIRequests();       
    enterLowPowerMode();      
}
