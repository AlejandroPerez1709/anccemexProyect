<!-- app/views/usuarios/index.php -->

<h2>Listado de Usuarios</h2>
<a href="index.php?route=usuarios/create" class="btn btn-primary margin-bottom-15">Registrar Nuevo Usuario</a>

<?php
// Mensajes de éxito o error generales del listado
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
                    <td>
                        <?php echo htmlspecialchars($usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($usuario['rol'])); // Pone la primera letra en mayúscula ?></td>
                     <td>
                        <span style="color: <?php echo ($usuario['estado'] == 'activo') ? 'green' : 'red'; ?>;">
                            <?php echo htmlspecialchars(ucfirst($usuario['estado'])); ?>
                        </span>
                    </td>
                     <td><?php echo isset($usuario['created_at']) ? date('d/m/Y H:i', strtotime($usuario['created_at'])) : '-'; ?></td>
                     <td><?php echo isset($usuario['ultimo_login']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca'; ?></td>
                    <td>
                        <?php // No permitir editar o eliminar al usuario actual logueado desde esta lista ?>
                        <?php if ($usuario['id_usuario'] !== $_SESSION['user']['id_usuario']): ?>
                            <a href="index.php?route=usuarios/edit&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?route=usuarios_delete&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-danger" 
                            onclick="return confirm('¿Estás seguro de eliminar a este usuario?\nNombre: <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno']); ?>\nUsername: <?php echo htmlspecialchars($usuario['username']); ?>\n\n¡Esta acción no se puede deshacer!')">Eliminar</a>
                        <?php else: ?>
                            <span class="gray">(Usuario actual)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center" >No hay usuarios registrados</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>