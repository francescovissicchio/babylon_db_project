<?php
session_start();
require 'config.php';

$messaggio = ''; // questa variabile viene poi stampata in HTML

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM utente WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $messaggio = "❌ Utente non trovato.";
        } else {
            $utente = $result->fetch_assoc();

            if ($utente['cancellato']) {
                $messaggio = "⚠️ Il tuo account è disattivato. <a href='recupera_account.php'>Clicca qui per riattivarlo</a>.";
            } elseif (password_verify($password, $utente['password'])) {
                $_SESSION['id_utente'] = $utente['id_utente'];
                $_SESSION['nome'] = $utente['nome'];
                $_SESSION['tipo_utente'] = $utente['tipo_utente'];
                header("Location: profilo.php");
                exit;
            } else {
                $messaggio = "❌ Password errata.";
            }
        }
    } else {
        $messaggio = "⚠️ Inserisci email e password.";
    }
}
?>



<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - Babylon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('uploads/giardini_pensili.jpg') no-repeat center center fixed;
            background-size: 1600px 750px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.4);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: 600;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            background-color: #333333;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #333333;
        }

        .home-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #333333;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .home-link:hover {
            background-color: #333333;
        }

        .alert {
        background: rgba(255, 0, 0, 0.1);
        border: 1px solid red;
        color: darkred;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: left;
        }

    </style>
</head>
<body>
    <div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($messaggio)): ?>
        <div class="alert"><?= $messaggio ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        
        <p style="margin-top:20px;">
            Hai disattivato il tuo account?
        <a href="recupera_account.php" style="color:#00ffff; text-decoration:none;">Recuperalo qui</a>.
        </p>

        <button type="submit">Login</button>
    </form>
    <a class="home-link" href="index.php">🏠 Torna alla Home</a>
</div>
</body>
</html>

