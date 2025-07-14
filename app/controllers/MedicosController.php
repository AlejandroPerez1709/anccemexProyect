<?php
// app/controllers/MedicosController.php
require_once __DIR__ . '/../models/Medico.php';
// Asegúrate de incluir la función check_permission de config.php
require_once __DIR__ . '/../../config/config.php'; 

class MedicosController {

    // Los métodos de verificación de sesión se han consolidado en check_permission()

    /**
     * Muestra el formulario para crear un nuevo médico.
     */
    public function create() {
        check_permission(); // Se usa la función estándar para verificar permisos.
        // Recuperar datos del formulario si hubo un error de validación previo para repoblar
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); // Limpiar después de recuperarlos

        $pageTitle = 'Registrar Nuevo Médico';
        $currentRoute = 'medicos/create';
        $contentView = __DIR__ . '/../views/medicos/create.php';
        // Pasar $formData a la vista
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario y guarda el nuevo médico.
     */
    public function store() {
        check_permission(); // Se usa la función estándar para verificar permisos.
        
        // Guardar todos los datos POST en sesión al inicio para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $especialidad = trim($_POST['especialidad'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $numero_cedula = trim($_POST['numero_cedula_profesional'] ?? '');
            $entidad = trim($_POST['entidad_residencia'] ?? '');
            $num_ancce = trim($_POST['numero_certificacion_ancce'] ?? '');
            $estado = trim($_POST['estado'] ?? 'activo');
            $id_usuario_registro = $_SESSION['user']['id_usuario'];

            // --- Validaciones del Lado del Servidor (MEJORADAS) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
            if (empty($telefono)) $errors[] = "El teléfono es obligatorio.";
            if (empty($email)) $errors[] = "El email es obligatorio.";
            if (empty($numero_cedula)) $errors[] = "El número de cédula profesional es obligatorio.";
            if (empty($entidad)) $errors[] = "La entidad de residencia es obligatoria.";

            // 2. Validación de formato de nombres y apellidos (letras y espacios)
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            
            // 3. Validación de email (formato válido)
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email es inválido.";
            }

