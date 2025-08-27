<?php
//app/views/socios/index.php

function build_pagination_url($page, $searchTerm) {
    $query_params = ['route' => 'socios_index', 'page' => $page];
    if (!empty($searchTerm)) {
        $query_params['search'] = $searchTerm;
    }
    return 'index.php?' . http_build_query($query_params);
}
?>

<div class="page-title-container">
    <h2>Listado de Socios</h2>
</div>

<div class="table-header-controls">
    <a href="index.php?route=socios/create" class="btn btn-primary">Registrar Nuevo Socio</a>
    <a href="index.php?route=socios_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="socios_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, ganadería, código..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <div class="search-buttons">
            <button type="submit" class="btn btn-secondary">Buscar</button>
            <a href="index.php?route=socios_index" class="btn btn-primary">Limpiar</a>
        </div>
    </form>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $searchTerm); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $searchTerm); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $searchTerm); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); } ?>
<?php if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nombre Titular</th>
                <th>Apellidos</th>
                <th>Nombre Ganadería</th>
                <th>Email Contacto</th>
                <th>Teléfono Contacto</th>
                <th>Cód. Ganadero</th>
                <th>Estado</th>
                <th>Fecha Reg.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($socios) && count($socios) > 0): ?>
                <?php foreach($socios as $socio): ?>
                     <tr class="clickable-row"
                         data-nombre-completo="<?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido_paterno'] . ' ' . $socio['apellido_materno']); ?>"
                         data-rfc="<?php echo htmlspecialchars($socio['identificacion_fiscal_titular'] ?? '-'); ?>"
                         data-email="<?php echo htmlspecialchars($socio['email'] ?? '-'); ?>"
                         data-telefono="<?php echo htmlspecialchars($socio['telefono'] ?? '-'); ?>"
                         data-ganaderia="<?php echo htmlspecialchars($socio['nombre_ganaderia'] ?? '-'); ?>"
                         data-codigo="<?php echo htmlspecialchars($socio['codigoGanadero'] ?? '-'); ?>"
                         data-direccion="<?php echo htmlspecialchars($socio['direccion'] ?? '-'); ?>"
                         data-fecha-registro="<?php echo !empty($socio['fechaRegistro']) ? date('d/m/Y', strtotime($socio['fechaRegistro'])) : '-'; ?>"
                         data-estado="<?php echo htmlspecialchars(ucfirst($socio['estado'])); ?>"
                         data-doc-id-id="<?php echo $socio['document_status']['ID_OFICIAL_TITULAR'] ?: '0'; ?>"
                         data-doc-rfc-id="<?php echo $socio['document_status']['CONSTANCIA_FISCAL'] ?: '0'; ?>"
                         data-doc-domicilio-id="<?php echo $socio['document_status']['COMPROBANTE_DOM_GANADERIA'] ?: '0'; ?>"
                         data-doc-propiedad-id="<?php echo $socio['document_status']['TITULO_PROPIEDAD_RANCHO'] ?: '0'; ?>">

                        <td><?php echo $socio['id_socio']; ?></td>
                        <td><?php echo htmlspecialchars($socio['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($socio['apellido_paterno'] . ' ' . $socio['apellido_materno']); ?></td>
                        <td><?php echo !empty($socio['nombre_ganaderia']) ? htmlspecialchars($socio['nombre_ganaderia']) : '-'; ?></td>
                        <td><?php echo !empty($socio['email']) ? htmlspecialchars($socio['email']) : '-'; ?></td>
                        <td><?php echo !empty($socio['telefono']) ? htmlspecialchars($socio['telefono']) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($socio['codigoGanadero']); ?></td>
                        <td><span style="color: <?php echo ($socio['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($socio['estado'])); ?></span></td>
                        <td><?php echo isset($socio['fechaRegistro']) ? date('d/m/Y', strtotime($socio['fechaRegistro'])) : '-'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=socios/edit&id=<?php echo $socio['id_socio']; ?>" class="btn btn-warning">Editar</a>
                                <button class="btn btn-danger" onclick="confirmDeactivation(event, <?php echo $socio['id_socio']; ?>, '<?php echo htmlspecialchars(addslashes($socio['nombre'] . ' ' . $socio['apellido_paterno'])); ?>')">
                                    Desactivar
                                 </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                            No se encontraron socios que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay socios registrados.
                        <?php endif; ?>
                     </td> 
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $searchTerm); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $searchTerm); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $searchTerm); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Socio</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                 <div class="modal-section-title">
                    <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.2914 5.99994H20.0002C20.5525 5.99994 21.0002 6.44766 21.0002 6.99994V13.9999C21.0002 14.5522 20.5525 14.9999 20.0002 14.9999H18.0002L13.8319 9.16427C13.3345 8.46797 12.4493 8.16522 11.6297 8.41109L9.14444 9.15668C8.43971 9.3681 7.6758 9.17551 7.15553 8.65524L6.86277 8.36247C6.41655 7.91626 6.49011 7.17336 7.01517 6.82332L12.4162 3.22262C13.0752 2.78333 13.9312 2.77422 14.5994 3.1994L18.7546 5.8436C18.915 5.94571 19.1013 5.99994 19.2914 5.99994ZM5.02708 14.2947L3.41132 15.7085C2.93991 16.1209 2.95945 16.8603 3.45201 17.2474L8.59277 21.2865C9.07284 21.6637 9.77592 21.5264 10.0788 20.9963L10.7827 19.7645C11.2127 19.012 11.1091 18.0682 10.5261 17.4269L7.82397 14.4545C7.09091 13.6481 5.84722 13.5771 5.02708 14.2947ZM7.04557 5H3C2.44772 5 2 5.44772 2 6V13.5158C2 13.9242 2.12475 14.3173 2.35019 14.6464C2.3741 14.6238 2.39856 14.6015 2.42357 14.5796L4.03933 13.1658C5.47457 11.91 7.65103 12.0343 8.93388 13.4455L11.6361 16.4179C12.6563 17.5401 12.8376 19.1918 12.0851 20.5087L11.4308 21.6538C11.9937 21.8671 12.635 21.819 13.169 21.4986L17.5782 18.8531C18.0786 18.5528 18.2166 17.8896 17.8776 17.4146L12.6109 10.0361C12.4865 9.86205 12.2652 9.78636 12.0603 9.84783L9.57505 10.5934C8.34176 10.9634 7.00492 10.6264 6.09446 9.7159L5.80169 9.42313C4.68615 8.30759 4.87005 6.45035 6.18271 5.57524L7.04557 5Z"></path></svg>
                    <h4>Información del Titular</h4>
                </div>
                 <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Nombre Completo:</span><span class="modal-value" id="modalNombreCompleto"></span></div>
                    <div class="modal-field"><span class="modal-label">RFC:</span><span class="modal-value" id="modalRfc"></span></div>
                    <div class="modal-field"><span class="modal-label">Email:</span><span class="modal-value" id="modalEmail"></span></div>
                    <div class="modal-field"><span class="modal-label">Teléfono:</span><span class="modal-value" id="modalTelefono"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21 21H3V11L12 3L21 11V21ZM19 19V12L12 5.69L5 12V19H19Z"></path></svg>
                    <h4>Datos de la Ganadería</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Nombre Ganadería:</span><span class="modal-value" id="modalGanaderia"></span></div>
                    <div class="modal-field"><span class="modal-label">Código Ganadero:</span><span class="modal-value" id="modalCodigo"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Dirección:</span><span class="modal-value" id="modalDireccion"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7C4.79086 3 3 4.79086 3 7V17C3 19.2091 4.79086 21 7 21H17C19.2091 21 21 19.2091 21 17V7C21 4.79086 19.2091 3 17 3ZM19 17C19 18.1046 18.1046 19 17 19H7C5.89543 19 5 18.1046 5 17V7C5 5.89543 5.89543 5 7 5H17C18.1046 5 19 5.89543 19 7V17ZM15.2929 9.29289L11 13.5858L8.70711 11.2929L7.29289 12.7071L11 16.4142L16.7071 10.7071L15.2929 9.29289Z"></path></svg>
                    <h4>Estado y Documentos</h4>
                 </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Fecha de Registro:</span><span class="modal-value" id="modalFechaRegistro"></span></div>
                    <div class="modal-field"><span class="modal-label">Estado:</span><span class="modal-value" id="modalEstado"></span></div>
                </div>
                 <div class="modal-docs">
                    <label class="custom-checkbox-container">
                        Identificación Oficial
                        <input type="checkbox" id="modalDocId" disabled>
                         <span class="checkmark"></span>
                        <a href="#" target="_blank" class="view-doc-icon" id="modalDocIdView" title="Ver Documento">👁️</a>
                    </label>
                    <label class="custom-checkbox-container">
                        Constancia Fiscal (RFC)
                        <input type="checkbox" id="modalDocRfc" disabled>
                        <span class="checkmark"></span>
                         <a href="#" target="_blank" class="view-doc-icon" id="modalDocRfcView" title="Ver Documento">👁️</a>
                    </label>
                    <label class="custom-checkbox-container">
                        Comprobante de Domicilio
                        <input type="checkbox" id="modalDocDomicilio" disabled>
                         <span class="checkmark"></span>
                         <a href="#" target="_blank" class="view-doc-icon" id="modalDocDomicilioView" title="Ver Documento">👁️</a>
                    </label>
                    <label class="custom-checkbox-container">
                        Título de Propiedad
                        <input type="checkbox" id="modalDocPropiedad" disabled>
                        <span class="checkmark"></span>
                         <a href="#" target="_blank" class="view-doc-icon" id="modalDocPropiedadView" title="Ver Documento">👁️</a>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/socios-index.js"></script>