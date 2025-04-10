<?php
include '../config.php'; // Connexion à la base de données principale

// Inclure le fichier config.php pour la base Tapkey (assurez-vous que le chemin est correct)
include '../Tapkey/config.php'; // Chemin vers le config.php de Tapkey

// Traitement du formulaire d'ajout de bien
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données envoyées par le formulaire
    $id_proprietaire = $_POST['id_proprietaire'];  // ID du propriétaire passé via le formulaire (ou autre méthode)
    $type_bien = $_POST['type_bien'];
    $titre = $_POST['titre'];
    $prix_par_nuit = $_POST['prix_par_nuit'];
    $description = $_POST['description'];
    $surface = $_POST['surface'];
    $nombre_pieces = $_POST['nombre_pieces'];
    $capacite = $_POST['capacite'];
    $adresse = $_POST['adresse'];
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $parking = isset($_POST['parking']) ? 1 : 0;
    $cuisine = isset($_POST['cuisine']) ? 1 : 0;
    $tv = isset($_POST['tv']) ? 1 : 0;
    $climatisation = isset($_POST['climatisation']) ? 1 : 0;
    $chauffage = isset($_POST['chauffage']) ? 1 : 0;
    $serrure_electronique = isset($_POST['serrure_electronique']) ? 1 : 0;
    $numero_serie_tapkey = $_POST['numero_serie_tapkey'];

    // Vérifier si le numéro de série Tapkey est fourni
    if (!empty($numero_serie_tapkey)) {
        // Vérifier si ce numéro de série existe déjà dans la base de données Tapkey
        $sql_check = "SELECT COUNT(*) FROM tapkey_cle WHERE numero_serie = :numero_serie_tapkey"; // Table tapkey_cle dans la base Tapkey
        $stmt_check = $pdo_tapkey->prepare($sql_check);
        $stmt_check->bindParam(':numero_serie_tapkey', $numero_serie_tapkey);
        $stmt_check->execute();

        $count = $stmt_check->fetchColumn();
        if ($count > 0) {
            echo 'Ce numéro de série Tapkey est déjà utilisé pour un autre bien dans la base Tapkey.';
            exit();
        }
    }

    // Vérifier si une image a été téléchargée
    if (isset($_FILES['photos']) && $_FILES['photos']['error'] == 0) {
        // Vérifier l'extension de l'image
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($_FILES['photos']['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            echo 'Erreur : seul les fichiers JPG, JPEG, PNG et GIF sont autorisés.';
            exit();
        }

        // Générer un nom unique pour l'image téléchargée
        $photo_name = uniqid('bien_', true) . '.' . $file_extension;
        $target_directory = '../photos/'; // Dossier où l'image sera stockée

        // Créer le dossier si nécessaire
        if (!is_dir($target_directory)) {
            mkdir($target_directory, 0755, true);
        }

        // Déplacer l'image téléchargée dans le dossier
        $target_file = $target_directory . $photo_name;
        if (!move_uploaded_file($_FILES['photos']['tmp_name'], $target_file)) {
            echo 'Erreur lors du téléchargement de l\'image.';
            exit();
        }
    } else {
        // Si aucune image n'a été téléchargée
        $photo_name = null;
    }

    // Préparer la requête d'insertion dans la base de données principale
    $sql = "INSERT INTO biens (id_proprietaire, type_bien, titre, prix_par_nuit, description, surface, nombre_pieces, capacite, adresse, photos, wifi, parking, cuisine, tv, climatisation, chauffage, serrure_electronique, numero_serie_tapkey) 
            VALUES (:id_proprietaire, :type_bien, :titre, :prix_par_nuit, :description, :surface, :nombre_pieces, :capacite, :adresse, :photos, :wifi, :parking, :cuisine, :tv, :climatisation, :chauffage, :serrure_electronique, :numero_serie_tapkey)";

    try {
        // Préparer la déclaration pour la base principale
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':id_proprietaire', $id_proprietaire);
        $stmt->bindParam(':type_bien', $type_bien);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':prix_par_nuit', $prix_par_nuit);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':surface', $surface);
        $stmt->bindParam(':nombre_pieces', $nombre_pieces);
        $stmt->bindParam(':capacite', $capacite);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':photos', $photo_name); // Lier le nom de l'image téléchargée
        $stmt->bindParam(':wifi', $wifi);
        $stmt->bindParam(':parking', $parking);
        $stmt->bindParam(':cuisine', $cuisine);
        $stmt->bindParam(':tv', $tv);
        $stmt->bindParam(':climatisation', $climatisation);
        $stmt->bindParam(':chauffage', $chauffage);
        $stmt->bindParam(':serrure_electronique', $serrure_electronique);
        $stmt->bindParam(':numero_serie_tapkey', $numero_serie_tapkey);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo 'Bien publié avec succès !';
        } else {
            echo 'Erreur lors de la publication du bien.';
        }
    } catch (PDOException $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
}
?>
