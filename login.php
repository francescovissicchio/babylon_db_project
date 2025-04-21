<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se i campi esistono
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM utente WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Utente non trovato.";
        } else {
            $utente = $result->fetch_assoc();
            if (password_verify($password, $utente['password'])) {
                $_SESSION['id_utente'] = $utente['id_utente'];
                $_SESSION['nome'] = $utente['nome'];
                $_SESSION['tipo_utente'] = $utente['tipo_utente'];

                header("Location: profilo.php");
                exit;
            } else {
                echo "Password errata.";
            }
        }
    } else {
        echo "Inserisci email e password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Babylon</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>

