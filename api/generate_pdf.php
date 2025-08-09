<?php
/**
 * Générateur de factures PDF
 * 
 * Génère une facture d'électricité au format HTML/PDF
 * pour les factures payées des utilisateurs authentifiés
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

// Récupération de l'ID de la facture depuis l'URL
$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) respond(['error' => 'ID facture manquant'], 400);

// Vérification que la facture appartient à l'utilisateur et est payée
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND user_id = ? AND status = 'paid'");
$stmt->execute([$invoice_id, $user_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) respond(['error' => 'Facture non trouvée ou non payée'], 404);

// Récupération des informations utilisateur pour la facture
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcul de la consommation basé sur le montant facturé
$kwh_consumed = $invoice['amount'] / 100; // 100 FC par kWh

/**
 * Génère le HTML de la facture d'électricité
 * 
 * @param array $invoice Données de la facture
 * @param array $user Informations utilisateur
 * @param float $kwh_consumed Consommation en kWh
 * @return string HTML formaté de la facture
 */
function generateInvoiceHTML($invoice, $user, $kwh_consumed) {
    // Calcul des dates d'émission et d'échéance
    $issue_date = new DateTime($invoice['issued_at']);
    $due_date = clone $issue_date;
    $due_date->add(new DateInterval('P30D')); // +30 jours pour l'échéance
    
    return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        /* Styles CSS pour la facture */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: white; }
        .header { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; padding: 2rem; }
        .company-info { display: flex; justify-content: space-between; margin-bottom: 2rem; }
        .logo { font-size: 2.5rem; font-weight: bold; }
        .company-details { text-align: right; font-size: 0.9rem; }
        .invoice-title { font-size: 2rem; font-weight: bold; text-align: center; margin: 1rem 0; }
        .invoice-meta { display: flex; justify-content: space-between; font-size: 0.95rem; }
        .content { padding: 2rem; }
        .billing-info { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .billing-section { background: #f8fafc; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #3b82f6; }
        .billing-section h3 { color: #1e3a8a; margin-bottom: 1rem; font-weight: 600; }
        .consumption-details { background: #f0f9ff; border: 2px solid #bfdbfe; border-radius: 8px; padding: 1.5rem; margin: 2rem 0; }
        .consumption-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem; }
        .consumption-item { text-align: center; padding: 1rem; background: white; border-radius: 6px; }
        .consumption-value { font-size: 1.5rem; font-weight: bold; color: #1e3a8a; }
        .consumption-label { font-size: 0.8rem; color: #64748b; text-transform: uppercase; }
        .invoice-table { width: 100%; border-collapse: collapse; margin: 2rem 0; }
        .invoice-table th { background: #1e3a8a; color: white; padding: 1rem; text-align: left; }
        .invoice-table td { padding: 1rem; border-bottom: 1px solid #e2e8f0; }
        .total-section { background: #1e3a8a; color: white; padding: 1.5rem; border-radius: 8px; margin: 2rem 0; }
        .total-item { display: flex; justify-content: space-between; padding: 0.5rem 0; }
        .total-final { font-size: 1.5rem; font-weight: bold; border-top: 2px solid white; padding-top: 1rem; margin-top: 1rem; }
        .footer { background: #f1f5f9; padding: 2rem; text-align: center; color: #64748b; font-size: 0.9rem; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- En-tête de la facture -->
        <div class="header">
            <div class="company-info">
                <div>
                    <div class="logo">Energy+</div>
                    <div>Société Nationale d\'Électricité</div>
                </div>
                <div class="company-details">
                    <div><strong>ENERGY+ S.A.</strong></div>
                    <div>Avenue du Cercle, 123, Gombe</div>
                    <div>Kinshasa, République Démocratique du Congo</div>
                    <div>Tél: +243 81 234 5678</div>
                    <div>Email: factures@energyplus.cd</div>
                    <div>RCCM: CD/KIN/RCCM/23-A-00123</div>
                </div>
            </div>
            
            <div class="invoice-title">FACTURE D\'ÉLECTRICITÉ</div>
            
            <!-- Métadonnées de la facture -->
            <div class="invoice-meta">
                <div>
                    <strong>N° Facture:</strong> 2025-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) . '<br>
                    <strong>Date d\'émission:</strong> ' . $issue_date->format('d/m/Y') . '
                </div>
                <div>
                    <strong>Statut:</strong> PAYÉE<br>
                    <strong>Date de paiement:</strong> ' . (new DateTime($invoice['paid_at']))->format('d/m/Y') . '
                </div>
            </div>
        </div>

        <!-- Contenu principal de la facture -->
        <div class="content">
            <!-- Informations de facturation -->
            <div class="billing-info">
                <div class="billing-section">
                    <h3>Informations Client</h3>
                    <div><strong>' . htmlspecialchars($user['name']) . '</strong></div>
                    <div>N° Client: ' . str_pad($user['id'], 9, '0', STR_PAD_LEFT) . '</div>
                    <div>Email: ' . htmlspecialchars($user['email']) . '</div>
                    <div>Kinshasa, RDC</div>
                </div>
                
                <div class="billing-section">
                    <h3>Installation</h3>
                    <div><strong>Compteur N°:</strong> KIN-' . str_pad($user['id'], 9, '0', STR_PAD_LEFT) . '</div>
                    <div><strong>Type:</strong> Résidentiel</div>
                    <div><strong>Puissance souscrite:</strong> 5 kVA</div>
                    <div><strong>Tarif:</strong> Particulier Standard</div>
                </div>
            </div>

            <!-- Détail de la consommation -->
            <div class="consumption-details">
                <h3 style="color: #1e3a8a; margin-bottom: 1rem; text-align: center;">Détail de la Consommation</h3>
                <div class="consumption-grid">
                    <div class="consumption-item">
                        <div class="consumption-value">' . number_format($kwh_consumed, 1) . '</div>
                        <div class="consumption-label">kWh Consommés</div>
                    </div>
                    <div class="consumption-item">
                        <div class="consumption-value">31</div>
                        <div class="consumption-label">Jours</div>
                    </div>
                    <div class="consumption-item">
                        <div class="consumption-value">' . number_format($kwh_consumed/31, 2) . '</div>
                        <div class="consumption-label">kWh/jour</div>
                    </div>
                    <div class="consumption-item">
                        <div class="consumption-value">100</div>
                        <div class="consumption-label">FC/kWh</div>
                    </div>
                </div>
            </div>

            <!-- Tableau détaillé de facturation -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Montant (FC)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Consommation électrique</td>
                        <td>' . number_format($kwh_consumed, 1) . ' kWh</td>
                        <td>100 FC</td>
                        <td>' . number_format($invoice['amount'], 0) . '</td>
                    </tr>
                    <tr>
                        <td>Frais de service</td>
                        <td>1</td>
                        <td>1,500 FC</td>
                        <td>1,500</td>
                    </tr>
                </tbody>
            </table>

            <!-- Section des totaux -->
            <div class="total-section">
                <div class="total-item">
                    <span>Sous-total:</span>
                    <span>' . number_format($invoice['amount'] + 1500, 0) . ' FC</span>
                </div>
                <div class="total-item">
                    <span>TVA (16%):</span>
                    <span>' . number_format(($invoice['amount'] + 1500) * 0.16, 0) . ' FC</span>
                </div>
                <div class="total-item total-final">
                    <span>TOTAL PAYÉ:</span>
                    <span>' . number_format(($invoice['amount'] + 1500) * 1.16, 0) . ' FC</span>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p><strong>Energy+ - Éclairons l\'avenir ensemble</strong></p>
            <p>Service Client: 0800 123 456 (gratuit) | Website: www.energyplus.cd</p>
            <p>Facture générée le ' . date('d/m/Y H:i') . '</p>
        </div>
    </div>
</body>
</html>';
}

// Génération du HTML de la facture
$html = generateInvoiceHTML($invoice, $user, $kwh_consumed);

// Note: Pour une utilisation en production, installer DomPDF via Composer
// require_once 'vendor/autoload.php';
// use Dompdf\Dompdf;
// $dompdf = new Dompdf();
// $dompdf->loadHtml($html);
// $dompdf->setPaper('A4', 'portrait');
// $dompdf->render();
// $dompdf->stream('Facture_Energy+_' . $invoice['id'] . '.pdf');

// En attendant l'installation de DomPDF, affichage HTML direct
header('Content-Type: text/html; charset=utf-8');
echo $html;
?>