<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../includes/conexion.php';

// Verificar ID
if (!isset($_GET['id'])) {
    echo "ID de sorteo no especificado.";
    exit();
}

$id = intval($_GET['id']);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha_sorteo'];
    $descripcion = $_POST['descripcion'];
    $activo = $_POST['activo'];
    $destinatario = isset($_POST['destinatario']) ? intval($_POST['destinatario']) : 1;

    // Actualizar sorteo sin imagen
    $sql = "UPDATE sorteos SET nombre = ?, fecha_sorteo = ?, descripcion = ?, activo = ?, destinatario = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssii", $nombre, $fecha, $descripcion, $activo, $destinatario, $id);
    $stmt->execute();

    // Subir nuevas imágenes si existen
    if (!empty($_FILES['imagenes']['name'][0])) {
        $carpeta = '../img_premios/';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['imagenes']['error'][$index] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['imagenes']['name'][$index], PATHINFO_EXTENSION);
                $nombreFinal = 'premio_' . uniqid() . '.' . $extension;
                $rutaDestino = $carpeta . $nombreFinal;

                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $rutaGuardada = 'img_premios/' . $nombreFinal;

                    $stmtImg = $conexion->prepare("INSERT INTO imagenes_sorteo (sorteo_id, url_imagen) VALUES (?, ?)");
                    $stmtImg->bind_param("is", $id, $rutaGuardada);
                    $stmtImg->execute();
                }
            }
        }
    }

    header("Location: listar_sorteos.php");
    exit();
}

// Obtener datos actuales del sorteo
$stmt = $conexion->prepare("SELECT * FROM sorteos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "Sorteo no encontrado.";
    exit();
}
$sorteo = $res->fetch_assoc();

// Obtener imágenes actuales
$stmtImgs = $conexion->prepare("SELECT * FROM imagenes_sorteo WHERE sorteo_id = ?");
$stmtImgs->bind_param("i", $id);
$stmtImgs->execute();
$imagenes = $stmtImgs->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Sorteo - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="form-container">
        <h2>Editar Sorteo: <?php echo htmlspecialchars($sorteo['nombre']); ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Nombre del sorteo:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($sorteo['nombre']); ?>" required>

            <label>Fecha y hora del sorteo:</label>
            <input type="datetime-local" name="fecha_sorteo" value="<?php echo date('Y-m-d\TH:i', strtotime($sorteo['fecha_sorteo'])); ?>" required>

            <label>Descripción:</label>
            <textarea name="descripcion" rows="4" required><?php echo htmlspecialchars($sorteo['descripcion']); ?></textarea>

            <label>¿Activo?</label>
            <select name="activo">
                <option value="si" <?php echo $sorteo['activo'] === 'si' ? 'selected' : ''; ?>>Sí</option>
                <option value="no" <?php echo $sorteo['activo'] === 'no' ? 'selected' : ''; ?>>No</option>
            </select>

            <label>¿Quiénes pueden participar?</label>
            <select name="destinatario">
                <option value="1" <?php echo $sorteo['destinatario'] == 1 ? 'selected' : ''; ?>>🌐 Todos</option>
                <option value="2" <?php echo $sorteo['destinatario'] == 2 ? 'selected' : ''; ?>>⭐ Solo afiliados</option>
                <option value="0" <?php echo $sorteo['destinatario'] == 0 ? 'selected' : ''; ?>>🧍‍♂️ Solo no afiliados</option>
            </select>

            <label>Imágenes actuales:</label><br>
            <?php if ($imagenes->num_rows > 0): ?>
                <?php while ($img = $imagenes->fetch_assoc()): ?>
                    <img src="../<?php echo htmlspecialchars($img['url_imagen']); ?>" alt="Imagen" style="width: 120px; margin: 8px; border-radius: 6px;">
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #777;">No hay imágenes para este sorteo.</p>
            <?php endif; ?>

            <label>Agregar más imágenes:</label>
            <input type="file" name="imagenes[]" accept="image/*" multiple>

            <input type="submit" value="Actualizar sorteo">
        </form>

        <p style="text-align: center;"><a href="listar_sorteos.php" style="color: #2c5364; font-weight: bold;">⬅️ Volver a lista de sorteos</a></p>
    </div>
</body>

</html>
