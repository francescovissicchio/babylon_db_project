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

// CSRF token setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function deduciSpecializzazione($testo) {
    $url = 'http://localhost:8000/deduci';

    $data = json_encode(['sintomi' => $testo]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return "Medicina generale"; // fallback in caso di errore
    }

    curl_close($ch);

    $result = json_decode($response, true);
    return $result['specializzazione'] ?? "Medicina generale";
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
    <title>Chatbot Babylon</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('uploads/hammurabi.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }

        .container {
            background: rgba(0, 0, 0, 0.4);
            padding: 30px;
            max-width: 700px;
            margin: 50px auto;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(255,255,255,0.7);
        }

        .bot-box {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 5px solid #333333;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .progress div {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-bottom: 3px solid #ccc;
            color: #ccc;
        }

        .progress .active {
            border-color: #333333;
            font-weight: bold;
            color: #fff;
        }

        textarea, input[type="radio"] {
            margin-top: 10px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: none;
            resize: none;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
        }

        th, td {
            padding: 10px;
            border: 1px solid #444;
            color: #fff;
            text-align: center;
        }

        button {
            background-color: #333333;
            color: white;
            padding: 10px 20px;
            border: none;
            margin-top: 15px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #333333;
        }

        a.button {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 20px;
            background: #f0f0f0;
            color: black;
            text-decoration: none;
            border-radius: 8px;
        }

        a.button.secondary {
            background: #333333;
        }

        a.button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">


<div class="progress">
    <div class="<?= $step === 1 ? 'active' : '' ?>">1. Inserisci Sintomi</div>
    <div class="<?= $step === 2 ? 'active' : '' ?>">2. Scegli Medico</div>
    <div class="<?= $step === 3 ? 'active' : '' ?>">3. Conferma</div>
</div>

<?php if ($step === 1): ?>
    <div class="bot-box">
        <strong>Chatbot Babylon:</strong> Ciao! Raccontami i tuoi sintomi e ti aiuterò a trovare lo specialista più adatto.
    </div>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label for="sintomi">Descrivi i tuoi sintomi:</label><br>
        <textarea name="sintomi" rows="5" required></textarea><br><br>
        <button type="submit">Invia Sintomi</button>
    </form>

<?php elseif ($step === 2): ?>
    <div class="bot-box">
        <strong>Chatbot Babylon:</strong> Ho analizzato i tuoi sintomi. Questi sono i medici più adatti:
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
        <p><strong>Chatbot Babylon:</strong> Nessun medico disponibile al momento con la specializzazione richiesta.</p>
        <a href="chatbot_logic.php">🔁 Reinserisci sintomi</a>
    <?php endif; ?>

<?php elseif ($step === 3): ?>
    <div class="bot-box">
        <strong>Chatbot Babylon:</strong> ✅ Visita prenotata con successo! Puoi trovare i dettagli nel tuo profilo.
    </div>
<?php endif; ?>

<a href="profilo.php" style="display:inline-block; margin-top: 10px; padding: 10px 20px; background:#f0f0f0; color:black; text-decoration:none; border-radius:8px;">
👤 Torna al Profilo
</a>

<a href="index.php" style="display:inline-block; margin-top: 30px; padding: 10px 20px; background:#333333; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>
</body>
</html>



