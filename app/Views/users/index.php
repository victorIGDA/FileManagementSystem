<?php $title = 'Usuarios'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Usuarios</h1>
        <p>Accesos, roles y estados de cuenta.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/usuarios/crear') ?>">
        + Nuevo usuario
    </a>
</div>

<section class="panel">
    <form class="filter-bar" method="get">
        <input
            class="form-control"
            name="q"
            value="<?= e($q) ?>"
            placeholder="Buscar por nombre, usuario o correo"
        >
        <button class="btn btn-dark">Buscar</button>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong>
                                <?= e($user['nombre_completo'] ?: $user['nombre_usuario']) ?>
                            </strong>
                            <small class="d-block text-secondary">
                                @<?= e($user['nombre_usuario']) ?>
                            </small>
                        </td>
                        <td><?= e($user['correo']) ?></td>
                        <td><?= e($user['rol']) ?></td>
                        <td>
                            <span class="status <?= $user['estado'] ? 'active' : 'inactive' ?>">
                                <?= $user['estado'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a
                                class="btn btn-sm btn-outline-secondary"
                                href="<?= url('/usuarios/' . $user['id_usuario'] . '/editar') ?>"
                            >
                                Editar
                            </a>
                            <form
                                class="d-inline"
                                method="post"
                                action="<?= url('/usuarios/' . $user['id_usuario'] . '/estado') ?>"
                                data-confirm="¿Cambiar el estado de esta cuenta?"
                            >
                                <?= csrf_field() ?>
                                <button
                                    class="btn btn-sm btn-outline-<?= $user['estado'] ? 'danger' : 'success' ?>"
                                >
                                    <?= $user['estado'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
