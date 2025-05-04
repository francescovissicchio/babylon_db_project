<?php
session_start();
require 'config.php'; // connessione al DB

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dati generali
    $nome = $_POST['nome'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $tipo_utente = $_POST['tipo_utente'] ?? null;

    // Verifica campi base
    if (!$nome || !$email || !$password || !$tipo_utente) {
        $messaggio = "⚠️ Compila tutti i campi richiesti.";
    } else {
        // Verifica se email già registrata
        $stmt = $conn->prepare("SELECT * FROM utente WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $messaggio = "⚠️ Email già in uso.";
        } else {
            // Hash della password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Inserimento in utente
            $stmt = $conn->prepare("INSERT INTO utente (nome, email, password, tipo_utente) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $email, $hashed_password, $tipo_utente);
            $stmt->execute();

            $id_utente = $conn->insert_id;

            // Medico o Paziente
            if ($tipo_utente === "Medico") {
                $specializzazione = $_POST['specializzazione'] ?? 'Medicina generale';
                $stmt = $conn->prepare("INSERT INTO medico (id_medico, specializzazione) VALUES (?, ?)");
                $stmt->bind_param("is", $id_utente, $specializzazione);
            } else {
                $data_nascita = $_POST['data_nascita'] ?? null;
                $sesso = $_POST['sesso'] ?? null;
                $stmt = $conn->prepare("INSERT INTO paziente (id_paziente, data_nascita, sesso) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $id_utente, $data_nascita, $sesso);
            }

            $stmt->execute();

            $messaggio = "✅ Registrazione completata con successo.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione - Progetto Babylon</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f4f4f4;
            padding: 30px;
        }
        .form-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 500px;
            margin: auto;
            box-shadow: 0px 0px 10px #ccc;
        }
        h2 { color: #0077cc; }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
        }
        button {
            background-color: #0077cc;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .message {
            margin: 10px 0;
            font-weight: bold;
            color: red;
        }
    </style>
    <script>
        function mostraCampi() {
            const tipo = document.getElementById("tipo_utente").value;
            document.getElementById("campiMedico").style.display = tipo === "Medico" ? "block" : "none";
            document.getElementById("campiPaziente").style.display = tipo === "Paziente" ? "block" : "none";
        }
    </script>
</head>
<body>

<div class="form-box">
    <h2>📋 Registrazione</h2>

    <?php if ($messaggio): ?>
        <div class="message"><?php echo $messaggio; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Nome:</label>
        <input type="text" name="nome" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Tipo utente:</label>
        <select name="tipo_utente" id="tipo_utente" onchange="mostraCampi()" required>
            <option value="">-- Seleziona --</option>
            <option value="Paziente">Paziente</option>
            <option value="Medico">Medico</option>
        </select>

        <div id="campiMedico" style="display:none;">
            <label>Specializzazione:</label>
            <input type="text" name="specializzazione" placeholder="Es. Cardiologia">
        </div>

        <div id="campiPaziente" style="display:none;">
            <label>Data di nascita:</label>
            <input type="date" name="data_nascita">
            <label>Sesso:</label>
            <input type="text" name="sesso" placeholder="Es. Maschio, Femmina">
        </div>

        <button type="submit">Registrati</button>
    </form>

    <a href="index.php" style="display:inline-block; margin-top: 20px;">🏠 Torna alla Home</a>
</div>

</body>
</html>
