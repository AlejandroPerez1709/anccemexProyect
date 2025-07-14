<?php
//app/views/usuarios/edit.php
// Asegurar que las variables existan
// $usuario contendrá los datos del usuario si se cargó correctamente.
// $formData tendrá prioridad si hubo un error de validación en el POST.
$usuario = $usuario ?? null;
$formData = $formData ?? $usuario; // Repoblar con datos del usuario o de la sesión si hubo error
?>

<h2>Editar Usuario: <?php echo htmlspecialchars($usuario['username'] ?? 'Usuario no encontrado'); ?></h2>

<?php
// Mensajes de error y éxito ahora se gestionan en master.php.
// Eliminamos la lógica duplicada aquí.
?>

<div class="form-container">
    <?php if ($usuario): // Solo mostrar formulario si el usuario existe ?>
    <form action="index.php?route=usuarios_update" method="POST" id="usuarioEditForm">
        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuario['id_usuario'] ?? ''); ?>">

        <div class="form-group">
            <label for="nombre">Nombre: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" id="nombre"
                   value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno"
                   value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>"
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="apellido_materno">Apellido Materno: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="apellido_materno" id="apellido_materno"
                   value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>"
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="email">Email: <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="email"
                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="username">Nombre de Usuario: <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="username" id="username"
                   value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                   required pattern="[a-zA-Z0-9_]{4,20}" title="Entre 4 y 20 caracteres alfanuméricos o guión bajo (_)">
        </div>

        <div class="form-group">
            <label for="password">Nueva Contraseña:</label>
            <input type="password" class="form-control" name="password" id="password"
                   minlength="8" title="Mínimo 8 caracteres si se cambia">
            <small>Dejar en blanco para no cambiar la contraseña.</small>
        </div>

        <div class="form-group">
            <label for="rol">Rol: <span class="text-danger">*</span></label>
            <select class="form-control" name="rol" id="rol" required>
                <option value="" disabled <?php echo empty($formData['rol']) ? 'selected' : ''; ?>>Seleccione un rol</option>
                <option value="usuario" <?php echo (isset($formData['rol']) && $formData['rol'] == 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                <option value="superusuario" <?php echo (isset($formData['rol']) && $formData['rol'] == 'superusuario') ? 'selected' : ''; ?>>Superusuario</option>
            </select>
        </div>

        <div class="form-group">
            <label for="estado">Estado: <span class="text-danger">*</span></label>
            <select class="form-control" name="estado" id="estado" required>
                <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>

        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        <a href="index.php?route=usuarios_index" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php else: ?>
        <div class='alert alert-error'>Usuario no encontrado.</div>
        <a href="index.php?route=usuarios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Las validaciones de pattern, required, minlength etc. ya las maneja el navegador.
    // La validación robusta se realiza en el servidor.
    // Eliminamos los 'alert' de JS ya que el servidor se encarga de los mensajes detallados.
    document.getElementById('usuarioEditForm').addEventListener('submit', function(e) {
        // No hay JS de validación agresivo aquí. El servidor es la fuente de verdad.
    });
});
</script>