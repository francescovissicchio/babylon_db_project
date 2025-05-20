<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    echo "Accesso negato.";
    exit;
}


$query = "
     SELECT 
        v.id_visita,
        v.data_visita,
        v.esito_visita,
        CONCAT(paz_utente.nome, ' ', paz_utente.cognome) AS paziente,
        CONCAT(med_utente.nome, ' ', med_utente.cognome) AS medico,
        c.sintomi_riportati
    FROM visita v
    JOIN paziente p ON v.id_paziente = p.id_paziente
    JOIN utente paz_utente ON p.id_paziente = paz_utente.id_utente
    JOIN medico m ON v.id_medico = m.id_medico
    JOIN utente med_utente ON m.id_medico = med_utente.id_utente
    LEFT JOIN chatbot c ON v.id_chatbot = c.id_chatbot
    ORDER BY v.data_visita DESC
";

$result = $conn->query($query);

?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco Visite</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('uploads/jinn.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        .container {
            background: rgba(0, 0, 0, 0.75);
            padding: 30px;
            max-width: 900px;
            margin: 50px auto;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(255,255,255,0.2);
        }

        h2 {
            text-align: center;
            color: #00bfff;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
        }

        th, td {
            padding: 10px;
            border: 1px solid #444;
            color: #fff;
            text-align: left;
        }

        th {
            background-color: rgba(0, 123, 255, 0.3);
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        a.button {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background: #0077cc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
        }

        a.button:hover {
            background: #005fa3;
        }

        p {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Elenco completo delle visite</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Paziente</th>
                    <th>Medico</th>
                    <th>Sintomi (da Chatbot)</th>
                    <th>Esito</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_visita']) ?></td>
                        <td><?= htmlspecialchars($row['data_visita']) ?></td>
                        <td><?= htmlspecialchars($row['paziente']) ?></td>
                        <td><?= htmlspecialchars($row['medico']) ?></td>
                        <td><?= htmlspecialchars($row['sintomi_riportati']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['esito_visita'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Nessuna visita trovata.</p>
        <?php endif; ?>

        <div style="text-align:center;">
            <a href="index.php" class="button">🏠 Torna alla Home</a>
        </div>
    </div>
</body>
</html>

