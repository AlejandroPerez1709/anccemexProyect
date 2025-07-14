<?php
// app/views/empleados/create.php

// Recuperar datos del formulario si hubo error. Viene del controlador.
$formData = $formData ?? []; // Asegurar que $formData exista

// Mensajes de error y warning ya se gestionan en master.php.
// Eliminamos la lógica duplicada aquí.
?>

<h2>Registrar Nuevo Empleado</h2>

<div class="form-container">
    <form action="index.php?route=empleados_store" method="POST" id="empleadoForm">
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
            <label for="email">Email:<span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="email" 
                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                   required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Ingrese un email válido">
        </div>
        <div class="form-group">
            <label for="direccion">Dirección:<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="direccion" id="direccion" 
                   value="<?php echo htmlspecialchars($formData['direccion'] ?? ''); ?>" 
                   required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos: letras, números, espacios, #, °, ., - y coma">
        </div>
         <div class="form-group">
             <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
             <input type="tel" class="form-control" name="telefono" id="telefono" 
                    value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>" 
                    required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
         </div>
        <div class="form-group">
            <label for="puesto">Puesto: <span class="text-danger">*</span></label>
            <select class="form-control" name="puesto" id="puesto" required>
                <option value="" disabled <?php echo empty($formData['puesto']) ? 'selected' : ''; ?>>Seleccione un puesto</option>
                <?php 
                $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
                $puesto_actual = $formData['puesto'] ?? ''; // Obtener puesto actual del formData
                foreach ($puestos_permitidos as $puesto_opcion) {
                    $selected_attr = ($puesto_actual == $puesto_opcion) ? 'selected' : '';
                    echo "<option value=\"{$puesto_opcion}\" {$selected_attr}>" . htmlspecialchars($puesto_opcion) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_ingreso">Fecha de Ingreso: <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="fecha_ingreso" id="fecha_ingreso" required
                   value="<?php echo htmlspecialchars($formData['fecha_ingreso'] ?? ''); ?>"
                   max="<?php echo date('Y-m-d'); ?>"
                   min="<?php echo date('Y-m-d', strtotime('-40 years')); ?>"
                   title="La fecha no puede ser futura ni demasiado antigua.">
             <small>No puede ser futura ni menor de 40 años atrás.</small>
        </div>

        <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary">Crear Empleado</button>
         <a href="index.php?route=empleados_index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Las validaciones de pattern y required ya las maneja el navegador.
    // La validación de email y longitud de teléfono ahora las hace el servidor principalmente.
    // Este script JS ya no es estrictamente necesario ya que las validaciones fuertes son server-side.
    // Sin embargo, si quieres mantener validaciones client-side (para feedback inmediato), podrías hacerlo.
    // Por ahora, eliminamos los 'alert' de JS ya que el servidor se encarga de los mensajes.
    document.getElementById('empleadoForm').addEventListener('submit', function(e) {
        // No hay JS de validación agresivo aquí. El servidor es la fuente de verdad.
        // Los atributos pattern, required, min, max del HTML ya proveen validación básica.
    });
});
</script>