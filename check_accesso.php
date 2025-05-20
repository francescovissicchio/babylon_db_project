<?php
require_once 'config.php'; // Assicurati che abbia la connessione $conn

if (!isset($_SESSION['id_utente']) || !isset($_SESSION['tipo_utente'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];

// Controlla se l'utente è stato disattivato
$stmt = $conn->prepare("SELECT cancellato FROM utente WHERE id_utente = ?");
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row && $row['cancellato']) {
    session_destroy();
    header("Location: login.php?msg=account_disattivato");
    exit;
}
