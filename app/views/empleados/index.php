<h2>Listado de Empleados</h2>

<div class="table-header-controls">
    <a href="index.php?route=empleados/create" class="btn btn-primary">Registrar Nuevo Empleado</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="empleados_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, puesto, email..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=empleados_index" class="btn btn-primary">Limpiar</a>
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
                <th>Puesto</th>
                <th>Estado</th>
                <th>Fecha Ingreso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($empleados) && count($empleados) > 0): ?>
                <?php foreach($empleados as $empleado): ?>
                    <tr>
                        <td><?php echo $empleado['id_empleado']; ?></td>
                        <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['apellido_paterno'] . ' ' . $empleado['apellido_materno']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['puesto']); ?></td>
                        <td>
                            <span style="color: <?php echo ($empleado['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;">
                                <?php echo htmlspecialchars(ucfirst($empleado['estado'])); ?>
                            </span>
                        </td>
                        <td><?php echo isset($empleado['fecha_ingreso']) ? date('d/m/Y', strtotime($empleado['fecha_ingreso'])) : ''; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=empleados/edit&id=<?php echo $empleado['id_empleado']; ?>" class="btn btn-warning">Editar</a>
                                <button class="btn btn-danger" onclick="confirmDeactivation(<?php echo $empleado['id_empleado']; ?>, '<?php echo htmlspecialchars(addslashes($empleado['nombre'] . ' ' . $empleado['apellido_paterno'])); ?>')">
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
                            No se encontraron empleados que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay empleados registrados.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDeactivation(empleadoId, empleadoName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al empleado: ${empleadoName}`,
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
            form.action = `index.php?route=empleados_delete&id=${empleadoId}`;

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