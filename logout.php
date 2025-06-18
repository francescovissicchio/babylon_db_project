<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Logout</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('uploads/pensili2.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .logout-container {
            background: rgba(0, 0, 0, 0.6); /* semi-trasparente */
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
        }

        .logout-container h1 {
            margin-bottom: 20px;
            font-size: 28px;
        }

        .home-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #333333;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .home-button:hover {
            background-color: #005fa3;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h1>✅ Logout effettuato con successo.</h1>
        <a href="index.php" class="home-button">🏠 Torna alla Home</a>
    </div>
</body>
</html>

