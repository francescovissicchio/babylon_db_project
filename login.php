<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "telemedicina");
if ($mysqli->connect_errno) die("Errore: " . $mysqli->connect_error);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $mysqli->real_escape_string($_POST["email"]);
    $password = $_POST["password"];
    $res = $mysqli->query("SELECT * FROM Utente WHERE email = '$email'");
    if ($res->num_rows == 1) {
        $utente = $res->fetch_assoc();
        if (password_verify($password, $utente["password"])) {
            $_SESSION["id_utente"] = $utente["id_utente"];
            $_SESSION["nome"] = $utente["nome"];
            $_SESSION["tipo_utente"] = $utente["tipo_utente"];
            header("Location: " . strtolower($utente["tipo_utente"]) . ".php");
            exit;
        }
    }
    echo "<p>Login fallito</p>";
}
?>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Accedi">
</form>
