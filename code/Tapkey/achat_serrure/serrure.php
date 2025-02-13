<?php
// Inclure le fichier de configuration pour la connexion à la base de données
require_once 'config.php';

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Préparation de la requête pour insérer un nouvel achat
        $stmt = $pdo->prepare("INSERT INTO achat_serrure (id) VALUES (NULL)");
        
        // Exécution de la requête
        $stmt->execute();
        
        // Redirection avec un paramètre de succès
        header("Location: serrure.html?success=1");
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de l'achat : " . $e->getMessage();
    }
}
?>
