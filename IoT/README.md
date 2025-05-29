# 🔌 AirlockUnlock - Code IoT (ESP32)

Ce dossier contient le code à téléverser sur un ESP32 pour contrôler l’ouverture de serrure via l'application mobile AirlockUnlock.

---

#### 🧷 Schéma de branchement

📌 Branchez l’ESP32 à la serrure selon le schéma suivant :  
📸 *(![Branchement ESP 32](circuit.png))*

#### 🛠 Étapes dans l’IDE Arduino
1. Activez le **point d’accès mobile** de votre PC (hotspot).
2. Ouvrez le logiciel **Arduino**.
3. Copier le fichier **arduino.h** du dossier `IoT/`.
4. Modifiez les identifiants Wi-Fi & l'adresse IP :

```cpp
const char* ssid = "NomDuReseau";
const char* password = "MotDePasse";

const char* backendHost = "192.168.1.160";
```

**Configuration des biblihotèques**

6. **Ouvre l’IDE Arduino**
Ajoute cette URL dans "URL de gestionnaire de cartes supplémentaires" :

```bash
https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
```
Va dans Outils > Type de carte > Gestionnaire de cartes
**Installe esp32 by Espressif Systems**

Sélectionne ta carte ESP32 :
**Outils > Type de carte > ESP32 Dev Module (ou autre modèle ESP32)**

Aller dans le Gestionnaire de bibliothèques
**Clique sur Croquis > Inclure une bibliothèque > Gérer les bibliothèques...**

Rechercher **ESP32Servo**
Dans la barre de recherche, tape : ESP32Servo

Installer la bibliothèque
Trouve ESP32Servo (par Kevin Harrington) et clique sur Installer.

6. Téléversez le code sur la carte ESP32.
7. Dans le **Moniteur série**, récupérez l’adresse IP attribuée à l’ESP32.

```bash
✅ Wi-Fi connecté !
Adresse IP : XXX.XXX.XXX.XXX
```

---