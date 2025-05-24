
# 📘 README - Site Web

## 🚀 Lancement des services (UP)

### Script : `./docker-up.sh `

Ce script :

- Lance les conteneurs en arrière-plan (`docker compose up -d`)
- Attend que MySQL démarre

### ▶️ Pour l'exécuter :

```bash
./docker-up.sh
```

---

## 🛑 Arrêt des services (DOWN)

### Script : `./docker-down.sh`

Ce script :

- Commit automatiquement les fichiers SQL dans Git avec le message `"save"`
- Push les changements sur la branche `Web`
- Arrête tous les conteneurs (`docker compose down`)

### ⛔ Pour l'exécuter :

```bash
./docker-down.sh
```

### Pour recuperer les donner du repo github :

```bash
git fetch origin 
git reset --hard origin/Web
```
