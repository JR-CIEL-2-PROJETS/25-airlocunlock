# 🔌 AirlockUnlock - Code IoT (ESP32)

Ce dossier contient le code à téléverser sur un ESP32 pour contrôler l’ouverture de serrure via l'application mobile AirlockUnlock.

---

## 🚀 Instructions

1. Ouvrez le projet avec **Arduino IDE**.
2. Connectez l’ESP32 en USB à votre ordinateur.
3. Cliquez sur **Téléverser** pour envoyer le code à l’ESP32.

---

## 📡 Connexion Wi-Fi

Avant de téléverser le code, **vous devez adapter les identifiants Wi-Fi** afin que l’ESP32 puisse se connecter au bon réseau (généralement celui utilisé par la tablette ou l’application mobile).

Dans le fichier source, modifiez les lignes suivantes selon votre configuration réseau :

```cpp
const char* ssid = "ciel";       // 🔁 Remplacez par le nom (SSID) de votre point d’accès Wi-Fi
const char* password = "U7803k66";  // 🔁 Remplacez par le mot de passe associé
