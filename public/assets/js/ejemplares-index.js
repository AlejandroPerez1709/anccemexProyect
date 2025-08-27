/*
    public/assets/js/ejemplares-index.js
    Lógica de JavaScript para la vista de listado de Ejemplares.
    - Manejo del modal de vista rápida.
    - Confirmación de desactivación de ejemplar.
*/
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

                // Lógica para los íconos de visualización
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