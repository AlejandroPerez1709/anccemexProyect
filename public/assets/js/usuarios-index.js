/*
    public/assets/js/usuarios-index.js
    Lógica de JavaScript para la vista de listado de Usuarios.
    - Manejo del modal de vista rápida.
    - Confirmación de desactivación de usuario.
*/
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el modal de información
    const modal = document.getElementById('infoModal');
    if (modal) {
        const closeButton = modal.querySelector('.close-button');
        const rows = document.querySelectorAll('.clickable-row');

        // Referencias a los spans del modal
        const modalNombreCompleto = document.getElementById('modalNombreCompleto');
        const modalUsername = document.getElementById('modalUsername');
        const modalEmail = document.getElementById('modalEmail');
        const modalRol = document.getElementById('modalRol');
        const modalEstado = document.getElementById('modalEstado');
        const modalCreado = document.getElementById('modalCreado');
        const modalUltimoLogin = document.getElementById('modalUltimoLogin');

        rows.forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.action-buttons') || this.querySelector('span').textContent.includes('(Usuario actual)')) {
                    return;
                }

                // Llenar datos generales del modal
                modalNombreCompleto.textContent = this.dataset.nombreCompleto;
                modalUsername.textContent = this.dataset.username;
                modalEmail.textContent = this.dataset.email;
                modalRol.textContent = this.dataset.rol;
                modalEstado.textContent = this.dataset.estado;
                modalCreado.textContent = this.dataset.creado;
                modalUltimoLogin.textContent = this.dataset.ultimoLogin;
                
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

function confirmDeactivation(event, usuarioId, usuarioName) {
    // Detener la propagación para que no active el modal de la fila
    event.stopPropagation();
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al usuario: ${usuarioName}`,
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
            form.action = `index.php?route=usuarios_delete&id=${usuarioId}`;

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