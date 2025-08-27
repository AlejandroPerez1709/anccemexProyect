/*
    public/assets/js/reportes.js 
    Lógica de JavaScript para el Módulo de Reportes.
    - Manejo de la visibilidad de los filtros.
    - Inicialización del gráfico de resultados.
*/
document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA PARA MOSTRAR/OCULTAR FILTROS ---
    const tipoReporteSelect = document.getElementById('tipo_reporte');
    if (tipoReporteSelect) {
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
        actualizarFiltrosVisibles(); // Ejecutar al cargar la página
    }

    // --- LÓGICA PARA INICIALIZAR EL GRÁFICO ---
    const ctx = document.getElementById('reportChart');
    if (ctx && ctx.dataset.chartConfig) {
        const config = JSON.parse(ctx.dataset.chartConfig);
        let chartType = config.type || 'pie';
        let chartData = {};
        let chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } };

        if (chartType === 'bar') {
            chartData = {
                labels: config.labels,
                datasets: [
                    {
                        label: 'Servicios Creados',
                        data: config.data_creados,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Servicios Completados',
                        data: config.data_completados,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            };
            chartOptions.scales = { y: { beginAtZero: true, ticks: { stepSize: 1 } } };
        } else { // 'pie' o 'doughnut'
            chartData = {
                labels: config.labels,
                datasets: [{
                    label: 'Distribución de Resultados',
                    data: config.data,
                    backgroundColor: ['rgba(46, 125, 50, 0.7)','rgba(255, 159, 64, 0.7)','rgba(54, 162, 235, 0.7)','rgba(255, 99, 132, 0.7)','rgba(153, 102, 255, 0.7)','rgba(255, 206, 86, 0.7)','rgba(75, 192, 192, 0.7)','rgba(201, 203, 207, 0.7)'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            };
        }

        new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: chartOptions
        });
    }
});