# 📘 README - Site Web

Dans la racine du projet, exécutez la commande suivante :

```bash
./docker-up.sh
```

Ce script va :
- Détecter automatiquement l’adresse IP locale (pour l’API et le Web).
- Démarrer les services suivants : **PHP API**, **MySQL**, **phpMyAdmin**, **Nginx**, **Site Web**.
- Importer automatiquement les bases de données : `airlockunlock` et `tapkey`.

⚠️ Si vous rencontrez un problème de permission pour exécuter le script :

```bash
chmod +x docker-up.sh
```
---

Pour accéder au site Web, ouvrez votre navigateur et entrez l’URL :

```
https://{adresse-IP-de-votre-PC}:422
```

#### 🔍 Comment récupérer votre adresse IP locale :
- Sur **Linux** :  
  ```bash
  hostname -I
  ```
- Sur **Windows** :  
  ```bash
  ipconfig
  ```

---