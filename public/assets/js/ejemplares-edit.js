/*
    public/assets/js/ejemplares-edit.js
    Lógica de JavaScript para la vista de edición de Ejemplares.
    - Manejo del atributo 'max' para la fecha de nacimiento.
    - Confirmación y eliminación de documentos vía AJAX.
*/
document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    var fechaNacInput = document.getElementById('fechaNacimiento');
    if (fechaNacInput) { 
        fechaNacInput.setAttribute('max', today); 
    }
    
    const deleteButtons = document.querySelectorAll('.btn-delete-ajax');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            const docId = this.dataset.docId;
            const docName = this.dataset.docName;
            const url = this.href;
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Se eliminará el documento "${docName}" permanentemente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const docElement = document.getElementById('doc-item-' + docId);
                            if(docElement) {
                                docElement.style.transition = 'opacity 0.5s ease';
                                docElement.style.opacity = '0';
                                setTimeout(() => {
                                    docElement.remove();
                                }, 500);
                            }
                            
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Documento Eliminado',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Ocurrió un problema de comunicación con el servidor.', 'error');
                    });
                }
            });
        });
    });
});