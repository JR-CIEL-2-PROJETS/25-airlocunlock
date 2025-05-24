# 🔐 Deploiement

## 🧠 Description des dossiers

### 🔧 `APIs/`

- Contient le code PHP des services backend.
- Utilise Docker pour l'environnement serveur + base de données.

### 📱 `AppMobile/`

- Application Android native.
- Téléchargez le fichier APK compilé et installez-le sur un smartphone Android.

### 📡 `IoT/`

- Contient le code Arduino pour l’ESP32.
- ✨ **À faire :** téléverser ce code sur l'ESP32 via Arduino.

### 🌐 `Web/`

- Interface Web.

---

## 🚀 Déploiement

1. Lancer les services :
   ```bash
   ./docker-up.sh
   ```

2. Travailler avec l’API / Web / AppMobile

3. Sauvegarder et stopper :
   ```bash
   ./docker-down.sh
   ```

---