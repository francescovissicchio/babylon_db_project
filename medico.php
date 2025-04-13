<?php
session_start();
if (!isset($_SESSION["id_utente"]) || $_SESSION["tipo_utente"] != "Medico") exit("Accesso negato");
$mysqli = new mysqli("localhost", "root", "", "telemedicina");
$id = $_SESSION["id_utente"];
$visite = $mysqli->query("SELECT v.data_visita, v.descrizione, u.nome AS paziente FROM Visita v
JOIN Presiede p ON v.id_visita = p.id_visita
JOIN Prenota pr ON pr.id_visita = v.id_visita
JOIN Paziente pa ON pa.id_paziente = pr.id_paziente
JOIN Utente u ON u.id_utente = pa.id_paziente
WHERE p.id_medico = $id");
echo '<h2>Visite</h2><ul>';
while($v = $visite->fetch_assoc()) echo "<li>{$v['data_visita']} - {$v['descrizione']} (Paziente: {$v['paziente']})</li>";
echo '</ul><a href="logout.php">Logout</a>';
?>
