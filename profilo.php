<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente']) || !isset($_SESSION['tipo_utente'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$tipo_utente = $_SESSION['tipo_utente'];

if ($tipo_utente === 'Admin') {
    echo "<h1>Admin</h1>";
    echo "<img src='uploads/babylon.jpg' alt='Admin Avatar' style='width:150px; height:150px; border-radius:50%; object-fit:cover;'><br>";
    echo "<p>Email: admin@babylon.it</p>";

    echo "<h2>📊 Statistiche</h2>";
    $res = $conn->query("SELECT tipo_utente, COUNT(*) AS totale FROM utente GROUP BY tipo_utente");
    $tot = [];
    while ($r = $res->fetch_assoc()) $tot[$r['tipo_utente']] = $r['totale'];

    echo "<ul>
        <li>👥 Utenti totali: <strong>" . array_sum($tot) . "</strong></li>
        <li>🪰 Medici: " . ($tot['Medico'] ?? 0) . "</li>
        <li>👤 Pazienti: " . ($tot['Paziente'] ?? 0) . "</li>
        <li>🔧 Admin: " . ($tot['Admin'] ?? 0) . "</li>
    </ul>";

    $v = $conn->query("SELECT COUNT(*) AS visite FROM visita")->fetch_assoc();
    echo "<p>🗕️ Visite totali (già svolte + in attesa di conferma): <strong>{$v['visite']}</strong></p>";

    $m = $conn->query("SELECT COUNT(*) AS disponibili FROM medico WHERE disponibilita = 1")->fetch_assoc();
    echo "<p>✅ Medici disponibili: <strong>{$m['disponibili']}</strong></p>";

    $c = $conn->query("SELECT COUNT(*) AS chatbot FROM chatbot")->fetch_assoc();
    echo "<p>🤖 Numero interrogazioni Chatbot Babylon: <strong>{$c['chatbot']}</strong></p>";

    echo "<hr><h2>🌟 Ultimi 10 utenti</h2>";
    $res = $conn->query("SELECT nome, cognome, email, tipo_utente FROM utente ORDER BY id_utente DESC LIMIT 10");
    echo "<table border='1'><tr><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
    while ($r = $res->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($r['nome']) . ' ' . htmlspecialchars($r['cognome']) . "</td><td>" . htmlspecialchars($r['email']) . "</td><td>" . htmlspecialchars($r['tipo_utente']) . "</td></tr>";
    }
    echo "</table>";

    echo "<hr><h2>📋 Ultime 10 visite</h2>";
    $q = "
        SELECT v.data_visita, v.esito_visita,
               pz.nome AS nome_paziente, pz.cognome AS cognome_paziente,
               m.nome AS nome_medico, m.cognome AS cognome_medico
        FROM visita v
        JOIN utente pz ON v.id_paziente = pz.id_utente
        JOIN utente m ON v.id_medico = m.id_utente
        ORDER BY v.data_visita DESC
        LIMIT 10
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
} else {
    // Carica dati utente
    $stmt = $conn->prepare("SELECT nome, cognome, email, foto_profilo FROM utente WHERE id_utente = ?");
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $utente = $stmt->get_result()->fetch_assoc();

    $foto = $utente['foto_profilo'] ? "uploads/" . htmlspecialchars($utente['foto_profilo']) : "uploads/" . ($tipo_utente === 'Medico' ? 'medico.jpg' : 'paziente.jpg');
    $nome = htmlspecialchars($utente['nome']);
    $cognome = htmlspecialchars($utente['cognome']);
    $email = htmlspecialchars($utente['email']);
    $data_registrazione = htmlspecialchars($utente['data_registrazione']);

    // Gestione upload immagine profilo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_foto']) && isset($_FILES['foto'])) {
    $file = $_FILES['foto'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] === UPLOAD_ERR_OK && in_array($ext, $allowed)) {
        $newFileName = 'user_' . $id_utente . '_' . time() . '.' . $ext;
        $uploadPath = 'uploads/' . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Salva il nuovo nome file nel DB
            $stmt = $conn->prepare("UPDATE utente SET foto_profilo = ? WHERE id_utente = ?");
            $stmt->bind_param("si", $newFileName, $id_utente);
            $stmt->execute();

            echo "<p style='color:green;'>✅ Immagine del profilo aggiornata con successo.</p>";
            // Aggiorna $foto per mostrare subito l'immagine nuova
            $foto = $uploadPath;
        } else {
            echo "<p style='color:red;'>❌ Errore nel salvataggio del file.</p>";
        }
    } else {
        echo "<p style='color:red;'>⚠️ File non valido. Ammessi: jpg, jpeg, png, gif, webp.</p>";
    }
}


    if ($tipo_utente === 'Medico') {
    echo "<h1>Doc $nome $cognome</h1>";
    } else {
    echo "<h1>Salve, $nome $cognome!</h1>";
    }


    echo "<img src='$foto?v=" . time() . "' style='width:150px; height:150px; border-radius:50%; object-fit:cover;'><br>";
    echo "<p>Email: $email</p>";
    echo "<p>🗓️ Registrato il: $data_registrazione</p>";

    echo "<form method='POST' enctype='multipart/form-data'>
            <input type='file' name='foto' accept='image/*' required>
            <button type='submit' name='upload_foto'>📤 Cambia Foto</button>
          </form>";

    if ($tipo_utente === 'Medico') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['toggle_disponibilita'])) {
                $nuovo_stato = ($_POST['stato_attuale'] == 1) ? 0 : 1;
                $stmt = $conn->prepare("UPDATE medico SET disponibilita = ? WHERE id_medico = ?");
                $stmt->bind_param("ii", $nuovo_stato, $id_utente);
                $stmt->execute();
                echo "<p style='color:green;'>✅ Stato disponibilità aggiornato.</p>";
            }

            if (isset($_POST['aggiorna_specializzazione'])) {
                $specializzazione = $_POST['specializzazione'] ?? '';
                $specializzazioni_valide = ['Cardiologia', 'Dermatologia', 'Neurologia', 'Ortopedia', 'Medicina generale'];
                if (in_array($specializzazione, $specializzazioni_valide)) {
                    $stmt = $conn->prepare("UPDATE medico SET specializzazione = ? WHERE id_medico = ?");
                    $stmt->bind_param("si", $specializzazione, $id_utente);
                    $stmt->execute();
                    echo "<p style='color:green;'>✅ Specializzazione aggiornata.</p>";
                }
            }

            if (isset($_POST['accetta_visita'])) {
                $chatbot_id = (int)$_POST['chatbot_id'];
                $data_visita = $_POST['data_visita'] ?? null;
                if ($data_visita && DateTime::createFromFormat('Y-m-d\TH:i', $data_visita) !== false) {
                    $stmt = $conn->prepare("SELECT id_paziente FROM chat WHERE id_chatbot = ?");
                    $stmt->bind_param("i", $chatbot_id);
                    $stmt->execute();
                    $res = $stmt->get_result()->fetch_assoc();

                    if ($res) {
                        $id_paziente = $res['id_paziente'];
                        $stmt = $conn->prepare("INSERT INTO visita (id_paziente, id_medico, id_chatbot, data_visita, esito_visita) VALUES (?, ?, ?, ?, ?)");
                        $esito = "In attesa...";
                        $stmt->bind_param("iiiss", $id_paziente, $id_utente, $chatbot_id, $data_visita, $esito);
                        $stmt->execute();
                        echo "<p style='color:green;'>✅ Visita confermata per $data_visita.</p>";
                    }
                }
            }

            if (isset($_POST['rifiuta_visita'])) {
                $chatbot_id = (int)$_POST['chatbot_id'];
                $stmt = $conn->prepare("DELETE FROM sceglie WHERE id_chatbot = ? AND id_medico = ?");
                $stmt->bind_param("ii", $chatbot_id, $id_utente);
                $stmt->execute();
                echo "<p style='color:red;'>❌ Richiesta rifiutata.</p>";
            }
        }

        $stmt = $conn->prepare("SELECT specializzazione, rating, disponibilita FROM medico WHERE id_medico = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $medico = $stmt->get_result()->fetch_assoc();

        $specializzazione = htmlspecialchars($medico['specializzazione']);
        $rating = htmlspecialchars($medico['rating']);
        $disponibilita = (int)$medico['disponibilita'];
        $stato = $disponibilita ? '🟢 Disponibile' : '🔴 Non disponibile';

        echo "<p>Specializzazione: $specializzazione</p>";
        echo "<p>Rating: $rating</p>";
        echo "<p><strong>Stato:</strong> $stato</p>";

        echo "<form method='POST'>
                <input type='hidden' name='stato_attuale' value='$disponibilita'>
                <button type='submit' name='toggle_disponibilita'>🔄 Cambia Disponibilità</button>
              </form>";

        echo "<h3>✏️ Modifica Specializzazione</h3>
              <form method='POST'>
                  <select name='specializzazione'>
                      <option value='Cardiologia'>Cardiologia</option>
                      <option value='Dermatologia'>Dermatologia</option>
                      <option value='Neurologia'>Neurologia</option>
                      <option value='Ortopedia'>Ortopedia</option>
                      <option value='Medicina generale'>Medicina generale</option>
                  </select>
                  <button type='submit' name='aggiorna_specializzazione'>Salva</button>
              </form>";

        $stmt = $conn->prepare("SELECT COUNT(*) AS richieste
                                FROM sceglie s
                                JOIN chat c ON s.id_chatbot = c.id_chatbot
                                WHERE s.id_medico = ? AND c.id_chatbot NOT IN (
                                    SELECT id_chatbot FROM visita WHERE id_medico = ?
                                )");
        $stmt->bind_param("ii", $id_utente, $id_utente);
        $stmt->execute();
        $notifiche = $stmt->get_result()->fetch_assoc()['richieste'];

        if ($notifiche > 0) {
            echo "<p style='color:orange;'>🔔 Hai $notifiche richiesta/e da pazienti tramite Babylon</p>";
        }

        echo "<h2>📅 Visite Programmate</h2>";
        $stmt = $conn->prepare("SELECT v.data_visita, u.nome, u.cognome, u.email, v.esito_visita
                                FROM visita v
                                JOIN utente u ON v.id_paziente = u.id_utente
                                WHERE v.id_medico = ?
                                ORDER BY v.data_visita ASC");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $visite = $stmt->get_result();

        if ($visite->num_rows > 0) {
            echo "<table border='1'>
                    <tr><th>Data e Ora</th><th>Paziente</th><th>Email</th><th>Esito</th></tr>";
            while ($v = $visite->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($v['data_visita']) . "</td>
                        <td>" . htmlspecialchars($v['nome'] . ' ' . $v['cognome']) . "</td>
                        <td>" . htmlspecialchars($v['email']) . "</td>
                        <td>" . htmlspecialchars($v['esito_visita']) . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nessuna visita programmata.</p>";
        }
    }

    elseif ($tipo_utente === 'Paziente') {
        // Codice per paziente
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
            echo "<p>🗓️ Registrato il: $data_registrazione</p>";
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

            echo "<h3>🔄 Aggiorna statura e peso</h3>
                <form method='POST'>
                    <label>Statura (cm):</label><br>
                    <input type='number' name='statura_cm' value='" . htmlspecialchars($statura) . "' required><br>
                    <label>Peso (kg):</label><br>
                    <input type='number' name='peso_kg' value='" . htmlspecialchars($peso) . "' step='0.1' required><br><br>
                    <button type='submit' name='aggiorna_dati'>📅 Salva</button>
                </form>";
        }
        echo "<h2>🤖 Interazione con Babylon</h2>";
        echo "<p>Hai bisogno di un consulto? Puoi descrivere i tuoi sintomi al nostro chatbot Babylon!</p>";
        echo "<form method='GET' action='chatbot_logic.php'>
            <button type='submit'>💬 Avvia Interazione con Babylon</button>
        </form>";

        echo "<h2>📅 Storico Visite</h2>";

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
            echo "<h2>🤖 Richieste Babylon</h2>";
$stmt = $conn->prepare("
    SELECT u.nome, u.cognome, u.email, c.id_chatbot, c.data_avvio, cb.sintomi_riportati
    FROM chat c
    JOIN chatbot cb ON cb.id_chatbot = c.id_chatbot
    JOIN utente u ON c.id_paziente = u.id_utente
    ORDER BY c.data_avvio DESC
");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr><th>Paziente</th><th>Email</th><th>Data Richiesta</th><th>Sintomi</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $nome_paziente = htmlspecialchars($row['nome'] . ' ' . $row['cognome']);
        $email = htmlspecialchars($row['email']);
        $data_avvio = htmlspecialchars($row['data_avvio']);
        $sintomi = htmlspecialchars($row['sintomi_riportati']);
        echo "<tr>
                <td>$nome_paziente</td>
                <td>$email</td>
                <td>$data_avvio</td>
                <td>$sintomi</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nessuna nuova richiesta tramite Babylon.</p>";
}


    }



    }

echo '<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">🏠 Torna alla Home</a>';
?>









