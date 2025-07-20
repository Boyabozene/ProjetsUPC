
<?php
require '../config/db.php';
require '../includes/functions.php';

$user_id = is_authenticated();
if (!$user_id) respond(['error' => 'Unauthorized'], 403);

$action = $_GET['action'] ?? '';

if ($action === 'generate') {
    $stmt = $pdo->prepare("SELECT SUM(kwh) AS total FROM consumption WHERE user_id = ? AND MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())");
    $stmt->execute([$user_id]);
    $total_kwh = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $amount = $total_kwh * 100; // 100 FC per kWh

    $stmt = $pdo->prepare("INSERT INTO invoices (user_id, amount, status, issued_at) VALUES (?, ?, 'unpaid', NOW())");
    $stmt->execute([$user_id, $amount]);

    respond(['message' => 'Invoice generated', 'amount' => $amount]);
}

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY issued_at DESC");
    $stmt->execute([$user_id]);
    respond($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($action === 'pay') {
    $invoice_id = $_POST['invoice_id'] ?? null;
    $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid', paid_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$invoice_id, $user_id]);
    respond(['message' => 'Invoice paid']);
}

respond(['error' => 'Invalid action'], 400);
?>
