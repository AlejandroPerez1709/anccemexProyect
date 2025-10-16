/*
    Lógica de JavaScript para la vista de listado de Tipos de Servicio.
    - Manejo del modal de vista rápida.
    - Confirmación de desactivación de tipo de servicio.
*/
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los elementos del DOM
    const modal = document.getElementById('infoModal');
    if (modal) {
        const closeButton = modal.querySelector('.close-button');
        const rows = document.querySelectorAll('.clickable-row');

        // Contenido del modal
        const modalNombre = document.getElementById('modalNombre');
        const modalCodigo = document.getElementById('modalCodigo');
        const modalEstado = document.getElementById('modalEstado');
        const modalReqMedico = document.getElementById('modalReqMedico');
        const modalDescripcion = document.getElementById('modalDescripcion');

        // Abrir el modal al hacer clic en una fila
        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                // Evita abrir el modal si se hizo clic en un botón dentro de la fila
                if (event.target.closest('.action-buttons')) {
                    return;
                }

                // Llenar el modal con los datos de la fila
                modalNombre.textContent = this.dataset.nombre;
                modalCodigo.textContent = this.dataset.codigo;
                modalEstado.textContent = this.dataset.estado;
                modalReqMedico.textContent = this.dataset.reqMedico;
                modalDescripcion.textContent = this.dataset.descripcion;

                // Mostrar el modal
                modal.style.display = 'block';
            });
        });

        // Cerrar el modal con el botón 'X'
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Cerrar el modal si se hace clic fuera de la ventana
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }
});

// --- NUEVA FUNCIÓN AÑADIDA ---
function confirmDeactivation(event, tipoId, tipoName) {
    event.preventDefault(); // Prevenir la navegación del enlace
    event.stopPropagation(); // Prevenir que se abra el modal
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará el tipo de servicio: "${tipoName}". No podrás usarlo para nuevos servicios.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, redirigir a la URL de desactivación
            window.location.href = event.currentTarget.href;
        }
    });
}