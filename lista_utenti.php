<?php
session_start();
require 'config.php';
require 'check_accesso.php';

// Verifica accesso Admin
if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Recupero tutti gli utenti
$query = "
    SELECT 
        u.id_utente, u.nome, u.cognome, u.email, u.password, u.tipo_utente,
        m.Specializzazione, m.Rating, m.Disponibilita,
        p.data_nascita, p.Sesso
    FROM utente u
    LEFT JOIN medico m ON u.id_utente = m.id_medico
    LEFT JOIN paziente p ON u.id_utente = p.id_paziente
    ORDER BY u.tipo_utente, u.nome, u.cognome
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Lista completa utenti</title>
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
            max-width: 1200px;
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
            text-align: left;
            color: white;
        }

        th {
            background-color: rgba(0, 123, 255, 0.3);
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        td[style*="monospace"] {
            word-break: break-all;
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

        .actions {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📄 Lista completa di tutti gli utenti</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nome e cognome</th>
                    <th>Email</th>
                    <th>Password (hash)</th>
                    <th>Tipo</th>
                    <th>Specializzazione</th>
                    <th>Rating</th>
                    <th>Disponibile</th>
                    <th>Data Nascita</th>
                    <th>Sesso</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_utente']) ?></td>
                        <td><?= htmlspecialchars($row['nome'] . ' ' . $row['cognome']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td style="font-family: monospace"><?= htmlspecialchars($row['password']) ?></td>
                        <td><?= htmlspecialchars($row['tipo_utente']) ?></td>
                        <td><?= $row['Specializzazione'] ?? '—' ?></td>
                        <td><?= $row['Rating'] ?? '—' ?></td>
                        <td><?= isset($row['Disponibilita']) ? ($row['Disponibilita'] ? 'Sì' : 'No') : '—' ?></td>
                        <td><?= $row['data_nascita'] ?? '—' ?></td>
                        <td><?= $row['Sesso'] ?? '—' ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Nessun utente trovato.</p>
        <?php endif; ?>

        <div class="actions">
            <a href="index.php" class="button">🏠 Torna alla Home</a>
        </div>
    </div>
</body>
</html>


