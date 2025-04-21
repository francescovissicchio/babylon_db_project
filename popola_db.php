<?php
require 'config.php';

// 1. Pulisce le tabelle collegate (ordine importante per i vincoli FK)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE visita");
$conn->query("TRUNCATE TABLE chat");
$conn->query("TRUNCATE TABLE sceglie");
$conn->query("TRUNCATE TABLE paziente");
$conn->query("TRUNCATE TABLE medico");
$conn->query("TRUNCATE TABLE chatbot");
$conn->query("TRUNCATE TABLE utente");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

function randomNome() {
    $nomi = ['Luca', 'Marco', 'Anna', 'Giulia', 'Francesco', 'Martina', 'Simone', 'Sara', 'Alessandro', 'Laura'];
    $cognomi = ['Rossi', 'Bianchi', 'Verdi', 'Russo', 'Esposito', 'Ferrari', 'Romano', 'Gallo', 'Costa', 'Greco'];
    return $nomi[array_rand($nomi)] . ' ' . $cognomi[array_rand($cognomi)];
}

function randomEmail($index) {
    return "utente$index@babylon.com";
}

function randomPassword() {
    return password_hash("password123", PASSWORD_DEFAULT);
}

function randomSesso() {
    return rand(0, 1) ? 'Maschio' : 'Femmina';
}

function randomDataNascita() {
    $start = strtotime("1950-01-01");
    $end = strtotime("2005-12-31");
    return date("Y-m-d", rand($start, $end));
}

function randomSpecializzazione() {
    $specializzazioni = [
        'Cardiologia', 'Pneumologia', 'Dermatologia', 'Neurologia',
        'Gastroenterologia', 'Oftalmologia', 'Psichiatria', 'Ortopedia',
        'Endocrinologia', 'Medicina generale'
    ];
    return $specializzazioni[array_rand($specializzazioni)];
}

function insertUtente($conn, $nome, $email, $password, $tipo) {
    $stmt = $conn->prepare("INSERT INTO utente (nome, email, password, tipo_utente) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $password, $tipo);
    $stmt->execute();
    return $conn->insert_id;
}

// Popola Medici
for ($i = 1; $i <= 20; $i++) {
    $nome = randomNome();
    $email = randomEmail("medico$i");
    $password = randomPassword();
    $specializzazione = randomSpecializzazione();
    $rating = round(rand(30, 50) / 10, 1); // 3.0 - 5.0
    $disponibile = rand(0, 1);

    $id_utente = insertUtente($conn, $nome, $email, $password, 'Medico');

    $stmt = $conn->prepare("INSERT INTO medico (id_medico, Specializzazione, Rating, Disponibilita) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdi", $id_utente, $specializzazione, $rating, $disponibile);
    $stmt->execute();
}

// Popola Pazienti
for ($i = 1; $i <= 50; $i++) {
    $nome = randomNome();
    $email = randomEmail("paziente$i");
    $password = randomPassword();
    $sesso = randomSesso();
    $data_nascita = randomDataNascita();

    $id_utente = insertUtente($conn, $nome, $email, $password, 'Paziente');

    $stmt = $conn->prepare("INSERT INTO paziente (id_paziente, data_nascita, Sesso) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_utente, $data_nascita, $sesso);
    $stmt->execute();
}

echo "<h3>Database resettato e popolato con successo!</h3>";
echo "<p>Inseriti 20 medici e 50 pazienti nella base dati.</p>";
echo "<a href='login.php'>Vai al login</a>";
?>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>

