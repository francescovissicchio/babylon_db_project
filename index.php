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
            padding-left: 0;
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
            width: 100%;
            max-width: 400px;
            margin: auto;
            box-shadow: 0px 0px 10px #ccc;
        }
        .welcome {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>🩺 Progetto Babylon</h1>

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


            <?php if ($_SESSION['tipo_utente'] === 'Paziente'): ?>
                <li><a href="chatbot_logic.php">🤖 Chatbot visita</a></li>
            <?php elseif ($_SESSION['tipo_utente'] === 'Medico'): ?>
                <li><a href="medico_dashboard.php">🩺 Area Medico</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['tipo_utente'] === 'Admin'): ?>
                <li><a href="lista_utenti.php">📄 Lista Utenti Registrati</a></li>
                <li><a href="popola_db.php">🧪 Rigenera dati demo</a></li>
                <li><a href="simula_visite.php">📅 Simula Visite</a></li>
                <li><a href="tutte_visite.php">📋 Tutte le Visite</a></li>
                <li><a href="azzera_database.php" onclick="return confirm('Sei sicuro di voler azzerare tutto il database?')">🗑️ Azzera Database</a></li>
            <?php endif; ?>


            <li><a href="logout.php">🚪 Logout</a></li>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>

