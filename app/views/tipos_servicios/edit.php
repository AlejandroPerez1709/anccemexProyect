<!-- app/views/tipos_servicio/edit.php -->

<?php $tipoServicio = $tipoServicio ?? null; // Asegura que exista ?>
<h2>Editar Tipo de Servicio: <?php echo htmlspecialchars($tipoServicio['nombre'] ?? 'Error'); ?></h2>

<?php
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
?>

<div class="form-container">
     <?php if ($tipoServicio): ?>
    <form action="index.php?route=tipos_servicios_update" method="POST" id="tipoServicioEditForm">
        <input type="hidden" name="id_tipo_servicio" value="<?php echo $tipoServicio['id_tipo_servicio']; ?>">

        <div class="form-group">
            <label for="nombre">Nombre del Servicio: <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($tipoServicio['nombre']); ?>" required maxlength="150">
        </div>

        <div class="form-group">
            <label for="codigo_servicio">Código Oficial (Ej: 619):</label>
            <input type="text" class="form-control" name="codigo_servicio" id="codigo_servicio" value="<?php echo htmlspecialchars($tipoServicio['codigo_servicio'] ?? ''); ?>" maxlength="10">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción Breve:</label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="3"><?php echo htmlspecialchars($tipoServicio['descripcion'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="documentos_requeridos">Documentos Requeridos (informativo):</label>
            <textarea class="form-control" name="documentos_requeridos" id="documentos_requeridos" rows="3"><?php echo htmlspecialchars($tipoServicio['documentos_requeridos'] ?? ''); ?></textarea>
             <small>Ej: Carta Titularidad Endosada, Comprobante Pago.</small>
        </div>

         <div class="form-group">
            <label>Características:</label>
            <div>
                <input type="checkbox" id="requiere_medico" name="requiere_medico" value="1" <?php echo !empty($tipoServicio['requiere_medico']) ? 'checked' : ''; ?>>
                <label for="requiere_medico"> Requiere Médico?</label>
            </div>
             </div>

         <div class="form-group">
            <label for="estado">Estado: <span style="color: red;">*</span></label>
            <select class="form-control" name="estado" id="estado" required>
                <option value="activo" <?php echo ($tipoServicio['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo ($tipoServicio['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

         <p><small><span style="color: red;">*</span> Campos obligatorios</small></p>

        <button type="submit" class="btn btn-primary">Actualizar Tipo Servicio</button>
        <a href="index.php?route=tipos_servicios_index" class="btn btn-secondary">Cancelar</a>
    </form>
     <?php else: ?>
        <div class='alert alert-error'>Tipo de Servicio no encontrado.</div>
        <a href="index.php?route=tipos_servicios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>