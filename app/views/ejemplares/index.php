<?php
// app/views/ejemplares/index.php

// Helper para construir la URL con los parámetros de búsqueda y página
function build_pagination_url($page, $searchTerm) {
    $query_params = ['route' => 'ejemplares_index', 'page' => $page];
    if (!empty($searchTerm)) {
        $query_params['search'] = $searchTerm;
    }
    return 'index.php?' . http_build_query($query_params);
}
?>

<div class="page-title-container">
    <h2>Listado de Ejemplares</h2>
</div>

<div class="table-header-controls">
    <a href="index.php?route=ejemplares/create" class="btn btn-primary">Registrar Nuevo Ejemplar</a>
    <a href="index.php?route=ejemplares_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="ejemplares_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, código, cód. ganadero..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <div class="search-buttons">
            <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=ejemplares_index" class="btn btn-primary">Limpiar</a>
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
                <th>Nombre</th>
                <th>Código Ejemplar</th>
                <th>Socio Propietario (Cód. Gan.)</th>
                <th>Sexo</th>
                <th>Fecha Nac.</th>
                <th>Raza</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($ejemplares) && count($ejemplares) > 0): ?>
                <?php foreach($ejemplares as $ejemplar): ?>
                    <tr class="clickable-row"
                        data-nombre="<?php echo htmlspecialchars($ejemplar['nombre'] ?? '-'); ?>"
                        data-codigo="<?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] ?? '-'); ?>"
                        data-socio="<?php echo htmlspecialchars($ejemplar['nombre_socio'] . ' (' . ($ejemplar['socio_codigo_ganadero'] ?? 'S/C') . ')'); ?>"
                        data-sexo="<?php echo htmlspecialchars($ejemplar['sexo'] ?? '-'); ?>"
                        data-fecha-nacimiento="<?php echo !empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-'; ?>"
                        data-raza="<?php echo htmlspecialchars($ejemplar['raza'] ?? '-'); ?>"
                        data-capa="<?php echo htmlspecialchars($ejemplar['capa'] ?? '-'); ?>"
                        data-microchip="<?php echo htmlspecialchars($ejemplar['numero_microchip'] ?? '-'); ?>"
                        data-certificado="<?php echo htmlspecialchars($ejemplar['numero_certificado'] ?? '-'); ?>"
                        data-estado="<?php echo htmlspecialchars(ucfirst($ejemplar['estado'])); ?>"
                        
                        data-doc-pasaporte-id="<?php echo $ejemplar['document_status']['PASAPORTE_DIE'] ?: '0'; ?>"
                        data-doc-adn-id="<?php echo $ejemplar['document_status']['RESULTADO_ADN'] ?: '0'; ?>"
                        data-doc-lg-id="<?php echo $ejemplar['document_status']['CERTIFICADO_INSCRIPCION_LG'] ?: '0'; ?>"
                        data-doc-foto-id="<?php echo $ejemplar['document_status']['FOTO_IDENTIFICACION'] ?: '0'; ?>">
                        
                        <td><?php echo $ejemplar['id_ejemplar']; ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['nombre'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['nombre_socio'] ?? 'Socio Desconocido'); ?> (<?php echo htmlspecialchars($ejemplar['socio_codigo_ganadero'] ?? 'S/C'); ?>)</td>
                        <td><?php echo htmlspecialchars($ejemplar['sexo'] ?? '-'); ?></td>
                        <td><?php echo !empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['raza'] ?? '-'); ?></td>
                        <td><span style="color: <?php echo ($ejemplar['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($ejemplar['estado'])); ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=ejemplares/edit&id=<?php echo $ejemplar['id_ejemplar']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <button class="btn btn-danger btn-sm" onclick="confirmDeactivation(<?php echo $ejemplar['id_ejemplar']; ?>, '<?php echo htmlspecialchars(addslashes($ejemplar['nombre'])); ?>')">Desactivar</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                            No se encontraron ejemplares que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay ejemplares registrados.
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
            <h2 id="modalTitle">Detalles del Ejemplar</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                 <div class="modal-section-title">
                    <svg class="menu-icon" viewBox="-2.5 0 63 63" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>Horse-shoe</title> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Horse-shoe" transform="translate(1.000000, 1.000000)" stroke="#000000" stroke-width="2"> <path d="M52,54 L48.3,54 C53.1,45.3 56,35.5 56,28 C56,12.5 43.5,0 28,0 C12.5,0 0,12.5 0,28 C0,35.5 4,45.3 8.8,54 L5,54 L5,61 L17.9,61 C23.9,61 21.5,56.8 21.5,56.8 C21.5,56.8 9.8,38.5 9.8,27.8 C9.8,17.9 18,9.9 28.1,9.9 C38.2,9.9 46.4,17.9 46.4,27.8 C46.4,38.3 39.5,48.3 36.3,56.5 C35.2,59.2 36.4,61 40.1,61 L52,61 L52,54 L52,54 Z"></path> <path d="M27,6 L29,6"></path> <path d="M12,10 L14,10"></path> <path d="M41,10 L43,10"></path> <path d="M48,18 L50,18"></path> <path d="M6,17.9 L8,17.9"></path> <path d="M50,26 L52,26"></path> <path d="M50,35 L52,35"></path> <path d="M5,35 L7,35"></path> <path d="M8,44 L10,44"></path> <path d="M47,44 L49,44"></path> <path d="M43,54 L44.9,54"></path> <path d="M12,54 L14,54"></path> <path d="M4,26 L6,26"></path> </g> </g> </g></svg>
                    <h4>Información General</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Nombre:</span><span class="modal-value" id="modalNombre"></span></div>
                    <div class="modal-field"><span class="modal-label">Código:</span><span class="modal-value" id="modalCodigo"></span></div>
                    <div class="modal-field full-width"><span class="modal-label">Socio Propietario:</span><span class="modal-value" id="modalSocio"></span></div>
                    <div class="modal-field"><span class="modal-label">Sexo:</span><span class="modal-value" id="modalSexo"></span></div>
                    <div class="modal-field"><span class="modal-label">Fecha de Nacimiento:</span><span class="modal-value" id="modalFechaNacimiento"></span></div>
                    <div class="modal-field"><span class="modal-label">Raza:</span><span class="modal-value" id="modalRaza"></span></div>
                    <div class="modal-field"><span class="modal-label">Capa:</span><span class="modal-value" id="modalCapa"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7C4.79086 3 3 4.79086 3 7V17C3 19.2091 4.79086 21 7 21H17C19.2091 21 21 19.2091 21 17V7C21 4.79086 19.2091 3 17 3ZM19 17C19 18.1046 18.1046 19 17 19H7C5.89543 19 5 18.1046 5 17V7C5 5.89543 5.89543 5 7 5H17C18.1046 5 19 5.89543 19 7V17ZM15.2929 9.29289L11 13.5858L8.70711 11.2929L7.29289 12.7071L11 16.4142L16.7071 10.7071L15.2929 9.29289Z"></path></svg>
                    <h4>Registros y Documentos</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">N° Microchip:</span><span class="modal-value" id="modalMicrochip"></span></div>
                    <div class="modal-field"><span class="modal-label">N° Certificado LG:</span><span class="modal-value" id="modalCertificado"></span></div>
                    <div class="modal-field"><span class="modal-label">Estado:</span><span class="modal-value" id="modalEstado"></span></div>
                </div>
                <div class="modal-docs">
                    <label class="custom-checkbox-container">Pasaporte / DIE
                        <input type="checkbox" id="modalDocPasaporte" disabled><span class="checkmark"></span>
                        <a href="#" target="_blank" class="view-doc-icon" id="modalDocPasaporteView" title="Ver Documento">👁️</a>
                    </label>
                    <label class="custom-checkbox-container">Resultado de ADN
                        <input type="checkbox" id="modalDocAdn" disabled><span class="checkmark"></span>
                        <a href="#" target="_blank" class="view-doc-icon" id="modalDocAdnView" title="Ver Documento">👁️</a>
                    </label>
                    <label class="custom-checkbox-container">Certificado de Inscripción LG
                        <input type="checkbox" id="modalDocLg" disabled><span class="checkmark"></span>
                        <a href="#" target="_blank" class="view-doc-icon" id="modalDocLgView" title="Ver Documento">👁️</a>
                    </label>
                    <label class="custom-checkbox-container">Foto de Identificación
                        <input type="checkbox" id="modalDocFoto" disabled><span class="checkmark"></span>
                        <a href="#" target="_blank" class="view-doc-icon" id="modalDocFotoView" title="Ver Documento">👁️</a>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el modal de información
    const modal = document.getElementById('infoModal');
    if (modal) {
        const closeButton = modal.querySelector('.close-button');
        const rows = document.querySelectorAll('.clickable-row');

        const modalNombre = document.getElementById('modalNombre');
        const modalCodigo = document.getElementById('modalCodigo');
        const modalSocio = document.getElementById('modalSocio');
        const modalSexo = document.getElementById('modalSexo');
        const modalFechaNacimiento = document.getElementById('modalFechaNacimiento');
        const modalRaza = document.getElementById('modalRaza');
        const modalCapa = document.getElementById('modalCapa');
        const modalMicrochip = document.getElementById('modalMicrochip');
        const modalCertificado = document.getElementById('modalCertificado');
        const modalEstado = document.getElementById('modalEstado');

        const modalDocPasaporte = document.getElementById('modalDocPasaporte');
        const modalDocAdn = document.getElementById('modalDocAdn');
        const modalDocLg = document.getElementById('modalDocLg');
        const modalDocFoto = document.getElementById('modalDocFoto');
        const modalDocPasaporteView = document.getElementById('modalDocPasaporteView');
        const modalDocAdnView = document.getElementById('modalDocAdnView');
        const modalDocLgView = document.getElementById('modalDocLgView');
        const modalDocFotoView = document.getElementById('modalDocFotoView');

        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.action-buttons')) {
                    return;
                }

                // Llenar datos generales
                modalNombre.textContent = this.dataset.nombre;
                modalCodigo.textContent = this.dataset.codigo;
                modalSocio.textContent = this.dataset.socio;
                modalSexo.textContent = this.dataset.sexo;
                modalFechaNacimiento.textContent = this.dataset.fechaNacimiento;
                modalRaza.textContent = this.dataset.raza;
                modalCapa.textContent = this.dataset.capa;
                modalMicrochip.textContent = this.dataset.microchip;
                modalCertificado.textContent = this.dataset.certificado;
                modalEstado.textContent = this.dataset.estado;

                // Checkboxes
                modalDocPasaporte.checked = this.dataset.docPasaporteId !== '0';
                modalDocAdn.checked = this.dataset.docAdnId !== '0';
                modalDocLg.checked = this.dataset.docLgId !== '0';
                modalDocFoto.checked = this.dataset.docFotoId !== '0';

                // Lógica para los íconos
                let docPasaporteId = this.dataset.docPasaporteId;
                if (docPasaporteId && docPasaporteId !== '0') {
                    modalDocPasaporteView.href = `index.php?route=documento_download&id=${docPasaporteId}`;
                    modalDocPasaporteView.style.display = 'inline-block';
                } else {
                    modalDocPasaporteView.style.display = 'none';
                }

                let docAdnId = this.dataset.docAdnId;
                if (docAdnId && docAdnId !== '0') {
                    modalDocAdnView.href = `index.php?route=documento_download&id=${docAdnId}`;
                    modalDocAdnView.style.display = 'inline-block';
                } else {
                    modalDocAdnView.style.display = 'none';
                }
                
                let docLgId = this.dataset.docLgId;
                if (docLgId && docLgId !== '0') {
                    modalDocLgView.href = `index.php?route=documento_download&id=${docLgId}`;
                    modalDocLgView.style.display = 'inline-block';
                } else {
                    modalDocLgView.style.display = 'none';
                }
                
                let docFotoId = this.dataset.docFotoId;
                if (docFotoId && docFotoId !== '0') {
                    modalDocFotoView.href = `index.php?route=documento_download&id=${docFotoId}`;
                    modalDocFotoView.style.display = 'inline-block';
                } else {
                    modalDocFotoView.style.display = 'none';
                }
                
                modal.style.display = 'block';
            });
        });

        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }
});

function confirmDeactivation(ejemplarId, ejemplarName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al ejemplar: ${ejemplarName}`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Razón de la desactivación',
        inputPlaceholder: 'Escribe el motivo aquí...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return '¡Necesitas escribir una razón para la desactivación!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `index.php?route=ejemplares_delete&id=${ejemplarId}`;

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'razon';
            reasonInput.value = result.value;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>