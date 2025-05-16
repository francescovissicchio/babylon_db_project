<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$tipo_utente = $_SESSION['tipo_utente'];

// Recupera info utente con nome e cognome
$stmt = $conn->prepare("SELECT nome, cognome, email, tipo_utente FROM utente WHERE id_utente = ?");
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();

$nome = htmlspecialchars($utente['nome']);
$cognome = htmlspecialchars($utente['cognome']);
$email = htmlspecialchars($utente['email']);
$tipo = htmlspecialchars($utente['tipo_utente']);

echo "<h1>Benvenuto, $nome $cognome!</h1>";
echo "<p>Email: $email</p>";
echo "<p>Tipo utente: $tipo</p>";

// ✅ Eliminazione account (solo pazienti)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['elimina_account']) && $tipo_utente === 'Paziente') {
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    $conn->query("DELETE FROM visita WHERE id_paziente = $id_utente");
    $conn->query("DELETE FROM chat WHERE id_paziente = $id_utente");
    $conn->query("DELETE FROM sceglie WHERE id_chatbot IN (SELECT id_chatbot FROM chat WHERE id_paziente = $id_utente)");
    $conn->query("DELETE FROM chatbot WHERE id_chatbot IN (SELECT id_chatbot FROM chat WHERE id_paziente = $id_utente)");
    $conn->query("DELETE FROM paziente WHERE id_paziente = $id_utente");
    $conn->query("DELETE FROM utente WHERE id_utente = $id_utente");

    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    session_destroy();
    header("Location: index.php");
    exit;
}

// 👨‍⚕️ Se Medico
if ($tipo_utente === 'Medico') {
    $stmt = $conn->prepare("SELECT * FROM medico WHERE id_medico = ?");
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $medico = $stmt->get_result()->fetch_assoc();

    if ($medico) {
        echo "<h2>Dati Medico</h2>";
        echo "<p>Specializzazione: " . htmlspecialchars($medico['Specializzazione']) . "</p>";
        echo "<p>Rating: " . htmlspecialchars($medico['Rating']) . "</p>";
    }
}

// 👤 Se Paziente
elseif ($tipo_utente === 'Paziente') {

    // ✅ Aggiorna peso e statura
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiorna_dati'])) {
        $statura_cm = (int)$_POST['statura_cm'];
        $peso_kg = (float)$_POST['peso_kg'];

        if ($statura_cm >= 50 && $statura_cm <= 250 && $peso_kg >= 10 && $peso_kg <= 300) {
            $stmt = $conn->prepare("UPDATE paziente SET statura_cm = ?, peso_kg = ? WHERE id_paziente = ?");
            $stmt->bind_param("idi", $statura_cm, $peso_kg, $id_utente);
            $stmt->execute();
            echo "<p style='color:green;'>✅ Dati aggiornati con successo.</p>";
        } else {
            echo "<p style='color:red;'>⚠️ Inserisci valori realistici per statura e peso.</p>";
        }
    }

    // Recupera dati paziente
    $stmt = $conn->prepare("SELECT * FROM paziente WHERE id_paziente = ?");
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $paziente = $stmt->get_result()->fetch_assoc();

    if ($paziente) {
        $data_nascita = $paziente['data_nascita'] ?? 'N/A';
        $sesso = $paziente['sesso'] ?? 'N/A';
        $statura = $paziente['statura_cm'] ?? null;
        $peso = $paziente['peso_kg'] ?? null;

        echo "<h2>Dati Paziente</h2>";
        echo "<p>Data di nascita: " . htmlspecialchars($data_nascita) . "</p>";
        echo "<p>Sesso: " . htmlspecialchars($sesso) . "</p>";

        if ($statura && $peso) {
            $bmi = $peso / pow($statura / 100, 2);
            $bmi = round($bmi, 1);
            if ($bmi < 18.5) $categoria = "Sottopeso";
            elseif ($bmi < 25) $categoria = "Normopeso";
            elseif ($bmi < 30) $categoria = "Sovrappeso";
            else $categoria = "Obesità";

            echo "<p>Statura: {$statura} cm</p>";
            echo "<p>Peso: {$peso} kg</p>";
            echo "<p><strong>BMI: {$bmi} ({$categoria})</strong></p>";
        } else {
            echo "<p>Statura e peso non ancora registrati.</p>";
        }

        // 🔁 Form aggiornamento
        echo "<h3>🔄 Aggiorna statura e peso</h3>
            <form method='POST'>
                <label>Statura (cm):</label><br>
                <input type='number' name='statura_cm' value='" . htmlspecialchars($statura) . "' required><br>
                <label>Peso (kg):</label><br>
                <input type='number' name='peso_kg' value='" . htmlspecialchars($peso) . "' step='0.1' required><br><br>
                <button type='submit' name='aggiorna_dati'>💾 Salva</button>
            </form>";
    }

    // 📋 Storico visite
    echo "<h2>Storico Visite</h2>";
    $query = "
        SELECT v.data_visita, v.esito_visita, m.Specializzazione, u.nome AS nome_medico, u.cognome AS cognome_medico
        FROM visita v
        JOIN medico m ON v.id_medico = m.id_medico
        JOIN utente u ON m.id_medico = u.id_utente
        WHERE v.id_paziente = ?
        ORDER BY v.data_visita DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr><th>Data Visita</th><th>Medico</th><th>Specializzazione</th><th>Esito</th></tr>";
        while ($v = $result->fetch_assoc()) {
            $nome_medico = htmlspecialchars($v['nome_medico']) . ' ' . htmlspecialchars($v['cognome_medico']);
            echo "<tr>
                    <td>" . htmlspecialchars($v['data_visita']) . "</td>
                    <td>$nome_medico</td>
                    <td>" . htmlspecialchars($v['Specializzazione']) . "</td>
                    <td>" . htmlspecialchars($v['esito_visita']) . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nessuna visita registrata.</p>";
    }

    echo "<hr>";
    echo "<a href='chatbot_logic.php' style='display:inline-block; padding: 10px 20px; background:#00aa66; color:white; text-decoration:none; border-radius:5px;'>💬 Chatta con il Chatbot Babylon</a>";
    echo "<hr>";
    echo "<form method='POST' onsubmit=\"return confirm('⚠️ Sei sicuro di voler eliminare definitivamente il tuo account?');\">
            <input type='hidden' name='elimina_account' value='1'>
            <button type='submit' style='background:red; color:white; padding:10px 20px; border:none; border-radius:5px;'>❌ Elimina Account</button>
          </form>";
}

