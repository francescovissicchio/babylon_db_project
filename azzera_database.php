<?php
session_start();
require 'config.php';
require 'check_accesso.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    echo "<div class='container'><h2>❌ Accesso negato.</h2></div>";
    exit;
}

$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$conn->query("TRUNCATE TABLE visita");
$conn->query("TRUNCATE TABLE chat");
$conn->query("TRUNCATE TABLE sceglie");
$conn->query("TRUNCATE TABLE chatbot");
$conn->query("TRUNCATE TABLE paziente");
$conn->query("TRUNCATE TABLE medico");

$conn->query("DELETE FROM utente WHERE email != 'admin@babylon.com'");

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Database Reset</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-image: url('uploads/jinn.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            text-align: center;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.6); /* Trasparenza sul contenuto */
            padding: 40px;
            margin: 100px auto;
            max-width: 600px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(255,255,255,0.2);
        }

        h2 {
            margin-top: 0;
            font-size: 28px;
        }

        p {
            font-size: 18px;
        }

        a.button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #0077cc;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        a.button:hover {
            background-color: #005fa3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧹 Database completamente azzerato (eccetto admin)</h2>
        <p>Tutte le tabelle sono state svuotate con successo.</p>
        <a href="index.php" class="button">🏠 Torna alla Home</a>
    </div>
</body>
</html>

