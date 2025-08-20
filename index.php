<?php
$pageTitle = 'Bienvenido';
$additionalHeadContent = '<link href="colors.css" rel="stylesheet">';

ob_start();
?>

<div class="container mt-5">
        <h1 class="text-center">NES</h1>
        <form id="login-form" action="auth/login.php" method="POST" class="p-4 border rounded shadow-sm">
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" id="login-button" class="btn btn-primary w-100" name="Enviar">Iniciar sesión</button>
        </form>
    </div>

<?php
$content = ob_get_clean();
require_once 'pulseras/frontend/templates/layout.php';
?>
