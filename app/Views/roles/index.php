<?php $title = 'Roles y permisos'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Roles y permisos</h1>
        <p>Control de acceso extensible sin cambios de código.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/roles/crear') ?>">
        + Nuevo rol
    </a>
</div>

<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Descripción</th>
                    <th>Usuarios</th>
                    <th>Permisos</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td class="item-title">
                            <?= e($role['nombre']) ?>
                        </td>
                        <td><?= e($role['descripcion'] ?: '—') ?></td>
                        <td><?= (int) $role['usuarios'] ?></td>
                        <td><?= (int) $role['permisos'] ?></td>
                        <td>
                            <span class="status <?= $role['estado'] ? 'active' : 'inactive' ?>">
                                <?= $role['estado'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a
                                class="btn btn-sm btn-outline-secondary"
                                href="<?= url('/roles/' . $role['id_rol'] . '/editar') ?>"
                            >
                                Editar
                            </a>

                            <?php if ((int) $role['id_rol'] !== 1): ?>
                                <form
                                    class="d-inline"
                                    method="post"
                                    action="<?= url('/roles/' . $role['id_rol'] . '/estado') ?>"
                                    data-confirm="¿Cambiar el estado de este rol?"
                                >
                                    <?= csrf_field() ?>
                                    <button
                                        class="btn btn-sm btn-outline-<?= $role['estado'] ? 'danger' : 'success' ?>"
                                    >
                                        <?= $role['estado'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
