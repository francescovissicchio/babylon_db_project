<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['tipo_utente'] !== 'Admin') {
    echo "Accesso negato.";
    exit;
}

// RESET solo delle visite collegate
$conn->query("DELETE FROM visita");
$conn->query("DELETE FROM chat");
$conn->query("DELETE FROM sceglie");
$conn->query("DELETE FROM chatbot");

// Recupera medici disponibili e pazienti
$medici = $conn->query("SELECT id_medico FROM medico WHERE disponibilita = 1")->fetch_all(MYSQLI_ASSOC);
$pazienti = $conn->query("SELECT id_paziente FROM paziente")->fetch_all(MYSQLI_ASSOC);

if (count($medici) === 0 || count($pazienti) === 0) {
    echo "<h3>❌ Nessun medico disponibile o pazienti registrati.</h3>";
    exit;
}

// Limita visite per medico (max 5)
$visite_per_medico = [];
foreach ($medici as $m) {
    $visite_per_medico[$m['id_medico']] = 0;
}

// Dati random
$sintomi_possibili = ['Febbre', 'Mal di testa', 'Tosse', 'Dolore toracico', 'Affaticamento', 'Rash', 'Vertigini'];
$esiti_possibili = ['Controllo OK', 'Necessario follow-up', 'Prescrizione farmaco', 'Esame consigliato'];

$visite_create = 0;
$max_visite = 200;

for ($i = 0; $i < $max_visite; $i++) {
    // Medico random (massimo 5 visite)
    $tentativi = 0;
    do {
        $rand = array_rand($medici);
        $id_medico = $medici[$rand]['id_medico'];
        $tentativi++;
    } while ($visite_per_medico[$id_medico] >= 5 && $tentativi < 20);

    if ($visite_per_medico[$id_medico] >= 5) {
        continue;
    }

    $id_paziente = $pazienti[array_rand($pazienti)]['id_paziente'];

    // Sintomi casuali
    $sintomi = array_rand(array_flip($sintomi_possibili), rand(1, 3));
    $sintomi = is_array($sintomi) ? implode(", ", $sintomi) : $sintomi;

    // Data e ora casuale negli ultimi 30 giorni
    $giorni_fa = rand(0, 29);
    $ora = str_pad(rand(8, 20), 2, '0', STR_PAD_LEFT);
    $minuto = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    $data_visita = date("Y-m-d", strtotime("-$giorni_fa days")) . " $ora:$minuto:00";

    // Chatbot
    $stmt = $conn->prepare("INSERT INTO chatbot (nome_bot, sintomi_riportati) VALUES (?, ?)");
    $bot = "MedBot";
    $stmt->bind_param("ss", $bot, $sintomi);
    $stmt->execute();
    $id_chatbot = $conn->insert_id;

    // Chat
    $stmt = $conn->prepare("INSERT INTO chat (id_paziente, id_chatbot, data_avvio, data_fine) VALUES (?, ?, ?, ?)");
    $data_chat = substr($data_visita, 0, 10);
    $stmt->bind_param("iiss", $id_paziente, $id_chatbot, $data_chat, $data_chat);
    $stmt->execute();

    // Scelta medico
    $stmt = $conn->prepare("INSERT INTO sceglie (id_chatbot, id_medico) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_chatbot, $id_medico);
    $stmt->execute();

    // Visita
    $esito = $esiti_possibili[array_rand($esiti_possibili)];
    $stmt = $conn->prepare("INSERT INTO visita (id_paziente, id_medico, id_chatbot, data_visita, esito_visita) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $id_paziente, $id_medico, $id_chatbot, $data_visita, $esito);
    $stmt->execute();

    $visite_per_medico[$id_medico]++;
    $visite_create++;
}

// Ora mostra la tabella aggiornata
echo "<h2>📋 Visite rigenerate: $visite_create</h2>";

$query = "
    SELECT v.id_visita, v.data_visita, v.esito_visita,
           paz.nome AS paziente,
           med.nome AS medico,
           c.sintomi_riportati
    FROM visita v
    JOIN utente paz ON v.id_paziente = paz.id_utente
    JOIN utente med ON v.id_medico = med.id_utente
    LEFT JOIN chatbot c ON v.id_chatbot = c.id_chatbot
    ORDER BY v.data_visita DESC
";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='6' cellspacing='0'>
            <tr>
                <th>ID</th>
                <th>Data/Ora</th>
                <th>Paziente</th>
                <th>Medico</th>
                <th>Sintomi</th>
                <th>Esito</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id_visita']}</td>
                <td>{$row['data_visita']}</td>
                <td>" . htmlspecialchars($row['paziente']) . "</td>
                <td>" . htmlspecialchars($row['medico']) . "</td>
                <td>" . htmlspecialchars($row['sintomi_riportati']) . "</td>
                <td>" . nl2br(htmlspecialchars($row['esito_visita'])) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nessuna visita trovata.</p>";
}
?>

<a href="index.php" style="display:inline-block; margin-top: 20px; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>

