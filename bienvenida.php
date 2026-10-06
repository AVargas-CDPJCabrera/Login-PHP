<?php
session_start();

// Comprobar que existe la sesión
if (!isset($_SESSION['perfil'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - Área Privada</title>
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg); color: var(--text); padding: 2rem 1rem; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { width: 100%; max-width: 580px; background: var(--card); padding: 2.5rem; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        h1 { font-size: 1.85rem; color: #0f172a; margin-bottom: 0.5rem; }
        p.subtitle { color: #64748b; margin-bottom: 1.75rem; }
        .info-card { background: #f1f5f9; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem; }
        .info-item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; }
        .info-item:last-child { border-bottom: none; }
        .info-label { font-weight: 500; color: #475569; }
        .info-val { font-weight: 600; color: #0f172a; }
        .badge { display: inline-block; background: #dbeafe; color: #1e40af; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap; }
        .btn { padding: 0.7rem 1.2rem; border-radius: 6px; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: all 0.15s ease; text-align: center; }
        .btn-outline { border: 1px solid #cbd5e1; color: #334155; }
        .btn-outline:hover { background: #f8fafc; }
        .btn-danger { background: #ef4444; color: white; border: none; }
        .btn-danger:hover { background: #dc2626; }
        .btn-admin { background: #f59e0b; color: white; border: none; }
        .btn-admin:hover { background: #d97706; }
    </style>
</head>
<body>

<div class="container">
    <h1>¡Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>! 👋</h1>
    <p class="subtitle">Bienvenido a tu área privada de usuario.</p>

    <div class="info-card">
        <div class="info-item">
            <span class="info-label">Usuario / Nick:</span>
            <span class="info-val"><?= htmlspecialchars($_SESSION['nick'] ?? 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Correo Electrónico:</span>
            <span class="info-val"><?= htmlspecialchars($_SESSION['email'] ?? 'N/A') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Perfil de Sesión:</span>
            <span class="info-val"><span class="badge"><?= htmlspecialchars($_SESSION['perfil'] ?? 'usuario') ?></span></span>
        </div>
        <div class="info-item">
            <span class="info-label">ID de Sesión:</span>
            <span class="info-val" style="font-family: monospace; font-size: 0.85rem;"><?= htmlspecialchars(session_id()) ?></span>
        </div>
    </div>

    <div class="actions">
        <?php if ($_SESSION['perfil'] === 'admin'): ?>
            <a href="admin.php" class="btn btn-admin">Ir al Panel de Administrador</a>
        <?php endif; ?>
        <a href="index.html" class="btn btn-outline">Portal Principal</a>
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</div>

</body>
</html>
