<?php

header('Content-Type: application/json');

ini_set('display_errors', 1);

error_reporting(E_ALL);


include '../config.php';

require_once __DIR__ . '/../../vendor/autoload.php';


use \Firebase\JWT\JWT;

use \Firebase\JWT\Key;


header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

echo json_encode(['status' => 'error', 'message' => 'Méthode HTTP non autorisée.']);

exit;

}


// Récupération du token : cookie ou header Authorization

$token = null;


// 1. Cookie

if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {

$token = $_COOKIE['auth_token'];

}


// 2. Header Authorization

if (!$token) {

$headers = getallheaders();

if (isset($headers['Authorization'])) {

$authHeader = $headers['Authorization'];

} elseif (isset($headers['authorization'])) {

$authHeader = $headers['authorization'];

}


if (isset($authHeader) && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {

$token = $matches[1];

}

}


if (!$token) {

echo json_encode(['status' => 'error', 'message' => 'Token manquant.']);

exit;

}


try {

$key = getenv('JWT_SECRET_KEY');

if (!$key) {

echo json_encode(['status' => 'error', 'message' => 'Erreur : Clé secrète manquante.']);

exit();

}


$decoded = JWT::decode($token, new Key($key, 'HS256'));

$id_client = $decoded->id_client;


// 🔧 Ajout de numero_serie_tapkey à la sélection SQL

$sql = "SELECT

r.id_reservation,

r.date_arrivee,

r.date_depart,

r.nombre_personnes,

r.statut,

r.id_bien,

b.titre,

b.photos,

b.numero_serie_tapkey

FROM reservations r

INNER JOIN biens b ON r.id_bien = b.id_bien

WHERE r.id_client = :id_client

ORDER BY r.date_arrivee DESC";


$stmt = $pdo->prepare($sql);

$stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);

$stmt->execute();

$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);


$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST'];

$baseUrl = $protocol . $host . "/AirlockUnlock/bien/photos/";


foreach ($reservations as &$reservation) {

$reservation['photo_url'] = !empty($reservation['photos']) ? $baseUrl . $reservation['photos'] : null;

unset($reservation['photos']); // facultatif

}


echo json_encode([

'status' => 'success',

'reservations' => $reservations

]);

} catch (Exception $e) {

echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);

}

?>