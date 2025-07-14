<?php
// app/views/dashboard/index.php
?>

<h2>Listado de Ejemplares</h2>
<a href="index.php?route=ejemplares/create" class="btn btn-primary" style="margin-bottom: 15px;">Registrar Nuevo Ejemplar</a>
<?php /* ... mensajes ... */ ?>
<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Nombre</th>
            <th>Código Ejemplar</th> <th>Socio Propietario (Cód. Gan.)</th> <th>Sexo</th>
            <th>Fecha Nac.</th>
            <th>Raza</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php $ejemplares = $ejemplares ?? []; ?>
        <?php if(count($ejemplares) > 0): ?>
            <?php foreach($ejemplares as $ejemplar): ?>
                <tr>
                    <td><?php echo $ejemplar['id_ejemplar']; ?></td>
                    <td><?php echo htmlspecialchars($ejemplar['nombre'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] ?? '-'); ?></td> <td>
                        <?php echo htmlspecialchars($ejemplar['nombre_socio'] ?? 'Socio Desconocido'); ?>
                        (<abbr title="Código Ganadero"><?php echo htmlspecialchars($ejemplar['socio_codigo_ganadero'] ?? 'S/C'); ?></abbr>)
                    </td>
                    <td><?php echo htmlspecialchars($ejemplar['sexo'] ?? '-'); ?></td>
                    <td><?php echo !empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($ejemplar['raza'] ?? '-'); ?></td>
                    <td><span style="color: <?php echo ($ejemplar['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($ejemplar['estado'])); ?></span></td>
                    <td>
                        <a href="index.php?route=ejemplares/edit&id=<?php echo $ejemplar['id_ejemplar']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="index.php?route=ejemplares_delete&id=<?php echo $ejemplar['id_ejemplar']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar ejemplar?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="text-center"">No hay ejemplares registrados</td> </tr>
        <?php endif; ?>
    </tbody>
</table>