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

<h2>Listado de Ejemplares</h2>

<div class="table-header-controls">
    <a href="index.php?route=ejemplares/create" class="btn btn-primary">Registrar Nuevo Ejemplar</a>
    <a href="index.php?route=ejemplares_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="ejemplares_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, código, cód. ganadero..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        <a href="index.php?route=ejemplares_index" class="btn btn-primary">Limpiar</a>
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
                        data-doc-pasaporte="<?php echo $ejemplar['document_status']['PASAPORTE_DIE'] ? '1' : '0'; ?>"
                        data-doc-adn="<?php echo $ejemplar['document_status']['RESULTADO_ADN'] ? '1' : '0'; ?>"
                        data-doc-lg="<?php echo $ejemplar['document_status']['CERTIFICADO_INSCRIPCION_LG'] ? '1' : '0'; ?>"
                        data-doc-foto="<?php echo $ejemplar['document_status']['FOTO_IDENTIFICACION'] ? '1' : '0'; ?>">
                        <td><?php echo $ejemplar['id_ejemplar']; ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['nombre'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['codigo_ejemplar'] ?? '-'); ?></td>
                        <td>
                            <?php echo htmlspecialchars($ejemplar['nombre_socio'] ?? 'Socio Desconocido'); ?>
                            (<?php echo htmlspecialchars($ejemplar['socio_codigo_ganadero'] ?? 'S/C'); ?>)
                        </td>
                        <td><?php echo htmlspecialchars($ejemplar['sexo'] ?? '-'); ?></td>
                        <td><?php echo !empty($ejemplar['fechaNacimiento']) ? date('d/m/Y', strtotime($ejemplar['fechaNacimiento'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($ejemplar['raza'] ?? '-'); ?></td>
                        <td><span style="color: <?php echo ($ejemplar['estado'] == 'activo') ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($ejemplar['estado'])); ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <a href="index.php?route=ejemplares/edit&id=<?php echo $ejemplar['id_ejemplar']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <button class="btn btn-danger btn-sm" onclick="confirmDeactivation(<?php echo $ejemplar['id_ejemplar']; ?>, '<?php echo htmlspecialchars(addslashes($ejemplar['nombre'])); ?>')">
                                    Desactivar
                                </button>
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
            <p><strong>Nombre:</strong> <span id="modalNombre"></span></p>
            <p><strong>Código de Ejemplar:</strong> <span id="modalCodigo"></span></p>
            <p><strong>Socio Propietario:</strong> <span id="modalSocio"></span></p>
            <hr>
            <p><strong>Sexo:</strong> <span id="modalSexo"></span></p>
            <p><strong>Fecha de Nacimiento:</strong> <span id="modalFechaNacimiento"></span></p>
            <p><strong>Raza:</strong> <span id="modalRaza"></span></p>
            <p><strong>Capa:</strong> <span id="modalCapa"></span></p>
            <hr>
            <p><strong>N° Microchip:</strong> <span id="modalMicrochip"></span></p>
            <p><strong>N° Certificado LG:</strong> <span id="modalCertificado"></span></p>
            <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
            <hr>
            <p><strong>Estado de Documentos:</strong></p>
            <label class="custom-checkbox-container">Pasaporte / DIE
                <input type="checkbox" id="modalDocPasaporte" disabled>
                <span class="checkmark"></span>
            </label>
            <label class="custom-checkbox-container">Resultado de ADN
                <input type="checkbox" id="modalDocAdn" disabled>
                <span class="checkmark"></span>
            </label>
            <label class="custom-checkbox-container">Certificado de Inscripción LG
                <input type="checkbox" id="modalDocLg" disabled>
                <span class="checkmark"></span>
            </label>
            <label class="custom-checkbox-container">Foto de Identificación
                <input type="checkbox" id="modalDocFoto" disabled>
                <span class="checkmark"></span>
            </label>
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

        // Referencias a los spans del modal
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
        // Checkboxes de documentos
        const modalDocPasaporte = document.getElementById('modalDocPasaporte');
        const modalDocAdn = document.getElementById('modalDocAdn');
        const modalDocLg = document.getElementById('modalDocLg');
        const modalDocFoto = document.getElementById('modalDocFoto');

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

                // Marcar/desmarcar checkboxes de documentos
                modalDocPasaporte.checked = this.dataset.docPasaporte === '1';
                modalDocAdn.checked = this.dataset.docAdn === '1';
                modalDocLg.checked = this.dataset.docLg === '1';
                modalDocFoto.checked = this.dataset.docFoto === '1';
                
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

// Lógica para la desactivación (ya existente)
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