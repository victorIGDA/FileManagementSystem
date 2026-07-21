<?php
use App\Core\Auth;

$current = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$me = Auth::user();
$pageTitle = $title ?? 'Gestor de audio';
$isAdminSection = str_contains($current, '/categorias') || str_contains($current, '/usuarios') || str_contains($current, '/roles');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(App\Core\Csrf::token()) ?>">
    <title><?= e($pageTitle) ?> · Arca de Salvación</title>
    <script>try{if(localStorage.getItem('sidebarCompact')==='true')document.documentElement.classList.add('sidebar-compact')}catch(e){}</script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Navegación principal">
        <div class="sidebar-header">
            <a class="brand" href="<?= url('/') ?>" title="Arca de Salvación Radio">
                <span class="brand-mark"><i class="bi bi-broadcast-pin"></i></span>
                <span class="brand-copy"><strong>Arca de Salvación</strong><small>Radio 95.3 FM</small></span>
            </a>
            <button class="sidebar-toggle d-none d-lg-grid" id="sidebarToggle" type="button" aria-label="Contraer menú" title="Contraer menú">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>
            <button class="sidebar-close d-lg-none" type="button" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"><i class="bi bi-x-lg"></i></button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label"><span>Principal</span></div>
            <a class="nav-link <?= $current === parse_url(url('/'), PHP_URL_PATH) ? 'active' : '' ?>" href="<?= url('/') ?>" title="Panel principal">
                <i class="bi bi-grid-1x2-fill"></i><span>Panel principal</span>
            </a>
            <?php if (Auth::can('audios.ver')): ?>
                <a class="nav-link <?= str_contains($current, '/audios') ? 'active' : '' ?>" href="<?= url('/audios') ?>" title="Biblioteca de audio">
                    <i class="bi bi-music-note-list"></i><span>Biblioteca de audio</span>
                </a>
            <?php endif; ?>
            <?php if (Auth::can('metricas.ver')): ?>
                <a class="nav-link <?= str_contains($current, '/metricas') ? 'active' : '' ?>" href="<?= url('/metricas') ?>" title="Métricas">
                    <i class="bi bi-bar-chart-line-fill"></i><span>Métricas</span>
                </a>
            <?php endif; ?>

            <?php if (Auth::can('*')): ?>
                <div class="nav-section-label"><span>Gestión</span></div>
                <button class="nav-link nav-group-toggle <?= $isAdminSection ? 'active-parent' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#adminMenu" aria-expanded="<?= $isAdminSection ? 'true' : 'false' ?>" title="Administración">
                    <i class="bi bi-shield-lock-fill"></i><span>Administración</span><i class="bi bi-chevron-down group-chevron"></i>
                </button>
                <div class="collapse nav-submenu <?= $isAdminSection ? 'show' : '' ?>" id="adminMenu">
                    <a class="nav-link <?= str_contains($current, '/categorias') ? 'active' : '' ?>" href="<?= url('/categorias') ?>" title="Categorías"><i class="bi bi-tags-fill"></i><span>Categorías</span></a>
                    <a class="nav-link <?= str_contains($current, '/usuarios') ? 'active' : '' ?>" href="<?= url('/usuarios') ?>" title="Usuarios"><i class="bi bi-people-fill"></i><span>Usuarios</span></a>
                    <a class="nav-link <?= str_contains($current, '/roles') ? 'active' : '' ?>" href="<?= url('/roles') ?>" title="Roles y permisos"><i class="bi bi-key-fill"></i><span>Roles y permisos</span></a>
                </div>
            <?php endif; ?>

            <div class="nav-section-label"><span>Cuenta</span></div>
            <a class="nav-link <?= str_contains($current, '/perfil') ? 'active' : '' ?>" href="<?= url('/perfil') ?>" title="Mi perfil">
                <i class="bi bi-person-circle"></i><span>Mi perfil</span>
            </a>
        </nav>

        <div class="sidebar-user">
            <?php if ($me['foto_perfil']): ?>
                <img class="avatar" src="<?= url('/perfil/foto/' . $me['foto_perfil']) ?>" alt="Foto de perfil">
            <?php else: ?>
                <span class="avatar avatar-fallback" aria-label="Usuario sin fotografía"><i class="bi bi-person-fill"></i></span>
            <?php endif; ?>
            <span class="sidebar-user-copy"><strong><?= e($me['nombre_completo'] ?: $me['nombre_usuario']) ?></strong><small><?= e($me['rol']) ?></small></span>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu-button d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
                <div class="topbar-title"><small>Gestor de audio</small><strong><?= e($pageTitle) ?></strong></div>
            </div>
            <div class="dropdown ms-auto">
                <button class="user-menu" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-menu-copy d-none d-sm-block"><strong><?= e($me['nombre_completo'] ?: $me['nombre_usuario']) ?></strong><small><?= e($me['rol']) ?></small></span>
                    <?php if ($me['foto_perfil']): ?>
                        <img class="avatar" src="<?= url('/perfil/foto/' . $me['foto_perfil']) ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <span class="avatar avatar-fallback" aria-label="Usuario sin fotografía"><i class="bi bi-person-fill"></i></span>
                    <?php endif; ?>
                    <i class="bi bi-chevron-down user-chevron"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end user-dropdown">
                    <div class="dropdown-header"><strong><?= e($me['nombre_usuario']) ?></strong><small><?= e($me['correo']) ?></small></div>
                    <a class="dropdown-item" href="<?= url('/perfil') ?>"><i class="bi bi-person-gear"></i> Configurar perfil</a>
                    <div class="dropdown-divider"></div>
                    <form method="post" action="<?= url('/logout') ?>"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form>
                </div>
            </div>
        </header>
        <div class="content container-fluid">
            <?php if ($message = flash('success')): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill"></i> <?= e($message) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <?php if ($message = flash('error')): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($message) ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
