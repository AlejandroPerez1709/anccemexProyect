<?php
//app/views/empleados/edit.php
// Asegurar que las variables existan
// $empleado contendrá los datos del empleado si se cargó correctamente.
// $formData tendrá prioridad si hubo un error de validación en el POST.
$empleado = $empleado ?? null; 
$formData = $formData ?? $empleado; // Repoblar con datos del empleado o de la sesión si hubo error
?>

<div class="form-container">
    <h2>Editar Empleado</h2>
    <?php if ($empleado): // Solo mostrar formulario si el empleado existe ?>
    <form action="index.php?route=empleados_update" method="POST" id="empleadoEditForm">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($empleado['id_empleado'] ?? ''); ?>">

        <fieldset>
            <legend>Datos Personales</legend>
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
                           placeholder=" " required>
                    <label for="email">Email:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-group-full">
                <input type="text" name="direccion" id="direccion" 
                       value="<?php echo htmlspecialchars($formData['direccion'] ?? ''); ?>" 
                       placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos: letras, números, espacios, #, °, ., - y coma">
                <label for="direccion">Dirección:<span class="text-danger">*</span></label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Datos Laborales</legend>
            <div class="form-row">
                <div class="form-group">
                    <input type="tel" name="telefono" id="telefono" 
                           value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" 
                           placeholder=" " required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
                    <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
                </div>
                <div class="form-group">
                    <select name="puesto" id="puesto" required>
                        <option value="" disabled <?php echo empty($formData['puesto']) ? 'selected' : ''; ?>>Seleccione un puesto</option>
                        <?php 
                        $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
                        $puesto_actual = $formData['puesto'] ?? '';
                        foreach ($puestos_permitidos as $puesto_opcion) {
                            $selected_attr = ($puesto_actual == $puesto_opcion) ? 'selected' : '';
                            echo "<option value=\"{$puesto_opcion}\" {$selected_attr}>" . htmlspecialchars($puesto_opcion) . "</option>";
                        }
                        ?>
                    </select>
                    <label for="puesto">Puesto:<span class="text-danger">*</span></label>
                </div>
            </div>
            <div class="form-group-full">
                <input type="date" name="fecha_ingreso" id="fecha_ingreso" 
                       value="<?php echo htmlspecialchars($formData['fecha_ingreso'] ?? ''); ?>" 
                       placeholder=" " required
                       max="<?php echo date('Y-m-d'); ?>"
                       min="<?php echo date('Y-m-d', strtotime('-40 years')); ?>"
                       title="La fecha no puede ser futura ni demasiado antigua.">
                <label for="fecha_ingreso">Fecha de Ingreso:<span class="text-danger">*</span></label>
                <small>No puede ser futura ni más de 40 años atrás.</small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // La validación robusta se realiza en el servidor.
    // Los atributos pattern, required, min, max del HTML ya proveen validación básica del lado del cliente.
    // El placeholder=" " es importante para el efecto flotante de la etiqueta.
});
</script>