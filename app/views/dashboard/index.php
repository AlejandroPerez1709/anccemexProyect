<?php
// app/views/dashboard/index.php
?>
<div class="page-title-container">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['nombre']); ?></h2>
</div>
<p style="text-align: center; margin-top: -20px; margin-bottom: 30px;">Te damos la bienvenida al sistema de gestión documental de ANCCEMEX.</p>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-card-icon socios">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13.204 15.021L12.5 15.6L11.796 15.021C9.623 13.52 7.031 13 5 13H4V11H5C7.337 11 9.943 11.54 12.003 12.98L12.5 13.36L12.997 12.98C15.057 11.54 17.663 11 20 11H21V13H20C17.969 13 15.377 13.52 13.204 15.021ZM13.003 17.02L12.5 17.4L11.997 17.02C10.048 15.662 7.676 15 5 15H4V17H5C7.782 17 10.252 17.611 12.003 18.98L12.5 19.36L12.997 18.98C14.748 17.611 17.218 17 20 17H21V15H20C17.324 15 14.952 15.662 13.003 17.02ZM12.5 8C13.881 8 15 6.881 15 5.5C15 4.119 13.881 3 12.5 3C11.119 3 10 4.119 10 5.5C10 6.881 11.119 8 12.5 8Z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Socios Activos</span>
            <span class="stat-card-number"><?php echo $totalSociosActivos; ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon servicios">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19 22H5C3.34315 22 2 20.6569 2 19V5C2 3.34315 3.34315 2 5 2H19C20.6569 2 22 3.34315 22 5V19C22 20.6569 20.6569 22 19 22ZM19 4H5V19H19V4ZM8 7H16V9H8V7ZM8 11H16V13H8V11Z"></path></svg>
        </div>
        <div class="stat-card-info">
            <span class="stat-card-title">Servicios en Proceso</span>
            <span class="stat-card-number"><?php echo $totalServiciosActivos; ?></span>
        </div>
    </div>
</div>