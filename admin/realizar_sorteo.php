<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$sorteoId = null;
$cantidad = null;
$tickets = [];
$ganadores = [];
$error = "";

// Obtener sorteos al inicio para siempre mostrar el combo
$sorteos = $conexion->query("SELECT id, nombre FROM sorteos ORDER BY fecha_sorteo DESC")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sorteoId = intval($_POST['sorteo_id'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 0);

    // Obtener todos los participantes
    $stmt = $conexion->prepare("SELECT t.codigo_ticket, c.nombre 
        FROM tickets t 
        INNER JOIN clientes c ON c.id = t.cliente_id
        WHERE t.sorteo_id = ?");
    $stmt->bind_param("i", $sorteoId);
    $stmt->execute();
    $tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Si se ejecuta sorteo
    if (isset($_POST['ejecutar_sorteo'])) {
        if ($cantidad > count($tickets)) {
            $error = "No hay suficientes tickets para seleccionar $cantidad ganador(es).";
        } else {
            shuffle($tickets);
            $ganadores = array_slice($tickets, 0, $cantidad);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>🎯 Realizar Sorteo - CLEF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-center text-blue-800 mb-6">🎯 Realizar Sorteo</h1>

        <form method="POST" class="grid md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="sorteo_id" class="font-semibold">Selecciona un sorteo:</label>
                <select name="sorteo_id" id="sorteo_id" class="w-full border rounded p-2" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($sorteos as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= isset($sorteoId) && $sorteoId == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="cantidad" class="font-semibold">Cantidad de ganadores:</label>
                <input type="number" name="cantidad" id="cantidad" class="w-full border rounded p-2" min="1">
            </div>

            <div class="col-span-full flex flex-col sm:flex-row gap-4">
                <button name="buscar_participantes" type="submit" class="w-full bg-yellow-500 text-white py-2 rounded hover:bg-yellow-600">
                    🔍 Buscar Participantes
                </button>
                <button name="ejecutar_sorteo" type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    🏆 Ejecutar Sorteo
                </button>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="text-red-600 font-semibold text-center mb-4"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($sorteoId ?? false): ?>
            <!-- Lista de Participantes -->
            <div class="mb-10">
                <h2 class="text-xl font-bold text-gray-700 mb-3">👥 Lista de Participantes</h2>
                <?php if (count($tickets) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm bg-white border rounded overflow-hidden">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="p-2 text-left">#</th>
                                    <th class="p-2 text-left">Nombre</th>
                                    <th class="p-2 text-left">Código de Ticket</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $index => $t): ?>
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="p-2"><?= $index + 1 ?></td>
                                        <td class="p-2"><?= htmlspecialchars($t['nombre']) ?></td>
                                        <td class="p-2"><?= htmlspecialchars($t['codigo_ticket']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 italic">No hay participantes aún para este sorteo.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (count($ganadores) > 0): ?>
            <!-- Lista de Ganadores -->
            <div class="mt-8">
                <h2 class="text-xl font-bold text-green-700 mb-4">🏅 Ganadores del Sorteo</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($ganadores as $i => $g): ?>
                        <div class="bg-green-50 border border-green-300 rounded p-4 shadow-sm">
                            <p class="font-semibold text-green-800">#<?= $i + 1 ?> <?= htmlspecialchars($g['nombre']) ?></p>
                            <p class="text-sm text-green-600 mt-1">🎫 Ticket: <strong><?= htmlspecialchars($g['codigo_ticket']) ?></strong></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
