<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    echo "Accesso negato.";
    exit;
}

echo "<h2>📋 Elenco completo delle visite</h2>";

$query = "
    SELECT v.id_visita, v.data_visita, v.esito_visita,
           paz.nome AS paziente,
           med.nome AS medico,
           c.sintomi_riportati
    FROM visita v
    JOIN utente paz ON v.id_paziente = paz.id_utente
    JOIN utente med ON v.id_medico = med.id_utente
    LEFT JOIN chatbot c ON v.id_chatbot = c.id_chatbot
    ORDER BY v.data_visita DESC
";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='6' cellspacing='0'>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Paziente</th>
                <th>Medico</th>
                <th>Sintomi (da Chatbot)</th>
                <th>Esito</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id_visita']}</td>
                <td>{$row['data_visita']}</td>
                <td>" . htmlspecialchars($row['paziente']) . "</td>
                <td>" . htmlspecialchars($row['medico']) . "</td>
                <td>" . htmlspecialchars($row['sintomi_riportati']) . "</td>
                <td>" . nl2br(htmlspecialchars($row['esito_visita'])) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nessuna visita trovata.</p>";
}
?>

<a href="index.php" style="display:inline-block; margin-top: 20px; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>
