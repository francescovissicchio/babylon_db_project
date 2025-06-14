<?php
session_start();
require 'config.php';
require 'check_accesso.php';

// Solo l'Admin può accedere
if (!isset($_SESSION['tipo_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Toast message JS injection
$toast_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_utente = (int)$_POST['id_utente'];
    $nuovo_stato = ($_POST['azione'] === 'attiva') ? 0 : 1;

    $stmt = $conn->prepare("UPDATE utente SET cancellato = ? WHERE id_utente = ?");
    $stmt->bind_param("ii", $nuovo_stato, $id_utente);
    $stmt->execute();

    $toast_message = $nuovo_stato ? "✅ Account disattivato." : "✅ Account riattivato.";
}

// Recupera utenti
$res = $conn->query("SELECT id_utente, nome, cognome, email, tipo_utente, cancellato FROM utente WHERE tipo_utente != 'Admin' ORDER BY id_utente DESC");

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti - Babylon</title>
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

        h1 {
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

        button {
            background: #00bfff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #009acd;
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

        /* Toast */
        #toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #333;
            color: white;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 9999;
            bottom: 30px;
            right: 30px;
            font-size: 16px;
            opacity: 0;
            transition: opacity 0.5s, bottom 0.5s;
        }

        #toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👮‍♂️ Gestione Utenti</h1>

        <table>
            <tr><th>Nome</th><th>Email</th><th>Tipo</th><th>Stato</th><th>Azione</th></tr>
            <?php while ($row = $res->fetch_assoc()): 
                $id = $row['id_utente'];
                $nome = htmlspecialchars($row['nome'] . ' ' . $row['cognome']);
                $email = htmlspecialchars($row['email']);
                $tipo = htmlspecialchars($row['tipo_utente']);
                $cancellato = (int)$row['cancellato'];

                $stato = $cancellato ? "🔴 Disattivato" : "🟢 Attivo";
                $azione = $cancellato ? "attiva" : "disattiva";
                $etichetta = $cancellato ? "✅ Riattiva" : "❌ Disattiva";
            ?>
            <tr>
                <td><?= $nome ?></td>
                <td><?= $email ?></td>
                <td><?= $tipo ?></td>
                <td><?= $stato ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Sei sicuro?');">
                        <input type="hidden" name="id_utente" value="<?= $id ?>">
                        <input type="hidden" name="azione" value="<?= $azione ?>">
                        <button type="submit"><?= $etichetta ?></button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <div class="actions" style="text-align:center;">
            <a href="profilo.php" class="button">🔙 Torna al Profilo</a>
        </div>
    </div>

    <div id="toast"></div>

    <?php if ($toast_message): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toast = document.getElementById("toast");
            toast.textContent = <?= json_encode($toast_message) ?>;
            toast.classList.add("show");
            setTimeout(() => toast.classList.remove("show"), 3000);
        });
    </script>
    <?php endif; ?>
</body>
</html>

