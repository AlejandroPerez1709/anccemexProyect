<!-- app/views/empleados/edit.php -->
<?php
$empleado = $empleado ?? null; // Asegurar que exista
// Calcular fechas min/max
$minFechaIngreso = date('Y-m-d', strtotime('-40 years'));
$maxFechaIngreso = date('Y-m-d'); // Hoy
?>
<h2>Editar Empleado</h2>

<?php if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<div class="form-container">
    <?php if ($empleado): ?>
    <form action="index.php?route=empleados_update" method="POST" id="empleadoEditForm">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($empleado['id_empleado']); ?>">

        <div class="form-group"> <label for="nombre">Nombre: <span style="color: red;">*</span></label> <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($empleado['nombre'] ?? ''); ?>" required pattern="[...]" title="..."> </div>
         <div class="form-group"> <label for="apellido_paterno">Ap Paterno: <span style="color: red;">*</span></label> <input type="text" name="apellido_paterno" id="apellido_paterno" class="form-control" value="<?php echo htmlspecialchars($empleado['apellido_paterno'] ?? ''); ?>" required pattern="[...]" title="..."> </div>
         <div class="form-group"> <label for="apellido_materno">Ap Materno: <span style="color: red;">*</span></label> <input type="text" name="apellido_materno" id="apellido_materno" class="form-control" value="<?php echo htmlspecialchars($empleado['apellido_materno'] ?? ''); ?>" required pattern="[...]" title="..."> </div>
         <div class="form-group"> <label for="email">Email:</label> <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($empleado['email'] ?? ''); ?>" required pattern="[...]" title="..."> </div>
         <div class="form-group"> <label for="direccion">Dirección:</label> <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($empleado['direccion'] ?? ''); ?>" required pattern="[...]" title="..."> </div>
         <div class="form-group"> <label for="telefono">Teléfono:</label> <input type="tel" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($empleado['telefono'] ?? ''); ?>" required pattern="[0-9]{10}" title="..."> </div>
         
         <div class="form-group">
            <label for="puesto">Puesto: <span style="color: red;">*</span></label>
            <select name="puesto" id="puesto" class="form-control" required>
            <option value="" disabled <?php echo empty($empleado['puesto']) ? 'selected' : ''; ?>>Seleccione un puesto</option>

            <?php 
             
            $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
            $puesto_actual = $empleado['puesto'] ?? ''; // Obtener puesto actual

            foreach ($puestos_permitidos as $puesto_opcion) {
                $selected_attr = ($puesto_actual == $puesto_opcion) ? 'selected' : '';
                echo "<option value=\"{$puesto_opcion}\" {$selected_attr}>" . htmlspecialchars($puesto_opcion) . "</option>";
            }
            ?>
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_ingreso">Fecha de Ingreso:</label>
            <input type="date" class="form-control" name="fecha_ingreso" id="fecha_ingreso" required
                   value="<?php echo htmlspecialchars($empleado['fecha_ingreso'] ?? ''); ?>"
                   max="<?php echo $maxFechaIngreso; // Fecha máxima es hoy ?>"
                   min="<?php echo $minFechaIngreso; // *** Fecha mínima añadida *** ?>"
                   title="La fecha no puede ser futura ni demasiado antigua.">
            <small>No puede ser futura ni más de 40 años atrás.</small>
        </div>

        <p><small><span style="color: red;">*</span> Campos obligatorios</small></p>
        <button type="submit" class="btn btn-primary">Actualizar Empleado</button>
        <a href="index.php?route=empleados_index" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php else: ?>
         <div class='alert alert-error'>Empleado no encontrado.</div>
         <a href="index.php?route=empleados_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script> /* Script existente para validar email no necesita cambios */ </script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Establecer la fecha máxima como hoy
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    today = yyyy + '-' + mm + '-' + dd;
    document.getElementById('fecha_ingreso').setAttribute('max', today);
    
    // Validación adicional de email
    document.getElementById('empleadoEditForm').addEventListener('submit', function(e) {
        var email = document.getElementById('email').value;
        var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Por favor ingrese un correo electrónico válido');
            return false;
        }
    });
});
</script>