<?php
// app/views/usuarios/create.php

// Recuperar errores y datos del formulario si los hay
$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);
?>

<div class="form-container"> <h2>Registrar Nuevo Usuario</h2>
    <form action="index.php?route=usuarios_store" method="POST" id="usuarioForm">
        <fieldset>
            <legend>Datos de Usuario</legend>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="nombre" id="nombre"
                           value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="nombre">Nombre:<span class="text-danger">*</span></label>
                    <?php if (isset($errors['nombre'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['nombre'][0]); ?></div>
                    <?php endif; ?>
                </div>
                 <div class="form-group">
                    <input type="text" name="apellido_paterno" id="apellido_paterno"
                           value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="apellido_paterno">Apellido Paterno:<span class="text-danger">*</span></label>
                    <?php if (isset($errors['apellido_paterno'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['apellido_paterno'][0]); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                     <input type="text" name="apellido_materno" id="apellido_materno"
                           value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>"
                           placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                    <label for="apellido_materno">Apellido Materno:<span class="text-danger">*</span></label>
                    <?php if (isset($errors['apellido_materno'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['apellido_materno'][0]); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                     <input type="email" name="email" id="email"
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                           placeholder=" " required autocomplete="off">
                    <label for="email">Email:<span class="text-danger">*</span></label>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['email'][0]); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                     <input type="text" name="username" id="username"
                           value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                           placeholder=" " required pattern="[a-zA-Z0-9_]{4,20}" title="Entre 4 y 20 caracteres alfanuméricos o guión bajo (_)" autocomplete="off">
                    <label for="username">Nombre de Usuario:<span class="text-danger">*</span></label>
                    <?php if (isset($errors['username'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['username'][0]); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password"
                           placeholder=" " required minlength="8" title="Mínimo 8 caracteres" autocomplete="new-password">
                    <label for="password">Contraseña:<span class="text-danger">*</span></label>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['password'][0]); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                     <select name="rol" id="rol" required>
                        <option value="" disabled <?php echo empty($formData['rol']) ? 'selected' : ''; ?>>-</option>
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
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="index.php?route=usuarios_index" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>