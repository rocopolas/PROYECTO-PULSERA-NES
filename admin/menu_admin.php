<?php
session_start();

// Verifica si el usuario está autenticado y es administrador
if (!isset($_SESSION['username'])) {
    header("Location: ../index.html");
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="../pulseras/dashboard.php">Panel de Administración</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../pulseras/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/register.php">Registrar Usuario</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pulseras/register_pulsera.php">Registrar Pulsera</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="registrar_equipo.php">Registrar Equipo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios_pulseras.php">Administrar Usuarios y Pulseras</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="nav-link text-light">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
