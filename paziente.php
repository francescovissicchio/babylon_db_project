<?php
session_start();
if (!isset($_SESSION["id_utente"]) || $_SESSION["tipo_utente"] != "Paziente") exit("Accesso negato");
$mysqli = new mysqli("localhost", "root", "", "telemedicina");
$id = $_SESSION["id_utente"];
$visite = $mysqli->query("SELECT v.data_visita, v.descrizione, u.nome AS medico FROM Visita v
JOIN Prenota pr ON v.id_visita = pr.id_visita
JOIN Presiede p ON p.id_visita = v.id_visita
JOIN Medico m ON m.id_medico = p.id_medico
JOIN Utente u ON u.id_utente = m.id_medico
WHERE pr.id_paziente = $id");
echo '<h2>Le tue visite</h2><ul>';
while($v = $visite->fetch_assoc()) echo "<li>{$v['data_visita']} - {$v['descrizione']} (Medico: Dr. {$v['medico']})</li>";
echo '</ul><a href="chatbot.php">Chatbot</a> | <a href="logout.php">Logout</a>';
?>
