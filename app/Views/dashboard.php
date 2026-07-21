<?php $title = 'Panel principal'; ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">Resumen general</p>
        <h1>Panel principal</h1>
        <p>Inventario y actividad reciente de la emisora.</p>
    </div>
    <?php if (App\Core\Auth::can('audios.crear')): ?>
        <a class="btn btn-primary" href="<?= url('/audios/crear') ?>"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Cargar audio</a>
    <?php endif; ?>
</div>

<?php
$summary = [
    ['Audios disponibles', $stats['audios'], 'bi-music-note-beamed', 'blue'],
    ['Categorías activas', $stats['categorias'], 'bi-tags-fill', 'cyan'],
    ['Usuarios activos', $stats['usuarios'], 'bi-people-fill', 'indigo'],
    ['Reproducciones · 30 días', $stats['reproducciones'], 'bi-play-circle-fill', 'sky'],
];
?>
<div class="row g-3 mb-4">
    <?php foreach ($summary as [$label, $value, $icon, $tone]): ?>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-card-icon <?= $tone ?>"><i class="bi <?= $icon ?>"></i></div>
                <span><?= e($label) ?></span>
                <strong><?= number_format($value) ?></strong>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <section class="panel h-100">
            <div class="panel-title"><h2><i class="bi bi-pie-chart-fill text-primary me-2"></i>Audios por categoría</h2></div>
            <?php $max = max(array_column($categories, 'total') ?: [1]); ?>
            <?php foreach ($categories as $item): ?>
                <div class="bar-row">
                    <span><?= e($item['nombre']) ?></span>
                    <div class="bar-track"><i style="width:<?= $max ? (int) $item['total'] / $max * 100 : 0 ?>%"></i></div>
                    <strong><?= (int) $item['total'] ?></strong>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
    <div class="col-xl-5">
        <section class="panel h-100">
            <div class="panel-title"><h2><i class="bi bi-clock-history text-primary me-2"></i>Archivos recientes</h2><a href="<?= url('/audios') ?>">Ver todos</a></div>
            <?php if (!$recent): ?><p class="empty"><i class="bi bi-inbox d-block fs-2 mb-2"></i>Aún no hay archivos cargados.</p><?php endif; ?>
            <div class="list-group list-group-flush">
                <?php foreach ($recent as $item): ?>
                    <a class="list-group-item list-group-item-action px-0" href="<?= url('/audios/' . $item['id_audio']) ?>">
                        <strong><?= e($item['titulo']) ?></strong>
                        <small class="d-block text-secondary"><?= e($item['categoria']) ?> · <?= e(date('d/m/Y', strtotime($item['fecha_registro']))) ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

