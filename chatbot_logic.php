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

    $stmt = $conn->prepare("INSERT INTO sceglie (id_chatbot, id_medico) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_chatbot, $id_medico);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO visita (id_paziente, id_medico) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_paziente, $id_medico);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO chat (id_paziente, id_chatbot, data_avvio) VALUES (?, ?, CURDATE())");
    $stmt->bind_param("ii", $id_paziente, $id_chatbot);
    $stmt->execute();
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
        }
        .bot-box {
            background-color: #f0f8ff;
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
        img.bot-avatar {
            width: 60px;
            vertical-align: middle;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<!-- Barra di avanzamento -->
<div class="progress">
    <div class="<?= $step === 1 ? 'active' : '' ?>">1. Inserisci Sintomi</div>
    <div class="<?= $step === 2 ? 'active' : '' ?>">2. Scegli Medico</div>
    <div class="<?= $step === 3 ? 'active' : '' ?>">3. Visita Creata</div>
</div>

<?php if ($step === 1): ?>
    <div class="bot-box">
        <img src="https://cdn-icons-png.flaticon.com/512/4712/4712109.png" class="bot-avatar" alt="Bot">
        <strong>Dr. Babylon:</strong> Ciao! Raccontami i tuoi sintomi e ti aiuterò a trovare lo specialista più adatto.
    </div>

    <form method="POST">
        <label for="sintomi">Descrivi i tuoi sintomi:</label><br>
        <textarea name="sintomi" rows="5" cols="60" required></textarea><br><br>
        <button type="submit">Invia</button>
    </form>

<?php elseif ($step === 2): ?>
    <div class="bot-box">
        <strong>Dr. Babylon:</strong> In base ai sintomi che hai riportato, ecco i medici che possono aiutarti. Scegli quello che preferisci.
    </div>

    <?php if ($medici->num_rows > 0): ?>
        <form method="POST">
            <input type="hidden" name="id_chatbot" value="<?= $id_chatbot ?>">
            <table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th>Seleziona</th>
                    <th>Nome</th>
                    <th>Rating</th>
                </tr>
                <?php while ($row = $medici->fetch_assoc()): ?>
                    <tr>
                        <td><input type="radio" name="id_medico" value="<?= $row['id_utente'] ?>" required></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= $row['Rating'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table><br>
            <button type="submit">Conferma Medico</button>
        </form>
    <?php else: ?>
        <p><strong>Dr. Babylon:</strong> Mi dispiace, al momento non ci sono medici disponibili per la specializzazione individuata.</p>
    <?php endif; ?>

<?php elseif ($step === 3): ?>
    <div class="bot-box">
        <strong>Dr. Babylon:</strong> Visita registrata con successo! Puoi trovare i dettagli nel tuo profilo.
    </div>
    <a href="profilo.php">Torna al tuo profilo</a>
<?php endif; ?>

</body>
</html>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>
