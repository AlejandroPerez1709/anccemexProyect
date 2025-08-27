/*
    public/assets/js/empleados-index.js
    Lógica de JavaScript para la vista de listado de Empleados.
    - Manejo del modal de vista rápida.
    - Confirmación de desactivación de empleado.
*/
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el modal de información
    const modal = document.getElementById('infoModal');
    if (modal) {
        const closeButton = modal.querySelector('.close-button');
        const rows = document.querySelectorAll('.clickable-row');

        // Referencias a los spans del modal
        const modalNombreCompleto = document.getElementById('modalNombreCompleto');
        const modalPuesto = document.getElementById('modalPuesto');
        const modalEmail = document.getElementById('modalEmail');
        const modalTelefono = document.getElementById('modalTelefono');
        const modalDireccion = document.getElementById('modalDireccion');
        const modalFechaIngreso = document.getElementById('modalFechaIngreso');
        const modalEstado = document.getElementById('modalEstado');

        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.action-buttons')) {
                    return;
                }

                // Llenar datos generales del modal
                modalNombreCompleto.textContent = this.dataset.nombreCompleto;
                modalPuesto.textContent = this.dataset.puesto;
                modalEmail.textContent = this.dataset.email;
                modalTelefono.textContent = this.dataset.telefono;
                modalDireccion.textContent = this.dataset.direccion;
                modalFechaIngreso.textContent = this.dataset.fechaIngreso;
                modalEstado.textContent = this.dataset.estado;
                
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

function confirmDeactivation(event, empleadoId, empleadoName) {
    // Detener la propagación para que no active el modal de la fila
    event.stopPropagation();
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al empleado: ${empleadoName}`,
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
            form.action = `index.php?route=empleados_delete&id=${empleadoId}`;
            
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