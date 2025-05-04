<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    echo "Accesso negato.";
    exit;
}

$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// ⚠️ Ordine importante per vincoli FK
$conn->query("TRUNCATE TABLE visita");
$conn->query("TRUNCATE TABLE chat");
$conn->query("TRUNCATE TABLE sceglie");
$conn->query("TRUNCATE TABLE chatbot");
$conn->query("TRUNCATE TABLE paziente");
$conn->query("TRUNCATE TABLE medico");

// Elimina tutti gli utenti tranne l'admin
$conn->query("DELETE FROM utente WHERE email != 'admin@babylon.com'");

$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "<h2>🧹 Database completamente azzerato (eccetto admin)</h2>";
echo "<p>Tutte le tabelle sono state svuotate.</p>";
?>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>
