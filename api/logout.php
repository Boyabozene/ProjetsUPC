<?php
/**
 * API de déconnexion utilisateur
 * 
 * Détruit complètement la session utilisateur de manière sécurisée
 * en supprimant toutes les données de session et les cookies
 * 
 * @author Energy+ Team
 * @version 1.0
 */

// Inclusion des dépendances
require '../config/db.php';      // Connexion à la base de données
require '../includes/functions.php'; // Fonctions utilitaires et sécurité

// Démarrer la session de manière sécurisée
start_session();

// Destruction complète et sécurisée de la session
if (session_status() === PHP_SESSION_ACTIVE) {
    // Étape 1: Vider toutes les variables de session
    $_SESSION = array();
    
    // Étape 2: Supprimer le cookie de session côté client
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Étape 3: Détruire la session côté serveur
    session_destroy();
}

// Enregistrement de l'événement de déconnexion pour audit de sécurité
secure_log('INFO', 'Utilisateur déconnecté');

// Réponse JSON confirmant la déconnexion
respond(['message' => 'Déconnexion réussie']);
?>