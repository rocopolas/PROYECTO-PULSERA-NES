<?php
$pageTitle = 'Bienvenido';
$additionalHeadContent = '<link href="colors.css" rel="stylesheet">';

ob_start();
?>

<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Bienvenido</h2>
            <div class="d-grid gap-2">
                <a href="auth/login.php" class="btn btn-primary">Iniciar Sesión</a>
                <a href="auth/register.php" class="btn btn-secondary">Registrarse</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'pulseras/templates/layout.php';
?>
