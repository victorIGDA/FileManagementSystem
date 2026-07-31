<?php $title = 'Categorías'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Categorías</h1>
        <p>Clasificaciones disponibles para los recursos sonoros.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/categorias/crear') ?>">
        + Nueva categoría
    </a>
</div>

<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="item-title">
                            <?= e($category['nombre']) ?>
                        </td>
                        <td>
                            <?= e($category['descripcion'] ?: '—') ?>
                        </td>
                        <td>
                            <span class="status <?= $category['estado'] ? 'active' : 'inactive' ?>">
                                <?= $category['estado'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a
                                class="btn btn-sm btn-outline-secondary"
                                href="<?= url('/categorias/' . $category['id_categoria'] . '/editar') ?>"
                            >
                                Editar
                            </a>
                            <form
                                class="d-inline"
                                method="post"
                                action="<?= url('/categorias/' . $category['id_categoria'] . '/estado') ?>"
                                data-confirm="¿Cambiar el estado de esta categoría?"
                            >
                                <?= csrf_field() ?>
                                <button
                                    class="btn btn-sm btn-outline-<?= $category['estado'] ? 'danger' : 'success' ?>"
                                >
                                    <?= $category['estado'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