            // 4. Validación de teléfono (exactamente 10 dígitos numéricos)
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }

            // 5. Validación de Cédula Profesional (solo letras y números)
            if (!empty($numero_cedula) && !preg_match('/^[A-Za-z0-9]+$/', $numero_cedula)) {
                $errors[] = "La cédula profesional solo debe contener letras y números (sin espacios ni símbolos).";
            }

            // 6. Validación de Certificación ANCCE (letras, números y guiones)
            if (!empty($num_ancce) && !preg_match('/^[A-Za-z0-9\-]+$/', $num_ancce)) { 
                $errors[] = "El número de certificación ANCCE contiene caracteres no válidos.";
            }

            // 7. Validación de estado (solo valores permitidos)
            $estados_permitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estados_permitidos)) {
                $errors[] = "El estado seleccionado no es válido.";
            }
            
            // --- Fin Validaciones ---

            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=medicos/create");
                exit;
            }

            // Preparar datos para guardar
            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'especialidad' => $especialidad ?: null, // Puede ser null si está vacío
                'telefono' => $telefono,
                'email' => $email,
                'numero_cedula_profesional' => $numero_cedula,
                'entidad_residencia' => $entidad,
                'numero_certificacion_ancce' => $num_ancce ?: null, // Puede ser null
                'estado' => $estado,
                'id_usuario' => $id_usuario_registro
            ];

            // Intentar guardar el médico
            if(Medico::store($data)) {
                $_SESSION['message'] = "Médico registrado exitosamente.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión
            } else {
                // Si hubo un error en el modelo (ej. duplicado de email, error de DB),
                // el modelo ya debería haber establecido un $_SESSION['error_details'].
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al guardar el médico. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al registrar el médico: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=medicos/create"); 
                exit;
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=medicos/create"); 
            exit; 
        }

        // Redirigir al listado si todo fue exitoso
        header("Location: index.php?route=medicos_index"); 
        exit;
    }

    /**
     * Muestra la lista de todos los médicos.
     */
    public function index() {
        check_permission(); // Se usa la función estándar para verificar permisos.
        
        $medicos = Medico::getAll();
        $pageTitle = 'Listado de Médicos';
        $currentRoute = 'medicos_index';
        $contentView = __DIR__ . '/../views/medicos/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un médico existente.
     */
    public function edit($id = null) {
        check_permission(); // Se usa la función estándar para verificar permisos.
        
        $medicoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if($medicoId) {
            $medico = Medico::getById($medicoId);
            if($medico) {
                // Si se cargan datos de un médico, usarlos para repoblar el formulario
                // Si hubo un error previo en update(), $_SESSION['form_data'] tendrá prioridad
                $formData = $_SESSION['form_data'] ?? $medico; // Repoblar con datos del médico o de la sesión si hubo error
                unset($_SESSION['form_data']); // Limpiar después de usarlos
                
                $pageTitle = 'Editar Médico';
                $currentRoute = 'medicos/edit';
                $contentView = __DIR__ . '/../views/medicos/edit.php';
                // Pasar $formData a la vista
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return;
            } else { 
                $_SESSION['error'] = "Médico no encontrado."; 
            }
        } else { 
            $_SESSION['error'] = "ID de médico no especificado.";
        }
        header("Location: index.php?route=medicos_index"); 
        exit;
    }

    /**
     * Procesa los datos del formulario de edición y actualiza el médico.
     */
    public function update() {
        check_permission(); // Se usa la función estándar para verificar permisos.
        
        // Recuperar el ID al inicio para poder redirigir a la misma página de edición en caso de error
        $id = filter_input(INPUT_POST, 'id_medico', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de médico inválido."; 
            header("Location: index.php?route=medicos_index"); // Redirigir a la lista si el ID es nulo o inválido
            exit;
        }
        // Guardar todos los datos POST en sesión para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST; 

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $especialidad = trim($_POST['especialidad'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $numero_cedula = trim($_POST['numero_cedula_profesional'] ?? '');
            $entidad = trim($_POST['entidad_residencia'] ?? '');
            $num_ancce = trim($_POST['numero_certificacion_ancce'] ?? '');
            $estado = trim($_POST['estado'] ?? 'activo');
            $id_usuario_edicion = $_SESSION['user']['id_usuario'];

            // --- Validaciones (similar a store) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
            if (empty($telefono)) $errors[] = "El teléfono es obligatorio.";
            if (empty($email)) $errors[] = "El email es obligatorio.";
            if (empty($numero_cedula)) $errors[] = "El número de cédula profesional es obligatorio.";
            if (empty($entidad)) $errors[] = "La entidad de residencia es obligatoria.";

            // 2. Validación de formato de nombres y apellidos
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            
            // 3. Validación de email (formato válido)
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email es inválido.";
            }

            // 4. Validación de teléfono (exactamente 10 dígitos numéricos)
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }

            // 5. Validación de Cédula Profesional (solo letras y números)
            if (!empty($numero_cedula) && !preg_match('/^[A-Za-z0-9]+$/', $numero_cedula)) {
                $errors[] = "La cédula profesional solo debe contener letras y números (sin espacios ni símbolos).";
            }

            // 6. Validación de Certificación ANCCE (letras, números y guiones)
            if (!empty($num_ancce) && !preg_match('/^[A-Za-z0-9\-]+$/', $num_ancce)) { 
                $errors[] = "El número de certificación ANCCE contiene caracteres no válidos.";
            }

            // 7. Validación de estado (solo valores permitidos)
            $estados_permitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estados_permitidos)) {
                $errors[] = "El estado seleccionado no es válido.";
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=medicos/edit&id=" . $id); 
                exit;
            }

            // Preparar datos para actualizar
            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'especialidad' => $especialidad ?: null,
                'telefono' => $telefono,
                'email' => $email,
                'numero_cedula_profesional' => $numero_cedula,
                'entidad_residencia' => $entidad,
                'numero_certificacion_ancce' => $num_ancce ?: null,
                'estado' => $estado,
                'id_usuario' => $id_usuario_edicion
            ];

            // Intentar actualizar el médico. El modelo ya maneja errores de DB.
            if(Medico::update($id, $data)) {
                $_SESSION['message'] = "Médico actualizado exitosamente.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión
            } else {
                // Si hubo un error en el modelo, mostrar error genérico o específico
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el médico. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al actualizar el médico: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=medicos/edit&id=" . $id); 
                exit;
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=medicos/edit&id=" . $id); 
            exit; 
        }

        // Redirigir al listado tras éxito
        header("Location: index.php?route=medicos_index"); 
        exit;
    }

    /**
     * Elimina un médico existente.
     */
    public function delete($id = null) {
        check_permission(); // Se usa la función estándar para verificar permisos.
        
        $medicoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($medicoId) {
            if(Medico::delete($medicoId)) { 
                $_SESSION['message'] = "Médico eliminado exitosamente.";
            } else { 
                $_SESSION['error'] = "Error al eliminar el médico. " . ($_SESSION['error_details'] ?? 'Puede que el médico tenga servicios asociados o no exista.'); 
                unset($_SESSION['error_details']); 
            }
        } else { 
            $_SESSION['error'] = "ID de médico no especificado.";
        }
        header("Location: index.php?route=medicos_index"); 
        exit;
    }
}