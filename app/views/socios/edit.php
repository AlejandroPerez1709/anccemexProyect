<!-- app/views/socios/edit.php -->
<?php
// Asegurar que las variables existan
$socio = $socio ?? null;
$documentosSocio = $documentosSocio ?? []; // Debe venir del controlador SociosController::edit
?>
<h2>Editar Socio: <?php echo htmlspecialchars($socio['nombre'] ?? 'Socio no encontrado'); ?> (<?php echo htmlspecialchars($socio['codigoGanadero'] ?? 'N/A'); ?>)</h2>

<?php
// Mostrar mensajes de sesión
if (isset($_SESSION['error'])) { echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
if (isset($_SESSION['warning'])) { echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); }
if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); }
?>

<div class="form-container">
     <?php if ($socio): ?>
    <form action="index.php?route=socios_update" method="POST" id="socioEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_socio" value="<?php echo $socio['id_socio']; ?>">

         <fieldset>
            <legend>Datos del Titular</legend>
            <div class="form-group">
                <label for="nombre">Nombre(s) Titular: <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($socio['nombre'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
            </div>
            <div class="form-group">
                <label for="apellido_paterno">Apellido Paterno: <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno" value="<?php echo htmlspecialchars($socio['apellido_paterno'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
            </div>
            <div class="form-group">
                <label for="apellido_materno">Apellido Materno: <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="apellido_materno" id="apellido_materno" value="<?php echo htmlspecialchars($socio['apellido_materno'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
            </div>
             <div class="form-group">
                 <label for="telefono">Teléfono: <span style="color: red;">*</span></label>
                 <input type="tel" class="form-control" name="telefono" id="telefono" value="<?php echo htmlspecialchars($socio['telefono'] ?? ''); ?>"required pattern="[0-9]{10}" title="10 dígitos">
             </div>
            <div class="form-group">
                <label for="email">Email: <span style="color: red;">*</span></label>
                <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($socio['email'] ?? ''); ?>" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Formato de email no válido">
            </div>
             <div class="form-group">
                <label for="identificacion_fiscal_titular">RFC: <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="identificacion_fiscal_titular" id="identificacion_fiscal_titular" value="<?php echo htmlspecialchars($socio['identificacion_fiscal_titular'] ?? ''); ?>"required pattern="[A-Za-z0-9\-]+" title="Letras, números y guiones">
            </div>
        </fieldset>

        <fieldset>
            <legend>Datos de la Ganadería</legend>
             <div class="form-group">
                <label for="nombre_ganaderia">Nombre Ganadería: <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="nombre_ganaderia" id="nombre_ganaderia" value="<?php echo htmlspecialchars($socio['nombre_ganaderia'] ?? ''); ?>" maxlength="150" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos">
            </div>
             <div class="form-group">
                <label for="direccion">Dirección: <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="direccion" id="direccion" value="<?php echo htmlspecialchars($socio['direccion'] ?? ''); ?>" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos">
            </div>
             <div class="form-group">
                <label for="codigoGanadero">Código Ganadero: <span style="color: red;">*</span></label>
                <input type="text" class="form-control" name="codigoGanadero" id="codigoGanadero" value="<?php echo htmlspecialchars($socio['codigoGanadero'] ?? ''); ?>" required pattern="[A-Za-z0-9]+" title="Letras y números">
            </div>
             <div class="form-group">
                <label for="fechaRegistro">Fecha de Registro (Socio):</label>
                <input type="date" class="form-control" name="fechaRegistro" id="fechaRegistro" value="<?php echo htmlspecialchars($socio['fechaRegistro'] ?? ''); ?>" required>
            </div>
             <div class="form-group">
                <label for="estado">Estado:</label>
                <select class="form-control" name="estado" id="estado" required>
                    <option value="activo" <?php echo (isset($socio['estado']) && $socio['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo (isset($socio['estado']) && $socio['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </fieldset>

        <fieldset>
             <legend>Documentos del Socio</legend>
             <div style="margin-bottom: 20px;">
                 <h4>Documentos Actuales:</h4>
                 <?php if (!empty($documentosSocio)): ?>
                     <ul style="list-style: none; padding: 0;">
                         <?php foreach ($documentosSocio as $doc): ?>
                              <li style="border-bottom: 1px solid #eee; padding: 8px 0; display: flex; flex-wrap:wrap; justify-content: space-between; align-items: center; gap: 10px;">
                                  <div style="flex-grow: 1;">
                                     <strong><?php echo htmlspecialchars($doc['tipoDocumento']); ?>:</strong>
                                     <a href="index.php?route=documento_download&id=<?php echo $doc['id_documento']; ?>" target="_blank"> <?php echo htmlspecialchars($doc['nombreArchivoOriginal']); ?> </a>
                                     <small>(Subido: <?php echo date('d/m/Y H:i', strtotime($doc['fechaSubida'])); ?> por <?php echo htmlspecialchars($doc['uploaded_by_username'] ?? 'N/A'); ?>)</small>
                                      <?php if(!empty($doc['comentarios'])): ?> <p style="font-size: 0.85em; color: #555; margin-left: 15px; margin-top: 4px;"><i>Comentarios: <?php echo htmlspecialchars($doc['comentarios']); ?></i></p> <?php endif; ?>
                                 </div>
                                  <div style="white-space: nowrap;">
                                      <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario'): ?>
                                         <a href="index.php?route=documento_delete&id=<?php echo $doc['id_documento']; ?>&socio_id=<?php echo $socio['id_socio']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar documento?')">Eliminar</a>
                                      <?php endif; ?>
                                 </div>
                             </li>
                         <?php endforeach; ?>
                     </ul>
                 <?php else: ?> <p>No hay documentos registrados.</p> <?php endif; ?>
             </div>
             <hr>
             <h4>Subir/Actualizar Documentos:</h4>
             <small>Suba un archivo para añadirlo o actualizar uno existente del mismo tipo.</small>
               <div class="form-group"><label for="id_oficial_file">ID Oficial:</label><input type="file" class="form-control" name="id_oficial_file" accept=".pdf,.jpg,.png,.gif"></div>
               <div class="form-group"><label for="rfc_file">Constancia Fiscal:</label><input type="file" class="form-control" name="rfc_file" accept=".pdf,.jpg,.png,.gif"></div>
               <div class="form-group"><label for="domicilio_file">Comp. Domicilio:</label><input type="file" class="form-control" name="domicilio_file" accept=".pdf,.jpg,.png,.gif"></div>
               <div class="form-group"><label for="titulo_propiedad_file">Título Propiedad:</label><input type="file" class="form-control" name="titulo_propiedad_file" accept=".pdf,.jpg,.png,.gif"></div>
        </fieldset>

        <p><small><span style="color: red;">*</span> Campos obligatorios para datos del socio/ganadería.</small></p>
        <button type="submit" class="btn btn-primary">Actualizar Socio y Subir Documentos</button>
        <a href="index.php?route=socios_index" class="btn btn-secondary">Cancelar</a>
    </form>
     <?php else: ?>
        <div class='alert alert-error'>Socio no encontrado.</div>
        <a href="index.php?route=socios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>
<style> fieldset { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; } legend { padding: 0 10px; font-weight: bold; color: #333; } </style>
<script> /* JS para fecha max igual que antes */ </script>