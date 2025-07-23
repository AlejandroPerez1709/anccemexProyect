<h2>Listado de Médicos</h2>

<div class="table-header-controls">
    <a href="index.php?route=medicos/create" class="btn btn-primary">Registrar Nuevo Médico</a>

    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="medicos_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, cédula, email..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=medicos_index" class="btn btn-primary">Limpiar</a>
    </form>
</div>

<?php if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); } ?>
<?php if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
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
                            <div class="action-buttons">
                                <a href="index.php?route=medicos/edit&id=<?php echo $medico['id_medico']; ?>" class="btn btn-warning">Editar</a>
                                <button class="btn btn-danger" onclick="confirmDeactivation(<?php echo $medico['id_medico']; ?>, '<?php echo htmlspecialchars(addslashes($medico['nombre'] . ' ' . $medico['apellido_paterno'])); ?>')">
                                    Desactivar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                            No se encontraron médicos que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay médicos registrados.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDeactivation(medicoId, medicoName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al médico: ${medicoName}`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Razón de la desactivación',
        inputPlaceholder: 'Escribe el motivo aquí...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return '¡Necesitas escribir una razón para la desactivación!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `index.php?route=medicos_delete&id=${medicoId}`;

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'razon';
            reasonInput.value = result.value;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>