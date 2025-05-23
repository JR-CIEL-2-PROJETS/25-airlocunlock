
# 📘 README - APis

## 🚀 Lancement des services (UP)

### Script : `./docker-up.sh `

Ce script :

- Lance les conteneurs en arrière-plan (`docker compose up -d`)
- Attend que MySQL démarre
- Restaure automatiquement les bases de données `airlockunlock` et `Tapkey` à partir des fichiers `.sql` dans `code/`

### ▶️ Pour l'exécuter :

```bash
./docker-up.sh
```

---

## 🛑 Arrêt des services (DOWN)

### Script : `./docker-down.sh`

Ce script :

- Sauvegarde les bases de données MySQL (airlockunlock et Tapkey) dans `code/`
- Commit automatiquement les fichiers SQL dans Git avec le message `"save"`
- Push les changements sur la branche `API-1`
- Arrête tous les conteneurs (`docker compose down`)

### ⛔ Pour l'exécuter :

```bash
./docker-down.sh
```

### Pour recuperer les donner du repo github :

```bash
git fetch origin 
git reset --hard origin/API-1
```