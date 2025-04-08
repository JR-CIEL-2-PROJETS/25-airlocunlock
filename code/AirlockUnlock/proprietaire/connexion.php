<?php
include '../config.php'; // Assurez-vous que le fichier config.php est dans le même répertoire

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier si les données nécessaires existent pour la connexion
    if (!isset($_POST['email'], $_POST['mot_de_passe'])) {
        echo 'Erreur: Email et mot de passe sont requis.';
        exit();
    }

    // Récupérer les données envoyées par le propriétaire
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Validation des données
    if (empty($email) || empty($mot_de_passe)) {
        echo 'Erreur: L\'email et le mot de passe sont requis.';
        exit();
    }

    // Vérification de la validité de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Erreur: Adresse email invalide.';
        exit();
    }

    // Préparer la requête pour vérifier si le propriétaire existe
    $sql = "SELECT id_proprietaire, nom, email, mot_de_passe FROM proprietaires WHERE email = :email";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Vérifier si l'utilisateur existe
        $proprietaire = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($proprietaire) {
            // Vérifier le mot de passe
            if (password_verify($mot_de_passe, $proprietaire['mot_de_passe'])) {
                // Connexion réussie
                echo 'Connexion réussie, bienvenue ' . $proprietaire['nom'] . '!';
                // Rediriger vers une page protégée (par exemple : tableau_de_bord.php)
                session_start();
                $_SESSION['proprietaire_id'] = $proprietaire['id_proprietaire'];
                $_SESSION['proprietaire_nom'] = $proprietaire['nom'];
                header('Location: tableau_de_bord.php');
                exit();
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
}
?>