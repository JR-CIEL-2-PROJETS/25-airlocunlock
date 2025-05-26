<?php
include '../config.php'; // Assurez-vous que le fichier config.php est dans le même répertoire

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier si les données nécessaires existent
    if (!isset($_POST['nom'], $_POST['email'], $_POST['mot_de_passe'], $_POST['telephone'])) {
        echo 'Erreur: Tous les champs sont requis.';
        exit();
    }

    // Récupérer les données envoyées par le propriétaire
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $telephone = $_POST['telephone'];

    // Validation des données
    if (empty($nom) || empty($email) || empty($mot_de_passe) || empty($telephone)) {
        echo 'Erreur: Tous les champs sont requis.';
        exit();
    }

    // Vérification de la validité de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Erreur: Adresse email invalide.';
        exit();
    }

    // Vérification de la validité du téléphone
    if (!preg_match('/^[0-9]{10,15}$/', $telephone)) {
        echo 'Erreur: Le numéro de téléphone doit être valide (10-15 chiffres).';
        exit();
    }

    // Hashage du mot de passe
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

    // Préparer la requête d'insertion
    $sql = "INSERT INTO proprietaires (nom, email, mot_de_passe, telephone) VALUES (:nom, :email, :mot_de_passe, :telephone)";
    try {
        // Préparer la déclaration
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash);
        $stmt->bindParam(':telephone', $telephone);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo 'Propriétaire inscrit avec succès.';
        } else {
            echo 'Erreur lors de l\'inscription.';
        }
    } catch (PDOException $e) {
        echo 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage();
    }
}
?>