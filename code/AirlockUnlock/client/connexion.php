<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';



use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email'], $_POST['mot_de_passe'])) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur: Email et mot de passe sont requis.']);
        exit();
    }

    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    if (empty($email) || empty($mot_de_passe)) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur: L\'email et le mot de passe sont requis.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur: Adresse email invalide.']);
        exit();
    }

    $sql = "SELECT id_client, nom, email, mot_de_passe FROM clients WHERE email = :email";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            if (password_verify($mot_de_passe, $client['mot_de_passe'])) {
                // Connexion réussie, génération du token
                $key = "votre_cle_secrete"; // à garder sécurisé
                $iat = time();
                $exp = $iat + 3600; // 1 heure

                $payload = [
                    'id_client' => $client['id_client'],
                    'nom' => $client['nom'],
                    'role' => 'client',
                    'iat' => $iat,
                    'exp' => $exp
                ];

                $jwt = JWT::encode($payload, $key, 'HS256');

                // ➔ ICI, on renvoie aussi les infos attendues par Android
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Connexion réussie.',
                    'token' => $jwt,
                    'client_id' => $client['id_client'],
                    'nom' => $client['nom'],
                    'email' => $client['email']
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Mot de passe incorrect.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Utilisateur non trouvé.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la connexion : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée. Utilisez POST.']);
}

$pdo = null;
?>
