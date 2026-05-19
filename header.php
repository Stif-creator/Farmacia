<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmacia Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-heart-pulse-fill"></i> <span>Farmacia SaludPlus</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Navegación">
                <span class="navbar-toggler-icon text-white"><i class="bi bi-list" style="font-size:1.2rem"></i></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-bag-fill me-1"></i> Productos</a></li>
                        <li class="nav-item"><a class="nav-link" href="categorias.php"><i class="bi bi-tags-fill me-1"></i> Categorías</a></li>
                        <li class="nav-item"><a class="nav-link" href="marcas.php"><i class="bi bi-award-fill me-1"></i> Marcas</a></li>
                        <li class="nav-item"><a class="nav-link" href="proveedores.php"><i class="bi bi-people-fill me-1"></i> Proveedores</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_usuarios.php"><i class="bi bi-person-lines-fill me-1"></i> Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_ventas.php"><i class="bi bi-receipt-cutoff me-1"></i> Ventas</a></li>
                    <?php elseif (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-fill me-1"></i> Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="carrito.php"><i class="bi bi-cart-fill me-1"></i> Carrito</a></li>
                        <li class="nav-item"><a class="nav-link" href="favoritos.php"><i class="bi bi-heart-fill me-1"></i> Favoritos</a></li>
                        <li class="nav-item"><a class="nav-link" href="mis_compras.php"><i class="bi bi-box-seam me-1"></i> Mis compras</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-fill me-1"></i> Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="categorias.php"><i class="bi bi-tags-fill me-1"></i> Categorías</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <button id="darkModeToggle" class="btn btn-outline-light btn-sm" type="button" aria-label="Cambiar tema"><i class="bi bi-moon-stars-fill"></i></button>
                    <?php if (isset($_SESSION['id_usuario'])): ?>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-dark rounded-pill px-3 py-2"> <i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?></span>
                            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Salir</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-light btn-sm"><i class="bi bi-box-arrow-in-right me-1"></i> Ingresar</a>
                        <a href="registro.php" class="btn btn-outline-light btn-sm"><i class="bi bi-person-plus me-1"></i> Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
<main class="container mt-5">