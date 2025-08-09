<?php
/**
 * Configuration de la base de données - Energy+ App
 * 
 * Ce fichier établit la connexion PDO vers MySQL pour l'application
 * de gestion de consommation électrique Energy+
 */

// Paramètres de connexion à la base de données
$host = 'localhost';        // Serveur de base de données
$db   = 'energy_db';        // Nom de la base de données
$user = 'root';             // Utilisateur MySQL
$pass = '';                 // Mot de passe MySQL (vide en local)

try {
    // Création de l'instance PDO avec configuration UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    
    // Configuration PDO pour les erreurs en mode exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // En cas d'erreur de connexion, retourner une erreur 500 et arrêter l'exécution
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}
?>
