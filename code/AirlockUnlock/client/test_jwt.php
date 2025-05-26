<?php
$autoload_path = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($autoload_path)) {
    die("Fichier autoload.php non trouvé ici : $autoload_path\n");
}

require_once $autoload_path;

echo "autoload.php chargé\n";

echo "Classe JWT trouvée : " . (class_exists('Firebase\JWT\JWT') ? 'oui' : 'non') . "\n";
