<?php
//app/views/usuarios/edit.php
// Asegurar que las variables existan
// $usuario contendrá los datos del usuario si se cargó correctamente.
// $formData tendrá prioridad si hubo un error de validación en el POST.
$usuario = $usuario ?? null;
$formData = $formData ?? $usuario; // Repoblar con datos del usuario o de la sesión si hubo error
?>

<div class="form-container"> <h2>Editar Usuario: <?php echo htmlspecialchars($usuario['username'] ?? 'Usuario no encontrado'); ?></h2>
    <?php if ($usuario): // Solo mostrar formulario si el usuario existe ?>
    <form action="index.php?route=usuarios_update" method="POST" id="usuarioEditForm">
        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuario['id_usuario'] ?? ''); ?>">

        <fieldset>
            <legend>Datos de Usuario</legend>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="nombre" id="nombre"
                           value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="nombre">Nombre:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <input type="text" name="apellido_paterno" id="apellido_paterno"
                           value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="apellido_paterno">Apellido Paterno:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="apellido_materno" id="apellido_materno"
                           value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="apellido_materno">Apellido Materno:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email"
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                           placeholder=" " required autocomplete="off">
                    <label for="email">Email:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="username" id="username"
                           value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                           placeholder=" " required pattern="[a-zA-Z0-9_]{4,20}" title="Entre 4 y 20 caracteres alfanuméricos o guión bajo (_)" autocomplete="off">
                    <label for="username">Nombre de Usuario:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password"
                           placeholder=" " minlength="8" title="Mínimo 8 caracteres si se cambia" autocomplete="new-password">
                    <label for="password">Nueva Contraseña:</label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <select name="rol" id="rol" required>
                        <option value="" disabled <?php echo empty($formData['rol']) ? 'selected' : ''; ?>>Seleccione un rol</option>
                        <option value="usuario" <?php echo (isset($formData['rol']) && $formData['rol'] == 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                        <option value="superusuario" <?php echo (isset($formData['rol']) && $formData['rol'] == 'superusuario') ? 'selected' : ''; ?>>Superusuario</option>
                    </select>
                    <label for="rol">Rol:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <select name="estado" id="estado" required>
                        <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                    <label for="estado">Estado:<span class="text-danger">*</span></label>
                </div>
            </div>
        </fieldset>

         <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>

        <div class="form-actions-bottom">
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
            <a href="index.php?route=usuarios_index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
    <?php else: ?>
        <div class='alert alert-error'>Usuario no encontrado.</div>
        <a href="index.php?route=usuarios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // La validación robusta se realiza en el servidor.
    // Los atributos pattern, required, minlength etc. del HTML ya proveen validación básica del lado del cliente.
    // El placeholder=" " es importante para el efecto flotante de la etiqueta.
});
</script>