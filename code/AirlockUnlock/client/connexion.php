<?php
include '../config.php';

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
                session_start();
                $_SESSION['client_id'] = $client['id_client'];
                $_SESSION['client_nom'] = $client['nom'];

                // Connexion réussie
                echo json_encode(['status' => 'success', 'message' => 'Connexion réussie.', 'client_id' => $client['id_client'], 'nom' => $client['nom']]);
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
