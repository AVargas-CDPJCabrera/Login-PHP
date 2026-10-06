<?php
// Configuración de la base de datos
$host     = 'localhost';
$dbname   = 'registrouser'; // Cambia por el nombre de tu BD
$username = 'root';              // Tu usuario de MySQL
$password = '';                  // Tu contraseña de MySQL

$errores = [];
$exito   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recogida y saneamiento de datos
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido1 = trim($_POST['apellido1'] ?? '');
    $apellido2 = trim($_POST['apellido2'] ?? '');
    $nick      = trim($_POST['nick'] ?? '');
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $clave     = $_POST['clave'] ?? '';
    $clave2    = $_POST['clave_confirm'] ?? '';
    $nif       = !empty($_POST['nif']) ? trim($_POST['nif']) : null;
    $telefono  = !empty($_POST['telefono']) ? (int)$_POST['telefono'] : null;
    $domicilio = !empty($_POST['domicilio']) ? trim($_POST['domicilio']) : null;
    $poblacion = !empty($_POST['poblacion']) ? trim($_POST['poblacion']) : null;
    $provincia = !empty($_POST['provincia']) ? trim($_POST['provincia']) : null;

    // 2. Validaciones básicas
    if (empty($nombre) || empty($apellido1) || empty($apellido2) || empty($nick)) {
        $errores[] = 'El nombre, apellidos y nick son obligatorios.';
    }
    if (!$email) {
        $errores[] = 'Introduce un correo electrónico válido.';
    }
    if (strlen($clave) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($clave !== $clave2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // 3. Procesar fotografía (LONGBLOB)
    $fotografia = null;
    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
        $tipoArchivo = mime_content_type($_FILES['fotografia']['tmp_name']);
        if (str_starts_with($tipoArchivo, 'image/')) {
            $fotografia = file_get_contents($_FILES['fotografia']['tmp_name']);
        } else {
            $errores[] = 'El archivo subido debe ser una imagen válida.';
        }
    }

    // 4. Inserción en la base de datos si no hay errores
    if (empty($errores)) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Comprobar si el nick o email ya existen
            $stmtCheck = $pdo->prepare("SELECT Usuario_id FROM usuarios WHERE Usuario_nick = ? OR Usuario_email = ? LIMIT 1");
            $stmtCheck->execute([$nick, $email]);
            if ($stmtCheck->fetch()) {
                $errores[] = 'El nombre de usuario (nick) o el correo ya están registrados.';
            } else {
                // Generar hash seguro de la contraseña
                $claveHash = password_hash($clave, PASSWORD_DEFAULT);

                // Campos automáticos requeridos por la tabla
                $fechaAlta       = date('Y-m-d');
                $bloqueado       = 0;
                $numeroIntentos  = 0;
                $perfil          = 'usuario';
                $tokenAleatorio  = bin2hex(random_bytes(16)); // Token de 32 caracteres

                $sql = "INSERT INTO usuarios (
                    Usuario_nombre, Usuario_apellido1, Usuario_apellido2,
                    Usuario_nick, Usuario_clave, Usuario_fecha_alta,
                    Usuario_email, Usuario_bloqueado, Usuario_numero_intentos,
                    Usuario_token_aleatorio, Usuario_domicilio, Usuario_poblacion,
                    Usuario_provincia, Usuario_perfil, Usuario_nif,
                    Usuario_numero_telefono, Usuario_fotografia
                ) VALUES (
                    :nombre, :apellido1, :apellido2,
                    :nick, :clave, :fecha_alta,
                    :email, :bloqueado, :intentos,
                    :token, :domicilio, :poblacion,
                    :provincia, :perfil, :nif,
                    :telefono, :fotografia
                )";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellido1', $apellido1);
                $stmt->bindParam(':apellido2', $apellido2);
                $stmt->bindParam(':nick', $nick);
                $stmt->bindParam(':clave', $claveHash);
                $stmt->bindParam(':fecha_alta', $fechaAlta);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':bloqueado', $bloqueado, PDO::PARAM_INT);
                $stmt->bindParam(':intentos', $numeroIntentos, PDO::PARAM_INT);
                $stmt->bindParam(':token', $tokenAleatorio);
                $stmt->bindParam(':domicilio', $domicilio);
                $stmt->bindParam(':poblacion', $poblacion);
                $stmt->bindParam(':provincia', $provincia);
                $stmt->bindParam(':perfil', $perfil);
                $stmt->bindParam(':nif', $nif);
                $stmt->bindParam(':telefono', $telefono, $telefono ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindParam(':fotografia', $fotografia, PDO::PARAM_LOB);

                $stmt->execute();
                $exito = '¡Registro completado con éxito! Ya puedes iniciar sesión.';
            }
        } catch (PDOException $e) {
            $errores[] = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --error: #ef4444;
            --success: #22c55e;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg); color: var(--text); padding: 2rem 1rem; display: flex; justify-content: center; }

        .container {
            width: 100%;
            max-width: 720px;
            background: var(--card);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        h1 { font-size: 1.75rem; margin-bottom: 0.5rem; color: #0f172a; }
        p.subtitle { color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem; }

        fieldset {
            border: none;
            margin-bottom: 1.75rem;
        }

        legend {
            font-weight: 600;
            font-size: 1.1rem;
            color: #0f172a;
            border-bottom: 2px solid var(--border);
            padding-bottom: 0.4rem;
            margin-bottom: 1rem;
            width: 100%;
        }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }

        .form-group { display: flex; flex-direction: column; margin-bottom: 1rem; }
        label { font-size: 0.875rem; font-weight: 500; margin-bottom: 0.35rem; color: #334155; }
        label span.req { color: var(--error); }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="file"] {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border-radius: 6px;
            border: 1px solid var(--border);
            font-size: 0.95rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        input[type="file"] { padding: 0.45rem; background: var(--bg); }

        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            background: var(--primary);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s ease-in-out;
        }

        .btn-submit:hover { background: var(--primary-hover); }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert ul { margin-left: 1.25rem; }

        @media (max-width: 640px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .container { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Registro de Usuario</h1>
    <p class="subtitle">Rellena los datos para crear tu cuenta en el sistema.</p>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errores as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($exito) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <!-- 1. Datos Personales -->
        <fieldset>
            <legend>Datos Personales</legend>
            <div class="form-group">
                <label for="nombre">Nombre <span class="req">*</span></label>
                <input type="text" id="nombre" name="nombre" maxlength="45" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="apellido1">Primer Apellido <span class="req">*</span></label>
                    <input type="text" id="apellido1" name="apellido1" maxlength="45" required value="<?= htmlspecialchars($_POST['apellido1'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="apellido2">Segundo Apellido <span class="req">*</span></label>
                    <input type="text" id="apellido2" name="apellido2" maxlength="45" required value="<?= htmlspecialchars($_POST['apellido2'] ?? '') ?>">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="nif">NIF / DNI</label>
                    <input type="text" id="nif" name="nif" maxlength="10" placeholder="12345678X" value="<?= htmlspecialchars($_POST['nif'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" maxlength="12" placeholder="600123456" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                </div>
            </div>
        </fieldset>

        <!-- 2. Credenciales y Acceso -->
        <fieldset>
            <legend>Cuenta y Acceso</legend>
            <div class="grid-2">
                <div class="form-group">
                    <label for="nick">Nombre de usuario (Nick) <span class="req">*</span></label>
                    <input type="text" id="nick" name="nick" maxlength="45" required value="<?= htmlspecialchars($_POST['nick'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico <span class="req">*</span></label>
                    <input type="email" id="email" name="email" maxlength="45" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="clave">Contraseña <span class="req">*</span></label>
                    <input type="password" id="clave" name="clave" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="clave_confirm">Confirmar Contraseña <span class="req">*</span></label>
                    <input type="password" id="clave_confirm" name="clave_confirm" minlength="6" required>
                </div>
            </div>
        </fieldset>

        <!-- 3. Dirección y Ubicación -->
        <fieldset>
            <legend>Dirección (Opcional)</legend>
            <div class="form-group">
                <label for="domicilio">Domicilio</label>
                <input type="text" id="domicilio" name="domicilio" maxlength="45" placeholder="Calle, número, piso..." value="<?= htmlspecialchars($_POST['domicilio'] ?? '') ?>">
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="poblacion">Población</label>
                    <input type="text" id="poblacion" name="poblacion" maxlength="45" value="<?= htmlspecialchars($_POST['poblacion'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="provincia">Provincia</label>
                    <input type="text" id="provincia" name="provincia" maxlength="45" value="<?= htmlspecialchars($_POST['provincia'] ?? '') ?>">
                </div>
            </div>
        </fieldset>

        <!-- 4. Fotografía -->
        <fieldset>
            <legend>Fotografía de Perfil</legend>
            <div class="form-group">
                <label for="fotografia">Subir Imagen</label>
                <input type="file" id="fotografia" name="fotografia" accept="image/*">
            </div>
        </fieldset>

        <button type="submit" class="btn-submit">Registrarse</button>
    </form>
</div>

</body>
</html>