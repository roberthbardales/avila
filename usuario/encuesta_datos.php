<?php
session_start();
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Completar Datos - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css"> <!-- O tu hoja de estilo que uses -->
    <style>
        body {
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            color: #2c5364;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .button {
            width: 100%;
            background-color: #2c5364;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #1b3546;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
            display: none;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>📝 Completa tus datos</h2>

        <div class="error" id="errorMensaje"></div>

        <form id="formulario" method="POST" action="procesar_encuesta_datos.php">
            <!-- En su lugar, usar input hidden con el ID del cliente si lo necesitas -->
            <input type="hidden" name="cliente_id" value="<?php echo $_SESSION['cliente_id']; ?>">

            <label>Nombre completo:</label>
            <input type="text" name="nombre" id="nombre" required>

            <label>Correo electrónico:</label>
            <input type="email" name="correo" id="correo" required>

            <label>Teléfono:</label>
            <input type="text" name="telefono" id="telefono" maxlength="9" required>

            <label>Provincia:</label>
            <select name="provincia" id="provincia" required>
                <option value="">Selecciona tu provincia</option>
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


            <input type="submit" class="button" value="Guardar y continuar">
        </form>
    </div>

    <script>
        document.getElementById('formulario').addEventListener('submit', function(e) {
            const errorDiv = document.getElementById('errorMensaje');
            errorDiv.style.display = 'none';
            errorDiv.innerHTML = '';

            const dni = document.getElementById('dni').value.trim();
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const provincia = document.getElementById('provincia').value;

            let errores = [];

            if (nombre === '') {
                errores.push('⚠️ El nombre no puede estar vacío.');
            }
            if (correo === '' || !correo.includes('@') || !correo.includes('.')) {
                errores.push('⚠️ El correo electrónico no es válido.');
            }
            if (telefono.length !== 9 || !/^\d+$/.test(telefono)) {
                errores.push('⚠️ El teléfono debe tener exactamente 9 dígitos numéricos.');
            }
            if (provincia === '') {
                errores.push('⚠️ Debes seleccionar una provincia válida.');
            }

            if (errores.length > 0) {
                e.preventDefault();
                errorDiv.innerHTML = errores.join('<br>');
                errorDiv.style.display = 'block';
            }
        });
    </script>

</body>

</html>