<?php
// app/views/dashboard/index.php
?>
<h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h2>
<p>Te damos la bienvenida al sistema de gestión documental de ANCCEMEX.</p>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Socios</h3>
        <div class="count">134</div>
    </div>
    <div class="stat-card">
        <h3>Servicios Activos</h3>
        <div class="count">23</div>
    </div>
</div>