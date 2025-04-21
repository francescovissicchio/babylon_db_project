<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Medico') {
    echo "Accesso negato.";
    exit;
}

$id_medico = $_SESSION['id_utente'];

$stmt = $conn->prepare("
    SELECT v.id_visita, v.data_visita, u.nome AS nome_paziente, v.esito_visita
    FROM visita v
    JOIN paziente p ON v.id_paziente = p.id_paziente
    JOIN utente u ON u.id_utente = p.id_paziente
    WHERE v.id_medico = ?
    ORDER BY v.data_visita DESC
");
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$visite = $stmt->get_result();
?>

<h2>Le tue visite</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>Data Visita</th>
        <th>Paziente</th>
        <th>Stato</th>
        <th>Esito</th>
        <th>Azioni</th>
    </tr>
    <?php while ($row = $visite->fetch_assoc()): ?>
        <tr>
            <td><?= $row['data_visita'] ?></td>
            <td><?= htmlspecialchars($row['nome_paziente']) ?></td>
            <td style="color:<?= $row['esito_visita'] ? 'green' : 'red' ?>">
                <?= $row['esito_visita'] ? 'Completata' : 'In attesa di esito' ?>
            </td>
            <td><?= $row['esito_visita'] ? htmlspecialchars($row['esito_visita']) : '—' ?></td>
            <td><a href="visita_esito.php?id_visita=<?= $row['id_visita'] ?>">Modifica Esito</a></td>
        </tr>
    <?php endwhile; ?>
</table>
<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>
