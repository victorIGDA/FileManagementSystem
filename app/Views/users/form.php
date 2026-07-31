<?php
$editing = $account !== null;
$title = $editing ? 'Editar usuario' : 'Nuevo usuario';
$action = $editing
    ? url('/usuarios/' . $account['id_usuario'] . '/editar')
    : url('/usuarios');
$selectedRole = (int) (
    $_SESSION['_old']['id_rol']
    ?? $account['id_rol']
    ?? 0
);
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Administración</p>
        <h1><?= e($title) ?></h1>
        <p>
            <?= $editing
                ? 'Actualiza la cuenta y su rol.'
                : 'Crea credenciales para un usuario autorizado.' ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= url('/usuarios') ?>">
        Cancelar
    </a>
</div>

<form class="panel" method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="nombre_completo">
                Nombre completo *
            </label>
            <input
                class="form-control"
                id="nombre_completo"
                name="nombre_completo"
                maxlength="150"
                value="<?= old('nombre_completo', $account['nombre_completo'] ?? '') ?>"
                required
            >
        </div>

        <div class="col-md-6">
            <label class="form-label" for="nombre_usuario">
                Nombre de usuario *
            </label>
            <input
                class="form-control"
                id="nombre_usuario"
                name="nombre_usuario"
                maxlength="100"
                value="<?= old('nombre_usuario', $account['nombre_usuario'] ?? '') ?>"
                required
            >
        </div>

        <div class="col-md-6">
            <label class="form-label" for="correo">Correo *</label>
            <input
                class="form-control"
                type="email"
                id="correo"
                name="correo"
                maxlength="150"
                value="<?= old('correo', $account['correo'] ?? '') ?>"
                required
            >
        </div>

        <div class="col-md-3">
            <label class="form-label" for="telefono">Teléfono</label>
            <input
                class="form-control"
                id="telefono"
                name="telefono"
                maxlength="25"
                value="<?= old('telefono', $account['telefono'] ?? '') ?>"
            >
        </div>

        <div class="col-md-3">
            <label class="form-label" for="id_rol">Rol *</label>
            <select
                class="form-select"
                id="id_rol"
                name="id_rol"
                required
            >
                <option value="">Seleccionar</option>
                <?php foreach ($roles as $role): ?>
                    <option
                        value="<?= $role['id_rol'] ?>"
                        <?= $selectedRole === $role['id_rol'] ? 'selected' : '' ?>
                    >
                        <?= e($role['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!$editing): ?>
            <div class="col-md-6">
                <label class="form-label" for="contrasena">
                    Contraseña inicial *
                </label>
                <input
                    class="form-control"
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    minlength="8"
                    required
                >
                <div class="form-text">Mínimo 8 caracteres.</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary">Guardar usuario</button>
    </div>
</form>

<?php if ($editing): ?>
    <form
        class="panel form-narrow mt-4"
        method="post"
        action="<?= url('/usuarios/' . $account['id_usuario'] . '/contrasena') ?>"
    >
        <?= csrf_field() ?>
        <h2 class="h5 mb-3">Restablecimiento administrativo</h2>
        <label class="form-label" for="nueva_contrasena">
            Nueva contraseña temporal
        </label>
        <div class="input-group">
            <input
                class="form-control"
                type="password"
                id="nueva_contrasena"
                name="nueva_contrasena"
                minlength="8"
                required
            >
            <button class="btn btn-outline-danger">Restablecer</button>
        </div>
    </form>
<?php endif; ?>
