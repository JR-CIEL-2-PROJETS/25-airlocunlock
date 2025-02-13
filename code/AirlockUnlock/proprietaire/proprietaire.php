<?php
include '../config.php'; // Assurez-vous que le fichier config.php est dans le même répertoire

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données envoyées par le client
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];

    // Validation des données
    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        echo 'Erreur: Tous les champs sont requis.';
        exit();
    }

    // Vérification de la validité de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Erreur: Adresse email invalide.';
        exit();
    }

    // Hashage du mot de passe
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Préparer la requête d'insertion
    $sql = "INSERT INTO inscription_propriétaire (username, email, password, phone) 
            VALUES (:username, :email, :password, :phone)";

    try {
        // Préparer la déclaration
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':phone', $phone);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo 'Client inscrit avec succès.';
        } else {
            echo 'Erreur lors de l\'inscription.';
        }
    } catch (PDOException $e) {
        echo 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage();
    }
} 

// Vérifier si la requête est de type GET pour la connexion
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Récupérer les données envoyées par le client (par exemple via les paramètres URL)
    $email = $_GET['email'];
    $password = $_GET['password'];

    // Validation des données
    if (empty($email) || empty($password)) {
        echo 'Erreur: L\'email et le mot de passe sont requis.';
        exit();
    }

    // Vérification de la validité de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Erreur: Adresse email invalide.';
        exit();
    }

    // Préparer la requête pour vérifier si l'utilisateur existe
    $sql = "SELECT id, username, email, password FROM inscription_propriétaire WHERE email = :email";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Vérifier si l'utilisateur existe
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Vérifier le mot de passe
            if (password_verify($password, $user['password'])) {
                // Connexion réussie
                echo 'Connexion réussie, bienvenue ' . $user['username'] . '!';
            } else {
                // Mot de passe incorrect
                echo 'Mot de passe incorrect.';
            }
        } else {
            // L'email n'existe pas
            echo 'Utilisateur non trouvé.';
        }

    } catch (PDOException $e) {
        echo 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage();
    }
} else {
    // Si la méthode n'est pas POST ni GET, retourner une erreur
    echo 'Méthode HTTP non autorisée. Utilisez POST ou GET.';
}

// Fermer la connexion
$pdo = null;
?>
