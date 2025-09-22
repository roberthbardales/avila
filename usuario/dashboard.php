<?php
session_start();
require_once '../includes/conexion.php';

$clienteId = $_SESSION['cliente_id'] ?? null;
if (!$clienteId) {
    header("Location: login.php");
    exit;
}

try {
    $sql = "
    SELECT 
        s.*,
        (
            SELECT COUNT(DISTINCT t.cliente_id) 
            FROM tickets t 
            WHERE t.sorteo_id = s.id
        ) AS total_participantes,
        (
            SELECT i.url_imagen 
            FROM imagenes_sorteo i 
            WHERE i.sorteo_id = s.id 
            LIMIT 1
        ) AS url_imagen
    FROM sorteos s
    ORDER BY s.fecha_sorteo ASC
";
    // Cargar sorteos con manejo de errores
    $sorteosStmt = $conexion->query($sql);
    $sorteos = $sorteosStmt ? $sorteosStmt->fetch_all(MYSQLI_ASSOC) : [];

    // Datos del usuario en una sola consulta
    $stmtUsuario = $conexion->prepare("SELECT es_afiliado, nombre FROM clientes WHERE id = ?");
    $stmtUsuario->bind_param("i", $_SESSION['cliente_id']);
    $stmtUsuario->execute();
    $usuario = $stmtUsuario->get_result()->fetch_assoc() ?? [];

    // Asignar valores
    $esAfiliado = (bool)($usuario['es_afiliado'] ?? false);
    $_SESSION['es_afiliado'] = $esAfiliado;
    $_SESSION['cliente_nombre'] = htmlspecialchars($usuario['nombre'] ?? 'Usuario');

    // Contar TODOS los tickets del usuario (corregido)
    $stmtTotal = $conexion->prepare("SELECT COUNT(*) AS total FROM tickets WHERE cliente_id = ?");
    $stmtTotal->bind_param("i", $clienteId);
    $stmtTotal->execute();
    $resultadoTotal = $stmtTotal->get_result();
    $totalData = $resultadoTotal->fetch_assoc();
    $totalUserTickets = $totalData['total'] ?? 0;

    setlocale(LC_TIME, 'es_ES.UTF-8');
} catch (Exception $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $totalUserTickets = 0; // Valor por defecto si hay error
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giveaway Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #2c5364;
            /* Color base del dashboard */
        }

        .card {
            background-color: #1e3a4a;
            /* Un tono más oscuro derivado */
        }

        .btn-primary {
            background-color: #0f6cbf;
        }

        .btn-primary:hover {
            background-color: #0c5aa1;
        }

        .btn-locked {
            background-color: #4b5563;
        }
    </style>
</head>

<body class="text-white font-sans">
    <div class="max-w-7xl mx-auto p-6">
        <!-- Perfil de Usuario -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <img src="https://i.pravatar.cc/100" alt="User Photo" class="w-20 h-20 rounded-full border-4 border-white" />
                <div>
                    <h2 class="text-2xl font-semibold"><?= $_SESSION['cliente_nombre'] ?></h2>
                    <p class="text-sm text-gray-300"><?= number_format($totalUserTickets) ?> Tickets generados</p>
                    <span class="inline-block <?= $esAfiliado ? 'bg-green-700' : 'bg-red-700' ?> text-xs px-3 py-1 rounded mt-1">
                        <?= $esAfiliado ? 'Afiliado' : 'No Afiliado' ?>
                    </span>
                </div>
            </div>

            <form action="logout.php" method="POST">
                <button type="submit" class="bg-blue-400 hover:bg-blue-500 text-white font-semibold py-2 px-4 rounded transition duration-300">
                    🔒 Cerrar sesión
                </button>
            </form>
        </div>

        <h3 class="text-xl font-bold mb-6">Your Giveaways</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($sorteos as $sorteo): ?>
                <?php
                $fechaObj = new DateTime($sorteo['fecha_sorteo']);
                $fechaTexto = $fechaObj->format("M d, Y H:i");
                $fechaISO = $fechaObj->format("Y-m-d\TH:i:s");

                // Verificar permisos
                $destinatario = (int) $sorteo['destinatario'];
                $puedeIngresar = $destinatario == 1 || ($destinatario == 2 && $esAfiliado) || ($destinatario == 3 && !$esAfiliado);

                $etiqueta = match ($destinatario) {
                    1 => 'Público',
                    2 => 'Solo Afiliados',
                    3 => 'Solo No Afiliados',
                    default => 'Desconocido'
                };
                ?>
                <div class="card p-4 rounded-xl shadow-lg relative">
                    <span class="absolute top-2 right-2 bg-slate-700 text-xs px-2 py-1 rounded">
                        <?= $etiqueta ?>
                    </span>

                    <p class="text-sm text-gray-300"><?= $fechaTexto ?></p>
                    <img src="<?= htmlspecialchars('../' . ($sorteo['url_imagen'] ?? 'img/sin-imagen.jpg')) ?>" loading="lazy"
                        alt="Imagen Premio"
                        class="w-full h-48 object-cover my-3 rounded-lg" />
                    <p class="text-xl font-semibold mb-1" id="temporizador-<?= $sorteo['id'] ?>" data-fecha="<?= $fechaISO ?>">
                        Cargando...
                    </p>
                    <p><?= number_format($sorteo['total_participantes']) ?> Participantes</p>

                    <?php if ($puedeIngresar): ?>
                        <form action="ver_sorteo.php" method="GET">
                            <input type="hidden" name="id" value="<?= $sorteo['id'] ?>">
                            <button class="w-full btn-primary text-white py-2 rounded-lg font-medium">Ingresar</button>
                        </form>
                    <?php else: ?>
                        <button class="w-full btn-locked text-white py-2 rounded-lg font-medium flex items-center justify-center gap-2 cursor-not-allowed">
                            🔒 No disponible para tu perfil
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        // Temporizador optimizado
        function actualizarTemporizadores() {
            document.querySelectorAll('[id^="temporizador-"]').forEach(element => {
                const fechaSorteo = new Date(element.dataset.fecha);
                const ahora = new Date();
                const diff = fechaSorteo - ahora;

                if (diff <= 0) {
                    element.innerText = "🎉 En curso o finalizado";
                    return;
                }

                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
                const m = Math.floor((diff / (1000 * 60)) % 60);
                const s = Math.floor((diff / 1000) % 60);

                element.innerText = `${d}d ${h}h ${m}m ${s}s restantes`;
            });
        }

        setInterval(actualizarTemporizadores, 1000);
        actualizarTemporizadores();
    </script>

</body>

</html>