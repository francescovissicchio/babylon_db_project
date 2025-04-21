<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Medico') {
    echo "Accesso negato.";
    exit;
}

$id_visita = $_GET['id_visita'] ?? null;

if (!$id_visita) {
    echo "ID visita mancante.";
    exit;
}

// Recupero visita
$stmt = $conn->prepare("
    SELECT u.nome AS paziente, v.data_visita, v.esito_visita
    FROM visita v
    JOIN paziente p ON v.id_paziente = p.id_paziente
    JOIN utente u ON u.id_utente = p.id_paziente
    WHERE v.id_visita = ?
");
$stmt->bind_param("i", $id_visita);
$stmt->execute();
$visita = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esito = $_POST['esito'];

    $stmt = $conn->prepare("UPDATE visita SET esito_visita = ? WHERE id_visita = ?");
    $stmt->bind_param("si", $esito, $id_visita);
    $stmt->execute();

    echo "<p>Esito aggiornato con successo.</p>";
    echo "<a href='medico_dashboard.php'>Torna alla dashboard</a>";
    exit;
}
?>

<h2>Esito visita</h2>
<p><strong>Paziente:</strong> <?= htmlspecialchars($visita['paziente']) ?></p>
<p><strong>Data visita:</strong> <?= $visita['data_visita'] ?></p>

<form method="POST">
    <label for="esito">Esito della visita:</label><br>
    <textarea name="esito" rows="5" cols="60" required><?= htmlspecialchars($visita['esito_visita']) ?></textarea><br><br>
    <button type="submit">Salva Esito</button>
</form>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>