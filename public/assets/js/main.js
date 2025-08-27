/*
    public/assets/js/main.js
    Lógica principal de JavaScript para el Layout
    - Manejo del menú lateral desplegable.
    - Manejo del modal de previsualización de imágenes.
*/
document.addEventListener('DOMContentLoaded', function() {
    
    // Lógica para el menú lateral (sidebar)
    const menuItems = document.querySelectorAll('.sidebar-menu .has-submenu > a');
    menuItems.forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const parentLi = this.parentElement;
            parentLi.classList.toggle('open');
            const submenu = parentLi.querySelector('.sidebar-submenu');
            submenu.classList.toggle('visible');
        });
    });

    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('is-open');
            this.classList.toggle('is-active');
        });
    }

    // Lógica para el modal de previsualización de imágenes
    const imageModal = document.getElementById("imagePreviewModal");
    if (imageModal) {
        const modalImg = document.getElementById("modalImage");
        const captionText = document.getElementById("imageCaption");
        const closeImageModal = document.querySelector(".image-modal-close");

        // Usamos delegación de eventos para escuchar clics en todo el documento
        document.addEventListener('click', function(event) {
            // Buscamos si el clic fue en un enlace de documento que sea una imagen
            const link = event.target.closest('a.document-link');
            
            if (link && link.dataset.isImage === 'true') {
                event.preventDefault(); // Prevenimos la acción por defecto del enlace
                
                imageModal.style.display = "block";
                modalImg.src = link.href;
                captionText.innerHTML = link.title;
            }
        });

        // Función para cerrar el modal
        function closeModal() {
            if (imageModal) {
                imageModal.style.display = "none";
            }
        }

        // Asignar evento de cierre al botón 'X' y al fondo del modal
        if (closeImageModal) {
            closeImageModal.onclick = closeModal;
        }
        
        imageModal.onclick = function(event) {
            // Cierra el modal solo si se hace clic en el fondo, no en la imagen
            if (event.target === imageModal) {
                closeModal();
            }
        }
    }
});