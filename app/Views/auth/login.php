<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar sesión · Arca de Salvación</title>
    <link
        rel="icon"
        type="image/webp"
        href="<?= url('/assets/img/favicon.webp') ?>"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
</head>
<body class="login-page">
    <main class="login-card">
        <div class="brand justify-content-center mb-4">
            <img
                class=""
                style="width: 120px;height: 120px;"
                src="<?= url('/assets/img/logoarca.webp') ?>"
                alt="Arca de Salvación Radio 95.3 FM"
            >
        </div>

        <div class="text-center mb-4">
            <h1 class="h3">Bienvenido</h1>
            <p class="text-secondary">
                Ingresa al gestor de archivos de audio
            </p>
        </div>

        <?php if ($message = flash('error')): ?>
            <div class="alert alert-danger">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('/login') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="identificador">
                    Usuario o correo
                </label>
                <input
                    class="form-control form-control-lg"
                    id="identificador"
                    name="identificador"
                    value="<?= old('identificador') ?>"
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <div class="mb-4">
                <label class="form-label" for="contrasena">
                    Contraseña
                </label>
                <input
                    class="form-control form-control-lg"
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button class="btn btn-primary btn-lg w-100">
                Ingresar
            </button>
        </form>
    </main>
</body>
</html>
