/*
    Lógica de JavaScript para la vista de listado de Servicios.
    - Manejo del modal de vista rápida y actualización de estado.
    - Llamadas AJAX para obtener estados válidos y guardar el nuevo estado.
    - Confirmación de cancelación de servicio.
*/
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('infoModal');
    if(modal) {
        const closeButtons = modal.querySelectorAll('.close-button');
        const rows = document.querySelectorAll('.clickable-row');
        const modalIdServicio = document.getElementById('modalIdServicio');
        const modalTipoServicio = document.getElementById('modalTipoServicio');
        const modalSocio = document.getElementById('modalSocio');
        const modalEjemplar = document.getElementById('modalEjemplar');
        const modalObservaciones = document.getElementById('modalObservaciones');
        const modalMedicoAsignado = document.getElementById('modalMedicoAsignado');
        const modalUltimaModif = document.getElementById('modalUltimaModif');
        const modalDocSolicitud = document.getElementById('modalDocSolicitud');
        const modalDocPago = document.getElementById('modalDocPago');
        const modalDocSolicitudView = document.getElementById('modalDocSolicitudView');
        const modalDocPagoView = document.getElementById('modalDocPagoView');
        const modalEstadoSelect = document.getElementById('modalEstadoSelect');
        const btnGuardarEstado = document.getElementById('btnGuardarEstado');
        const modalMotivoRechazoContainer = document.getElementById('modalMotivoRechazoContainer');
        const modalMotivoRechazo = document.getElementById('modalMotivoRechazo');
        const modalFechaSolicitud = document.getElementById('modalFechaSolicitud');
        const modalFechaAsignacionMedico = document.getElementById('modalFechaAsignacionMedico');
        const modalFechaVisitaMedico = document.getElementById('modalFechaVisitaMedico');
        const modalFechaEnvioLg = document.getElementById('modalFechaEnvioLg');
        const modalFechaRecepcionLg = document.getElementById('modalFechaRecepcionLg');
        const modalFechaFinalizacion = document.getElementById('modalFechaFinalizacion');
        const modalMedicoContainer = document.getElementById('modalMedicoContainer');
        const modalFechaAsignacionMedicoContainer = document.getElementById('modalFechaAsignacionMedicoContainer');
        const modalFechaVisitaMedicoContainer = document.getElementById('modalFechaVisitaMedicoContainer');
        const modalFechaEnvioLgContainer = document.getElementById('modalFechaEnvioLgContainer');
        const modalFechaRecepcionLgContainer = document.getElementById('modalFechaRecepcionLgContainer');
        const modalFechaFinalizacionContainer = document.getElementById('modalFechaFinalizacionContainer');
        
        let currentServiceId = null;
        let currentRowElement = null;

        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.action-buttons')) { return; }
                
                currentRowElement = this; 
                currentServiceId = this.dataset.idServicio;
                
                modalIdServicio.textContent = this.dataset.idServicio;
                modalTipoServicio.textContent = this.dataset.tipoServicio;
                modalSocio.textContent = this.dataset.socio;
                modalEjemplar.textContent = this.dataset.ejemplar;
                modalObservaciones.textContent = this.dataset.observaciones || 'Sin observaciones.';
                modalUltimaModif.textContent = this.dataset.ultimaModif;

                function fillAndToggle(element, container, dataAttribute) {
                     if (dataAttribute && dataAttribute.trim() !== '') {
                        element.textContent = dataAttribute;
                        container.style.display = 'block';
                     } else {
                        container.style.display = 'none';
                     }
                }

                fillAndToggle(modalFechaSolicitud, modalFechaSolicitud.parentElement, this.dataset.fechaSolicitud);
                fillAndToggle(modalMedicoAsignado, modalMedicoContainer, this.dataset.medicoAsignado);
                fillAndToggle(modalFechaAsignacionMedico, modalFechaAsignacionMedicoContainer, this.dataset.fechaAsignacionMedico);
                fillAndToggle(modalFechaVisitaMedico, modalFechaVisitaMedicoContainer, this.dataset.fechaVisitaMedico);
                fillAndToggle(modalFechaEnvioLg, modalFechaEnvioLgContainer, this.dataset.fechaEnvioLg);
                fillAndToggle(modalFechaRecepcionLg, modalFechaRecepcionLgContainer, this.dataset.fechaRecepcionLg);
                fillAndToggle(modalFechaFinalizacion, modalFechaFinalizacionContainer, this.dataset.fechaFinalizacion);

                modalMotivoRechazo.value = '';
                
                let docSolicitudId = this.dataset.docSolicitudId;
                modalDocSolicitud.checked = docSolicitudId !== '0';
                if (docSolicitudId && docSolicitudId !== '0') {
                    modalDocSolicitudView.href = `index.php?route=documento_download&id=${docSolicitudId}`;
                    modalDocSolicitudView.style.display = 'inline-block';
                } else {
                    modalDocSolicitudView.style.display = 'none';
                }

                let docPagoId = this.dataset.docPagoId;
                modalDocPago.checked = docPagoId !== '0';
                if (docPagoId && docPagoId !== '0') {
                    modalDocPagoView.href = `index.php?route=documento_download&id=${docPagoId}`;
                    modalDocPagoView.style.display = 'inline-block';
                } else {
                    modalDocPagoView.style.display = 'none';
                }

                modalEstadoSelect.innerHTML = '<option>Cargando...</option>';
                modalEstadoSelect.disabled = true;
                
                fetch(`index.php?route=servicios_get_valid_states&id=${currentServiceId}`)
                    .then(response => response.json())
                    .then(estados => {
                        modalEstadoSelect.innerHTML = '';
                        estados.forEach(estado => {
                            const option = document.createElement('option');
                            option.value = estado;
                            option.textContent = estado;
                            modalEstadoSelect.appendChild(option);
                        });
                        
                        modalEstadoSelect.value = currentRowElement.dataset.estado;
                        modalEstadoSelect.disabled = false;

                        // --- INICIO DE LA NUEVA LÓGICA ---
                        const finalStates = ['Completado', 'Rechazado', 'Cancelado'];
                        const currentState = currentRowElement.dataset.estado;

                        if (finalStates.includes(currentState)) {
                            modalEstadoSelect.disabled = true; // Deshabilitar el <select>
                            btnGuardarEstado.disabled = true;  // Deshabilitar el botón
                        } else {
                            modalEstadoSelect.disabled = false; // Habilitar el <select>
                            btnGuardarEstado.disabled = false; // Habilitar el botón
                        }
                        // --- FIN DE LA NUEVA LÓGICA ---

                        toggleMotivoRechazo();
                    })
                    .catch(error => {
                        console.error('Error al cargar los estados:', error);
                        modalEstadoSelect.innerHTML = '<option>Error al cargar</option>';
                    });
                
                modal.style.display = 'block';
            });
        });

        function closeModal() {
            modal.style.display = 'none';
        }

        closeButtons.forEach(btn => btn.addEventListener('click', closeModal));

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                closeModal();
            }
        });

        function toggleMotivoRechazo() {
            if (modalEstadoSelect.value === 'Rechazado') {
                modalMotivoRechazoContainer.style.display = 'block';
            } else {
                modalMotivoRechazoContainer.style.display = 'none';
            }
        }
        modalEstadoSelect.addEventListener('change', toggleMotivoRechazo);

        btnGuardarEstado.addEventListener('click', function() {
            const nuevoEstado = modalEstadoSelect.value;
            const motivo = modalMotivoRechazo.value;

            if (nuevoEstado === 'Rechazado' && motivo.trim() === '') {
                Swal.fire('Error', 'Debe proporcionar un motivo para el rechazo.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('id', currentServiceId);
            formData.append('estado', nuevoEstado);
            formData.append('motivo', motivo);

            fetch('index.php?route=servicios_update_status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal();
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Estado actualizado correctamente',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Ocurrió un problema de comunicación con el servidor.', 'error');
            });
        });
    }
});

function confirmCancel(event, url, servicioId) {
    event.preventDefault(); 
    event.stopPropagation();
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se cancelará el servicio #${servicioId}. ¡Esta acción no se puede deshacer!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar servicio',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}