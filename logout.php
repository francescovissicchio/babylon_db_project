<?php
session_start();
session_unset();
session_destroy();
echo "Logout effettuato.";
?>
<a href="index.php" style="display:inline-block; margin: 20px 0; padding: 10px 20px; background:#0077cc; color:white; text-decoration:none; border-radius:8px;">
🏠 Torna alla Home
</a>
