<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "telemedicina");
if ($mysqli->connect_errno) die("Errore DB: " . $mysqli->connect_error);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $mysqli->real_escape_string($_POST["nome"]);
    $email = $mysqli->real_escape_string($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $tipo = $_POST["tipo_utente"];
    $mysqli->query("INSERT INTO Utente (nome, email, password, tipo_utente) VALUES ('$nome', '$email', '$password', '$tipo')");
    $id = $mysqli->insert_id;
    if ($tipo == "Medico") {
        $spec = $mysqli->real_escape_string($_POST["specializzazione"]);
        $mysqli->query("INSERT INTO Medico (id_medico, specializzazione) VALUES ($id, '$spec')");
    } else {
        $data = $_POST["data_nascita"];
        $mysqli->query("INSERT INTO Paziente (id_paziente, data_nascita) VALUES ($id, '$data')");
    }
    echo "<p>Registrazione completata. <a href='login.php'>Accedi</a></p>";
}
?>
<form method="POST">
    Nome: <input type="text" name="nome" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    Tipo: 
    <select name="tipo_utente" onchange="toggle(this.value)">
        <option value="">--</option>
        <option value="Medico">Medico</option>
        <option value="Paziente">Paziente</option>
    </select><br>
    <div id="medico" style="display:none">Specializzazione: <input name="specializzazione"><br></div>
    <div id="paziente" style="display:none">Data di nascita: <input type="date" name="data_nascita"><br></div>
    <input type="submit" value="Registrati">
</form>
<script>
function toggle(tipo) {
    document.getElementById("medico").style.display = tipo == "Medico" ? "block" : "none";
    document.getElementById("paziente").style.display = tipo == "Paziente" ? "block" : "none";
}
</script>
