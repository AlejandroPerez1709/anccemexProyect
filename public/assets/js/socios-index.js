/*
    public/assets/js/socios-index.js
    Lógica de JavaScript para la vista de listado de Socios.
    - Manejo del modal de vista rápida.
    - Confirmación de desactivación de socio.
*/
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('infoModal');
    if (modal) {
        const closeButton = modal.querySelector('.close-button');
        const rows = document.querySelectorAll('.clickable-row');

        const modalNombreCompleto = document.getElementById('modalNombreCompleto');
        const modalRfc = document.getElementById('modalRfc');
        const modalEmail = document.getElementById('modalEmail');
        const modalTelefono = document.getElementById('modalTelefono');
        const modalGanaderia = document.getElementById('modalGanaderia');
        const modalCodigo = document.getElementById('modalCodigo');
        const modalDireccion = document.getElementById('modalDireccion');
        const modalFechaRegistro = document.getElementById('modalFechaRegistro');
        const modalEstado = document.getElementById('modalEstado');
        
        const modalDocId = document.getElementById('modalDocId');
        const modalDocRfc = document.getElementById('modalDocRfc');
        const modalDocDomicilio = document.getElementById('modalDocDomicilio');
        const modalDocPropiedad = document.getElementById('modalDocPropiedad');
        
        const modalDocIdView = document.getElementById('modalDocIdView');
        const modalDocRfcView = document.getElementById('modalDocRfcView');
        const modalDocDomicilioView = document.getElementById('modalDocDomicilioView');
        const modalDocPropiedadView = document.getElementById('modalDocPropiedadView');

        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.action-buttons')) {
                    return;
                }

                modalNombreCompleto.textContent = this.dataset.nombreCompleto;
                modalRfc.textContent = this.dataset.rfc;
                modalEmail.textContent = this.dataset.email;
                modalTelefono.textContent = this.dataset.telefono;
                modalGanaderia.textContent = this.dataset.ganaderia;
                modalCodigo.textContent = this.dataset.codigo;
                modalDireccion.textContent = this.dataset.direccion;
                modalFechaRegistro.textContent = this.dataset.fechaRegistro;
                modalEstado.textContent = this.dataset.estado;

                modalDocId.checked = this.dataset.docIdId !== '0';
                modalDocRfc.checked = this.dataset.docRfcId !== '0';
                modalDocDomicilio.checked = this.dataset.docDomicilioId !== '0';
                modalDocPropiedad.checked = this.dataset.docPropiedadId !== '0';

                let docId = this.dataset.docIdId;
                if (docId && docId !== '0') {
                    modalDocIdView.href = `index.php?route=documento_download&id=${docId}`;
                    modalDocIdView.style.display = 'inline-block';
                } else {
                    modalDocIdView.style.display = 'none';
                }

                let docRfcId = this.dataset.docRfcId;
                if (docRfcId && docRfcId !== '0') {
                    modalDocRfcView.href = `index.php?route=documento_download&id=${docRfcId}`;
                    modalDocRfcView.style.display = 'inline-block';
                } else {
                    modalDocRfcView.style.display = 'none';
                }

                let docDomicilioId = this.dataset.docDomicilioId;
                if (docDomicilioId && docDomicilioId !== '0') {
                    modalDocDomicilioView.href = `index.php?route=documento_download&id=${docDomicilioId}`;
                    modalDocDomicilioView.style.display = 'inline-block';
                } else {
                    modalDocDomicilioView.style.display = 'none';
                }

                let docPropiedadId = this.dataset.docPropiedadId;
                if (docPropiedadId && docPropiedadId !== '0') {
                    modalDocPropiedadView.href = `index.php?route=documento_download&id=${docPropiedadId}`;
                    modalDocPropiedadView.style.display = 'inline-block';
                } else {
                    modalDocPropiedadView.style.display = 'none';
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

function confirmDeactivation(event, socioId, socioName) {
    event.stopPropagation();
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al socio: ${socioName}`,
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
            form.action = `index.php?route=socios_delete&id=${socioId}`;
            
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