<?php
session_start();
include '../config.php'; // Assurez-vous que vous incluez votre config.php pour la connexion à la base de données principale

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['proprietaire_id'])) {
    echo 'Vous devez être connecté pour voir vos publications.';
    exit();
}

// Récupérer l'ID du propriétaire connecté
$id_proprietaire = $_SESSION['proprietaire_id'];

// Requête pour récupérer les publications (biens) du propriétaire connecté
$sql = "SELECT * FROM biens WHERE id_proprietaire = :id_proprietaire";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_proprietaire', $id_proprietaire);
$stmt->execute();

// Vérifier s'il y a des biens publiés
$biens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($biens) > 0) {
    // Afficher les biens publiés
    echo '<h1>Mes publications</h1>';
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Titre</th>';
    echo '<th>Type</th>';
    echo '<th>Prix par nuit</th>';
    echo '<th>Surface</th>';
    echo '<th>Capacité</th>';
    echo '<th>Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Boucle pour afficher chaque bien
    foreach ($biens as $bien) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($bien['titre']) . '</td>';
        echo '<td>' . htmlspecialchars($bien['type_bien']) . '</td>';
        echo '<td>' . htmlspecialchars($bien['prix_par_nuit']) . ' €</td>';
        echo '<td>' . htmlspecialchars($bien['surface']) . ' m²</td>';
        echo '<td>' . htmlspecialchars($bien['capacite']) . ' personnes</td>';
        echo '<td>';
        echo '<a href="modifier_bien.php?id=' . $bien['id_bien'] . '">Modifier</a> | ';
        echo '<a href="supprimer_bien.php?id=' . $bien['id_bien'] . '">Supprimer</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo 'Aucune publication trouvée.';
}

?>
