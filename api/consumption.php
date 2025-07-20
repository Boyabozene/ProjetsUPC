
<?php
require '../config/db.php';
require '../includes/functions.php';

$user_id = is_authenticated();
if (!$user_id) respond(['error' => 'Unauthorized'], 403);

$conso = rand(5, 20); // kWh

$stmt = $pdo->prepare("INSERT INTO consumption (user_id, kwh) VALUES (?, ?)");
$stmt->execute([$user_id, $conso]);

respond(['message' => "Consommation enregistrée", 'kwh' => $conso]);
?>
