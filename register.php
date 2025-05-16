<?php
session_start();
require 'config.php';

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? null;
    $cognome = $_POST['cognome'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $tipo_utente = $_POST['tipo_utente'] ?? null;

    if (!$nome || !$cognome || !$email || !$password || !$tipo_utente) {
        $messaggio = "⚠️ Compila tutti i campi richiesti.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM utente WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $messaggio = "⚠️ Email già in uso.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO utente (nome, cognome, email, password, tipo_utente) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $cognome, $email, $hashed_password, $tipo_utente);
            $stmt->execute();
            $id_utente = $stmt->insert_id;

            if ($tipo_utente === "Medico") {
                $specializzazione = $_POST['specializzazione'] ?? 'Medicina generale';
                $stmt = $conn->prepare("INSERT INTO medico (id_medico, specializzazione) VALUES (?, ?)");
                $stmt->bind_param("is", $id_utente, $specializzazione);
                $stmt->execute();
                $messaggio = "✅ Registrazione completata con successo.";
            } else {
                $data_nascita = $_POST['data_nascita'] ?? null;
                $sesso = $_POST['sesso'] ?? null;
                $statura_cm = $_POST['statura_cm'] ?? null;
                $peso_kg = $_POST['peso_kg'] ?? null;

                if ($statura_cm < 50 || $statura_cm > 250 || $peso_kg < 10 || $peso_kg > 300) {
                    $messaggio = "⚠️ Inserisci valori realistici per statura e peso.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO paziente (id_paziente, data_nascita, sesso, statura_cm, peso_kg) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issid", $id_utente, $data_nascita, $sesso, $statura_cm, $peso_kg);
                    $stmt->execute();
                    $messaggio = "✅ Registrazione completata con successo.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione - Progetto Babylon</title>
    <style>
        body { font-family: Arial; background-color: #f4f4f4; padding: 30px; }
        .form-box {
            background: white; padding: 20px; border-radius: 10px;
            max-width: 500px; margin: auto; box-shadow: 0px 0px 10px #ccc;
        }
        h2 { color: #0077cc; }
        input, select {
            width: 100%; padding: 8px; margin: 10px 0;
        }
        button {
            background-color: #0077cc; color: white;
            border: none; padding: 10px 20px; cursor: pointer;
        }
        .message { margin: 10px 0; font-weight: bold; color: red; }
    </style>
    <script>
        function mostraCampi() {
            const tipo = document.getElementById("tipo_utente").value;
            document.getElementById("campiMedico").style.display = tipo === "Medico" ? "block" : "none";
            document.getElementById("campiPaziente").style.display = tipo === "Paziente" ? "block" : "none";
        }

        window.onload = mostraCampi;
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
        <input type="text" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? '') ?>" required>

        <label>Cognome:</label>
        <input type="text" name="cognome" value="<?php echo htmlspecialchars($_POST['cognome'] ?? '') ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Tipo utente:</label>
        <select name="tipo_utente" id="tipo_utente" onchange="mostraCampi()" required>
            <option value="">-- Seleziona --</option>
            <option value="Paziente" <?php if(($_POST['tipo_utente'] ?? '') === "Paziente") echo 'selected'; ?>>Paziente</option>
            <option value="Medico" <?php if(($_POST['tipo_utente'] ?? '') === "Medico") echo 'selected'; ?>>Medico</option>
        </select>

        <div id="campiMedico" style="display:none;">
            <label>Specializzazione:</label>
            <select name="specializzazione">
                <option value="Cardiologia" <?php if(($_POST['specializzazione'] ?? '') === "Cardiologia") echo 'selected'; ?>>Cardiologia</option>
                <option value="Dermatologia" <?php if(($_POST['specializzazione'] ?? '') === "Dermatologia") echo 'selected'; ?>>Dermatologia</option>
                <option value="Neurologia" <?php if(($_POST['specializzazione'] ?? '') === "Neurologia") echo 'selected'; ?>>Neurologia</option>
                <option value="Ortopedia" <?php if(($_POST['specializzazione'] ?? '') === "Ortopedia") echo 'selected'; ?>>Ortopedia</option>
                <option value="Medicina generale" <?php if(($_POST['specializzazione'] ?? '') === "Medicina generale") echo 'selected'; ?>>Medicina generale</option>
            </select>
        </div>

        <div id="campiPaziente" style="display:none;">
            <label>Data di nascita:</label>
            <input type="date" name="data_nascita" value="<?php echo htmlspecialchars($_POST['data_nascita'] ?? '') ?>">

            <label>Sesso:</label><br>
            <input type="radio" name="sesso" value="Maschio" <?php if(($_POST['sesso'] ?? '') === "Maschio") echo 'checked'; ?>> Maschio<br>
            <input type="radio" name="sesso" value="Femmina" <?php if(($_POST['sesso'] ?? '') === "Femmina") echo 'checked'; ?>> Femmina<br>

            <label>Statura (in cm):</label>
            <input type="number" name="statura_cm" min="50" max="250" value="<?php echo htmlspecialchars($_POST['statura_cm'] ?? '') ?>">

            <label>Peso (in kg):</label>
            <input type="number" name="peso_kg" step="0.1" min="10" max="300" value="<?php echo htmlspecialchars($_POST['peso_kg'] ?? '') ?>">
        </div>

        <button type="submit">Registrati</button>
    </form>

    <a href="index.php" style="display:inline-block; margin-top: 20px;">🏠 Torna alla Home</a>
</div>

</body>
</html>