// ⚙️ Se Admin
if ($tipo_utente === 'Admin') {
    echo "<h2>📊 Statistiche</h2>";
    $res = $conn->query("SELECT tipo_utente, COUNT(*) AS totale FROM utente GROUP BY tipo_utente");
    $tot = [];
    while ($r = $res->fetch_assoc()) $tot[$r['tipo_utente']] = $r['totale'];
    echo "<ul>
            <li>👥 Utenti totali: <strong>" . array_sum($tot) . "</strong></li>
            <li>🩺 Medici: " . ($tot['Medico'] ?? 0) . "</li>
            <li>👤 Pazienti: " . ($tot['Paziente'] ?? 0) . "</li>
            <li>🔧 Admin: " . ($tot['Admin'] ?? 0) . "</li>
          </ul>";

    $v = $conn->query("SELECT COUNT(*) AS visite FROM visita")->fetch_assoc();
    echo "<p>📅 Visite totali: <strong>{$v['visite']}</strong></p>";

    $m = $conn->query("SELECT COUNT(*) AS disponibili FROM medico WHERE disponibilita = 1")->fetch_assoc();
    echo "<p>✅ Medici disponibili: <strong>{$m['disponibili']}</strong></p>";

    $c = $conn->query("SELECT COUNT(*) AS chatbot FROM chatbot")->fetch_assoc();
    echo "<p>🤖 Chatbot creati: <strong>{$c['chatbot']}</strong></p>";

    echo "<hr><h2>🆕 Ultimi 5 utenti</h2>";
    $res = $conn->query("SELECT nome, cognome, email, tipo_utente FROM utente ORDER BY id_utente DESC LIMIT 5");
    echo "<table border='1'><tr><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
    while ($r = $res->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($r['nome']) . ' ' . htmlspecialchars($r['cognome']) . "</td><td>" . htmlspecialchars($r['email']) . "</td><td>" . htmlspecialchars($r['tipo_utente']) . "</td></tr>";
    }
    echo "</table>";

    echo "<hr><h2>📋 Ultime 5 visite</h2>";
    $q = "
        SELECT v.data_visita, v.esito_visita,
               pz.nome AS nome_paziente, pz.cognome AS cognome_paziente,
               m.nome AS nome_medico, m.cognome AS cognome_medico
        FROM visita v
        JOIN utente pz ON v.id_paziente = pz.id_utente
        JOIN utente m ON v.id_medico = m.id_utente
        ORDER BY v.data_visita DESC
        LIMIT 5
    ";
    $res = $conn->query($q);
    echo "<table border='1'><tr><th>Data</th><th>Paziente</th><th>Medico</th><th>Esito</th></tr>";
    while ($v = $res->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($v['data_visita']) . "</td>
                <td>" . htmlspecialchars($v['nome_paziente'] . ' ' . $v['cognome_paziente']) . "</td>
                <td>" . htmlspecialchars($v['nome_medico'] . ' ' . $v['cognome_medico']) . "</td>
                <td>" . htmlspecialchars($v['esito_visita']) . "</td>
              </tr>";
    }
    echo "</table>";
}
?>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>




