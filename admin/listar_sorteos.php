<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../includes/conexion.php';

// Activar/desactivar sorteo
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $nuevoEstado = ($_GET['toggle'] === 'si') ? 'no' : 'si';
    $stmt = $conexion->prepare("UPDATE sorteos SET activo = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevoEstado, $id);
    $stmt->execute();
    header("Location: listar_sorteos.php");
    exit();
}

// Eliminar sorteo
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM sorteos WHERE id = $id");
    header("Location: listar_sorteos.php");
    exit();
}

// Obtener todos los sorteos
$resultado = $conexion->query("SELECT * FROM sorteos ORDER BY fecha_sorteo DESC");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Sorteos - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .galeria-img {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .galeria-img img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>

<body>
    <div class="sorteos-container">
        <h2>📋 Lista de Sorteos</h2>
        <p><a href="admin_dashboard.php">⬅️ Volver al panel</a></p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Dirigido a</th>
                    <th>Imágenes</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo date("d/m/Y H:i", strtotime($row['fecha_sorteo'])); ?></td>
                        <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                        <td>
                            <?php
                            switch ($row['destinatario']) {
                                case 0: echo '🧍‍♂️ No afiliados'; break;
                                case 1: echo '🌐 Todos'; break;
                                case 2: echo '⭐ Solo afiliados'; break;
                                default: echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="galeria-img">
                                <?php
                                $sorteoId = $row['id'];
                                $stmtImg = $conexion->prepare("SELECT url_imagen FROM imagenes_sorteo WHERE sorteo_id = ?");
                                $stmtImg->bind_param("i", $sorteoId);
                                $stmtImg->execute();
                                $resImg = $stmtImg->get_result();

                                if ($resImg->num_rows > 0) {
                                    while ($img = $resImg->fetch_assoc()) {
                                        echo '<img src="../' . htmlspecialchars($img['url_imagen']) . '" alt="Imagen">';
                                    }
                                } else {
                                    echo '<span style="color:#999;">Sin imágenes</span>';
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <a href="?toggle=<?php echo $row['activo']; ?>&id=<?php echo $row['id']; ?>" class="button <?php echo ($row['activo'] === 'si') ? 'activo' : 'inactivo'; ?>">
                                <?php echo strtoupper($row['activo']); ?>
                            </a>
                        </td>
                        <td>
                            <a href="editar_sorteo.php?id=<?php echo $row['id']; ?>" class="button editar">Editar</a>
                            <a href="?eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este sorteo?')" class="button eliminar">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
