<!-- app/views/usuarios/edit.php -->

<h2>Editar Usuario: <?php echo htmlspecialchars($usuario['username']); ?></h2>

<?php
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}
?>

<div class="form-container">
    <form action="index.php?route=usuarios_update" method="POST" id="usuarioEditForm">
        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">

        <div class="form-group">
            <label for="nombre">Nombre: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno" value="<?php echo htmlspecialchars($usuario['apellido_paterno']); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="apellido_materno">Apellido Materno: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="apellido_materno" id="apellido_materno" value="<?php echo htmlspecialchars($usuario['apellido_materno']); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="email">Email: <span style="color: red;">*</span></label>
            <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Nombre de Usuario: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="username" id="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" required pattern="[a-zA-Z0-9_]{4,20}" title="Entre 4 y 20 caracteres alfanuméricos o guión bajo (_)">
        </div>

        <div class="form-group">
            <label for="password">Nueva Contraseña:</label>
            <input type="password" class="form-control" name="password" id="password" minlength="6" title="Mínimo 6 caracteres si se cambia">
            <small>Dejar en blanco para no cambiar la contraseña.</small>
        </div>

         <div class="form-group">
            <label for="rol">Rol: <span style="color: red;">*</span></label>
            <select class="form-control" name="rol" id="rol" required>
                <option value="" disabled>Seleccione un rol</option>
                <option value="usuario" <?php echo ($usuario['rol'] == 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                <option value="superusuario" <?php echo ($usuario['rol'] == 'superusuario') ? 'selected' : ''; ?>>Superusuario</option>
            </select>
        </div>

        <div class="form-group">
            <label for="estado">Estado: <span style="color: red;">*</span></label>
            <select class="form-control" name="estado" id="estado" required>
                <option value="activo" <?php echo ($usuario['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo ($usuario['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

         <p><small><span style="color: red;">*</span> Campos obligatorios</small></p>

        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        <a href="index.php?route=usuarios_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
// Validaciones JS opcionales para el formulario de edición
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('usuarioEditForm').addEventListener('submit', function(e) {
         var email = document.getElementById('email').value;
         var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
         if (!emailRegex.test(email)) {
             alert('Por favor ingrese un correo electrónico válido.');
             e.preventDefault();
             return false;
         }

         var password = document.getElementById('password').value;
         // Validar contraseña solo si se escribió algo
         if (password.length > 0 && password.length < 6) {
              alert('La nueva contraseña debe tener al menos 6 caracteres.');
              e.preventDefault();
              return false;
          }
    });
});
</script>