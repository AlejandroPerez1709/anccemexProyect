<!--app/views/empleados/edit.php-->
<div class="form-container">
    <h2>Editar Empleado</h2>
    <?php if ($empleado): ?>
    <form action="index.php?route=empleados_update" method="POST" id="empleadoEditForm">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($formData['id_empleado'] ?? ''); ?>">

        <fieldset>
            <legend>Datos Personales</legend>
            <div class="form-row">
                 <div class="form-group">
                    <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" placeholder=" " required>
                    <label for="nombre">Nombre:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="<?php echo htmlspecialchars($formData['apellido_paterno'] ?? ''); ?>" placeholder=" " required>
                    <label for="apellido_paterno">Apellido Paterno:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="apellido_materno" id="apellido_materno" value="<?php echo htmlspecialchars($formData['apellido_materno'] ?? ''); ?>" placeholder=" " required>
                    <label for="apellido_materno">Apellido Materno:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" placeholder=" " required>
                    <label for="email">Email:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-group-full">
                 <input type="text" name="direccion" id="direccion" value="<?php echo htmlspecialchars($formData['direccion'] ?? ''); ?>" placeholder=" " required>
                <label for="direccion">Dirección:<span class="text-danger">*</span></label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Datos Laborales</legend>
            <div class="form-row">
                <div class="form-group">
                    <input type="tel" name="telefono" id="telefono" value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" placeholder=" " required pattern="[0-9]{10}">
                    <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <select name="puesto" id="puesto" required>
                        <option value="" disabled>-- Seleccione un puesto --</option>
                        <option value="Administrativo" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Administrativo') ? 'selected' : ''; ?>>Administrativo</option>
                        <option value="Mensajero" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Mensajero') ? 'selected' : ''; ?>>Mensajero</option>
                        <option value="Gerente" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Gerente') ? 'selected' : ''; ?>>Gerente</option>
                        <option value="Medico" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Medico') ? 'selected' : ''; ?>>Medico</option>
                        <option value="Secretaria" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Secretaria') ? 'selected' : ''; ?>>Secretaria</option>
                        <option value="Organizador" <?php echo (isset($formData['puesto']) && $formData['puesto'] == 'Organizador') ? 'selected' : ''; ?>>Organizador</option>
                    </select>
                    <label for="puesto">Puesto:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="<?php echo htmlspecialchars($formData['fecha_ingreso'] ?? ''); ?>" placeholder=" " required>
                    <label for="fecha_ingreso">Fecha de Ingreso:<span class="text-danger">*</span></label>
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
        <button type="submit" class="btn btn-primary">Actualizar Empleado</button>
        <a href="index.php?route=empleados_index" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php else: ?>
        <div class='alert alert-error'>Empleado no encontrado.</div>
        <a href="index.php?route=empleados_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>