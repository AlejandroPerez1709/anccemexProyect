<h2>Listado de Ejemplares</h2>

<div class="table-header-controls">
    <a href="index.php?route=ejemplares/create" class="btn btn-primary">Registrar Nuevo Ejemplar</a>
    
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="ejemplares_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, código, cód. ganadero..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=ejemplares_index" class="btn btn-primary">Limpiar</a>
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
                <th>Código Ejemplar</th>
                <th>Socio Propietario (Cód. Gan.)</th>
                <th>Sexo</th>
                <th>Fecha Nac.</th>
                <th>Raza</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($ejemplares) && count($ejemplares) > 0): ?>
                <?php foreach($ejemplares as $ejemplar): ?>
                    <tr>
                        <td><?php echo $ejemplar['id_ejemplar']; ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['nombre'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] ?? '-'); ?></td>
                        <td>
                            <?php echo htmlspecialchars($ejemplar['nombre_socio'] ?? 'Socio Desconocido'); ?>
                            (<?php echo htmlspecialchars($ejemplar['socio_codigo_ganadero'] ?? 'S/C'); ?>)
                        </td>
                        <td><?php echo htmlspecialchars($ejemplar['sexo'] ?? '-'); ?></td>
                        <td><?php echo !empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['raza'] ?? '-'); ?></td>
                        <td><span style="color: <?php echo ($ejemplar['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($ejemplar['estado'])); ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=ejemplares/edit&id=<?php echo $ejemplar['id_ejemplar']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <button class="btn btn-danger btn-sm" onclick="confirmDeactivation(<?php echo $ejemplar['id_ejemplar']; ?>, '<?php echo htmlspecialchars(addslashes($ejemplar['nombre'])); ?>')">
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
                            No se encontraron ejemplares que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay ejemplares registrados.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDeactivation(ejemplarId, ejemplarName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al ejemplar: ${ejemplarName}`,
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
            form.action = `index.php?route=ejemplares_delete&id=${ejemplarId}`;

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