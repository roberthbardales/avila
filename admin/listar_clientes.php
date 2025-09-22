<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include '../includes/conexion.php';

// Eliminar cliente
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM clientes WHERE id = $id");
    header("Location: listar_clientes.php");
    exit();
}

// Exportar CSV
if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=clientes.csv');
    $salida = fopen('php://output', 'w');
    fputcsv($salida, ['ID', 'DNI', 'Nombre', 'Correo', 'Teléfono', 'Provincia', 'Contraseña', 'Afiliado']);

    $query = "SELECT * FROM clientes";
    $filtro = [];
    $params = [];

    if (!empty($_GET['buscar'])) {
        $filtro[] = "(dni LIKE ? OR nombre LIKE ?)";
        $buscar = "%" . trim($_GET['buscar']) . "%";
        $params[] = $buscar;
        $params[] = $buscar;
    }

    if (!empty($_GET['provincia'])) {
        $filtro[] = "provincia = ?";
        $params[] = $_GET['provincia'];
    }

    if (!empty($filtro)) {
        $query .= " WHERE " . implode(" AND ", $filtro);
    }

    $stmt = $conexion->prepare($query);
    if ($params) {
        $types = str_repeat("s", count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        fputcsv($salida, [
            $row['id'],
            $row['dni'],
            $row['nombre'],
            $row['correo'],
            $row['telefono'],
            $row['provincia'],
            $row['es_afiliado'] == 1 ? 'Sí' : 'No'
        ]);
    }
    exit();
}

// Buscar / Filtrar
$buscar = $_GET['buscar'] ?? '';
$provincia = $_GET['provincia'] ?? '';

$sql = "SELECT * FROM clientes";
$condiciones = [];
$parametros = [];

if ($buscar !== '') {
    $condiciones[] = "(dni LIKE ? OR nombre LIKE ?)";
    $parametros[] = "%$buscar%";
    $parametros[] = "%$buscar%";
}

if ($provincia !== '') {
    $condiciones[] = "provincia = ?";
    $parametros[] = $provincia;
}

if ($condiciones) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}
$sql .= " ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
if ($parametros) {
    $tipos = str_repeat("s", count($parametros));
    $stmt->bind_param($tipos, ...$parametros);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Obtener lista de provincias distintas
$provinciasRes = $conexion->query("SELECT DISTINCT provincia FROM clientes WHERE provincia IS NOT NULL AND provincia != '' ORDER BY provincia");
$provincias = [];
while ($row = $provinciasRes->fetch_assoc()) {
    $provincias[] = $row['provincia'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Clientes Registrados - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'actualizado'): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
            ✅ Cliente actualizado con éxito.
        </div>
    <?php endif; ?>
    <h2>👥 Clientes Registrados</h2>
    <p><a href="admin_dashboard.php">⬅️ Volver al panel</a></p>

    <!-- FORMULARIO DE BÚSQUEDA Y FILTRO -->
    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="buscar" placeholder="Buscar por DNI o nombre" value="<?php echo htmlspecialchars($buscar); ?>">
        <select name="provincia">
            <option value="">Todas las provincias</option>
            <?php foreach ($provincias as $prov): ?>
                <option value="<?php echo $prov; ?>" <?php echo $provincia === $prov ? 'selected' : ''; ?>>
                    <?php echo $prov; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="🔍 Buscar">
        <a href="listar_clientes.php">Limpiar</a>
        <a href="?exportar=csv&buscar=<?php echo urlencode($buscar); ?>&provincia=<?php echo urlencode($provincia); ?>" class="button" style="margin-left: 10px; background: green; color: white;">⬇️ Exportar CSV</a>
    </form>

    <!-- TABLA DE CLIENTES -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>DNI</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Provincia</th>
                <th>Contraseña</th>
                <th>Afiliado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['dni']; ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                    <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($row['provincia']); ?></td>
                    <td>
                        <?php echo (!empty($row['password'])) ? '<em style="color: gray;">••••••</em>' : '<span style="color: red;">No asignada</span>'; ?>
                    </td>
                    <td>
                        <?php echo ($row['es_afiliado'] == 1) ? '✅ Sí' : '❌ No'; ?>
                    </td>
                    <td>
                        <a href="editar_cliente.php?id=<?php echo $row['id']; ?>" class="button editar">Editar</a>
                        <a href="?eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Eliminar este cliente?');" class="button eliminar">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>