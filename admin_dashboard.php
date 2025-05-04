<?php
session_start();

// Accesso solo per admin
if (!isset($_SESSION['tipo_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Connessione DB
$mysqli = new mysqli("localhost", "root", "", "progetto_babylon_vissicchio");
if ($mysqli->connect_error) {
    die("Errore connessione DB: " . $mysqli->connect_error);
}

// Gestione eliminazione
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $mysqli->query("DELETE FROM utente WHERE id_utente = $id");
    header("Location: admin_dashboard.php");
    exit;
}

// Gestione aggiornamento tipo utente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $id = intval($_POST['id_utente']);
    $nuovo_ruolo = $mysqli->real_escape_string($_POST['tipo_utente']);
    $mysqli->query("UPDATE utente SET tipo_utente = '$nuovo_ruolo' WHERE id_utente = $id");
    header("Location: admin_dashboard.php");
    exit;
}

// Lista utenti
$result = $mysqli->query("SELECT id_utente, nome, email, tipo_utente FROM utente ORDER BY tipo_utente, nome");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; padding: 30px; background-color: #f4f4f4; }
        h1 { color: #cc0000; }
        table { width: 100%; max-width: 900px; background: white; border-collapse: collapse; box-shadow: 0 0 10px #aaa; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .form-inline { display: flex; gap: 10px; }
        select, button { padding: 5px; }
        .actions a { color: red; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

<h1>👨‍💼 Dashboard Amministratore</h1>
<p>Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></p>
<a href="index.php">🏠 Torna alla Home</a> | <a href="popola_db.php">🧪 Rigenera dati demo</a>
<br><br>

<h2>📋 Utenti registrati</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Tipo Utente</th>
        <th>Azioni</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_utente'] ?></td>
            <td><?= htmlspecialchars($row['nome']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
                <form method="POST" class="form-inline">
                    <input type="hidden" name="id_utente" value="<?= $row['id_utente'] ?>">
                    <select name="tipo_utente">
                        <?php
                        $tipi = ['Paziente', 'Medico', 'Admin'];
                        foreach ($tipi as $tipo) {
                            $selected = ($row['tipo_utente'] === $tipo) ? 'selected' : '';
                            echo "<option value='$tipo' $selected>$tipo</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" name="update_role">💾</button>
                </form>
            </td>
            <td class="actions">
                <a href="admin_dashboard.php?delete=<?= $row['id_utente'] ?>" onclick="return confirm('Sei sicuro di voler eliminare questo utente?')">🗑️ Elimina</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

