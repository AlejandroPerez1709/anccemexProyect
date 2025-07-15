<?php
// Asegurar que las variables existan y sean del tipo esperado
// $socio contendrá los datos del socio si se cargó correctamente.
// $formData tendrá prioridad si hubo un error de validación en el POST.
$socio = $socio ?? null; 
$documentosSocio = $documentosSocio ?? []; // Debe venir del controlador SociosController::edit
$formData = $formData ?? $socio; // Repoblar con datos del socio o de la sesión si hubo error
?>

<div class="form-container form-wide"> <h2>Editar Socio: <?php echo htmlspecialchars($socio['nombre'] ?? 'Socio no encontrado'); ?> (<?php echo htmlspecialchars($socio['codigoGanadero'] ?? 'N/A'); ?>)</h2>
    <?php if ($socio): // Solo mostrar formulario si el socio existe ?>
    <form action="index.php?route=socios_update" method="POST" id="socioEditForm" enctype="multipart/form-data">
        <input type="hidden" name="id_socio" value="<?php echo htmlspecialchars($socio['id_socio'] ?? ''); ?>">

        <div class="form-main-columns"> <div class="form-main-col left-col">
                <fieldset>
                    <legend>Datos del Titular</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="nombre" id="nombre"
                                   value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                                   placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                            <label for="nombre">Nombre(s) Titular:<span class="text-danger">*</span></label>
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
                            <input type="tel" name="telefono" id="telefono"
                                   value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>"
                                   placeholder=" " required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
                            <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="email" name="email" id="email"
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                                   placeholder=" " required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Formato de email no válido">
                            <label for="email">Email:<span class="text-danger">*</span></label>
                        </div>
                        <div class="form-group">
                            <input type="text" name="identificacion_fiscal_titular" id="identificacion_fiscal_titular"
                                   value="<?php echo htmlspecialchars($formData['identificacion_fiscal_titular'] ?? ''); ?>"
                                   placeholder=" " required pattern="[A-Za-z0-9\-]+" title="Letras, números y guiones permitidos">
                            <label for="identificacion_fiscal_titular">RFC:<span class="text-danger">*</span></label>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Datos de la Ganadería</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="nombre_ganaderia" id="nombre_ganaderia"
                                   value="<?php echo htmlspecialchars($formData['nombre_ganaderia'] ?? ''); ?>"
                                   placeholder=" " required maxlength="150" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos">
                            <label for="nombre_ganaderia">Nombre Ganadería:<span class="text-danger">*</span></label>
                        </div>
                        <div class="form-group">
                            <input type="text" name="direccion" id="direccion"
                                   value="<?php echo htmlspecialchars($formData['direccion'] ?? ''); ?>"
                                   placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.#°,\-]+" title="Caracteres permitidos">
                            <label for="direccion">Dirección:<span class="text-danger">*</span></label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="codigoGanadero" id="codigoGanadero"
                                   value="<?php echo htmlspecialchars($formData['codigoGanadero'] ?? ''); ?>"
                                   placeholder=" " required pattern="[A-Za-z0-9]+" title="Letras y números permitidos">
                            <label for="codigoGanadero">Código Ganadero:<span class="text-danger">*</span></label>
                        </div>
                        <div class="form-group">
                            <input type="date" name="fechaRegistro" id="fechaRegistro"
                                   value="<?php echo htmlspecialchars($formData['fechaRegistro'] ?? ''); ?>"
                                   placeholder=" " required max="<?php echo date('Y-m-d'); ?>">
                            <label for="fechaRegistro">Fecha de Registro (Socio):<span class="text-danger">*</span></label>
                        </div>
                    </div>
                    <div class="form-group-full">
                        <select name="estado" id="estado" required>
                            <option value="activo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (isset($formData['estado']) && $formData['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                        <label for="estado">Estado:<span class="text-danger">*</span></label>
                    </div>
                </fieldset>
            </div>

            <div class="form-main-col right-col">
                <fieldset>
                    <legend>Documentos del Socio</legend>
                    <small>Suba los documentos requeridos para el registro inicial.</small>

                    <h4>Documentos Actuales:</h4>
                    <?php if (!empty($documentosSocio)): ?>
                        <ul class="document-list">
                            <?php foreach ($documentosSocio as $doc): ?>
                                <li class="document-list-item">
                                    <div class="document-list-item-content">
                                        <strong><?php echo htmlspecialchars($doc['tipoDocumento']); ?>:</strong>
                                        <a href="index.php?route=documento_download&id=<?php echo htmlspecialchars($doc['id_documento']); ?>" target="_blank" title="Descargar <?php echo htmlspecialchars($doc['nombreArchivoOriginal']); ?>">
                                            <?php echo htmlspecialchars($doc['nombreArchivoOriginal']); ?>
                                        </a>
                                        <small>(Subido: <?php echo date('d/m/Y H:i', strtotime($doc['fechaSubida'])); ?> por <?php echo htmlspecialchars($doc['uploaded_by_username'] ?? 'N/A'); ?>)</small>
                                        <?php if(!empty($doc['comentarios'])): ?>
                                            <p class="document-comment"><i>Comentarios: <?php echo htmlspecialchars($doc['comentarios']); ?></i></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="document-list-item-actions">
                                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'superusuario'): ?>
                                            <a href="index.php?route=documento_delete&id=<?php echo htmlspecialchars($doc['id_documento']); ?>&socio_id=<?php echo htmlspecialchars($socio['id_socio']); ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('¿Seguro que quieres eliminar el documento \'<?php echo htmlspecialchars(addslashes($doc['nombreArchivoOriginal'])); ?>\'?\n¡Esta acción borrará el archivo permanentemente!')">Eliminar</a>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No hay documentos maestros registrados para este socio.</p>
                    <?php endif; ?>
                    <hr>

                    <h4>Subir/Actualizar Documentos:</h4>
                    <small>Suba un archivo para añadirlo o actualizar uno existente del mismo tipo.</small>
                    <div class="form-group-full">
                        <label for="id_oficial_file">Identificación Oficial Titular (INE/Pasaporte/Visa):</label>
                        <input type="file" name="id_oficial_file" id="id_oficial_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <label for="rfc_file">Constancia Fiscal (RFC):</label>
                        <input type="file" name="rfc_file" id="rfc_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <label for="domicilio_file">Comprobante Domicilio Ganadería:</label>
                        <input type="file" name="domicilio_file" id="domicilio_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    <div class="form-group-full">
                        <label for="titulo_propiedad_file">Título Propiedad Rancho:</label>
                        <input type="file" name="titulo_propiedad_file" id="titulo_propiedad_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                </fieldset>
                
                <p><small><span class="text-danger">*</span> Campos obligatorios</small></p>
                <div class="form-actions-bottom">
                    <button type="submit" class="btn btn-primary">Actualizar Socio y Subir Documentos</button>
                    <a href="index.php?route=socios_index" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
    <?php else: ?>
        <div class='alert alert-error'>Socio no encontrado.</div>
        <a href="index.php?route=socios_index" class="btn btn-secondary">Volver al listado</a>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    var fechaRegInput = document.getElementById('fechaRegistro');
    if (fechaRegInput) {
        fechaRegInput.setAttribute('max', today);
    }
});
</script>