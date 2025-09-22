<?php
session_start();
session_unset();      // Limpia todas las variables de sesión
session_destroy();    // Destruye la sesión actual
header("Location: admin_login.php"); // Redirige al login del admin
exit();
?>
