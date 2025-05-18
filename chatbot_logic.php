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

// CSRF token setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// Step 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sintomi'])) {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF non valido.");
    }

    $step = 2;
    $sintomi = trim($_POST['sintomi']);

    // Basic input validation
    if (!preg_match('/^[\p{L}\p{N}\s,.!?-]{10,}$/u', $sintomi)) {
        die("I sintomi inseriti non sono validi.");
    }

    $specializzazione = deduciSpecializzazione($sintomi);
    $nome_bot = "Dr. Babylon";

    $stmt = $conn->prepare("INSERT INTO chatbot (nome_bot, sintomi_riportati, specializzazione_dedotta) VALUES (?, ?, ?)");
    if (!$stmt) die("Errore query: " . $conn->error);
    $stmt->bind_param("sss", $nome_bot, $sintomi, $specializzazione);
    $stmt->execute();
    $id_chatbot = $conn->insert_id;
    $stmt->close();

    // Query modificata (Step 1)
    $stmt = $conn->prepare("
        SELECT u.id_utente, u.nome, m.Rating, m.Specializzazione
        FROM medico m
        JOIN utente u ON u.id_utente = m.id_medico
        WHERE m.Specializzazione = ? AND m.Disponibilita = TRUE
        ORDER BY m.Rating DESC
    ");

    if (!$stmt) die("Errore query: " . $conn->error);
    $stmt->bind_param("s", $specializzazione);
    $stmt->execute();
    $medici = $stmt->get_result();
}

// Step 2
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

    // Rimozione data CURDATE() (Step 2)
    $stmt = $conn->prepare("INSERT INTO chat (id_paziente, id_chatbot, data_avvio) VALUES (?, ?, NOW())"); // ← niente data_avvio
    $stmt->bind_param("ii", $id_paziente, $id_chatbot);
    $stmt->execute();
    $stmt->close();

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chatbot Medico</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f8f9fa;
        }
        .bot-box {
            background-color: #e8f0fe;
            border-left: 5px solid #0077cc;
            padding: 15px;
            margin-bottom: 20px;
        }
        .progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            max-width: 600px;
        }
        .progress div {
            flex: 1;
            text-align: center;
            padding: 8px;
            border-bottom: 3px solid #ccc;
        }
        .progress .active {
            border-color: #0077cc;
            font-weight: bold;
        }
        table {
            border-collapse: collapse;
            margin-top: 10px;
            width: 100%;
            max-width: 600px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        textarea {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="progress">
    <div class="<?= $step === 1 ? 'active' : '' ?>">1. Inserisci Sintomi</div>
    <div class="<?= $step === 2 ? 'active' : '' ?>">2. Scegli Medico</div>
    <div class="<?= $step === 3 ? 'active' : '' ?>">3. Conferma</div>
</div>

<?php if ($step === 1): ?>
    <div class="bot-box">
        <strong>Dr. Babylon:</strong> Ciao! Raccontami i tuoi sintomi e ti aiuterò a trovare lo specialista più adatto.
    </div>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label for="sintomi">Descrivi i tuoi sintomi:</label><br>
        <textarea name="sintomi" rows="5" required></textarea><br><br>
        <button type="submit">Invia Sintomi</button>
    </form>

<?php elseif ($step === 2): ?>
    <div class="bot-box">
        <strong>Dr. Babylon:</strong> Ho analizzato i tuoi sintomi. Questi sono i medici più adatti:
    </div>
    <?php if ($medici->num_rows > 0): ?>
        <form method="POST">
            <input type="hidden" name="id_chatbot" value="<?= $id_chatbot ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <table>
                <tr><th>Seleziona</th><th>Nome</th><th>Specializzazione</th><th>Rating</th></tr>
                <?php while ($row = $medici->fetch_assoc()): ?>
                <tr>
                <td><input type="radio" name="id_medico" value="<?= $row['id_utente'] ?>" required></td>
                <td><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['Specializzazione']) ?></td>
                <td><?= $row['Rating'] ?></td>
                </tr>
                <?php endwhile; ?>
            </table><br>
            <button type="submit">Conferma Medico</button>
        </form>
    <?php else: ?>
        <p><strong>Dr. Babylon:</strong> Nessun medico disponibile al momento con la specializzazione richiesta.</p>
        <a href="chatbot_logic.php">🔁 Reinserisci sintomi</a>
    <?php endif; ?>

<?php elseif ($step === 3): ?>
    <div class="bot-box">
        <strong>Dr. Babylon:</strong> ✅ Visita prenotata con successo! Puoi trovare i dettagli nel tuo profilo.
    </div>
    <a href="profilo.php">🔙 Torna al tuo profilo</a>
<?php endif; ?>

<a href="index.php" style="display:inline-block; margin-top: 30px; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>
</body>
</html>



