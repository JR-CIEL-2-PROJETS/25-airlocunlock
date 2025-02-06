<?php
// Connexion à la base de données
include '../config.php';

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $property_name = $_POST['property_name'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $rooms = $_POST['rooms'];

    // Vérification de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Répertoire où les images seront stockées
        $upload_dir = 'photo-bien/';
        
        // Assurez-vous que le répertoire existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);  
        }

        // Informations sur l'image
        $image_name = basename($_FILES['image']['name']);
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_path = $upload_dir . $image_name;

        // Déplacer l'image dans le répertoire de téléchargement
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            // Success, l'image est maintenant dans le dossier 'uploads/'
        } else {
            echo "Erreur lors du téléchargement de l'image.";
            exit();
        }
    } else {
        $image_path = null; // Pas d'image envoyée
    }

    // Préparer la requête d'insertion
    $sql = "INSERT INTO publier_bien (property_name, location, description, price, rooms, image_url)
            VALUES (:property_name, :location, :description, :price, :rooms, :image_url)";

    try {
        // Préparer la déclaration
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':property_name', $property_name);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':rooms', $rooms);
        $stmt->bindParam(':image_url', $image_path); // URL de l'image

        // Exécuter la requête
        if ($stmt->execute()) {
            // Afficher une alerte après la publication réussie
            echo "<script>alert('Bien publié avec succès !');</script>";
        } else {
            echo 'Erreur lors de la publication du bien.';
        }
    } catch (PDOException $e) {
        echo 'Erreur lors de l\'exécution de la requête : ' . $e->getMessage();
    }
} else {
    echo 'Aucune donnée soumise.';
}
?>
