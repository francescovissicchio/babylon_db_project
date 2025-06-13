<?php
session_start();
require 'config.php';
require 'check_accesso.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Paziente') {
    echo "Accesso non autorizzato.";
    exit;
}

$id_paziente = $_SESSION['id_utente'];
$step = 1;
$sintomi = '';
$medici = [];
$id_chatbot = null;

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function deduciSpecializzazione($testo) {
    $url = 'http://localhost:8000/deduci';
    $data = json_encode(['sintomi' => $testo]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return "Medicina Generale";
    }
    curl_close($ch);
    $result = json_decode($response, true);
    return $result['specializzazione'] ?? "Medicina Generale";
}

function specializzazioneFallback($originale) {
    $fallbacks = [
        'Dermatologia' => 'Medicina Generale',
        'Cardiologia' => 'Internista',
        'Neurologia' => 'Medicina Generale'
    ];
    return $fallbacks[$originale] ?? 'Medicina Generale';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sintomi'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF non valido.");
    }

    $step = 2;
    $sintomi = trim($_POST['sintomi']);

    if (strlen($sintomi) < 10 || strlen($sintomi) > 500) {
        die("Descrizione dei sintomi non valida (10-500 caratteri).");
    }

    $specializzazione = deduciSpecializzazione($sintomi);
    $nome_bot = "MedAI – Assistente Virtuale";

    $stmt = $conn->prepare("INSERT INTO chatbot (nome_bot, sintomi_riportati, specializzazione_dedotta) VALUES (?, ?, ?)");
    if (!$stmt) die("Errore query: " . $conn->error);
    $stmt->bind_param("sss", $nome_bot, $sintomi, $specializzazione);
    $stmt->execute();
    $id_chatbot = $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT u.id_utente, u.nome, m.Rating, m.Specializzazione
        FROM medico m
        JOIN utente u ON u.id_utente = m.id_medico
        WHERE m.Specializzazione = ? AND m.Disponibilita = TRUE
        ORDER BY m.Rating DESC
    ");
    $stmt->bind_param("s", $specializzazione);
    $stmt->execute();
    $medici = $stmt->get_result();

    if ($medici->num_rows === 0) {
        $specializzazione_secondaria = specializzazioneFallback($specializzazione);
        $stmt = $conn->prepare("
            SELECT u.id_utente, u.nome, m.Rating, m.Specializzazione
            FROM medico m
            JOIN utente u ON u.id_utente = m.id_medico
            WHERE m.Specializzazione = ? AND m.Disponibilita = TRUE
            ORDER BY m.Rating DESC
        ");
        $stmt->bind_param("s", $specializzazione_secondaria);
        $stmt->execute();
        $medici = $stmt->get_result();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_medico']) && isset($_POST['id_chatbot'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF non valido.");
    }

    $step = 3;
    $id_medico = (int)$_POST['id_medico'];
    $id_chatbot = (int)$_POST['id_chatbot'];

    $stmt = $conn->prepare("INSERT INTO sceglie (id_chatbot, id_medico) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_chatbot, $id_medico);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO visita (id_paziente, id_medico, id_chatbot) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $id_paziente, $id_medico, $id_chatbot);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO chat (id_paziente, id_chatbot, data_avvio) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $id_paziente, $id_chatbot);
    $stmt->execute();
    $stmt->close();
}

// Messaggi dinamici per maggiore realismo
$frasi_finali = [
    "✅ Visita prenotata! Il medico ti contatterà a breve.",
    "📅 Tutto confermato. Dai un’occhiata al tuo profilo per i dettagli.",
    "🔔 Visita salvata! Presto riceverai una notifica.",
    "👍 Ottimo! Sei stato affidato a uno specialista."
];
$messaggio_finale = $frasi_finali[array_rand($frasi_finali)];
?>




