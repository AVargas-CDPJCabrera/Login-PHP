<?php
session_start();

// Si ya tiene sesión iniciada, redirigir a su panel correspondiente
if (isset($_SESSION['perfil'])) {
    redirigirSegunPerfil($_SESSION['perfil']);
}

// Configuración de la base de datos
$host     = 'localhost';
$dbname   = 'registrouser'; // Base de datos
$username = 'root';
$password = '';

$error   = '';
$mensaje = '';

if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'logout') {
    $mensaje = 'Has cerrado sesión correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $clave = $_POST['clave'] ?? '';

    if (!$email || empty($clave)) {
        $error = 'Por favor, introduce el correo y la contraseña.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // 1. Buscar usuario por email
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE Usuario_email = ? LIMIT 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $error = 'Credenciales incorrectas.';
            } elseif ($usuario['Usuario_bloqueado'] == 1) {
                // Comprobar si la cuenta está bloqueada
                $error = 'Tu cuenta está bloqueada. Contacta con el administrador.';
            } else {
                // 2. Verificar contraseña cifrada
                if (password_verify($clave, $usuario['Usuario_clave'])) {
                    // Contraseña correcta: regenerar ID de sesión
                    session_regenerate_id(true);

                    // 3. Cargar variables de sesión
                    $_SESSION['id']     = $usuario['Usuario_id'];
                    $_SESSION['nombre'] = $usuario['Usuario_nombre'];
                    $_SESSION['nick']   = $usuario['Usuario_nick'];
                    $_SESSION['email']  = $usuario['Usuario_email'];
                    $_SESSION['perfil'] = strtolower(trim($usuario['Usuario_perfil'] ?? 'usuario'));

                    // 4. Actualizar fecha de última conexión y resetear intentos
                    $update = $pdo->prepare("UPDATE usuarios 
                                             SET Usuario_numero_intentos = 0, 
                                                 Usuario_fecha_ultima_conexion = CURDATE() 
                                             WHERE Usuario_id = ?");
                    $update->execute([$usuario['Usuario_id']]);

                    // 5. Redireccionar según el perfil
                    redirigirSegunPerfil($_SESSION['perfil']);

                } else {
                    // Contraseña incorrecta: incrementar intentos y bloquear al 3er fallo
                    $intentos = $usuario['Usuario_numero_intentos'] + 1;

                    if ($intentos >= 3) {
                        $update = $pdo->prepare("UPDATE usuarios 
                                                 SET Usuario_numero_intentos = ?, 
                                                     Usuario_bloqueado = 1, 
                                                     Usuario_fecha_bloqueo = CURDATE() 
                                                 WHERE Usuario_id = ?");
                        $update->execute([$intentos, $usuario['Usuario_id']]);
                        $error = 'Has superado el número máximo de intentos. Cuenta bloqueada.';
                    } else {
                        $update = $pdo->prepare("UPDATE usuarios SET Usuario_numero_intentos = ? WHERE Usuario_id = ?");
                        $update->execute([$intentos, $usuario['Usuario_id']]);
                        $error = 'Credenciales incorrectas. Intentos restantes: ' . (3 - $intentos);
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Error de conexión a la base de datos: ' . $e->getMessage();
        }
    }
}

// Función auxiliar para redirigir según el perfil
function redirigirSegunPerfil(string $perfil): void {
    if ($perfil === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: bienvenida.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { width: 100%; max-width: 420px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .top-nav { margin-bottom: 1rem; text-align: left; }
        .top-nav a { color: #2563eb; text-decoration: none; font-size: 0.875rem; }
        .top-nav a:hover { text-decoration: underline; }
        h1 { font-size: 1.6rem; margin-bottom: 0.35rem; text-align: center; color: #0f172a; }
        p.subtitle { color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; text-align: center; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.4rem; color: #334155; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .btn { width: 100%; padding: 0.85rem; background: #2563eb; color: #fff; font-size: 1rem; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: background 0.15s ease; }
        .btn:hover { background: #1d4ed8; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; padding: 0.75rem; border-radius: 6px; font-size: 0.875rem; margin-bottom: 1rem; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 0.75rem; border-radius: 6px; font-size: 0.875rem; margin-bottom: 1rem; }
        .footer-link { margin-top: 1.5rem; text-align: center; font-size: 0.875rem; color: #64748b; }
        .footer-link a { color: #2563eb; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>

<div class="card">
    <div class="top-nav">
        <a href="index.html">&larr; Menú Principal</a>
    </div>

    <h1>Iniciar Sesión</h1>
    <p class="subtitle">Ingresa tus credenciales para continuar</p>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <div class="alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn">Entrar al Sistema</button>
    </form>

    <div class="footer-link">
        ¿Aún no tienes cuenta? <a href="registro.php">Regístrate aquí</a>
    </div>
</div>

</body>
</html>
