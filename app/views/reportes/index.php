<?php
// app/views/reportes/index.php

// Aseguramos que las variables existan para evitar errores
$tiposServicioList = $tiposServicioList ?? [];
$sociosList = $sociosList ?? [];
$posiblesEstados = $posiblesEstados ?? [];
$resultados = $resultados ?? [];
$filtros_aplicados = $filtros_aplicados ?? [];
?>

<div class="page-title-container">
    <h2>Módulo de Reportes</h2>
</div>

<div class="form-container form-wide">
    <form action="index.php?route=reportes" method="POST" id="reporteForm">
        <fieldset>
            <legend>Generar Reporte de Servicios</legend>
            <p>Seleccione los filtros para generar un reporte de servicios. Puede exportar los resultados a Excel.</p>
            
            <div class="filter-controls">
                <div class="filter-item">
                    <label for="fecha_inicio" class="filter-label">Fecha Desde:</label>
                    <input type="date" name="filtros[fecha_inicio]" id="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($filtros_aplicados['fecha_inicio'] ?? ''); ?>">
                </div>
                <div class="filter-item">
                    <label for="fecha_fin" class="filter-label">Fecha Hasta:</label>
                    <input type="date" name="filtros[fecha_fin]" id="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($filtros_aplicados['fecha_fin'] ?? ''); ?>">
                </div>
                <div class="filter-item">
                    <label for="filtro_estado" class="filter-label">Estado:</label>
                    <select name="filtros[estado]" id="filtro_estado" class="form-control">
                        <option value="">-- Todos los Estados --</option>
                        <?php foreach ($posiblesEstados as $estado): ?>
                            <option value="<?php echo htmlspecialchars($estado); ?>" <?php echo (isset($filtros_aplicados['estado']) && $filtros_aplicados['estado'] === $estado) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($estado); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="filtro_tipo_id" class="filter-label">Tipo de Servicio:</label>
                    <select name="filtros[tipo_servicio_id]" id="filtro_tipo_id" class="form-control">
                        <option value="">-- Todos los Tipos --</option>
                        <?php foreach ($tiposServicioList as $id => $display): ?>
                            <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($filtros_aplicados['tipo_servicio_id']) && $filtros_aplicados['tipo_servicio_id'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($display); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <div class="form-actions-bottom">
            <button type="submit" name="accion" value="generar" class="btn btn-primary">Generar Reporte</button>
            <a href="index.php?route=reportes" class="btn btn-secondary">Limpiar Filtros</a>
        </div>
    </form>
</div>

<?php if (!empty($filtros_aplicados)): ?>
    <div class="report-results">
        <h3>Resultados del Reporte</h3>
        
        <div class="table-header-controls">
            <form action="index.php?route=reportes" method="POST" target="_blank">
                <input type="hidden" name="filtros[fecha_inicio]" value="<?php echo htmlspecialchars($filtros_aplicados['fecha_inicio'] ?? ''); ?>">
                <input type="hidden" name="filtros[fecha_fin]" value="<?php echo htmlspecialchars($filtros_aplicados['fecha_fin'] ?? ''); ?>">
                <input type="hidden" name="filtros[estado]" value="<?php echo htmlspecialchars($filtros_aplicados['estado'] ?? ''); ?>">
                <input type="hidden" name="filtros[tipo_servicio_id]" value="<?php echo htmlspecialchars($filtros_aplicados['tipo_servicio_id'] ?? ''); ?>">
                <button type="submit" name="accion" value="exportar" class="btn btn-secondary">Exportar a Excel</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>N° Servicio</th>
                        <th>Tipo Servicio</th>
                        <th>Socio</th>
                        <th>Ejemplar</th>
                        <th>Estado</th>
                        <th>Fecha Solicitud</th>
                        <th>Fecha Finalización</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resultados)): ?>
                        <?php foreach ($resultados as $servicio): ?>
                            <tr>
                                <td><?php echo $servicio['id_servicio']; ?></td>
                                <td><?php echo htmlspecialchars($servicio['tipo_servicio_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno']); ?></td>
                                <td><?php echo htmlspecialchars($servicio['ejemplar_nombre']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(['/', ' '], ['-', '-'], $servicio['estado'])); ?>">
                                        <?php echo htmlspecialchars($servicio['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo !empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-'; ?></td>
                                <td><?php echo !empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No se encontraron resultados para los filtros seleccionados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>