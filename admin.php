<?php
session_start();

// Comprobar autenticación
if (!isset($_SESSION['perfil'])) {
    header('Location: login.php');
    exit;
}

// Comprobar rol de administrador
if ($_SESSION['perfil'] !== 'admin') {
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Acceso Denegado</title><style>body{font-family:sans-serif;text-align:center;padding:3rem;background:#fef2f2;color:#991b1b;}a{color:#2563eb;}</style></head><body>';
    echo '<h1>⛔ 403 - Acceso Denegado</h1>';
    echo '<p>Tu perfil actual es <strong>' . htmlspecialchars($_SESSION['perfil']) . '</strong>. Esta página requiere permisos de <strong>admin</strong>.</p>';
    echo '<p><a href="bienvenida.php">&larr; Volver a tu área de usuario</a> | <a href="logout.php">Cerrar Sesión</a></p>';
    echo '</body></html>';
    exit;
}

// Configuración de la base de datos
$host     = 'localhost';
$dbname   = 'registrouser';
$username = 'root';
$password = '';
$usuarios = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $stmt = $pdo->query("SELECT Usuario_id, Usuario_nombre, Usuario_apellido1, Usuario_nick, Usuario_email, Usuario_perfil, Usuario_bloqueado, Usuario_numero_intentos, Usuario_fecha_ultima_conexion, Usuario_fecha_alta FROM usuarios ORDER BY Usuario_id DESC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorDb = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <style>
        :root {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg); color: var(--text-main); padding: 2rem 1.5rem; }
        .container { max-width: 1000px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem; flex-wrap: gap: 1rem; }
        h1 { font-size: 1.75rem; color: #fff; }
        .admin-badge { background: #dc2626; color: white; padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; margin-left: 0.5rem; }
        .nav-links a { color: var(--text-muted); text-decoration: none; margin-left: 1rem; font-size: 0.9rem; }
        .nav-links a:hover { color: #fff; }
        .btn-logout { background: #ef4444; color: white; padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 500; }
        .btn-logout:hover { background: #dc2626; }

        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem; }
        h2 { font-size: 1.25rem; margin-bottom: 1rem; color: #e2e8f0; }

        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { text-align: left; padding: 0.75rem 0.85rem; border-bottom: 1px solid var(--border); }
        th { color: var(--text-muted); font-weight: 600; background: #162032; }
        tr:hover { background: #243248; }

        .status-ok { color: #4ade80; font-weight: 600; }
        .status-blocked { color: #f87171; font-weight: 600; }
        .tag-perfil { background: #334155; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div>
            <h1>Panel de Administrador <span class="admin-badge">ADMIN</span></h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
                Sesión iniciada como: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong> (<?= htmlspecialchars($_SESSION['email']) ?>)
            </p>
        </div>
        <div class="nav-links">
            <a href="index.html">Menú Principal</a>
            <a href="bienvenida.php">Área Usuario</a>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <div class="card">
        <h2>Usuarios Registrados en el Sistema (<?= count($usuarios) ?>)</h2>
        <?php if (!empty($usuarios)): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Nick</th>
                            <th>Email</th>
                            <th>Perfil</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Última Conexión</th>
                            <th>Fecha Alta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['Usuario_id']) ?></td>
                                <td><?= htmlspecialchars($u['Usuario_nombre'] . ' ' . $u['Usuario_apellido1']) ?></td>
                                <td><?= htmlspecialchars($u['Usuario_nick']) ?></td>
                                <td><?= htmlspecialchars($u['Usuario_email']) ?></td>
                                <td><span class="tag-perfil"><?= htmlspecialchars($u['Usuario_perfil'] ?? 'usuario') ?></span></td>
                                <td>
                                    <?php if ($u['Usuario_bloqueado'] == 1): ?>
                                        <span class="status-blocked">🚫 Bloqueado</span>
                                    <?php else: ?>
                                        <span class="status-ok">✔ Activo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['Usuario_numero_intentos']) ?></td>
                                <td><?= htmlspecialchars($u['Usuario_fecha_ultima_conexion'] ?? 'Nunca') ?></td>
                                <td><?= htmlspecialchars($u['Usuario_fecha_alta']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted);">No hay usuarios registrados aún o ocurrió un error al consultar.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
