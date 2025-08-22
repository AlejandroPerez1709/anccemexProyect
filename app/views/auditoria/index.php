<?php
// app/views/auditoria/index.php

function build_pagination_url($page, $filters) {
    $query_params = array_merge(['route' => 'auditoria_index', 'page' => $page], $filters);
    return 'index.php?' . http_build_query($query_params);
}

// Para mantener los filtros en los enlaces de exportación y paginación
$current_filters = [
    'filtro_usuario_id' => $_GET['filtro_usuario_id'] ?? '',
    'filtro_fecha_inicio' => $_GET['filtro_fecha_inicio'] ?? '',
    'filtro_fecha_fin' => $_GET['filtro_fecha_fin'] ?? ''
];

$usuarios_list = $usuarios_list ?? [];
?>

<div class="page-title-container">
    <h2>Bitácora de Auditoría del Sistema</h2>
</div>

<form action="index.php" method="GET" class="filter-form">
    <input type="hidden" name="route" value="auditoria_index">
    <fieldset>
        <legend>Filtrar Registros</legend>
        <div class="filter-controls">
            <div class="filter-item">
                <label for="filtro_usuario_id" class="filter-label">Usuario:</label>
                <select name="filtro_usuario_id" id="filtro_usuario_id" class="form-control">
                    <option value="">-- Todos los Usuarios --</option>
                    <?php foreach($usuarios_list as $usuario): ?>
                        <option value="<?php echo $usuario['id_usuario']; ?>" <?php echo (($current_filters['filtro_usuario_id'] ?? '') == $usuario['id_usuario']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label for="filtro_fecha_inicio" class="filter-label">Fecha Desde:</label>
                <input type="date" name="filtro_fecha_inicio" id="filtro_fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($current_filters['filtro_fecha_inicio'] ?? ''); ?>">
            </div>
            <div class="filter-item">
                <label for="filtro_fecha_fin" class="filter-label">Fecha Hasta:</label>
                <input type="date" name="filtro_fecha_fin" id="filtro_fecha_fin" class="form-control" value="<?php echo htmlspecialchars($current_filters['filtro_fecha_fin'] ?? ''); ?>">
            </div>
            <div class="filter-buttons">
                <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
                <a href="index.php?route=auditoria_index" class="btn btn-primary btn-sm">Limpiar</a>
            </div>
        </div>
    </fieldset>
</form>

<div class="table-header-controls">
    <span>Total de registros: <?php echo $total_records ?? 0; ?></span>
    <a href="index.php?route=auditoria_export_excel&<?php echo http_build_query($current_filters); ?>" class="btn btn-secondary">Exportar a Excel</a>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $current_filters); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $current_filters); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $current_filters); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Fecha y Hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Entidad Afectada</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($registros) && count($registros) > 0): ?>
                <?php foreach($registros as $registro): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($registro['fecha_hora'])); ?></td>
                        <td><?php echo htmlspecialchars($registro['usuario_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($registro['accion']); ?></td>
                        <td>
                            <?php if(!empty($registro['tipo_entidad']) && !empty($registro['id_entidad'])): ?>
                                <?php echo htmlspecialchars($registro['tipo_entidad']) . ' (ID: ' . $registro['id_entidad'] . ')'; ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($registro['descripcion']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No hay registros de auditoría que coincidan con los filtros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>