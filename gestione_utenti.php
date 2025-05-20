<?php
session_start();
require 'config.php';
require 'check_accesso.php';


// Solo admin può accedere
if (!isset($_SESSION['tipo_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Azione: attiva o disattiva utente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_utente = (int)$_POST['id_utente'];
    $nuovo_stato = ($_POST['azione'] === 'attiva') ? 0 : 1;

    $stmt = $conn->prepare("UPDATE utente SET cancellato = ? WHERE id_utente = ?");
    $stmt->bind_param("ii", $nuovo_stato, $id_utente);
    $stmt->execute();

    $msg = $nuovo_stato ? "Account disattivato." : "Account riattivato.";
    echo "<p style='color:green;'>✅ $msg</p>";
}

// Recupera elenco utenti
$res = $conn->query("SELECT id_utente, nome, cognome, email, tipo_utente, cancellato FROM utente ORDER BY id_utente DESC");

echo "<h1>👮‍♂️ Gestione Utenti</h1>";
echo "<table border='1' cellpadding='8' cellspacing='0'>
        <tr><th>Nome</th><th>Email</th><th>Tipo</th><th>Stato</th><th>Azione</th></tr>";

while ($row = $res->fetch_assoc()) {
    $id = $row['id_utente'];
    $nome = htmlspecialchars($row['nome'] . ' ' . $row['cognome']);
    $email = htmlspecialchars($row['email']);
    $tipo = htmlspecialchars($row['tipo_utente']);
    $cancellato = (int)$row['cancellato'];

    $stato = $cancellato ? "🔴 Disattivato" : "🟢 Attivo";
    $azione = $cancellato ? "attiva" : "disattiva";
    $etichetta = $cancellato ? "✅ Riattiva" : "❌ Disattiva";

    echo "<tr>
            <td>$nome</td>
            <td>$email</td>
            <td>$tipo</td>
            <td>$stato</td>
            <td>
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='id_utente' value='$id'>
                    <input type='hidden' name='azione' value='$azione'>
                    <button type='submit'>$etichetta</button>
                </form>
            </td>
          </tr>";
}
echo "</table>";

echo "<br><a href='profilo.php'>🔙 Torna al Profilo</a>";
?>
