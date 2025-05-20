<?php
session_start();
require 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id_utente, cancellato FROM utente WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res && $res['cancellato']) {
        $stmt = $conn->prepare("UPDATE utente SET cancellato = 0 WHERE id_utente = ?");
        $stmt->bind_param("i", $res['id_utente']);
        $stmt->execute();
        $messaggio = "<p class='success'>✅ Account riattivato. Ora puoi <a href='login.php'>accedere</a>.</p>";
    } else {
        $messaggio = "<p class='error'>⚠️ Nessun account disattivato trovato con questa email.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Recupera Account</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('uploads/pensili2.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.01);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .box {
            background: rgba(255, 255, 255, 0.4);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.8);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }
        button {
            padding: 10px 20px;
            background-color: #0077cc;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #005fa3;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        a {
            color: #0077cc;
            text-decoration: none;
        }
        .home-button {
        display: inline-block;
        margin-top: 25px;
        padding: 10px 20px;
        background-color: #444;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }   
    .home-button:hover {
    background-color: #222;
    }

    </style>
</head>
<body>
    <div class="overlay">
        <div class="box">
            <h2>🔄 Recupera Account</h2>
            <?php echo $messaggio; ?>
            <form method="POST">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
                <button type="submit">Recupera</button>
            </form>
            <a href="index.php" class="home-button">🏠 Torna alla Home</a>
        </div>
    </div>
</body>
</html>
