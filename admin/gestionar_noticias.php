<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../includes/conexion.php';

// Obtener todas las noticias
$sql = "SELECT * FROM noticias ORDER BY fecha DESC";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Noticias - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">

        <h2 class="mb-4">📰 Gestión de Noticias</h2>
        <!-- Botón de regreso -->
        <div class="mb-3 text-start">
            <a href="admin_dashboard.php" class="btn btn-outline-secondary">⬅ Volver al Panel</a>
        </div>
        <div class="mb-3 text-end">
            <a href="crear_noticia.php" class="btn btn-success">➕ Nueva noticia</a>
        </div>

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Imágenes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado->num_rows > 0): ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= $fila['id']; ?></td>
                            <td><?= htmlspecialchars($fila['titulo']); ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($fila['tipo']); ?></span></td>
                            <td><?= date('d/m/Y', strtotime($fila['fecha'])); ?></td>
                            <td><?= substr(strip_tags($fila['descripcion']), 0, 60) . '...'; ?></td>
                            <td>
                                <?php
                                // Obtener imágenes asociadas a la noticia
                                $sqlImg = "SELECT ruta_imagen FROM noticia_imagenes WHERE noticia_id = ? LIMIT 3";
                                $stmtImg = $conexion->prepare($sqlImg);
                                $stmtImg->bind_param("i", $fila['id']);
                                $stmtImg->execute();
                                $resImg = $stmtImg->get_result();

                                while ($img = $resImg->fetch_assoc()):
                                ?>
                                    <img src="../<?= $img['ruta_imagen']; ?>" width="60" height="60" class="me-1 mb-1 rounded" alt="img">
                                <?php endwhile; ?>
                            </td>
                            <td>
                                <a href="editar_noticia.php?id=<?= $fila['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                <a href="eliminar_noticia.php?id=<?= $fila['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta noticia?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay noticias registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>