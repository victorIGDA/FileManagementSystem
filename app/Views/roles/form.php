<?php
$editing = $role !== null;
$title = $editing ? 'Editar rol' : 'Nuevo rol';
$action = $editing
    ? url('/roles/' . $role['id_rol'] . '/editar')
    : url('/roles');
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Administración</p>
        <h1><?= e($title) ?></h1>
        <p>Selecciona exactamente las operaciones autorizadas.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= url('/roles') ?>">
        Cancelar
    </a>
</div>

<form class="panel form-narrow" method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label" for="nombre">Nombre *</label>
        <input
            class="form-control"
            id="nombre"
            name="nombre"
            maxlength="50"
            value="<?= e($role['nombre'] ?? '') ?>"
            required
            <?= ($role['id_rol'] ?? 0) == 1 ? 'readonly' : '' ?>
        >
    </div>

    <div class="mb-4">
        <label class="form-label" for="descripcion">Descripción</label>
        <textarea
            class="form-control"
            id="descripcion"
            name="descripcion"
            maxlength="255"
        ><?= e($role['descripcion'] ?? '') ?></textarea>
    </div>

    <fieldset>
        <legend class="h6">Permisos</legend>

        <?php foreach ($permissions as $permission): ?>
            <div class="form-check permission-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="permisos[]"
                    value="<?= $permission['id_permiso'] ?>"
                    id="permiso-<?= $permission['id_permiso'] ?>"
                    <?= in_array((int) $permission['id_permiso'], $selected, true)
                        ? 'checked'
                        : '' ?>
                >
                <label
                    class="form-check-label"
                    for="permiso-<?= $permission['id_permiso'] ?>"
                >
                    <strong><?= e($permission['nombre']) ?></strong>
                    <small><?= e($permission['codigo']) ?></small>
                </label>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <button class="btn btn-primary mt-4">Guardar rol</button>
</form>
