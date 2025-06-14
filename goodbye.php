<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Account Disattivato</title>
    <style>
        body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: url('uploads/jinn.jpg') no-repeat center center fixed;
        background-size: cover;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; /* ✅ per centratura verticale */
        }


        .box {
        background: rgba(255, 255, 255, 0.6);
        display: inline-block;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.8);
        color: black; /* ✅ aggiunto */
        max-width: 500px;
        text-align: center;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #0077cc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>👋 Account disattivato</h1>
        <p>Hai disattivato il tuo account. Se cambi idea, puoi recuperarlo in qualsiasi momento.</p>
        <a href="recupera_account.php">🔄 Recupera Account</a><br>
        <a href="index.php">🏠 Torna alla Home</a>
    </div>
</body>
</html>
