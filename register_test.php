<?php
require 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $tipo = $_POST['tipo_utente'] ?? null;

    if (!$nome || !$email || !$password || !$tipo) {
        $messaggio = "⚠️ Tutti i campi sono obbligatori.";
    } else {
        // Controllo email già presente
        $stmt = $conn->prepare("SELECT id_utente FROM utente WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $messaggio = "⚠️ Email già registrata.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO utente (nome, email, password, tipo_utente) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $email, $hash, $tipo);
            $stmt->execute();

            $id = $conn->insert_id;

            if ($tipo === 'Medico') {
                $specializzazione = $_POST['specializzazione'] ?? 'Medicina generale';
                $stmt = $conn->prepare("INSERT INTO medico (id_medico, specializzazione) VALUES (?, ?)");
                $stmt->bind_param("is", $id, $specializzazione);
            } elseif ($tipo === 'Paziente') {
                $data_nascita = $_POST['data_nascita'] ?? '2000-01-01';
                $sesso = $_POST['sesso'] ?? 'ND';
                $stmt = $conn->prepare("INSERT INTO paziente (id_paziente, data_nascita, sesso) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $id, $data_nascita, $sesso);
            }

            if ($tipo !== 'Admin') {
                $stmt->execute();
            }

            $messaggio = "✅ Utente $tipo creato con successo.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Test - Babylon</title>
    <style>
        body { font-family: Arial; background-color: #f4f4f4; padding: 30px; }
        .box { background: white; padding: 20px; border-radius: 10px; width: 100%; max-width: 500px; margin: auto; box-shadow: 0 0 10px #ccc; }
        input, select { width: 100%; padding: 8px; margin: 8px 0; }
        button { background: #0077cc; color: white; padding: 10px; border: none; cursor: pointer; }
        .msg { font-weight: bold; margin-top: 10px; }
    </style>
    <script>
        function toggleFields() {
            const tipo = document.getElementById("tipo_utente").value;
            document.getElementById("medicoFields").style.display = tipo === "Medico" ? "block" : "none";
            document.getElementById("pazienteFields").style.display = tipo === "Paziente" ? "block" : "none";
        }
    </script>
</head>
<body>

<div class="box">
    <h2>🧪 Register Test</h2>

    <?php if ($messaggio): ?>
        <div class="msg"><?= $messaggio ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Nome:</label>
        <input type="text" name="nome" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Tipo utente:</label>
        <select name="tipo_utente" id="tipo_utente" onchange="toggleFields()" required>
            <option value="">-- Seleziona --</option>
            <option value="Admin">Admin</option>
            <option value="Medico">Medico</option>
            <option value="Paziente">Paziente</option>
        </select>

        <div id="medicoFields" style="display:none;">
            <label>Specializzazione:</label>
            <input type="text" name="specializzazione" placeholder="Es. Cardiologia">
        </div>

        <div id="pazienteFields" style="display:none;">
            <label>Data di nascita:</label>
            <input type="date" name="data_nascita">
            <label>Sesso:</label>
            <input type="text" name="sesso" placeholder="Es. Maschio">
        </div>

        <button type="submit">Crea utente</button>
    </form>

    <a href="index.php" style="display:inline-block; margin-top: 20px;">🏠 Torna alla Home</a>
</div>

</body>
</html>
