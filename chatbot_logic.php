<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Paziente') {
    echo "Accesso non autorizzato.";
    exit;
}

$id_paziente = $_SESSION['id_utente'];
$step = 1;
$sintomi = '';
$medici = [];
$id_chatbot = null;

function deduciSpecializzazione($testo) {
    $mappa = [
        "cuore" => "Cardiologia",
        "respiro" => "Pneumologia",
        "pelle" => "Dermatologia",
        "mal di testa" => "Neurologia",
        "stomaco" => "Gastroenterologia",
        "occhi" => "Oftalmologia"
    ];
    foreach ($mappa as $parola => $spec) {
        if (stripos($testo, $parola) !== false) {
            return $spec;
        }
    }
    return "Medicina generale";
}

// Step 1: Inserimento sintomi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sintomi'])) {
    $step = 2;
    $sintomi = $_POST['sintomi'];

    $stmt = $conn->prepare("INSERT INTO chatbot (nome_bot, sintomi_riportati) VALUES (?, ?)");
    $nome_bot = "Dr. Babylon";
    $stmt->bind_param("ss", $nome_bot, $sintomi);
    $stmt->execute();
    $id_chatbot = $conn->insert_id;

    $specializzazione = deduciSpecializzazione($sintomi);

    $stmt = $conn->prepare("
        SELECT u.id_utente, u.nome, m.Rating
        FROM medico m
        JOIN utente u ON u.id_utente = m.id_medico
        WHERE m.Specializzazione = ? AND m.Disponibilita = TRUE
        ORDER BY m.Rating DESC
    ");
    $stmt->bind_param("s", $specializzazione);
    $stmt->execute();
    $medici = $stmt->get_result();
}

// Step 2: Scelta del medico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_medico']) && isset($_POST['id_chatbot'])) {
    $step = 3;
    $id_medico = $_POST['id_medico'];
    $id_chatbot = $_POST['id_chatbot'];

    // Inserisce nella tabella "sceglie"
    $stmt = $conn->prepare("INSERT INTO sceglie (id_chatbot, id_medico) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_chatbot, $id_medico);
    $stmt->execute();

    // Inserisce nella tabella "visita" (✅ fix: collega id_chatbot!)
    $stmt = $conn->prepare("INSERT INTO visita (id_paziente, id_medico, id_chatbot) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $id_paziente, $id_medico, $id_chatbot);
    $stmt->execute();

    // Inserisce nella tabella "chat"
    $stmt = $conn->prepare("INSERT INTO chat (id_paziente, id_chatbot, data_avvio) VALUES (?, ?, CURDATE())");
    $stmt->bind_param("ii", $id_paziente, $id_chatbot);
    $stmt->execute();
}
?>

