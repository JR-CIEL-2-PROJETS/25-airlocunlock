<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérifie que les données nécessaires sont présentes
    if (!isset($_POST['email'], $_POST['mot_de_passe'])) {
        echo json_encode(['status' => 'error', 'message' => 'Email et mot de passe requis.']);
        exit();
    }

    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Validation de base
    if (empty($email) || empty($mot_de_passe)) {
        echo json_encode(['status' => 'error', 'message' => 'Champs obligatoires manquants.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Email invalide.']);
        exit();
    }

    try {
        // Requête SQL pour récupérer les infos du propriétaire
        $sql = "SELECT id_proprietaire, nom, email, mot_de_passe FROM proprietaires WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $proprietaire = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($proprietaire) {
            // Vérification du mot de passe
            if (password_verify($mot_de_passe, $proprietaire['mot_de_passe'])) {

                // Génération du token JWT
                $key = "votre_cle_secrete"; // Mets ici ta vraie clé secrète
                $iat = time();
                $exp = $iat + 3600; // expire en 1h

                $payload = [
                    'id_proprietaire' => $proprietaire['id_proprietaire'],
                    'nom' => $proprietaire['nom'],
                    'email' => $proprietaire['email'],
                    'role' => 'proprietaire',
                    'iat' => $iat,
                    'exp' => $exp
                ];

                $token = JWT::encode($payload, $key, 'HS256');

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Connexion réussie.',
                    'token' => $token
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Mot de passe incorrect.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Aucun compte trouvé avec cet email.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée.']);
}

$pdo = null;
?>
