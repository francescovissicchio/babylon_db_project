<?php
$host = "localhost";
$db = "progetto_babylon_vissicchio";
$user = "root";
$pass = ""; // <-- lasciato vuoto se non hai impostato una password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>

