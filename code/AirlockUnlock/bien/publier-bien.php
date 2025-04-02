<?php
// Connexion à la base de données
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérification de la présence des champs
    if (empty($_POST['property_name']) || empty($_POST['location']) || empty($_POST['description']) || empty($_POST['price']) || empty($_POST['rooms'])) {
        echo 'Tous les champs sont obligatoires.';
        exit();
    }

    // Récupérer les données du formulaire
    $property_name = $_POST['property_name'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $rooms = $_POST['rooms'];

    // Vérification de l'image
    // Vérifier si le fichier est bien téléchargé via HTTP
if (!isset($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
    echo "Erreur : Aucun fichier téléchargé ou problème lors de l'upload.";
    exit();
}

// Définir le dossier d'upload
$upload_dir = 'photo-bien/';

// Vérifier si le dossier d'upload existe, sinon le créer avec les bonnes permissions
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Vérifier si le dossier est accessible en écriture
if (!is_writable($upload_dir)) {
    echo "Erreur : Impossible d'écrire dans le dossier $upload_dir. Vérifiez les permissions.";
    exit();
}

$image_tmp_name = $_FILES['image']['tmp_name'];
$image_name = basename($_FILES['image']['name']);
$image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
$image_path = $upload_dir . $image_name;

// Vérifier l'extension de l'image
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($image_extension, $allowed_extensions)) {
    echo "Erreur : L'image doit être de type JPG, JPEG, PNG ou GIF.";
    exit();
}

// Vérifier la taille de l'image (limite de 2 Mo)
if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
    echo "Erreur : L'image ne doit pas dépasser 2 Mo.";
    exit();
}

// Vérifier si le fichier existe déjà et le renommer si nécessaire
if (file_exists($image_path)) {
    $image_name = time() . '-' . $image_name; // Préfixer avec le timestamp pour éviter les collisions
    $image_path = $upload_dir . $image_name;
}

// Déplacer l'image dans le répertoire de téléchargement
if (!move_uploaded_file($image_tmp_name, $image_path)) {
    echo "Erreur : Impossible de déplacer l'image. Vérifiez les permissions.";
    exit();
}

// Préparer le chemin de l'image pour la base de données
$image_url = $image_path;


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
        $stmt->bindParam(':image_url', $image_url); // URL de l'image

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
