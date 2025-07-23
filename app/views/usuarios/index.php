<h2>Listado de Usuarios</h2>

<div class="table-header-controls">
    <a href="index.php?route=usuarios/create" class="btn btn-primary">Registrar Nuevo Usuario</a>

    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="usuarios_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, email, username..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=usuarios_index" class="btn btn-primary">Limpiar</a>
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
                <th>Username</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creado en</th>
                <th>Último Login</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($usuarios) && count($usuarios) > 0): ?>
                <?php foreach($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id_usuario']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?></td>
                        <td>
                            <span style="color: <?php echo ($usuario['estado'] == 'activo') ? 'green' : 'red'; ?>;">
                                <?php echo htmlspecialchars(ucfirst($usuario['estado'])); ?>
                            </span>
                        </td>
                        <td><?php echo isset($usuario['created_at']) ? date('d/m/Y H:i', strtotime($usuario['created_at'])) : '-'; ?></td>
                        <td><?php echo isset($usuario['ultimo_login']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca'; ?></td>
                        <td>
                            <?php if ($usuario['id_usuario'] !== $_SESSION['user']['id_usuario']): ?>
                                <div class="action-buttons">
                                    <a href="index.php?route=usuarios/edit&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-warning">Editar</a>
                                    <button class="btn btn-danger" onclick="confirmDeactivation(<?php echo $usuario['id_usuario']; ?>, '<?php echo htmlspecialchars(addslashes($usuario['username'])); ?>')">
                                        Desactivar
                                    </button>
                                </div>
                            <?php else: ?>
                                <span style="color: #777;">(Usuario actual)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                            No se encontraron usuarios que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay usuarios registrados.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDeactivation(usuarioId, usuarioName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al usuario: ${usuarioName}`,
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
            form.action = `index.php?route=usuarios_delete&id=${usuarioId}`;

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