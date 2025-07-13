<!-- app/views/tipos_servicio/index.php -->

<h2>Catálogo: Tipos de Servicio</h2>



<?php
// Reutilizar el bloque de mensajes de master.php si se prefiere no duplicar
if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); }
if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Código</th>
            <th>Requiere Médico</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Asegurar que la variable exista y sea un array
        $tiposServicios = $tiposServicios ?? [];
        if(count($tiposServicios) > 0):
        ?>
            <?php foreach($tiposServicios as $tipo): ?>
                <tr>
                    <td><?php echo $tipo['id_tipo_servicio']; ?></td>
                    <td><?php echo htmlspecialchars($tipo['nombre']); ?></td>
                    <td><?php echo !empty($tipo['codigo_servicio']) ? htmlspecialchars($tipo['codigo_servicio']) : '-'; ?></td>
                    <td><?php echo !empty($tipo['requiere_medico']) ? 'Sí' : 'No'; ?></td>
                    <td>
                        <span style="color: <?php echo ($tipo['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;">
                            <?php echo htmlspecialchars(ucfirst($tipo['estado'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?route=tipos_servicios/edit&id=<?php echo $tipo['id_tipo_servicio']; ?>" class="btn btn-warning">Editar</a>
                        <a href="index.php?route=tipos_servicios_delete&id=<?php echo $tipo['id_tipo_servicio']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este tipo de servicio?\nNombre: <?php echo htmlspecialchars(addslashes($tipo['nombre'])); ?>\n\n¡Atención: Si hay servicios usando este tipo, podría causar errores!')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center;">No hay tipos de servicio registrados</td> </tr>
        <?php endif; ?>
    </tbody>
</table>