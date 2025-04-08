<?php
include '../config.php'; // Assurez-vous que le fichier de configuration est bien inclus

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données envoyées par le formulaire
    $email = $_POST['email'];
    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirmer_mdp = $_POST['confirmer_mdp'];

    // Vérifier si les champs sont remplis
    if (empty($email) || empty($nouveau_mdp) || empty($confirmer_mdp)) {
        echo "Tous les champs sont requis.";
        exit();
    }

    // Vérifier si les mots de passe correspondent
    if ($nouveau_mdp !== $confirmer_mdp) {
        echo "Les mots de passe ne correspondent pas.";
        exit();
    }

    // Vérifier si l'email est valide
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalide.";
        exit();
    }

    // Préparer la requête pour vérifier si l'email existe dans la base de données
    $sql = "SELECT id_proprietaire FROM proprietaires WHERE email = :email";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Vérifier si l'utilisateur existe
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            // Hacher le nouveau mot de passe
            $nouveau_mdp_hash = password_hash($nouveau_mdp, PASSWORD_BCRYPT);

            // Mettre à jour le mot de passe dans la base de données
            $update_sql = "UPDATE proprietaires SET mot_de_passe = :nouveau_mdp WHERE email = :email";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':nouveau_mdp', $nouveau_mdp_hash);
            $update_stmt->bindParam(':email', $email);

            if ($update_stmt->execute()) {
                echo "Mot de passe modifié avec succès.";
            } else {
                echo "Erreur lors de la modification du mot de passe.";
            }
        } else {
            echo "Aucun utilisateur trouvé avec cet email.";
        }
    } catch (PDOException $e) {
        echo 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage();
    }
} else {
    echo "Méthode HTTP non autorisée.";
}

// Fermer la connexion à la base de données
$pdo = null;
?>
