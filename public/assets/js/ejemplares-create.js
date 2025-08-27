/*
    public/assets/js/ejemplares-create.js
    Lógica de JavaScript para la vista de creación de Ejemplares.
    - Manejo del atributo 'max' para la fecha de nacimiento.
*/
document.addEventListener('DOMContentLoaded', function() {
    // Establecer la fecha máxima para Fecha de Nacimiento
    var today = new Date().toISOString().split('T')[0];
    var fechaNacInput = document.getElementById('fechaNacimiento');
    if (fechaNacInput) { 
        fechaNacInput.setAttribute('max', today); 
    }
});