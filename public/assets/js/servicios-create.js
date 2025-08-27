/*
    public/assets/js/servicios-create.js
    Lógica de JavaScript para la vista de creación de Servicios.
    - Carga dinámica de ejemplares por socio vía AJAX.
    - Visibilidad condicional del campo de médico.
*/
document.addEventListener('DOMContentLoaded', function() {
    const tipoServicioSelect = document.getElementById('tipo_servicio_id');
    const socioSelect = document.getElementById('socio_id');
    const ejemplarSelect = document.getElementById('ejemplar_id');
    const medicoSelect = document.getElementById('medico_id');
    const grupoMedico = document.getElementById('grupo_medico');
    const ejemplarMsg = document.getElementById('ejemplar_msg');

    function checkSelectValue(selectElement) {
        if (selectElement.value !== "") {
            selectElement.classList.add('is-filled');
        } else {
            selectElement.classList.remove('is-filled');
        }
    }
    
    function actualizarVisibilidadMedico() {
        if (!tipoServicioSelect) return;
        
        const selectedOption = tipoServicioSelect.options[tipoServicioSelect.selectedIndex];
        const requiereMedico = selectedOption && selectedOption.dataset.reqMedico === '1';

        grupoMedico.classList.toggle('hidden', !requiereMedico);
        if (!requiereMedico) {
            medicoSelect.value = ''; // Resetea la selección si se oculta
        }
        checkSelectValue(medicoSelect);
    }

    function cargarEjemplaresPorSocio() {
        const socioId = socioSelect.value;
        
        ejemplarSelect.innerHTML = '<option value="" selected disabled>Cargando...</option>';
        ejemplarSelect.disabled = true;
        checkSelectValue(ejemplarSelect);
        ejemplarMsg.textContent = 'Cargando ejemplares...';
        ejemplarMsg.classList.remove('text-danger');

        if (!socioId) {
            ejemplarSelect.innerHTML = '<option value="" selected disabled>-- Seleccione un socio primero --</option>';
            ejemplarMsg.textContent = 'Seleccione un socio para ver sus ejemplares.';
            checkSelectValue(ejemplarSelect);
            return;
        }

        fetch(`index.php?route=ejemplares_por_socio&socio_id=${socioId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta de la red.');
                }
                return response.json();
            })
            .then(data => {
                ejemplarSelect.innerHTML = ''; 
                
                if (data.length > 0) {
                    ejemplarSelect.disabled = false;
                    ejemplarMsg.textContent = 'Seleccione el ejemplar para el servicio.';
                    
                    let optionDefault = document.createElement('option');
                    optionDefault.value = "";
                    optionDefault.textContent = "-- Seleccione un ejemplar --";
                    optionDefault.disabled = true;
                    optionDefault.selected = true;
                    ejemplarSelect.appendChild(optionDefault);
                    
                    data.forEach(ejemplar => {
                        let option = document.createElement('option');
                        option.value = ejemplar.id_ejemplar;
                        option.textContent = `${ejemplar.nombre} (${ejemplar.codigo_ejemplar || 'S/C'})`;
                        ejemplarSelect.appendChild(option);
                    });
                } else {
                    ejemplarSelect.disabled = true;
                    ejemplarMsg.textContent = 'Este socio no tiene ejemplares activos registrados.';
                    ejemplarMsg.classList.add('text-danger');
                    let optionNone = document.createElement('option');
                    optionNone.textContent = "-- Sin ejemplares disponibles --";
                    ejemplarSelect.appendChild(optionNone);
                }
                checkSelectValue(ejemplarSelect);
            })
            .catch(error => {
                console.error('Error al cargar ejemplares:', error);
                ejemplarMsg.textContent = 'Error al cargar los ejemplares. Intente de nuevo.';
                ejemplarMsg.classList.add('text-danger');
                ejemplarSelect.innerHTML = '<option value="">-- Error --</option>';
                checkSelectValue(ejemplarSelect);
            });
    }

    if (tipoServicioSelect) tipoServicioSelect.addEventListener('change', function() {
        actualizarVisibilidadMedico();
        checkSelectValue(this);
    });

    if (socioSelect) socioSelect.addEventListener('change', function() {
        cargarEjemplaresPorSocio();
        checkSelectValue(this);
    });

    if (ejemplarSelect) ejemplarSelect.addEventListener('change', function() {
        checkSelectValue(this);
    });
    
    document.querySelectorAll('select').forEach(select => checkSelectValue(select));

    var today = new Date().toISOString().split('T')[0];
    var fechaSolInput = document.getElementById('fechaSolicitud');
    if(fechaSolInput) {
        fechaSolInput.setAttribute('max', today);
    }
});