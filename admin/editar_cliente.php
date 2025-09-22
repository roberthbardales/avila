<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../includes/conexion.php';

// Verificar que venga el ID
if (!isset($_GET['id'])) {
    echo "ID de cliente no proporcionado.";
    exit();
}

$clienteId = intval($_GET['id']);

// Obtener datos actuales del cliente
$stmt = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $clienteId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "Cliente no encontrado.";
    exit();
}

$cliente = $res->fetch_assoc(); // Ya lo tienes disponible aquí

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $provincia = $_POST['provincia'];
    $passwordInput = trim($_POST['password']);
    $passwordFinal = $cliente['password']; // Ya definida correctamente

    if (!empty($passwordInput)) {
        $passwordFinal = password_hash($passwordInput, PASSWORD_DEFAULT);
    }

    $es_afiliado = isset($_POST['es_afiliado']) ? intval($_POST['es_afiliado']) : 0;

    $stmt = $conexion->prepare("UPDATE clientes SET dni = ?, nombre = ?, correo = ?, telefono = ?, provincia = ?, password = ?, es_afiliado = ? WHERE id = ?");
    $stmt->bind_param("ssssssii", $dni, $nombre, $correo, $telefono, $provincia, $passwordFinal, $es_afiliado, $clienteId);
    $stmt->execute();

    header("Location: listar_clientes.php?msg=actualizado");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="form-container">
        <h2>Editar Cliente: <?php echo htmlspecialchars($cliente['nombre'] ?? 'Sin nombre'); ?></h2>
        <form method="POST">
            <label>DNI:</label>
            <input type="text" name="dni" value="<?php echo $cliente['dni']; ?>" required>

            <label>Nombre completo:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>">

            <label>Correo electrónico:</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($cliente['correo']); ?>">

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>">

            <label>Provincia:</label>
            <select name="provincia" required>
                <option value="">Selecciona una provincia</option>
                <?php
                $provincias = [
                    'Amazonas',
                    'Áncash',
                    'Apurímac',
                    'Arequipa',
                    'Ayacucho',
                    'Cajamarca',
                    'Callao',
                    'Cusco',
                    'Huancavelica',
                    'Huánuco',
                    'Ica',
                    'Junín',
                    'La Libertad',
                    'Lambayeque',
                    'Lima',
                    'Loreto',
                    'Madre de Dios',
                    'Moquegua',
                    'Pasco',
                    'Piura',
                    'Puno',
                    'San Martín',
                    'Tacna',
                    'Tumbes',
                    'Ucayali'
                ];
                foreach ($provincias as $prov) {
                    $selected = ($cliente['provincia'] === $prov) ? 'selected' : '';
                    echo "<option value=\"$prov\" $selected>$prov</option>";
                }
                ?>
            </select>

            <label>Contraseña:</label>
            <input type="password" name="password" id="password" placeholder="Dejar vacío para mantener la actual">
            <input type="checkbox" onclick="togglePassword()"> Mostrar contraseña

            <label>¿Es afiliado?</label>
            <select name="es_afiliado">
                <option value="1" <?php echo $cliente['es_afiliado'] == 1 ? 'selected' : ''; ?>>✅ Sí</option>
                <option value="0" <?php echo $cliente['es_afiliado'] == 0 ? 'selected' : ''; ?>>❌ No</option>
            </select>

            <input type="submit" value="Actualizar cliente">
        </form>

        <p><a href="listar_clientes.php">⬅️ Volver al listado de clientes</a></p>
    </div>
    <script>
        function togglePassword() {
            var pass = document.getElementById("password");
            pass.type = (pass.type === "password") ? "text" : "password";
        }
    </script>
</body>

</html>