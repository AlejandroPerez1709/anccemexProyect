<?php
// app/views/dashboard/index.php
?>
<div class="page-title-container">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['nombre']); ?>. Este es el estado actual del sistema.</h2>
</div>

<div class="dashboard-kpi-cards">
    <div class="stat-card border-primary">
        <div class="stat-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14.1213 10.4792C13.7308 10.0886 13.0976 10.0886 12.7071 10.4792L12 11.1863C11.2189 11.9673 9.95259 11.9673 9.17154 11.1863C8.39049 10.4052 8.39049 9.13888 9.17154 8.35783L14.8022 2.72568C16.9061 2.24973 19.2008 2.83075 20.8388 4.46875C23.2582 6.88811 23.3716 10.7402 21.1792 13.2939L19.071 15.4289L14.1213 10.4792ZM3.16113 4.46875C5.33452 2.29536 8.66411 1.98283 11.17 3.53116L7.75732 6.94362C6.19523 8.50572 6.19523 11.0384 7.75732 12.6005C9.27209 14.1152 11.6995 14.1611 13.2695 12.7382L13.4142 12.6005L17.6568 16.8431L13.4142 21.0858C12.6331 21.8668 11.3668 21.8668 10.5858 21.0858L3.16113 13.6611C0.622722 11.1227 0.622722 7.00715 3.16113 4.46875Z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Servicios en Proceso</span>
            <span class="stat-card-number"><?php echo $totalServiciosActivos; ?></span>
        </div>
    </div>
    <div class="stat-card border-success">
        <div class="stat-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10 15.172L19.364 5.808L20.778 7.222L10 18L3.222 11.222L4.636 9.808L10 15.172Z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Completados (Mes)</span>
            <span class="stat-card-number"><?php echo $statsServicios['completados_mes_actual']; ?></span>
        </div>
    </div>
    <div class="stat-card border-danger">
        <div class="stat-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M4.00024 18.2785L4.00024 19.2785L15.435 19.2785L15.435 18.2785L4.00024 18.2785ZM19.9995 11.4586L18.5853 10.0444L12.9284 15.7012L10.0998 12.8727L8.68555 14.2869L12.9284 18.5298L19.9995 11.4586ZM15.435 4.2785L4.00024 4.27851L4.00024 5.27851L15.435 5.2785L15.435 4.2785Z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Pendientes Docs/Pago</span>
            <span class="stat-card-number"><?php echo $statsServicios['pendientes_docs_pago']; ?></span>
        </div>
    </div>
    <div class="stat-card border-warning">
        <div class="stat-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM12 4C7.58 4 4 7.58 4 12C4 16.42 7.58 20 12 20C16.42 20 20 16.42 20 12C20 7.58 16.42 4 12 4ZM11 8H13V13H11V8ZM11 15H13V17H11V15Z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Tiempo Prom. Resolución</span>
            <span class="stat-card-number"><?php echo number_format($statsServicios['promedio_resolucion_dias'], 1); ?>d</span>
        </div>
    </div>
</div>


