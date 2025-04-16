<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';

include __DIR__ . '/../../Tapkey/config.php';



echo "Fichier de configuration Tapkey inclus avec succès.";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_proprietaire = $_POST['id_proprietaire'];
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

    try {
        if (!empty($_POST['numero_serie_tapkey'])) {
            $numero = trim($_POST['numero_serie_tapkey']);
            $stmt = $pdo_tapkey->prepare("SELECT COUNT(*) FROM Tapkey.cles_electroniques WHERE numero_serie = :numero");
            $stmt->execute([':numero' => $numero]);
            if ($stmt->fetchColumn() == 0) {
                echo json_encode(['error' => 'Ce numéro de série Tapkey n’existe pas.']);
                exit();
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM biens WHERE numero_serie_tapkey = :numero");
            $stmt->execute([':numero' => $numero]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'Ce numéro de série Tapkey est déjà utilisé.']);
                exit();
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
        exit();
    }
    
    
    

    if (isset($_FILES['photos']) && $_FILES['photos']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($_FILES['photos']['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            echo json_encode(['error' => 'Erreur : seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.']);
            exit();
        }

        $photo_name = uniqid('bien_', true) . '.' . $file_extension;
        $target_directory = '../photos/';

        if (!is_dir($target_directory)) {
            mkdir($target_directory, 0755, true);
        }

        $target_file = $target_directory . $photo_name;
        if (!move_uploaded_file($_FILES['photos']['tmp_name'], $target_file)) {
            echo json_encode(['error' => 'Erreur lors du téléchargement de l\'image.']);
            exit();
        }
    } else {
        $photo_name = null;
    }

    $sql = "INSERT INTO biens (
                id_proprietaire, type_bien, titre, prix_par_nuit, description, surface, nombre_pieces,
                capacite, adresse, photos, wifi, parking, cuisine, tv, climatisation, chauffage,
                serrure_electronique, numero_serie_tapkey
            ) VALUES (
                :id_proprietaire, :type_bien, :titre, :prix_par_nuit, :description, :surface, :nombre_pieces,
                :capacite, :adresse, :photos, :wifi, :parking, :cuisine, :tv, :climatisation, :chauffage,
                :serrure_electronique, :numero_serie_tapkey
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id_proprietaire', $id_proprietaire);
        $stmt->bindParam(':type_bien', $type_bien);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':prix_par_nuit', $prix_par_nuit);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':surface', $surface);
        $stmt->bindParam(':nombre_pieces', $nombre_pieces);
        $stmt->bindParam(':capacite', $capacite);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':photos', $photo_name);
        $stmt->bindParam(':wifi', $wifi);
        $stmt->bindParam(':parking', $parking);
        $stmt->bindParam(':cuisine', $cuisine);
        $stmt->bindParam(':tv', $tv);
        $stmt->bindParam(':climatisation', $climatisation);
        $stmt->bindParam(':chauffage', $chauffage);
        $stmt->bindParam(':serrure_electronique', $serrure_electronique);
        $stmt->bindParam(':numero_serie_tapkey', $numero_serie_tapkey);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Bien publié avec succès !']);
        } else {
            echo json_encode(['error' => 'Erreur lors de la publication du bien.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
    }
}
?>
