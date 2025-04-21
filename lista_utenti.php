<?php
require 'config.php';

// Recupero tutti gli utenti
$query = "
    SELECT 
        u.id_utente, u.nome, u.email, u.password, u.tipo_utente,
        m.Specializzazione, m.Rating, m.Disponibilita,
        p.data_nascita, p.Sesso
    FROM utente u
    LEFT JOIN medico m ON u.id_utente = m.id_medico
    LEFT JOIN paziente p ON u.id_utente = p.id_paziente
    ORDER BY u.tipo_utente, u.nome
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista completa utenti</title>
    <style>
        body {
            font-family: Arial;
            padding: 30px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 1200px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #0077cc;
            color: white;
        }
        h2 {
            color: #0077cc;
        }
    </style>
</head>
<body>

<h2>Lista completa di tutti gli utenti</h2>
<a href="index.php">🏠 Torna alla Home</a><br><br>

<table>
    <tr>
        <th>ID</th>
        <th>Nome</th>
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
            <td><?= $row['id_utente'] ?></td>
            <td><?= htmlspecialchars($row['nome']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td style="font-family: monospace"><?= htmlspecialchars($row['password']) ?></td>
            <td><?= $row['tipo_utente'] ?></td>
            <td><?= $row['Specializzazione'] ?? '—' ?></td>
            <td><?= $row['Rating'] ?? '—' ?></td>
            <td><?= isset($row['Disponibilita']) ? ($row['Disponibilita'] ? 'Sì' : 'No') : '—' ?></td>
            <td><?= $row['data_nascita'] ?? '—' ?></td>
            <td><?= $row['Sesso'] ?? '—' ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

