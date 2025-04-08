<?php
include '../config.php'; // Assurez-vous que le fichier config.php est dans le même répertoire

// Démarrer la session pour garder la trace de l'utilisateur
session_start();

// Vérifier si le formulaire de connexion a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier si les données nécessaires existent
    if (!isset($_POST['email'], $_POST['mot_de_passe'])) {
        echo 'Erreur: Email et mot de passe sont requis.';
        exit();
    }

    // Récupérer les données envoyées par le client
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

    // Préparer la requête pour vérifier si le client existe
    $sql = "SELECT id_client, nom, email, mot_de_passe FROM clients WHERE email = :email";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Vérifier si l'utilisateur existe
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            // Vérifier le mot de passe
            if (password_verify($mot_de_passe, $client['mot_de_passe'])) {
                // Connexion réussie, démarrer une session et rediriger
                $_SESSION['client_id'] = $client['id_client'];
                $_SESSION['client_nom'] = $client['nom'];

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
