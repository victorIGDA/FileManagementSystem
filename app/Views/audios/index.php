<?php $title = 'Biblioteca de audio'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">Recursos sonoros</p>
        <h1>Biblioteca de audio</h1>
        <p>
            <?= number_format($total) ?>
            archivo<?= $total === 1 ? '' : 's' ?>
            disponible<?= $total === 1 ? '' : 's' ?>.
        </p>
    </div>

    <?php if (App\Core\Auth::can('audios.crear')): ?>
        <a class="btn btn-primary" href="<?= url('/audios/crear') ?>">
            + Cargar audio
        </a>
    <?php endif; ?>
</div>

<section class="panel">
    <form class="filter-bar" method="get">
        <div class="flex-grow-1">
            <label class="visually-hidden" for="q">Buscar</label>
            <input
                class="form-control"
                id="q"
                name="q"
                value="<?= e($q) ?>"
                placeholder="Buscar por título, artista, locutor, cliente o palabra clave"
            >
        </div>

        <div>
            
            <label class="form-label" for="categoria">
                Categoría
            </label>
            <select
                class="form-select"
                id="categoria"
                name="categoria"
            >
                <option value="">Todas las categorías</option>
                <?php foreach ($categories as $item): ?>
                    <option
                        value="<?= $item['id_categoria'] ?>"
                        <?= $category === (int) $item['id_categoria']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($item['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="date-filter">
            <label class="form-label" for="fecha">
                Fecha (dia o mes)
            </label>
            <input
                class="form-control"
                id="fecha"
                name="fecha"
                value="<?= e($date) ?>"
                placeholder="AAAA-MM-DD o AAAA-MM"
                title="Usa AAAA-MM-DD para un dia exacto o AAAA-MM para un mes completo"
            >
        </div>

        <button class="btn btn-dark">Filtrar</button>

        <?php if ($q !== '' || $category || $date !== ''): ?>
            <a class="btn btn-outline-secondary" href="<?= url('/audios') ?>">
                Limpiar
            </a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Detalles</th>
                    <th>Registro</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($audios as $audio): ?>
                    <tr>
                        <td>
                            <a
                                class="item-title"
                                href="<?= url('/audios/' . $audio['id_audio']) ?>"
                            >
                                <?= e($audio['titulo']) ?>
                            </a>
                            <small class="d-block text-secondary">
                                <?= e($audio['nombre_original']) ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge text-bg-light border">
                                <?= e($audio['categoria']) ?>
                            </span>
                        </td>
                        <td>
                            <?= e(
                                $audio['artista']
                                ?: $audio['locutor']
                                ?: $audio['cliente']
                                ?: '—'
                            ) ?>
                            <small class="d-block text-secondary">
                                <?= number_format($audio['tamano_bytes'] / 1048576, 1) ?>
                                MB · <?= strtoupper(e($audio['extension'])) ?>
                            </small>
                        </td>
                        <td>
                            <?= e(date('d/m/Y', strtotime($audio['fecha_registro']))) ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a
                                class="btn btn-sm btn-outline-primary"
                                href="<?= url('/audios/' . $audio['id_audio']) ?>"
                            >
                                Ver
                            </a>

                            <?php if (App\Core\Auth::can('audios.editar')): ?>
                                <a
                                    class="btn btn-sm btn-outline-secondary"
                                    href="<?= url('/audios/' . $audio['id_audio'] . '/editar') ?>"
                                >
                                    Editar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$audios): ?>
                    <tr>
                        <td colspan="5" class="empty">
                            No se encontraron audios con esos criterios.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-end mb-0">
                <?php for ($pageNumber = 1; $pageNumber <= $pages; $pageNumber++): ?>
                    <?php
                    $pageQuery = http_build_query([
                        'q' => $q,
                        'categoria' => $category,
                        'fecha' => $date,
                        'pagina' => $pageNumber,
                    ]);
                    ?>
                    <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= e($pageQuery) ?>">
                            <?= $pageNumber ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</section>
