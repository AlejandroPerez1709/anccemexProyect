<!-- app/views/medicos/index.php -->

<h2>Listado de Médicos</h2>

<a href="index.php?route=medicos/create" class="btn btn-primary" style="margin-bottom: 15px;">Registrar Nuevo Médico</a>

<?php
if(isset($_SESSION['message'])){
    echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']);
}
if(isset($_SESSION['error'])){
    echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}
?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Cédula Prof.</th>
            <th>Cert. ANCCE</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if(isset($medicos) && count($medicos) > 0): ?>
            <?php foreach($medicos as $medico): ?>
                <tr>
                    <td><?php echo $medico['id_medico']; ?></td>
                    <td><?php echo htmlspecialchars($medico['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($medico['apellido_paterno'] . ' ' . $medico['apellido_materno']); ?></td>
                    <td><?php echo !empty($medico['email']) ? htmlspecialchars($medico['email']) : '-'; ?></td>
                    <td><?php echo !empty($medico['telefono']) ? htmlspecialchars($medico['telefono']) : '-'; ?></td>
                    <td><?php echo !empty($medico['numero_cedula_profesional']) ? htmlspecialchars($medico['numero_cedula_profesional']) : '-'; ?></td>
                    <td><?php echo !empty($medico['numero_certificacion_ancce']) ? htmlspecialchars($medico['numero_certificacion_ancce']) : '-'; ?></td>
                    <td>
                        <span style="color: <?php echo ($medico['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;">
                            <?php echo htmlspecialchars(ucfirst($medico['estado'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?route=medicos/edit&id=<?php echo $medico['id_medico']; ?>" class="btn btn-warning">Editar</a>
                        <a href="index.php?route=medicos_delete&id=<?php echo $medico['id_medico']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar a este médico?\nNombre: <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido_paterno']); ?>\n\n¡Esta acción podría no ser reversible!')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center;">No hay médicos registrados</td> </tr>
        <?php endif; ?>
    </tbody>
</table>