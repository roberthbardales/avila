<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Admin - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="panel-container">
        <h2>Panel de Administración - CLEF 🎛️</h2>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></p>

        <ul>
            <li><a href="crear_sorteo.php">➕ Crear nuevo sorteo</a></li>
            <li><a href="listar_sorteos.php">📋 Ver y administrar sorteos</a></li>
            <li><a href="crear_cliente.php">👤 Registrar nuevo cliente</a></li>
            <li><a href="listar_clientes.php">👥 Ver y administrar clientes</a></li>
            <li><a href="realizar_sorteo.php">🎯 Realizar sorteo</a></li>
            <li><a href="gestionar_noticias.php">📰 Gestión de noticias</a></li> <!-- NUEVO ENLACE -->
            <li><a href="logout_admin.php">🚪 Cerrar sesión</a></li>
        </ul>

    </div>
</body>

</html>