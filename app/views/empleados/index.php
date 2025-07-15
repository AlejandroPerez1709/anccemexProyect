<!-- app/views/empleados/index.php -->
<h2>Listado de Empleados</h2>

<a href="index.php?route=empleados/create" class="btn btn-primary margin-bottom-15">Registrar Nuevo Empleado</a>  

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Puesto</th>
            <th>Fecha Ingreso</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if(count($empleados) > 0): ?>
            <?php foreach($empleados as $empleado): ?>
                <tr>
                    <td><?php echo $empleado['id_empleado']; ?></td>
                    <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($empleado['apellido_paterno'] . ' ' . $empleado['apellido_materno']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                    <td><?php echo htmlspecialchars($empleado['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($empleado['puesto']); ?></td>
                    <td><?php echo isset($empleado['fecha_ingreso']) ? htmlspecialchars($empleado['fecha_ingreso']) : ''; ?></td>
                    <td>
                        <a href="index.php?route=empleados/edit&id=<?php echo $empleado['id_empleado']; ?>" class="btn btn-warning">Editar</a>
                        <a href="index.php?route=empleados_delete&id=<?php echo $empleado['id_empleado']; ?>" class="btn btn-danger" 
                        onclick="return confirm('¿Estás seguro de eliminar este empleado?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center" >No hay empleados registrados</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>