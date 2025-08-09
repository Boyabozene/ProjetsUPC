<?php
/**
 * API de gestion des factures d'électricité
 * 
 * Gère la génération, la liste, le paiement des factures
 * et la redirection vers le générateur PDF
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

// Récupération de l'action demandée via l'URL
$action = $_GET['action'] ?? '';

// ===== ACTION: GÉNÉRATION D'UNE NOUVELLE FACTURE =====
if ($action === 'generate') {
    // Calcul de la consommation depuis la dernière facture payée
    // ou depuis le début du mois courant
    $stmt = $pdo->prepare("
        SELECT COALESCE(MAX(paid_at), DATE_FORMAT(NOW(), '%Y-%m-01')) as last_reset 
        FROM invoices 
        WHERE user_id = ? AND status = 'paid'
    ");
    $stmt->execute([$user_id]);
    $last_reset = $stmt->fetch(PDO::FETCH_ASSOC)['last_reset'];
    
    // Somme de la consommation depuis la dernière remise à zéro
    $stmt = $pdo->prepare("
        SELECT SUM(kwh) AS total 
        FROM consumption 
        WHERE user_id = ? AND date >= ?
    ");
    $stmt->execute([$user_id, $last_reset]);
    $total_kwh = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Vérification qu'il y a de la consommation à facturer
    if($total_kwh == 0) {
        respond(['error' => 'Aucune consommation à facturer'], 400);
    }
    
    // Calcul du montant (100 FC par kWh)
    $amount = $total_kwh * 100;

    // Création de la nouvelle facture en base
    $stmt = $pdo->prepare("INSERT INTO invoices (user_id, amount, status, issued_at) VALUES (?, ?, 'unpaid', NOW())");
    $stmt->execute([$user_id, $amount]);

    // Réponse confirmant la génération
    respond(['message' => 'Facture générée', 'amount' => $amount, 'kwh' => $total_kwh]);
}

// ===== ACTION: LISTE DES FACTURES DE L'UTILISATEUR =====
if ($action === 'list') {
    // Récupération de toutes les factures de l'utilisateur (les plus récentes en premier)
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY issued_at DESC");
    $stmt->execute([$user_id]);
    respond($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ===== ACTION: PAIEMENT D'UNE FACTURE =====
if ($action === 'pay') {
    // Récupération de l'ID de la facture à payer
    $invoice_id = $_POST['invoice_id'] ?? null;
    
    // Mise à jour du statut de la facture en "payé" avec date de paiement
    $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid', paid_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$invoice_id, $user_id]);
    
    respond(['message' => 'Facture payée']);
}

// ===== ACTION: GÉNÉRATION PDF =====
if ($action === 'pdf') {
    // Récupération de l'ID de la facture pour le PDF
    $invoice_id = $_GET['id'] ?? null;
    if (!$invoice_id) respond(['error' => 'ID facture manquant'], 400);
    
    // Redirection vers le générateur PDF
    header('Location: generate_pdf.php?id=' . $invoice_id);
    exit;
}

// Action non reconnue
respond(['error' => 'Invalid action'], 400);
?>
