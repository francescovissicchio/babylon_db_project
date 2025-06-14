<?php
session_start();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Babylon - Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            background: url('uploads//ziggurat.jpg') no-repeat center center fixed;
            background-size: cover;
            animation: scrollBg 20s linear infinite alternate;
        }

        @keyframes scrollBg {
            0% {
                background-position: center top;
            }
            100% {
                background-position: center bottom;
            }
        }

        .box {
            background: rgba(255, 255, 255, 0.6);
            padding: 30px;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.8);
            text-align: center;
            z-index: 1;
            position: relative;
        }

        h1 {
            color: #333;
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .welcome {
            font-size: 1rem;
            margin-bottom: 25px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 15px 0;
        }

        a {
            text-decoration: none;
            color: #ffffff;
            background-color: #4facfe;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background-color 0.3s;
            font-weight: 600;
        }

        a:hover {
            background-color: #00c6ff;
        }

        strong {
            color: #00a2ff;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>🩺 Homepage Babylon</h1>

    <?php if (isset($_SESSION['nome'])): ?>
        <div class="welcome">
            <p>👋 Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>!</p>
            <p>Ruolo: <strong><?php echo htmlspecialchars($_SESSION['tipo_utente']); ?></strong></p>
        </div>
    <?php endif; ?>

    <ul>
        <?php if (!isset($_SESSION['id_utente'])): ?>
            <li><a href="register.php">📋 Registrati</a></li>
            <li><a href="login.php">🔐 Login</a></li>
        <?php else: ?>
            <?php if ($_SESSION['tipo_utente'] === 'Admin'): ?>
                <li><a href="profilo.php">🛠️ Area Admin</a></li>
            <?php else: ?>
                <li><a href="profilo.php">👤 Profilo personale</a></li>
            <?php endif; ?>


            <?php if ($_SESSION['tipo_utente'] === 'Admin'): ?>
                <li><a href="lista_utenti.php">📄 Lista Utenti Registrati</a></li>
                <li><a href="tutte_visite.php">📋 Tutte le Visite</a></li>
                <li><a href="azzera_database.php" onclick="return confirm('Sei sicuro di voler azzerare tutto il database?')">🗑️ Azzera Database</a></li>
            <?php endif; ?>

            <li><a href="logout.php">🚪 Logout</a></li>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>



