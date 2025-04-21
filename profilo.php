<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.html");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$nome = $_SESSION['nome'];
$tipo_utente = $_SESSION['tipo_utente'];

// Recupero dati personali
$stmt = $conn->prepare("SELECT * FROM utente WHERE id_utente = ?");
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();

echo "<h1>Benvenuto, $nome!</h1>";
echo "<p>Email: {$utente['email']}</p>";
echo "<p>Tipo utente: {$utente['tipo_utente']}</p>";

if ($tipo_utente === 'Medico') {
    // Dati medico
    $stmt = $conn->prepare("SELECT * FROM medico WHERE id_medico = ?");
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $medico = $stmt->get_result()->fetch_assoc();

    echo "<h2>Dati Medico</h2>";
    echo "<p>Specializzazione: {$medico['Specializzazione']}</p>";
    echo "<p>Rating: {$medico['Rating']}</p>";
} else {
    // Dati paziente
    $stmt = $conn->prepare("SELECT * FROM paziente WHERE id_paziente = ?");
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $paziente = $stmt->get_result()->fetch_assoc();

    echo "<h2>Dati Paziente</h2>";
    echo "<p>Data di nascita: {$paziente['data_nascita']}</p>";
    echo "<p>Sesso: {$paziente['Sesso']}</p>";

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
                    <td>{$visita['data_visita']}</td>
                    <td>{$visita['nome_medico']}</td>
                    <td>{$visita['Specializzazione']}</td>
                    <td>{$visita['esito_visita']}</td>
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
