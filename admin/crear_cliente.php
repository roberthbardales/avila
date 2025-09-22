<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $provincia = $_POST['provincia'];
    $password = trim($_POST['password']);
    if (empty($password)) {
        $password = $dni;
    }

    // Encriptar la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $es_afiliado = isset($_POST['es_afiliado']) ? intval($_POST['es_afiliado']) : 0;

    $stmt = $conexion->prepare("INSERT INTO clientes (dni, nombre, correo, telefono, provincia, password, es_afiliado)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $dni, $nombre, $correo, $telefono, $provincia, $passwordHash, $es_afiliado);
    $stmt->execute();

    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Cliente - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="form-container">
        <h2>➕ Registrar nuevo cliente</h2>
        <form method="POST">
            <label>DNI:</label>
            <input type="text" name="dni" required>

            <label>Nombre completo:</label>
            <input type="text" name="nombre">

            <label>Correo electrónico:</label>
            <input type="email" name="correo">

            <label>Teléfono:</label>
            <input type="text" name="telefono">

            <label>Provincia:</label>
            <select name="provincia" required>
                <option value="">Selecciona una provincia</option>
                <option value="Amazonas">Amazonas</option>
                <option value="Áncash">Áncash</option>
                <option value="Apurímac">Apurímac</option>
                <option value="Arequipa">Arequipa</option>
                <option value="Ayacucho">Ayacucho</option>
                <option value="Cajamarca">Cajamarca</option>
                <option value="Callao">Callao</option>
                <option value="Cusco">Cusco</option>
                <option value="Huancavelica">Huancavelica</option>
                <option value="Huánuco">Huánuco</option>
                <option value="Ica">Ica</option>
                <option value="Junín">Junín</option>
                <option value="La Libertad">La Libertad</option>
                <option value="Lambayeque">Lambayeque</option>
                <option value="Lima">Lima</option>
                <option value="Loreto">Loreto</option>
                <option value="Madre de Dios">Madre de Dios</option>
                <option value="Moquegua">Moquegua</option>
                <option value="Pasco">Pasco</option>
                <option value="Piura">Piura</option>
                <option value="Puno">Puno</option>
                <option value="San Martín">San Martín</option>
                <option value="Tacna">Tacna</option>
                <option value="Tumbes">Tumbes</option>
                <option value="Ucayali">Ucayali</option>
            </select>

            <label>Contraseña:</label>
            <input type="text" name="password" placeholder="Por defecto puede ser el DNI">

            <label>¿Es afiliado?</label>
            <select name="es_afiliado">
                <option value="1">✅ Sí</option>
                <option value="0" selected>❌ No</option>
            </select><br><br>

            <input type="submit" value="Registrar cliente">
        </form>

        <p><a href="admin_dashboard.php">⬅️ Volver al panel de administración</a></p>
    </div>
</body>

</html>