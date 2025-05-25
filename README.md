# 🔐 Déploiement

## 🗂 Sommaire

- [🧠 Description des dossiers](#-description-des-dossiers)
- [🚀 Déploiement](#-déploiement)
  - [1. Clonage du projet](#1-clonage-du-projet)
  - [2. Lancer les services backend et frontend](#2-lancer-les-services-backend-et-frontend)
  - [3. Installer les dépendances](#3-installer-les-dépendances)
  - [4. Configurer l’ESP32](#4-configurer-lesp32)
  - [5. Finaliser avec l’application mobile](#5-finaliser-avec-lapplication-mobile)
  - [6. Stopper et sauvegarder](#6-stopper-et-sauvegarder)

---

## 🧠 Description des dossiers

### 🔧 `APIs/`

- Contient le code PHP des services backend (authentification, réservation, etc.).
- Utilise Docker pour l'environnement serveur (PHP, MySQL, phpMyAdmin, Nginx).

### 📱 `AppMobile/`

- Application Android native (Java).
- Téléchargez le fichier APK compilé et installez-le sur un smartphone Android.
- ⚙️ Dans l'application, cliquez sur le logo engrenage en bas à droite pour configurer l'adresse IP de l'API et celle de l'ESP32.

### 📱 `IoT/`

- Contient le code Arduino pour l’ESP32.
- ✨ À faire : téléverser ce code sur l'ESP32 via l'IDE Arduino.
- 🛠 Pensez à adapter les informations Wi-Fi (`ssid` / `password`) dans le code selon le point d’accès utilisé par la tablette ou le smartphone.

### 🌐 `Web/`

- Interface Web de gestion.
- Se connecte aux mêmes APIs que l'app mobile.
- L'environnement React est également lancé via Docker.

---

## 🚀 Déploiement

### 1. Clonage du projet

Avant toute chose, commencez par cloner la branche `Deploiement` du dépôt :

```bash
git clone -b Deploiement https://github.com/JR-CIEL-2-PROJETS/25-airlocunlock.git
cd 25-airlocunlock
2. Lancer les services backend et frontend
Dans la racine du projet (là où se trouve le script docker-up.sh), exécutez :
```

```bash
   ./docker-up.sh
```
###  ▶️  Ce script va : `./docker-up.sh`

- détecter automatiquement l’adresse IP locale à utiliser comme REACT_APP_API_URL

- démarrer les services Docker (API, MySQL, phpMyAdmin, Nginx, Web)

- importer les bases de données airlockunlock et Tapkey

### 3. Installer les dépendances
Si ce n’est pas encore fait, installez les dépendances nécessaires dans le dossier APIs/code :

```bash
cd APIs/code
composer install
```
- Cela installera, entre autres, la bibliothèque firebase/php-jwt pour la gestion des tokens.

### 4. Configurer l’ESP32
Rendez-vous dans le dossier IoT/ et ouvrez le fichier dans Arduino IDE.

## 🔧 Modifiez les identifiants Wi-Fi dans le code :

```bash
const char* ssid = "NomDuRéseau";
const char* password = "MotDePasse";
```
Ensuite :

- Branchez l’ESP32

- Téléversez le code depuis l’IDE Arduino

### 5. Finaliser avec l’application mobile
Depuis un smartphone Android :

- Installez le fichier AirlockUnlock.apk (dans AppMobile/)

- Ouvrez l'application

- Cliquez sur l’icône en bas à droite 3 fois

- Saisissez l’adresse IP de l’API et celle de l’ESP32 (en utlisant ip a & le moniteur série pour l'ESP 32)

### 6. Stopper et sauvegarder
Lorsque vous avez terminé, exécutez :

```bash
./docker-down.sh
```

Ce script :

- arrête tous les conteneurs Docker (API, Web, BDD...)

- sauvegarde les bases de données dans APIs/code/back_airlockunlock.sql et back_tapkey.sql

- effectue un git push automatique vers la branche Deploiement