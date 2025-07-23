<h2>Listado de Socios</h2>

<div class="table-header-controls">
    <a href="index.php?route=socios/create" class="btn btn-primary">Registrar Nuevo Socio</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="socios_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, ganadería, código..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=socios_index" class="btn btn-primary">Limpiar</a>
    </form>
</div>

<?php if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); } ?>
<?php if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
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
                            <div class="action-buttons">
                                <a href="index.php?route=socios/edit&id=<?php echo $socio['id_socio']; ?>" class="btn btn-warning">Editar</a>
                                <button class="btn btn-danger" onclick="confirmDeactivation(<?php echo $socio['id_socio']; ?>, '<?php echo htmlspecialchars(addslashes($socio['nombre'] . ' ' . $socio['apellido_paterno'])); ?>')">
                                    Desactivar
                                </button>
                                </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                            No se encontraron socios que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay socios registrados.
                        <?php endif; ?>
                    </td> 
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDeactivation(socioId, socioName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al socio: ${socioName}`,
        icon: 'warning',
        input: 'textarea', // Añadimos un campo de texto
        inputLabel: 'Razón de la desactivación',
        inputPlaceholder: 'Escribe el motivo aquí...',
        inputAttributes: {
            'aria-label': 'Escribe el motivo aquí'
        },
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        // Validación para que el campo no esté vacío
        inputValidator: (value) => {
            if (!value) {
                return '¡Necesitas escribir una razón para la desactivación!'
            }
        }
    }).then((result) => {
        // Si el usuario confirma y ha escrito una razón
        if (result.isConfirmed && result.value) {
            // Creamos un formulario oculto para enviar los datos por POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `index.php?route=socios_delete&id=${socioId}`;

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'razon';
            reasonInput.value = result.value; // El texto del textarea
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>