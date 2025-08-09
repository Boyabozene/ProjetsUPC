<?php
/**
 * API de gestion de la consommation électrique
 * 
 * Simule la consommation d'un compteur électrique intelligent
 * et enregistre les données en base pour facturation
 * 
 * @author Energy+ Team
 * @version 1.0
 */

// Inclusion des dépendances
require '../config/db.php';      // Connexion à la base de données
require '../includes/functions.php'; // Fonctions utilitaires et sécurité

// Vérification de l'authentification utilisateur
$user_id = is_authenticated();
if (!$user_id) respond(['error' => 'Unauthorized'], 403);

// Simulation de la consommation réaliste d'un compteur électrique
// Génération aléatoire entre 1 et 2 kWh par intervalle de mesure
$conso = rand(1, 2);

// Enregistrement de la consommation en base de données
$stmt = $pdo->prepare("INSERT INTO consumption (user_id, kwh) VALUES (?, ?)");
$stmt->execute([$user_id, $conso]);

// Réponse confirmant l'enregistrement avec la valeur mesurée
respond(['message' => "Consommation enregistrée", 'kwh' => $conso]);
?>
