<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS : attention à remplacer http://localhost:3000 par l'URL de ton frontend
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include '../config.php';
include __DIR__ . '/../../Tapkey/config.php';

require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$key = getenv('JWT_SECRET_KEY');
if (!$key) {
    echo json_encode(['error' => 'Clé secrète JWT non définie.']);
    exit();
}

$token = null;
if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];
} else {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    echo json_encode(['error' => 'Token JWT manquant.']);
    exit();
}

try {
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    $id_proprietaire = $decoded->id_proprietaire;
} catch (Exception $e) {
    echo json_encode(['error' => 'Token invalide : ' . $e->getMessage()]);
    exit();
}


// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification des champs obligatoires
    $requiredFields = [
        'type_bien', 'titre', 'prix_par_nuit', 'description',
        'surface', 'nombre_pieces', 'capacite', 'adresse', 'numero_serie_tapkey'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            echo json_encode(['error' => "Le champ '$field' est obligatoire."]);
            exit();
        }
    }

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

    // Vérification Tapkey
    try {
        if (!empty($numero_serie_tapkey)) {
            $numero = trim($numero_serie_tapkey);
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
        echo json_encode(['error' => 'Erreur PDO Tapkey : ' . $e->getMessage()]);
        exit();
    }

    
    $photo_name = null;
    $image_url = null;

    if (isset($_FILES['photos']) && $_FILES['photos']['error'] === 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['photos']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['error' => 'Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.']);
            exit();
        }

        if (!getimagesize($_FILES['photos']['tmp_name'])) {
            echo json_encode(['error' => 'Le fichier n\'est pas une image valide.']);
            exit();
        }

        $photo_name = uniqid('bien_', true) . '.' . $file_extension;
        $target_directory = __DIR__ . '/photos/';

        if (!is_dir($target_directory) && !mkdir($target_directory, 0775, true)) {
            echo json_encode(['error' => 'Erreur création dossier photos.']);
            exit();
        }

        if (!is_writable($target_directory)) {
            echo json_encode(['error' => 'Le dossier photos n’est pas accessible en écriture.']);
            exit();
        }

        $target_file = $target_directory . $photo_name;

        if (!move_uploaded_file($_FILES['photos']['tmp_name'], $target_file)) {
            echo json_encode(['error' => 'Erreur lors de l\'upload.']);
            exit();
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = "$protocol://$host" . dirname($_SERVER['PHP_SELF']) . '/photos/';
        $image_url = $baseUrl . $photo_name;
    }

    // Insertion en base
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

        $stmt->bindParam(':id_proprietaire', $id_proprietaire, PDO::PARAM_INT);
        $stmt->bindParam(':type_bien', $type_bien);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':prix_par_nuit', $prix_par_nuit);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':surface', $surface);
        $stmt->bindParam(':nombre_pieces', $nombre_pieces, PDO::PARAM_INT);
        $stmt->bindParam(':capacite', $capacite);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':photos', $photo_name);
        $stmt->bindParam(':wifi', $wifi, PDO::PARAM_INT);
        $stmt->bindParam(':parking', $parking, PDO::PARAM_INT);
        $stmt->bindParam(':cuisine', $cuisine, PDO::PARAM_INT);
        $stmt->bindParam(':tv', $tv, PDO::PARAM_INT);
        $stmt->bindParam(':climatisation', $climatisation, PDO::PARAM_INT);
        $stmt->bindParam(':chauffage', $chauffage, PDO::PARAM_INT);
        $stmt->bindParam(':serrure_electronique', $serrure_electronique, PDO::PARAM_INT);
        $stmt->bindParam(':numero_serie_tapkey', $numero_serie_tapkey);

        if ($stmt->execute()) {
            echo json_encode([
                'message' => 'Votre bien a été publié avec succès !',
            ]);
        } else {
            echo json_encode(['error' => 'Erreur lors de la publication du bien.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur PDO : ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['error' => 'Méthode HTTP non autorisée']);
}
?>
