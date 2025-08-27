/*
    public/assets/js/dashboard-charts.js
    Lógica de JavaScript para los gráficos del Dashboard.
    - Gráfico de Dona para la distribución de estados.
    - Gráfico de Líneas para la productividad mensual.
*/
document.addEventListener('DOMContentLoaded', function() {
    
    // --- GRÁFICA DE DONA (ESTADOS) ---
    const doughnutCtx = document.getElementById('serviciosEstadoChart');
    if (doughnutCtx) {
        // Leemos los datos desde los atributos data-* del elemento canvas
        const doughnutLabels = JSON.parse(doughnutCtx.dataset.labels);
        const doughnutData = JSON.parse(doughnutCtx.dataset.data);
        
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
        // Leemos los datos desde los atributos data-* del elemento canvas
        const lineLabels = JSON.parse(lineCtx.dataset.labels);
        const creadosData = JSON.parse(lineCtx.dataset.creados);
        const completadosData = JSON.parse(lineCtx.dataset.completados);
        
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