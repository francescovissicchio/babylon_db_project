<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>🛠️ DEBUG LOGIN</h3>";
    
    // 1. Controllo dati ricevuti
    echo "<strong>Dati POST:</strong><br>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$email || !$password) {
        echo "❌ Email o password mancanti.";
        exit;
    }

    // 2. Query al database
    $stmt = $conn->prepare("SELECT * FROM utente WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "❌ Nessun utente trovato con email: <strong>$email</strong>";
        exit;
    }

    $utente = $result->fetch_assoc();

    echo "<strong>Record utente:</strong><br>";
    echo "<pre>";
    print_r($utente);
    echo "</pre>";

    // 3. Verifica password
    echo "<strong>Verifica password:</strong><br>";
    echo "Password inserita: <code>" . htmlspecialchars($password) . "</code><br>";
    echo "Hash salvato nel DB: <code>" . htmlspecialchars($utente['password']) . "</code><br><br>";

    if (password_verify($password, $utente['password'])) {
        echo "✅ Password corretta.<br><br>";

        $_SESSION['id_utente'] = $utente['id_utente'];
        $_SESSION['nome'] = $utente['nome'];
        $_SESSION['tipo_utente'] = $utente['tipo_utente'];

        echo "<strong>Login effettuato! Redirect a profilo.php...</strong>";
        header("refresh:2;url=profilo.php");
        exit;
    } else {
        echo "❌ Password errata.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Test - Babylon</title>
</head>
<body>
    <h2>🔐 Login di Test</h2>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Prova Login</button>
    </form>
</body>
</html>
