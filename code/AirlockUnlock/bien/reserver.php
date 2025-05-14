<?php
session_start();
include '../config.php';

// Vérifie que toutes les données nécessaires sont là
if (
    !isset($_POST['id_bien']) ||
    !isset($_POST['date_arrivee']) ||
    !isset($_POST['date_depart']) ||
    !isset($_POST['nombre_personnes'])
) {
    echo json_encode(['error' => 'Données de réservation incomplètes.']);
    exit();
}

$id_bien = $_POST['id_bien'];
$date_arrivee = $_POST['date_arrivee'];
$date_depart = $_POST['date_depart'];
$nombre_personnes = $_POST['nombre_personnes'];

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['id_client']) || !isset($_SESSION['email'])) {
    echo json_encode(['error' => 'Utilisateur non connecté.']);
    exit();
}

$id_client = $_SESSION['id_client'];
$email_client = $_SESSION['email'];

// Vérifier que le bien existe
$stmt = $pdo->prepare("SELECT COUNT(*) FROM biens WHERE id_bien = :id_bien");
$stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    echo json_encode(['error' => 'Le bien spécifié n\'existe pas.']);
    exit();
}

// Validation des dates
if (strtotime($date_arrivee) >= strtotime($date_depart)) {
    echo json_encode(['error' => 'La date d\'arrivée doit être antérieure à la date de départ.']);
    exit();
}

// Insertion de la réservation
$sql = "INSERT INTO reservations (id_client, id_bien, date_arrivee, date_depart, nombre_personnes, statut) 
        VALUES (:id_client, :id_bien, :date_arrivee, :date_depart, :nombre_personnes, 'confirmée')";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
    $stmt->bindParam(':date_arrivee', $date_arrivee);
    $stmt->bindParam(':date_depart', $date_depart);
    $stmt->bindParam(':nombre_personnes', $nombre_personnes, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Envoi d'email de confirmation
        $sujet = "Confirmation de votre réservation";
        $message = "Bonjour,\n\nVotre réservation a bien été confirmée :\n" .
                   "- Bien n°$id_bien\n" .
                   "- Dates : du $date_arrivee au $date_depart\n" .
                   "- Nombre de personnes : $nombre_personnes\n\n" .
                   "Merci pour votre confiance.\n\n" .
                   "Lien de téléchargement de l'application Airlockunlock :\n" .
                   "👉 https://airlockunlock.com/download\n\n" .
                   "Cordialement,\nL'équipe de réservation.";
        $headers = "From: reservation@airlockunlock.com\r\n" .
                   "Reply-To: contact@airlockunlock.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // Envoi de l'e-mail
        mail($email_client, $sujet, $message, $headers);

        echo json_encode(['success' => 'Réservation confirmée avec succès et email envoyé.']);
    } else {
        echo json_encode(['error' => 'Erreur lors de l\'enregistrement de la réservation.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur SQL : ' . $e->getMessage()]);
}
?>
