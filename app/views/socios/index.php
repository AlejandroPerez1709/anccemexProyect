<!-- app/views/socios/index.php -->

<h2>Listado de Socios</h2>

<a href="index.php?route=socios/create" class="btn btn-primary" style="margin-bottom: 15px;">Registrar Nuevo Socio</a>

<?php /* Mensajes de sesión */ if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); } if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre Titular</th>
            <th>Apellidos</th>
            <th>Nombre Ganadería</th>
            <th>Email Contacto</th>
            <th>Teléfono Contacto</th>
            <th>Cód. Ganadero</th>
            <th>Estado</th>
            <th>Fecha Reg.</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if(isset($socios) && count($socios) > 0): ?>
            <?php foreach($socios as $socio): ?>
                <tr>
                    <td><?php echo $socio['id_socio']; ?></td>
                    <td><?php echo htmlspecialchars($socio['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($socio['apellido_paterno'] . ' ' . $socio['apellido_materno']); ?></td>
                    <td><?php echo !empty($socio['nombre_ganaderia']) ? htmlspecialchars($socio['nombre_ganaderia']) : '-'; ?></td>
                    <td><?php echo !empty($socio['email']) ? htmlspecialchars($socio['email']) : '-'; ?></td>
                    <td><?php echo !empty($socio['telefono']) ? htmlspecialchars($socio['telefono']) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($socio['codigoGanadero']); ?></td>
                     <td><span style="color: <?php echo ($socio['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($socio['estado'])); ?></span></td>
                     <td><?php echo isset($socio['fechaRegistro']) ? date('d/m/Y', strtotime($socio['fechaRegistro'])) : '-'; ?></td>
                    <td>
                        <a href="index.php?route=socios/edit&id=<?php echo $socio['id_socio']; ?>" class="btn btn-warning">Editar</a>
                        <a href="index.php?route=socios_delete&id=<?php echo $socio['id_socio']; ?>" class="btn btn-danger" 
                        onclick="return confirm('¿Seguro de eliminar socio: <?php echo htmlspecialchars(addslashes($socio['nombre'] . ' ' . $socio['apellido_paterno'])); ?>?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" style="text-align: center;">No hay socios registrados</td> </tr>
        <?php endif; ?>
    </tbody>
</table>