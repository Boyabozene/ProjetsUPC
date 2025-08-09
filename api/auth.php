<?php
/**
 * API d'authentification des utilisateurs
 * 
 * Gère la connexion des utilisateurs avec validation
 * des identifiants et création de session sécurisée
 * 
 * @author Energy+ Team
 * @version 1.0
 */

// Inclusion des dépendances
require '../config/db.php';      // Connexion à la base de données
require '../includes/functions.php'; // Fonctions utilitaires et sécurité

// Démarrer la session de manière sécurisée
session_start();

// Récupération et décodage des données JSON envoyées
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';       // Email utilisateur (vide par défaut)
$password = $data['password'] ?? ''; // Mot de passe (vide par défaut)

// Recherche de l'utilisateur en base de données
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification des identifiants
if ($user && password_verify($password, $user['password'])) {
    // Connexion réussie : création de la session utilisateur
    $_SESSION['user_id'] = $user['id'];
    
    // Réponse de succès
    respond(['message' => 'Login successful']);
} else {
    // Identifiants incorrects : réponse d'erreur
    respond(['error' => 'Invalid credentials'], 401);
}
?>
