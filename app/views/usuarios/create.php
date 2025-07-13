<!-- app/views/usuarios/create.php -->

<h2>Registrar Nuevo Usuario</h2>

<?php
// Mostrar errores específicos si existen en la sesión (útil si rediriges aquí en caso de error)
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']); // Limpiar el error después de mostrarlo
}
?>

<div class="form-container">
    <form action="index.php?route=usuarios_store" method="POST" id="usuarioForm">
        <div class="form-group">
            <label for="nombre">Nombre: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="nombre" id="nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="apellido_paterno">Apellido Paterno: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="apellido_materno">Apellido Materno: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="apellido_materno" id="apellido_materno" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
        </div>

        <div class="form-group">
            <label for="email">Email: <span style="color: red;">*</span></label>
            <input type="email" class="form-control" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="username">Nombre de Usuario: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="username" id="username" required pattern="[a-zA-Z0-9_]{4,20}" title="Entre 4 y 20 caracteres alfanuméricos o guión bajo (_)">
        </div>

        <div class="form-group">
            <label for="password">Contraseña: <span style="color: red;">*</span></label>
            <input type="password" class="form-control" name="password" id="password" required minlength="6" title="Mínimo 6 caracteres">
            </div>

         <div class="form-group">
            <label for="rol">Rol: <span style="color: red;">*</span></label>
            <select class="form-control" name="rol" id="rol" required>
                <option value="" disabled selected>Seleccione un rol</option>
                <option value="usuario">Usuario</option>
                <option value="superusuario">Superusuario</option>
            </select>
        </div>

        <div class="form-group">
            <label for="estado">Estado: <span style="color: red;">*</span></label>
            <select class="form-control" name="estado" id="estado" required>
                <option value="activo" selected>Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <p><small><span style="color: red;">*</span> Campos obligatorios</small></p>

        <button type="submit" class="btn btn-primary">Crear Usuario</button>
         <a href="index.php?route=usuarios_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
// Puedes añadir validaciones JavaScript adicionales si lo necesitas
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('usuarioForm').addEventListener('submit', function(e) {
        var email = document.getElementById('email').value;
        var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(email)) {
            alert('Por favor ingrese un correo electrónico válido.');
            e.preventDefault(); // Detener el envío del formulario
            return false;
        }

         var password = document.getElementById('password').value;
         if (password.length < 6) {
             alert('La contraseña debe tener al menos 6 caracteres.');
             e.preventDefault();
             return false;
         }
         // Añadir más validaciones JS si es necesario (ej. confirmación de contraseña)
    });
});
</script>