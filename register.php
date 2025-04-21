<?php
session_start();
require 'config.php';

$nome = $_POST['nome'];
$email = $_POST['email'];
$password = $_POST['password'];
$tipo_utente = $_POST['tipo_utente']; // 'Medico' o 'Paziente'

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifica se email già esiste
$stmt = $conn->prepare("SELECT * FROM utente WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Email già registrata.";
    exit;
}

// Inserisci nella tabella utente
$stmt = $conn->prepare("INSERT INTO utente (nome, email, password, tipo_utente) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $email, $hashed_password, $tipo_utente);
$stmt->execute();

$id_utente = $conn->insert_id;

// Inserisci nella tabella medico o paziente
if ($tipo_utente === "Medico") {
    $specializzazione = $_POST['specializzazione'] ?? 'Medicina generale';
    $stmt = $conn->prepare("INSERT INTO medico (id_medico, Specializzazione) VALUES (?, ?)");
    $stmt->bind_param("is", $id_utente, $specializzazione);
} else {
    $data_nascita = $_POST['data_nascita'];
    $sesso = $_POST['sesso'];
    $stmt = $conn->prepare("INSERT INTO paziente (id_paziente, data_nascita, Sesso) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_utente, $data_nascita, $sesso);
}
$stmt->execute();

echo "Registrazione completata.";
?>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>