<?php
require 'config.php';

function randomNome() {
    $nomi = ['Luca', 'Marco', 'Anna', 'Giulia', 'Francesco', 'Martina', 'Simone', 'Sara', 'Alessandro', 'Laura'];
    $cognomi = ['Rossi', 'Bianchi', 'Verdi', 'Russo', 'Esposito', 'Ferrari', 'Romano', 'Gallo', 'Costa', 'Greco'];
    return $nomi[array_rand($nomi)] . ' ' . $cognomi[array_rand($cognomi)];
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

function probabilitaDisponibilita($percentuale = 70) {
    return rand(1, 100) <= $percentuale;
}

function insertUtente($conn, $nome, $email, $password, $tipo) {
    $stmt = $conn->prepare("INSERT INTO utente (nome, email, password, tipo_utente) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $password, $tipo);
    $stmt->execute();
    return $conn->insert_id;
}

// 1. Reset tabelle (tranne admin)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE visita");
$conn->query("TRUNCATE TABLE chat");
$conn->query("TRUNCATE TABLE sceglie");
$conn->query("TRUNCATE TABLE paziente");
$conn->query("TRUNCATE TABLE medico");
$conn->query("TRUNCATE TABLE chatbot");
$conn->query("DELETE FROM utente WHERE email != 'admin@babylon.com'");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// 2. Crea admin se non esiste
$stmt = $conn->prepare("SELECT id_utente FROM utente WHERE email = ?");
$admin_email = "admin@babylon.com";
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $admin_nome = "Admin Babylon";
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $admin_tipo = "Admin";
    insertUtente($conn, $admin_nome, $admin_email, $admin_password, $admin_tipo);
}

// 3. Crea 100 Medici (almeno 70 disponibili)
for ($i = 1; $i <= 100; $i++) {
    $nome = randomNome();
    $email = "medico$i@babylon.com";
    $password = randomPassword();
    $specializzazione = randomSpecializzazione();
    $rating = round(rand(30, 50) / 10, 1);
    $disponibile = probabilitaDisponibilita() ? 1 : 0;

    $id_utente = insertUtente($conn, $nome, $email, $password, 'Medico');

    $stmt = $conn->prepare("INSERT INTO medico (id_medico, Specializzazione, Rating, Disponibilita) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdi", $id_utente, $specializzazione, $rating, $disponibile);
    $stmt->execute();
}

// 4. Crea 100 Pazienti
for ($i = 1; $i <= 100; $i++) {
    $nome = randomNome();
    $email = "paziente$i@babylon.com";
    $password = randomPassword();
    $sesso = randomSesso();
    $data_nascita = randomDataNascita();

    $id_utente = insertUtente($conn, $nome, $email, $password, 'Paziente');

    $stmt = $conn->prepare("INSERT INTO paziente (id_paziente, data_nascita, Sesso) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_utente, $data_nascita, $sesso);
    $stmt->execute();
}

echo "<h3>✅ Database resettato e popolato con successo!</h3>";
echo "<p>👨‍⚕️ Medici inseriti: 100<br>🧑‍🤝‍🧑 Pazienti inseriti: 100<br>🔐 Admin mantenuto</p>";
echo "<a href='login.php'>Vai al login</a>";
?>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>




