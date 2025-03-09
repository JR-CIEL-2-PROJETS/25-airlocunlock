#include "bluetooth_handler.h"
#include <BluetoothSerial.h>

BluetoothSerial SerialBT;

void initBluetooth() {
    SerialBT.begin("SerrureESP32");
    Serial.println("Bluetooth prêt !");
}

void checkBluetoothCommands() {
    if (SerialBT.available()) {
        String command = SerialBT.readString();
        Serial.print("Commande reçue : ");
        Serial.println(command);

        if (command == "OPEN") {
            openLock();
        } else if (command == "CLOSE") {
            closeLock();
        }
    }
}
