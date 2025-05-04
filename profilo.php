<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$nome = $_SESSION['nome'];
$tipo_utente = $_SESSION['tipo_utente'];

echo "<h1>Benvenuto, $nome!</h1>";

// Recupero dati utente base (email, tipo_utente)
$stmt = $conn->prepare("SELECT email, tipo_utente FROM utente WHERE id_utente = ?");
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();

echo "<p>Email: " . htmlspecialchars($utente['email']) . "</p>";
echo "<p>Tipo utente: " . htmlspecialchars($utente['tipo_utente']) . "</p>";

// Se è Medico
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
// Se è Paziente
elseif ($tipo_utente === 'Paziente') {
    $stmt = $conn->prepare("SELECT * FROM paziente WHERE id_paziente = ?");
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $paziente = $stmt->get_result()->fetch_assoc();

    if ($paziente) {
        echo "<h2>Dati Paziente</h2>";
        echo "<p>Data di nascita: " . htmlspecialchars($paziente['data_nascita']) . "</p>";
        echo "<p>Sesso: " . htmlspecialchars($paziente['Sesso']) . "</p>";
    }

    // Storico visite
    echo "<h2>Storico Visite</h2>";
    $query = "
        SELECT v.data_visita, v.esito_visita, m.Specializzazione, u.nome AS nome_medico
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
                <tr>
                    <th>Data Visita</th>
                    <th>Medico</th>
                    <th>Specializzazione</th>
                    <th>Esito</th>
                </tr>";
        while ($visita = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($visita['data_visita']) . "</td>
                    <td>" . htmlspecialchars($visita['nome_medico']) . "</td>
                    <td>" . htmlspecialchars($visita['Specializzazione']) . "</td>
                    <td>" . htmlspecialchars($visita['esito_visita']) . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nessuna visita registrata.</p>";
    }
}
// Se è Admin
if ($tipo_utente === 'Admin') {
    echo "<h2>📊 Statistiche generali</h2>";

    // Totali utenti
    $res = $conn->query("SELECT tipo_utente, COUNT(*) as totale FROM utente GROUP BY tipo_utente");
    $totali = [];
    while ($r = $res->fetch_assoc()) {
        $totali[$r['tipo_utente']] = $r['totale'];
    }
    $total_users = array_sum($totali);

    echo "<ul>";
    echo "<li>👥 Utenti totali: <strong>$total_users</strong></li>";
    echo "<li>🩺 Medici: " . ($totali['Medico'] ?? 0) . "</li>";
    echo "<li>👤 Pazienti: " . ($totali['Paziente'] ?? 0) . "</li>";
    echo "<li>🔧 Admin: " . ($totali['Admin'] ?? 0) . "</li>";
    echo "</ul>";

    // Totali visite
    $res = $conn->query("SELECT COUNT(*) AS tot_visite FROM visita");
    $visite = $res->fetch_assoc();
    echo "<p>📅 Visite registrate: <strong>{$visite['tot_visite']}</strong></p>";

    // Medici disponibili
    $res = $conn->query("SELECT COUNT(*) as disponibili FROM medico WHERE disponibilita = 1");
    $disp = $res->fetch_assoc();
    echo "<p>✅ Medici disponibili: <strong>{$disp['disponibili']}</strong></p>";

    // Tot chatbot
    $res = $conn->query("SELECT COUNT(*) AS chatbot FROM chatbot");
    $bot = $res->fetch_assoc();
    echo "<p>🤖 Chatbot creati: <strong>{$bot['chatbot']}</strong></p>";

    echo "<hr>";

    echo "<h2>🆕 Ultimi 5 utenti registrati</h2>";
    $res = $conn->query("SELECT nome, email, tipo_utente FROM utente ORDER BY id_utente DESC LIMIT 5");

    echo "<table border='1'><tr><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
    while ($r = $res->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($r['nome']) . "</td><td>" . htmlspecialchars($r['email']) . "</td><td>{$r['tipo_utente']}</td></tr>";
    }
    echo "</table>";

    echo "<hr>";

    echo "<h2>📋 Ultime 5 visite registrate</h2>";
    $query = "
        SELECT v.data_visita, v.esito_visita,
               pz.nome AS paziente, m.nome AS medico
        FROM visita v
        JOIN utente pz ON v.id_paziente = pz.id_utente
        JOIN utente m ON v.id_medico = m.id_utente
        ORDER BY v.data_visita DESC
        LIMIT 5
    ";
    $res = $conn->query($query);

    if ($res->num_rows > 0) {
        echo "<table border='1'>
                <tr><th>Data</th><th>Paziente</th><th>Medico</th><th>Esito</th></tr>";
        while ($v = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$v['data_visita']}</td>
                    <td>{$v['paziente']}</td>
                    <td>{$v['medico']}</td>
                    <td>{$v['esito_visita']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nessuna visita registrata.</p>";
    }
}


?>

<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>

