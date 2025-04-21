<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Progetto Babylon - Home</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f2f2f2;
            padding: 30px;
        }
        h1 {
            color: #0077cc;
        }
        ul {
            list-style-type: none;
        }
        li {
            margin: 10px 0;
        }
        a {
            text-decoration: none;
            color: #0077cc;
            font-weight: bold;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0px 0px 10px #ccc;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>🩺 Progetto Babylon</h1>
    <ul>
        <li><a href="register.php">📋 Registrati</a></li>
        <li><a href="login.php">🔐 Login</a></li>
        <li><a href="chatbot_logic.php">🤖 Chatbot visita</a></li>
        <li><a href="profilo.php">👤 Profilo personale</a></li>
        <li><a href="medico_dashboard.php">🩺 Area Medico</a></li>
        <li><a href="popola_db.php">🧪 Rigenera dati demo</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
        <li><a href="lista_utenti.php">📄 Lista Utenti Registrati</a></li>
    </ul>
</div>

</body>
</html>
