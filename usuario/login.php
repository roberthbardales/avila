<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - CLEF</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            /* Azul oscuro */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            text-align: center;
            color: #2c5364;
            margin-bottom: 30px;
        }

        label {
            display: block;
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #2c5364;
            outline: none;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #2c5364;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #1b3546;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Iniciar Sesión en CLEF</h2>
        <?php if (isset($_GET['error'])): ?>
            <div style="background-color: #f87171; color: white; padding: 10px 15px; margin-bottom: 20px; border-radius: 8px; text-align: center;">
                <?php if ($_GET['error'] === 'usuario'): ?>
                    🚫 DNI no registrado.
                <?php elseif ($_GET['error'] === 'clave'): ?>
                    ❌ Contraseña incorrecta.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="procesar_login.php">
            <label for="dni">DNI:</label>
            <input type="text" name="dni" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" required>

            <input type="submit" value="Iniciar sesión">
        </form>
    </div>
</body>

</html>