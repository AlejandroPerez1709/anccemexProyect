<?php
// app/views/reportes/index.php

// --- INICIO DE NUEVAS FUNCIONES HELPER ---
function render_socios_rows($resultados) {
    foreach ($resultados as $socio) {
        echo '<tr>';
        echo '<td>' . $socio['id_socio'] . '</td>';
        echo '<td>' . htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido_paterno']) . '</td>';
        echo '<td>' . htmlspecialchars($socio['nombre_ganaderia']) . '</td>';
        echo '<td>' . htmlspecialchars($socio['codigoGanadero']) . '</td>';
        echo '<td>' . htmlspecialchars($socio['email']) . '</td>';
        echo '<td>' . htmlspecialchars($socio['telefono']) . '</td>';
        echo '<td>' . (!empty($socio['fechaRegistro']) ? date('d/m/Y', strtotime($socio['fechaRegistro'])) : '-') . '</td>';
        echo '<td><span class="status-badge status-' . strtolower($socio['estado']) . '">' . ucfirst($socio['estado']) . '</span></td>';
        echo '</tr>';
    }
}

function render_ejemplares_rows($resultados) {
    foreach ($resultados as $ejemplar) {
        echo '<tr>';
        echo '<td>' . $ejemplar['id_ejemplar'] . '</td>';
        echo '<td>' . htmlspecialchars($ejemplar['nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($ejemplar['nombre_socio']) . '</td>';
        echo '<td>' . htmlspecialchars($ejemplar['socio_codigo_ganadero']) . '</td>';
        echo '<td>' . htmlspecialchars($ejemplar['sexo']) . '</td>';
        echo '<td>' . (!empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-') . '</td>';
        echo '<td>' . htmlspecialchars($ejemplar['raza']) . '</td>';
        echo '<td><span class="status-badge status-' . strtolower($ejemplar['estado']) . '">' . ucfirst($ejemplar['estado']) . '</span></td>';
        echo '</tr>';
    }
}

function render_servicios_rows($resultados) {
    foreach ($resultados as $servicio) {
        echo '<tr>';
        echo '<td>' . $servicio['id_servicio'] . '</td>';
        echo '<td>' . htmlspecialchars($servicio['tipo_servicio_nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno']) . '</td>';
        echo '<td>' . htmlspecialchars($servicio['ejemplar_nombre']) . '</td>';
        echo '<td><span class="status-badge status-' . strtolower(str_replace(['/', ' '], ['-', '-'], $servicio['estado'])) . '">' . htmlspecialchars($servicio['estado']) . '</span></td>';
        echo '<td>' . (!empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-') . '</td>';
        echo '<td>' . (!empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : '-') . '</td>';
        echo '</tr>';
    }
}

function render_resumen_mensual_rows($resultados) {
    foreach ($resultados as $mes) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($mes['mes']) . '</td>';
        echo '<td>' . $mes['creados'] . '</td>';
        echo '<td>' . $mes['completados'] . '</td>';
        echo '</tr>';
    }
}
// --- FIN DE NUEVAS FUNCIONES HELPER ---

$tiposServicioList = $tiposServicioList ?? [];
$sociosList = $sociosList ?? [];
$posiblesEstadosServicio = $posiblesEstadosServicio ?? [];
$posiblesEstadosGenerales = $posiblesEstadosGenerales ?? [];
$posiblesSexos = $posiblesSexos ?? [];
$resultados = $resultados ?? [];
$filtros_aplicados = $filtros_aplicados ?? [];
$tipo_reporte_generado = $_GET['tipo_reporte'] ?? 'servicios';
$page = $page ?? 1;
$total_pages = $total_pages ?? 0;
$total_records = $total_records ?? 0;
$datos_grafica = $datos_grafica ?? [];
$socio_seleccionado = $socio_seleccionado ?? null;
$resumen_servicios_socio = $resumen_servicios_socio ?? null;

function build_report_pagination_url($page) {
    $query_params = $_GET;
    $query_params['page'] = $page;
    return 'index.php?' . http_build_query($query_params);
}
?>

<div class="page-title-container">
    <h2>Módulo de Reportes</h2>
</div>

<div class="form-container form-wide">
    <form action="index.php" method="GET" id="reporteForm">
        <input type="hidden" name="route" value="reportes">
        <fieldset>
            <legend>Generar Reporte</legend>
            <p>Seleccione el tipo de reporte y los filtros deseados. Puede exportar los resultados a Excel.</p>
            
            <div class="filter-controls">
                <div class="filter-item" style="flex-grow: 2;">
                    <label for="tipo_reporte" class="filter-label">Tipo de Reporte:</label>
                    <select name="tipo_reporte" id="tipo_reporte" class="form-control">
                        <option value="servicios" <?php echo ($tipo_reporte_generado === 'servicios') ? 'selected' : ''; ?>>Reporte de Servicios</option>
                        <option value="socios" <?php echo ($tipo_reporte_generado === 'socios') ? 'selected' : ''; ?>>Reporte de Socios</option>
                        <option value="ejemplares" <?php echo ($tipo_reporte_generado === 'ejemplares') ? 'selected' : ''; ?>>Reporte de Ejemplares</option>
                        <option value="servicios_resumen_mensual" <?php echo ($tipo_reporte_generado === 'servicios_resumen_mensual') ? 'selected' : ''; ?>>Resumen de Servicios por Mes</option>
                    </select>
                </div>
            </div>

            <div id="filtros_servicios" class="filter-group">
                <div class="filter-controls">
                    <div class="filter-item"><label for="fecha_inicio_serv" class="filter-label">Fecha Solicitud (Desde):</label><input type="date" name="filtros[servicios][fecha_inicio]" id="fecha_inicio_serv" class="form-control" value="<?php echo htmlspecialchars($filtros_aplicados['fecha_inicio'] ?? ''); ?>"></div>
                    <div class="filter-item"><label for="fecha_fin_serv" class="filter-label">Fecha Solicitud (Hasta):</label><input type="date" name="filtros[servicios][fecha_fin]" id="fecha_fin_serv" class="form-control" value="<?php echo htmlspecialchars($filtros_aplicados['fecha_fin'] ?? ''); ?>"></div>
                    <div class="filter-item"><label for="estado_serv" class="filter-label">Estado:</label><select name="filtros[servicios][estado]" id="estado_serv" class="form-control"><option value="">-- Todos --</option><?php foreach ($posiblesEstadosServicio as $estado): ?><option value="<?php echo htmlspecialchars($estado); ?>" <?php echo (isset($filtros_aplicados['estado']) && $filtros_aplicados['estado'] === $estado) ? 'selected' : ''; ?>><?php echo htmlspecialchars($estado); ?></option><?php endforeach; ?></select></div>
                    <div class="filter-item"><label for="tipo_servicio_id_serv" class="filter-label">Tipo de Servicio:</label><select name="filtros[servicios][tipo_servicio_id]" id="tipo_servicio_id_serv" class="form-control"><option value="">-- Todos --</option><?php foreach ($tiposServicioList as $id => $display): ?><option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($filtros_aplicados['tipo_servicio_id']) && $filtros_aplicados['tipo_servicio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option><?php endforeach; ?></select></div>
                    <div class="filter-item"><label for="socio_id_serv" class="filter-label">Socio:</label><select name="filtros[servicios][socio_id]" id="socio_id_serv" class="form-control"><option value="">-- Todos --</option><?php foreach ($sociosList as $id => $display): ?><option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($filtros_aplicados['socio_id']) && $filtros_aplicados['socio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option><?php endforeach; ?></select></div>
                </div>
            </div>

            <div id="filtros_socios" class="filter-group" style="display: none;">
                <div class="filter-controls">
                    <div class="filter-item"><label for="socio_id_soc" class="filter-label">Socio (Opcional):</label><select name="filtros[socios][socio_id]" id="socio_id_soc" class="form-control"><option value="">-- Ver todos los socios --</option><?php foreach ($sociosList as $id => $display): ?><option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($filtros_aplicados['socio_id']) && $filtros_aplicados['socio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option><?php endforeach; ?></select></div>
                    <div class="filter-item"><label for="estado_soc" class="filter-label">Estado:</label><select name="filtros[socios][estado]" id="estado_soc" class="form-control"><option value="">-- Todos --</option><?php foreach ($posiblesEstadosGenerales as $estado): ?><option value="<?php echo htmlspecialchars($estado); ?>" <?php echo (isset($filtros_aplicados['estado']) && $filtros_aplicados['estado'] === $estado) ? 'selected' : ''; ?>><?php echo ucfirst($estado); ?></option><?php endforeach; ?></select></div>
                </div>
            </div>

            <div id="filtros_ejemplares" class="filter-group" style="display: none;">
                <div class="filter-controls">
                    <div class="filter-item"><label for="socio_id_eje" class="filter-label">Socio Propietario:</label><select name="filtros[ejemplares][socio_id]" id="socio_id_eje" class="form-control"><option value="">-- Todos --</option><?php foreach ($sociosList as $id => $display): ?><option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($filtros_aplicados['socio_id']) && $filtros_aplicados['socio_id'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option><?php endforeach; ?></select></div>
                </div>
            </div>

            <div id="filtros_servicios_resumen_mensual" class="filter-group" style="display: none;">
                <div class="filter-controls">
                    <p style="padding: 10px; font-style: italic; color: #6c757d;">Este reporte muestra la actividad de los últimos 12 meses. No requiere filtros adicionales.</p>
                </div>
            </div>
        </fieldset>

        <div class="form-actions-bottom">
            <button type="submit" class="btn btn-primary">Generar Reporte</button>
            <a href="index.php?route=reportes" class="btn btn-secondary">Limpiar Filtros</a>
        </div>
    </form>
</div>

<?php if (isset($_GET['tipo_reporte'])): ?>
    <div class="report-results">
        <h3>
            <?php 
            if ($tipo_reporte_generado === 'socios' && $socio_seleccionado) {
                echo 'Resultados del Reporte para el Socio: ' . htmlspecialchars($socio_seleccionado['nombre'] . ' ' . $socio_seleccionado['apellido_paterno']);
            } else if ($tipo_reporte_generado === 'servicios_resumen_mensual') {
                echo 'Resultados del Reporte: Resumen de Servicios por Mes';
            } else {
                echo 'Resultados del Reporte de ' . ucfirst(str_replace('_', ' ', $tipo_reporte_generado));
            }
            ?>
        </h3>
        
        <?php if ($tipo_reporte_generado === 'socios' && $socio_seleccionado): ?>
            <div class="report-summary-container individual">
                <table class="summary-table">
                    <thead><tr><th>Socio</th><th>Cód. Ganadero</th><th>Servicios en Proceso</th><th>Servicios Finalizados</th><th>Total de Servicios</th></tr></thead>
                    <tbody><tr>
                        <td><?php echo htmlspecialchars($socio_seleccionado['nombre'] . ' ' . $socio_seleccionado['apellido_paterno']); ?></td>
                        <td><?php echo htmlspecialchars($socio_seleccionado['codigoGanadero']); ?></td>
                        <td><?php echo $resumen_servicios_socio['en_proceso']; ?></td>
                        <td><?php echo $resumen_servicios_socio['completados']; ?></td>
                        <td><?php echo $resumen_servicios_socio['total']; ?></td>
                    </tr></tbody>
                </table>
                <div class="summary-chart">
                    <?php if(!empty($datos_grafica) && !empty($datos_grafica['data']) && array_sum($datos_grafica['data']) > 0): ?>
                        <canvas id="reportChart"></canvas>
                    <?php else: ?>
                        <p class="text-center" style="padding-top: 50px;">Este socio no tiene servicios para generar una gráfica.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="report-summary-container">
                <div class="summary-kpi"><h4>Total de Registros</h4><p><?php echo $total_records; ?></p></div>
                <?php if ($tipo_reporte_generado !== 'servicios_resumen_mensual'): ?>
                <div class="summary-kpi"><h4>Total de Páginas</h4><p><?php echo $total_pages; ?></p></div>
                <?php endif; ?>
                <div class="summary-chart">
                    <?php if(!empty($datos_grafica) && !empty($datos_grafica['data']) && array_sum($datos_grafica['data']) > 0): ?>
                        <canvas id="reportChart"></canvas>
                    <?php else: ?>
                        <p class="text-center" style="padding-top: 50px;">No hay datos para generar una gráfica.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-header-controls">
            <a href="index.php?<?php echo http_build_query(['route' => 'reportes_export'] + $_GET); ?>" class="btn btn-secondary" target="_blank">Exportar a Excel</a>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="pagination-container">
            <ul class="pagination">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo build_report_pagination_url($page - 1); ?>">Anterior</a></li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo build_report_pagination_url($i); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo build_report_pagination_url($page + 1); ?>">Siguiente</a></li>
            </ul>
        </nav>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <?php 
                    if ($tipo_reporte_generado === 'socios' && $socio_seleccionado) {
                        echo '<tr><th>N° Servicio</th><th>Tipo Servicio</th><th>Ejemplar</th><th>Estado</th><th>Fecha Solicitud</th><th>Fecha Finalización</th></tr>';
                    } else {
                        switch($tipo_reporte_generado) {
                            case 'socios':
                                echo '<tr><th>N° Socio</th><th>Nombre Titular</th><th>Ganadería</th><th>Cód. Ganadero</th><th>Email</th><th>Teléfono</th><th>Fecha Registro</th><th>Estado</th></tr>';
                                break;
                            case 'ejemplares':
                                echo '<tr><th>N° Ejemplar</th><th>Nombre</th><th>Socio Propietario</th><th>Cód. Ganadero</th><th>Sexo</th><th>Fecha Nac.</th><th>Raza</th><th>Estado</th></tr>';
                                break;
                            case 'servicios_resumen_mensual':
                                echo '<tr><th>Mes</th><th>Servicios Creados</th><th>Servicios Completados</th></tr>';
                                break;
                            case 'servicios':
                            default:
                                echo '<tr><th>N° Servicio</th><th>Tipo Servicio</th><th>Socio</th><th>Ejemplar</th><th>Estado</th><th>Fecha Solicitud</th><th>Fecha Finalización</th></tr>';
                        }
                    }
                    ?>
                </thead>
                <tbody>
                    <?php if (!empty($resultados)):
                        if ($tipo_reporte_generado === 'socios' && $socio_seleccionado) {
                             foreach($resultados as $servicio) {
                                echo '<tr>';
                                echo '<td>' . $servicio['id_servicio'] . '</td>';
                                echo '<td>' . htmlspecialchars($servicio['tipo_servicio_nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($servicio['ejemplar_nombre']) . '</td>';
                                echo '<td><span class="status-badge status-' . strtolower(str_replace(['/', ' '], ['-', '-'], $servicio['estado'])) . '">' . htmlspecialchars($servicio['estado']) . '</span></td>';
                                echo '<td>' . (!empty($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-') . '</td>';
                                echo '<td>' . (!empty($servicio['fechaFinalizacion']) ? date('d/m/Y', strtotime($servicio['fechaFinalizacion'])) : '-') . '</td>';
                                echo '</tr>';
                             }
                        } else {
                            switch($tipo_reporte_generado) {
                                case 'socios': render_socios_rows($resultados); break;
                                case 'ejemplares': render_ejemplares_rows($resultados); break;
                                case 'servicios_resumen_mensual': render_resumen_mensual_rows($resultados); break;
                                case 'servicios': default: render_servicios_rows($resultados); break;
                            }
                        }
                    else: ?>
                        <tr><td colspan="8" class="text-center">No se encontraron resultados para los filtros seleccionados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="pagination-container" style="margin-top: 20px;">
            <ul class="pagination">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo build_report_pagination_url($page - 1); ?>">Anterior</a></li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo build_report_pagination_url($i); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo build_report_pagination_url($page + 1); ?>">Siguiente</a></li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoReporteSelect = document.getElementById('tipo_reporte');
    const filterGroups = {
        servicios: document.getElementById('filtros_servicios'),
        socios: document.getElementById('filtros_socios'),
        ejemplares: document.getElementById('filtros_ejemplares'),
        servicios_resumen_mensual: document.getElementById('filtros_servicios_resumen_mensual')
    };

    function actualizarFiltrosVisibles() {
        const seleccion = tipoReporteSelect.value;
        for (const key in filterGroups) {
            const group = filterGroups[key];
            if (group) {
                const isVisible = (key === seleccion);
                group.style.display = isVisible ? 'block' : 'none';
                group.querySelectorAll('input, select').forEach(input => {
                    input.disabled = !isVisible;
                });
            }
        }
    }
    tipoReporteSelect.addEventListener('change', actualizarFiltrosVisibles);
    actualizarFiltrosVisibles();

    <?php if(!empty($datos_grafica) && !empty($datos_grafica['data']) && array_sum($datos_grafica['data']) > 0): ?>
    const ctx = document.getElementById('reportChart');
    if (ctx) {
        let chartType = 'pie';
        let chartData = {
            labels: <?php echo json_encode($datos_grafica['labels']); ?>,
            datasets: [{
                label: 'Distribución de Resultados',
                data: <?php echo json_encode($datos_grafica['data'] ?? []); ?>,
                backgroundColor: ['rgba(46, 125, 50, 0.7)','rgba(255, 159, 64, 0.7)','rgba(54, 162, 235, 0.7)','rgba(255, 99, 132, 0.7)','rgba(153, 102, 255, 0.7)','rgba(255, 206, 86, 0.7)','rgba(75, 192, 192, 0.7)','rgba(201, 203, 207, 0.7)'],
                borderColor: '#fff',
                borderWidth: 1
            }]
        };
        let chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } };

        <?php if ($tipo_reporte_generado === 'servicios_resumen_mensual'): ?>
            chartType = 'bar';
            chartData.datasets = [
                {
                    label: 'Servicios Creados',
                    data: <?php echo json_encode($datos_grafica['data_creados']); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Servicios Completados',
                    data: <?php echo json_encode($datos_grafica['data_completados']); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ];
            chartOptions.scales = { y: { beginAtZero: true, ticks: { stepSize: 1 } } };
        <?php endif; ?>

        new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: chartOptions
        });
    }
    <?php endif; ?>
});
</script>