<div class="dashboard-grid">
    <div class="dashboard-chart-container">
        <h3 class="dashboard-section-title">Distribución de Servicios en Proceso</h3>
        <div class="chart-wrapper">
            <canvas id="serviciosEstadoChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-chart-container">
        <h3 class="dashboard-section-title">Carga de Trabajo vs Productividad (Últimos 12 Meses)</h3>
        <div class="chart-wrapper">
            <canvas id="serviciosMensualesChart"></canvas>
        </div>
    </div>

    <div class="dashboard-list-container">
        <h3 class="dashboard-section-title">⚠️ Servicios que Requieren Atención</h3>
        <table>
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Socio</th>
                    <th>Días Sin Actividad</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($serviciosAtencion)): ?>
                    <?php foreach($serviciosAtencion as $servicio): ?>
                        <tr>
                            <td>
                                <a href="index.php?route=servicios/edit&id=<?php echo $servicio['id_servicio']; ?>" class="btn-id" title="Ver/Editar Servicio">
                                    <?php echo $servicio['id_servicio']; ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno']); ?></td>
                            <td>
                                <span class="badge-danger">
                                    <?php echo $servicio['dias_sin_actualizar']; ?> días
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">¡Excelente! No hay servicios con retrasos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="dashboard-list-container">
        <h3 class="dashboard-section-title">Actividad Reciente</h3>
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Socio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($serviciosRecientes)): ?>
                    <?php foreach($serviciosRecientes as $servicio): ?>
                        <tr>
                            <td>
                                <a href="index.php?route=servicios/edit&id=<?php echo $servicio['id_servicio']; ?>" class="btn-id" title="Ver/Editar Servicio">
                                    <?php echo $servicio['id_servicio']; ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($servicio['socio_nombre'] . ' ' . $servicio['socio_apPaterno']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(['/', ' '], ['-', '-'], $servicio['estado'])); ?>">
                                    <?php echo htmlspecialchars($servicio['estado']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No hay actividad reciente.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="dashboard-module-cards">
    <a href="index.php?route=socios_index" class="module-card module-card-socios">
        <div class="module-card-icon">
            <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.2914 5.99994H20.0002C20.5525 5.99994 21.0002 6.44766 21.0002 6.99994V13.9999C21.0002 14.5522 20.5525 14.9999 20.0002 14.9999H18.0002L13.8319 9.16427C13.3345 8.46797 12.4493 8.16522 11.6297 8.41109L9.14444 9.15668C8.43971 9.3681 7.6758 9.17551 7.15553 8.65524L6.86277 8.36247C6.41655 7.91626 6.49011 7.17336 7.01517 6.82332L12.4162 3.22262C13.0752 2.78333 13.9312 2.77422 14.5994 3.1994L18.7546 5.8436C18.915 5.94571 19.1013 5.99994 19.2914 5.99994ZM5.02708 14.2947L3.41132 15.7085C2.93991 16.1209 2.95945 16.8603 3.45201 17.2474L8.59277 21.2865C9.07284 21.6637 9.77592 21.5264 10.0788 20.9963L10.7827 19.7645C11.2127 19.012 11.1091 18.0682 10.5261 17.4269L7.82397 14.4545C7.09091 13.6481 5.84722 13.5771 5.02708 14.2947ZM7.04557 5H3C2.44772 5 2 5.44772 2 6V13.5158C2 13.9242 2.12475 14.3173 2.35019 14.6464C2.3741 14.6238 2.39856 14.6015 2.42357 14.5796L4.03933 13.1658C5.47457 11.91 7.65103 12.0343 8.93388 13.4455L11.6361 16.4179C12.6563 17.5401 12.8376 19.1918 12.0851 20.5087L11.4308 21.6538C11.9937 21.8671 12.635 21.819 13.169 21.4986L17.5782 18.8531C18.0786 18.5528 18.2166 17.8896 17.8776 17.4146L12.6109 10.0361C12.4865 9.86205 12.2652 9.78636 12.0603 9.84783L9.57505 10.5934C8.34176 10.9634 7.00492 10.6264 6.09446 9.7159L5.80169 9.42313C4.68615 8.30759 4.87005 6.45035 6.18271 5.57524L7.04557 5Z"></path></svg>
        </div>
        <div class="module-card-content">
            <div class="module-card-number"><?php echo $totalSociosActivos; ?></div>
            <div class="module-card-title">Socios Activos</div>
        </div>
    </a>
    <a href="index.php?route=ejemplares_index" class="module-card module-card-ejemplares">
        <div class="module-card-icon">
            <svg class="menu-icon" viewBox="-2.5 0 63 63" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>Horse-shoe</title> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Horse-shoe" transform="translate(1.000000, 1.000000)" stroke="#000000" stroke-width="2"> <path d="M52,54 L48.3,54 C53.1,45.3 56,35.5 56,28 C56,12.5 43.5,0 28,0 C12.5,0 0,12.5 0,28 C0,35.5 4,45.3 8.8,54 L5,54 L5,61 L17.9,61 C23.9,61 21.5,56.8 21.5,56.8 C21.5,56.8 9.8,38.5 9.8,27.8 C9.8,17.9 18,9.9 28.1,9.9 C38.2,9.9 46.4,17.9 46.4,27.8 C46.4,38.3 39.5,48.3 36.3,56.5 C35.2,59.2 36.4,61 40.1,61 L52,61 L52,54 L52,54 Z"></path> <path d="M27,6 L29,6"></path> <path d="M12,10 L14,10"></path> <path d="M41,10 L43,10"></path> <path d="M48,18 L50,18"></path> <path d="M6,17.9 L8,17.9"></path> <path d="M50,26 L52,26"></path> <path d="M50,35 L52,35"></path> <path d="M5,35 L7,35"></path> <path d="M8,44 L10,44"></path> <path d="M47,44 L49,44"></path> <path d="M43,54 L44.9,54"></path> <path d="M12,54 L14,54"></path> <path d="M4,26 L6,26"></path> </g> </g> </g></svg>
        </div>
        <div class="module-card-content">
            <div class="module-card-number"><?php echo $totalEjemplaresActivos; ?></div>
            <div class="module-card-title">Ejemplares Activos</div>
        </div>
    </a>
    <a href="index.php?route=medicos_index" class="module-card module-card-medicos">
        <div class="module-card-icon">
            <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3H15V5H9V3ZM19 5H17V3C17 1.89543 16.1046 1 15 1H9C7.89543 1 7 1.89543 7 3V5H5C3.89543 5 3 5.89543 3 7V21C3 22.1046 3.89543 23 5 23H19C20.1046 23 21 22.1046 21 21V7C21 5.89543 20.1046 5 19 5ZM11 15H8V13H11V10H13V13H16V15H13V18H11V15Z"></path></svg>
        </div>
        <div class="module-card-content">
            <div class="module-card-number"><?php echo $totalMedicosActivos; ?></div>
            <div class="module-card-title">Médicos Activos</div>
        </div>
    </a>
    <a href="index.php?route=empleados_index" class="module-card module-card-empleados">
        <div class="module-card-icon">
            <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 4H5V20H19V4ZM12 13C10.6193 13 9.5 11.8807 9.5 10.5C9.5 9.11929 10.6193 8 12 8C13.3807 8 14.5 9.11929 14.5 10.5C14.5 11.8807 13.3807 13 12 13ZM7.5 18C7.5 15.5147 9.51472 13.5 12 13.5C14.4853 13.5 16.5 15.5147 16.5 18H7.5Z"></path></svg>
        </div>
        <div class="module-card-content">
            <div class="module-card-number"><?php echo $totalEmpleadosActivos; ?></div>
            <div class="module-card-title">Empleados Activos</div>
        </div>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- GRÁFICA DE DONA (ESTADOS) ---
    const doughnutCtx = document.getElementById('serviciosEstadoChart');
    if (doughnutCtx) {
        const doughnutLabels = <?php echo $doughnutChartLabelsJSON; ?>;
        const doughnutData = <?php echo $doughnutChartDataJSON; ?>;
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: doughnutLabels,
                datasets: [{
                    label: 'Servicios',
                    data: doughnutData,
                    backgroundColor: ['rgba(255, 99, 132, 0.7)','rgba(54, 162, 235, 0.7)','rgba(255, 206, 86, 0.7)','rgba(75, 192, 192, 0.7)','rgba(153, 102, 255, 0.7)','rgba(255, 159, 64, 0.7)','rgba(99, 255, 132, 0.7)'],
                    borderColor: ['rgba(255, 99, 132, 1)','rgba(54, 162, 235, 1)','rgba(255, 206, 86, 1)','rgba(75, 192, 192, 1)','rgba(153, 102, 255, 1)','rgba(255, 159, 64, 1)','rgba(99, 255, 132, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    // --- GRÁFICA DE LÍNEAS (MENSUAL) ---
    const lineCtx = document.getElementById('serviciosMensualesChart');
    if (lineCtx) {
        const lineLabels = <?php echo $lineChartLabelsJSON; ?>;
        const creadosData = <?php echo $lineChartCreadosJSON; ?>;
        const completadosData = <?php echo $lineChartCompletadosJSON; ?>;
        
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: [
                    {
                        label: 'Servicios Creados',
                        data: creadosData,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        fill: true,
                        tension: 0.1
                    },
                    {
                        label: 'Servicios Completados',
                        data: completadosData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        fill: true,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                           stepSize: 1 
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
});
</script>