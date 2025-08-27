<?php
// app/views/socios/create.php

// Recuperar errores y datos del formulario si los hay
$errors = $_SESSION['errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);
?>

<div class="form-container form-wide"> <h2>Registrar Nuevo Socio</h2>
    <form action="index.php?route=socios_store" method="POST" id="socioForm" enctype="multipart/form-data">
        <div class="form-main-columns"> <div class="form-main-col left-col">
                <fieldset>
                    <legend>Datos del Titular</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="nombre" id="nombre"
                                   value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                                   placeholder=" " required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios permitidos">
                            <label for="nombre">Nombre(s) Titular:<span class="text-danger">*</span></label>
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
                            <input type="tel" name="telefono" id="telefono"
                                   value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>"
                                   placeholder=" " required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
                            <label for="telefono">Teléfono:<span class="text-danger">*</span></label>
                            <?php if (isset($errors['telefono'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['telefono'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="email" name="email" id="email"
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                                   placeholder=" " required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Formato de email no válido">
                            <label for="email">Email:<span class="text-danger">*</span></label>
                            <?php if (isset($errors['email'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['email'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <input type="text" name="identificacion_fiscal_titular" id="identificacion_fiscal_titular"
                                   value="<?php echo htmlspecialchars($formData['identificacion_fiscal_titular'] ?? ''); ?>"
                                   placeholder=" " required pattern="[A-Za-z0-9\-]+" title="Letras, números y guiones permitidos">
                            <label for="identificacion_fiscal_titular">RFC:<span class="text-danger">*</span></label>
                            <?php if (isset($errors['identificacion_fiscal_titular'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['identificacion_fiscal_titular'][0]); ?></div>
                            <?php endif; ?>
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
                            <?php if (isset($errors['codigoGanadero'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['codigoGanadero'][0]); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <input type="date" name="fechaRegistro" id="fechaRegistro"
                                   value="<?php echo htmlspecialchars($formData['fechaRegistro'] ?? date('Y-m-d')); ?>"
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
                    <button type="submit" class="btn btn-primary">Registrar Socio</button>
                    <a href="index.php?route=socios_index" class="btn btn-secondary">Cancelar</a>
                 </div>
            </div>
        </div>
    </form>
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