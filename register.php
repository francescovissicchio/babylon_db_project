<?php
session_start();
require 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $conferma_password = $_POST['conferma_password'] ?? '';
    $tipo_utente = $_POST['tipo_utente'] ?? '';

    // ✅ Controlli preliminari
    if (!$nome || !$cognome || !$email || !$password || !$conferma_password || !$tipo_utente) {
        $messaggio = "⚠️ Compila tutti i campi richiesti.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messaggio = "⚠️ Inserisci un'email valida.";
    } elseif (strlen($password) < 8) {
        $messaggio = "⚠️ La password deve contenere almeno 8 caratteri.";
    } elseif ($password !== $conferma_password) {
        $messaggio = "⚠️ Le password non corrispondono.";
    } else {
        // ✅ Controllo se l'email è già registrata
        $stmt = $conn->prepare("SELECT id_utente FROM utente WHERE email = ?");
        if (!$stmt) {
            die("Errore nella preparazione della query: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $messaggio = "⚠️ Email già in uso.";
        } else {
            // ✅ Inizio transazione
            $conn->begin_transaction();
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Inserimento utente
                $stmt = $conn->prepare("INSERT INTO utente (nome, cognome, email, password, tipo_utente) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) throw new Exception("Errore nella query utente: " . $conn->error);
                $stmt->bind_param("sssss", $nome, $cognome, $email, $hashed_password, $tipo_utente);
                $stmt->execute();
                $id_utente = $stmt->insert_id;

                if ($tipo_utente === "Medico") {
                    // Inserimento medico
                    $specializzazione = $_POST['specializzazione'] ?? 'Medicina generale';
                    $stmt = $conn->prepare("INSERT INTO medico (id_medico, specializzazione) VALUES (?, ?)");
                    if (!$stmt) throw new Exception("Errore nella query medico: " . $conn->error);
                    $stmt->bind_param("is", $id_utente, $specializzazione);
                    $stmt->execute();
                } else {
                    // Inserimento paziente
                    $data_nascita = $_POST['data_nascita'] ?? null;
                    $sesso = $_POST['sesso'] ?? null;
                    $statura_cm = $_POST['statura_cm'] ?? null;
                    $peso_kg = $_POST['peso_kg'] ?? null;

                    if (!$data_nascita || !$sesso || !$statura_cm || !$peso_kg) {
                        throw new Exception("⚠️ Compila tutti i campi richiesti per il paziente.");
                    }

                    if ($statura_cm < 50 || $statura_cm > 250 || $peso_kg < 10 || $peso_kg > 300) {
                        throw new Exception("⚠️ Inserisci valori realistici per statura e peso.");
                    }

                    $stmt = $conn->prepare("INSERT INTO paziente (id_paziente, data_nascita, sesso, statura_cm, peso_kg) VALUES (?, ?, ?, ?, ?)");
                    if (!$stmt) throw new Exception("Errore nella query paziente: " . $conn->error);
                    $stmt->bind_param("issid", $id_utente, $data_nascita, $sesso, $statura_cm, $peso_kg);
                    $stmt->execute();
                }

                $conn->commit();
                $messaggio = "✅ Registrazione completata con successo.";
            } catch (Exception $e) {
                $conn->rollback();
                $messaggio = $e->getMessage();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrazione - Progetto Babylon</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; }
    body {
        font-family: 'Poppins', sans-serif;
        background: url('uploads/pensili2.jpg') no-repeat center center fixed;
        background-size: cover;
        margin: 0;
        padding: 40px 20px;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .form-box {
      background: rgba(255, 255, 255, 0.4);
      padding: 30px;
      border-radius: 20px;
      max-width: 600px; width: 100%;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(5px);
    }
    
    h2 {
      color: #0077cc;
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      font-weight: 600;
      margin-top: 15px;
      display: block;
    }
    input, select {
      width: 100%; padding: 10px; margin-top: 5px;
      border: 1px solid #ccc; border-radius: 8px;
      font-size: 14px;
    }
    button {
      margin-top: 25px;
      background-color: #0077cc; color: white;
      border: none; padding: 12px 20px;
      border-radius: 8px;
      cursor: pointer; width: 100%;
      font-size: 16px; font-weight: 600;
      transition: background-color 0.3s ease;
    }
    button:hover { background-color: #005fa3; }
    .message {
      margin-top: 15px;
      font-weight: bold;
      text-align: center;
      color: red;
    }
    .message:has(> .success) {
      color: green;
    }
    a {
      display: block;
      margin-top: 20px;
      text-align: center;
      color: #0077cc;
      font-weight: 600;
      text-decoration: none;
    }
    .radio-group {
      display: flex;
      gap: 15px;
      margin-top: 10px;
    
    }
    .message {
    margin-top: 15px;
    font-weight: bold;
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    background-color: rgba(255, 0, 0, 0.1);
    border: 1px solid red;
    color: darkred;
    }

    .message.success {
    background-color: rgba(0, 255, 0, 0.1);
    border: 1px solid green;
    color: green;
    }

    .radio-group input[type="radio"] { display: none; }
    .radio-group label {
      padding: 10px 20px;
      border: 2px solid #0077cc;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      background-color: #f0f8ff;
      color: #0077cc;
      transition: all 0.3s ease;
    }
    .radio-group input[type="radio"]:checked + label {
      background-color: #0077cc;
      color: white;
    }
    .hidden { display: none; }
  </style>
  <script>
    function mostraCampi() {
      const tipo = document.getElementById("tipo_utente").value;

      const medicoBox = document.getElementById("campiMedico");
      const pazienteBox = document.getElementById("campiPaziente");

      medicoBox.classList.toggle("hidden", tipo !== "Medico");
      pazienteBox.classList.toggle("hidden", tipo !== "Paziente");

      document.getElementById("specializzazione").required = (tipo === "Medico");

      const isPaziente = (tipo === "Paziente");
      document.getElementById("data_nascita").required = isPaziente;
      document.getElementById("statura_cm").required = isPaziente;
      document.getElementById("peso_kg").required = isPaziente;
      document.getElementById("sesso_m").required = isPaziente;
    }

    document.addEventListener("DOMContentLoaded", () => {
      mostraCampi();
      document.getElementById("tipo_utente").addEventListener("change", mostraCampi);
    });
  </script>
</head>
<body>
  <div class="form-box">
    <h2>📋 Registrazione</h2>

    <?php if ($messaggio): ?>
      <div class="message <?php echo (str_contains($messaggio, 'successo')) ? 'success' : ''; ?>">
        <?php echo $messaggio; ?>
        <?php if (str_contains($messaggio, 'successo')): ?>
          <br><a href="index.php">🔙 Torna alla Home</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!str_contains($messaggio, 'successo')): ?>
      <form method="POST" novalidate>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? '') ?>" required>

        <label for="cognome">Cognome:</label>
        <input type="text" id="cognome" name="cognome" value="<?php echo htmlspecialchars($_POST['cognome'] ?? '') ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <label for="conferma_password">Conferma Password:</label>
        <input type="password" name="conferma_password" id="conferma_password" required>

        <label for="tipo_utente">Tipo utente:</label>
        <select name="tipo_utente" id="tipo_utente" required>
          <option value="">-- Seleziona --</option>
          <option value="Paziente" <?php if(($_POST['tipo_utente'] ?? '') === "Paziente") echo 'selected'; ?>>Paziente</option>
          <option value="Medico" <?php if(($_POST['tipo_utente'] ?? '') === "Medico") echo 'selected'; ?>>Medico</option>
        </select>

        <div id="campiMedico" class="hidden">
          <label for="specializzazione">Specializzazione:</label>
          <select name="specializzazione" id="specializzazione">
            <?php
            $specializzazioni = [
                "Cardiologia", "Dermatologia", "Neurologia", "Ortopedia",
                "Medicina generale", "Psichiatria", "Gastroenterologia",
                "Pediatria", "Ginecologia", "Oftalmologia", "Endocrinologia", "Urologia"
            ];
            foreach ($specializzazioni as $spec) {
                $selected = ($_POST['specializzazione'] ?? '') === $spec ? 'selected' : '';
                echo "<option value='$spec' $selected>$spec</option>";
            }
            ?>
          </select>
        </div>

        <div id="campiPaziente" class="hidden">
          <label for="data_nascita">Data di nascita:</label>
          <input type="date" id="data_nascita" name="data_nascita" value="<?php echo htmlspecialchars($_POST['data_nascita'] ?? '') ?>">

          <label>Sesso:</label>
          <div class="radio-group">
            <input type="radio" id="sesso_m" name="sesso" value="Maschio" <?php if(($_POST['sesso'] ?? '') === "Maschio") echo 'checked'; ?>>
            <label for="sesso_m">Maschio</label>

            <input type="radio" id="sesso_f" name="sesso" value="Femmina" <?php if(($_POST['sesso'] ?? '') === "Femmina") echo 'checked'; ?>>
            <label for="sesso_f">Femmina</label>
          </div>

          <label for="statura_cm">Statura (in cm):</label>
          <input type="number" id="statura_cm" name="statura_cm" min="50" max="250" value="<?php echo htmlspecialchars($_POST['statura_cm'] ?? '') ?>">

          <label for="peso_kg">Peso (in kg):</label>
          <input type="number" id="peso_kg" name="peso_kg" step="0.1" min="10" max="300" value="<?php echo htmlspecialchars($_POST['peso_kg'] ?? '') ?>">
        </div>

        <button type="submit">Registrati</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>









