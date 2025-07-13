<?php
// app/controllers/MedicosController.php
require_once __DIR__ . '/../models/Medico.php';

class MedicosController {

    /**
     * Muestra el formulario para crear un nuevo médico.
     */
    public function create() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Añadir permisos si es necesario (ej. solo admin puede registrar médicos)
        /*
        if ($_SESSION['user']['rol'] !== 'superusuario') {
             $_SESSION['error'] = "No tienes permisos para registrar médicos.";
             header("Location: index.php?route=dashboard");
             exit;
        }
        */

        $pageTitle = 'Registrar Nuevo Médico';
        $currentRoute = 'medicos/create';
        $contentView = __DIR__ . '/../views/medicos/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario y guarda el nuevo médico.
     */
    public function store() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
         // Añadir permisos si es necesario

        if(isset($_POST)) {
            // Recoger datos
            $nombre = trim($_POST['nombre']);
            $apellido_paterno = trim($_POST['apellido_paterno']);
            $apellido_materno = trim($_POST['apellido_materno']);
            $especialidad = trim($_POST['especialidad'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $numero_cedula = trim($_POST['numero_cedula_profesional'] ?? '');
            $entidad = trim($_POST['entidad_residencia'] ?? '');
            $num_ancce = trim($_POST['numero_certificacion_ancce'] ?? '');
            $estado = trim($_POST['estado'] ?? 'activo');
            $id_usuario_registro = $_SESSION['user']['id_usuario'];

            // --- Validaciones ---
            $errors = [];
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";

            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre solo debe contener letras y espacios.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno solo debe contener letras y espacios.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno solo debe contener letras y espacios.";

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email no es válido.";
            }
             // Email es opcional en BD, pero si se ingresa, que sea válido
             if (empty($email)){ // Si el email está vacío, lo ponemos como NULL para la BD
                $email = null;
             }


            if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {
                 $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }

            // Validar formato de Cédula Profesional (Ejemplo: solo números, longitud variable)
             // Validación de Cédula Profesional (ahora permite letras y números)
            if (!empty($numero_cedula) && !preg_match('/^[A-Za-z0-9]+$/', $numero_cedula)) {
                $errors[] = "La cédula profesional solo debe contener letras y números (sin espacios ni símbolos).";
            }
             // Validar formato de Certificación ANCCE (Ejemplo: ANCCE-12345)
             if (!empty($num_ancce) && !preg_match('/^[A-Za-z0-9\-]+$/', $num_ancce)) { // Ajustar patrón
                 $errors[] = "El número de certificación ANCCE contiene caracteres no válidos.";
             }

             $estados_permitidos = ['activo', 'inactivo'];
             if (!in_array($estado, $estados_permitidos)) {
                 $errors[] = "El estado seleccionado no es válido.";
             }
            // --- Fin Validaciones ---

            if (!empty($errors)) {
                $_SESSION['error'] = implode("<br>", $errors);
                $_SESSION['form_data'] = $_POST; // Guardar datos para repoblar
                header("Location: index.php?route=medicos/create");
                exit;
            }

            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'especialidad' => $especialidad,
                'telefono' => $telefono,
                'email' => $email, // Puede ser null
                'numero_cedula_profesional' => $numero_cedula,
                'entidad_residencia' => $entidad,
                'numero_certificacion_ancce' => $num_ancce,
                'estado' => $estado,
                'id_usuario' => $id_usuario_registro
            ];

            $result = Medico::store($data);

            if($result !== false) {
                $_SESSION['message'] = "Médico registrado exitosamente con ID: " . $result;
                unset($_SESSION['form_data']);
            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados.';
                 unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al registrar el médico. " . $error_detail;
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=medicos/create");
                 exit;
            }
        } else {
             $_SESSION['error'] = "No se recibieron datos del formulario.";
        }
        header("Location: index.php?route=medicos_index");
        exit;
    }

    /**
     * Muestra la lista de todos los médicos.
     */
    public function index() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Añadir permisos si es necesario

        $medicos = Medico::getAll();

        $pageTitle = 'Listado de Médicos';
        $currentRoute = 'medicos_index';
        $contentView = __DIR__ . '/../views/medicos/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un médico existente.
     */
    public function edit() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
         // Añadir permisos si es necesario

        if(isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $medico = Medico::getById($id);

            if($medico) {
                $pageTitle = 'Editar Médico';
                $currentRoute = 'medicos/edit';
                $contentView = __DIR__ . '/../views/medicos/edit.php';
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
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
         // Añadir permisos si es necesario

        if(isset($_POST['id_medico']) && isset($_POST)) {
            $id = intval($_POST['id_medico']);

            // Recoger datos
            $nombre = trim($_POST['nombre']);
            $apellido_paterno = trim($_POST['apellido_paterno']);
            $apellido_materno = trim($_POST['apellido_materno']);
            $especialidad = trim($_POST['especialidad'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $numero_cedula = trim($_POST['numero_cedula_profesional'] ?? '');
            $entidad = trim($_POST['entidad_residencia'] ?? '');
            $num_ancce = trim($_POST['numero_certificacion_ancce'] ?? '');
            $estado = trim($_POST['estado'] ?? 'activo');
            $id_usuario_edicion = $_SESSION['user']['id_usuario'];

             // --- Validaciones (similares a store) ---
             $errors = [];
             if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
             if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
             if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
             if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre solo debe contener letras y espacios.";
             if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno solo debe contener letras y espacios.";
             if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno solo debe contener letras y espacios.";
             if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El formato del email no es válido.";
             if (empty($email)) $email = null; // Permitir NULL
             if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
             if (!empty($numero_cedula) && !preg_match('/^[0-9]+$/', $numero_cedula)) $errors[] = "La cédula profesional solo debe contener números.";
             if (!empty($num_ancce) && !preg_match('/^[A-Za-z0-9\-]+$/', $num_ancce)) $errors[] = "El número de certificación ANCCE contiene caracteres no válidos.";
             $estados_permitidos = ['activo', 'inactivo'];
             if (!in_array($estado, $estados_permitidos)) $errors[] = "El estado seleccionado no es válido.";
            // --- Fin Validaciones ---


             if (!empty($errors)) {
                 $_SESSION['error'] = implode("<br>", $errors);
                 header("Location: index.php?route=medicos/edit&id=" . $id);
                 exit;
             }

            $data = [
                 'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'especialidad' => $especialidad,
                'telefono' => $telefono,
                'email' => $email,
                'numero_cedula_profesional' => $numero_cedula,
                'entidad_residencia' => $entidad,
                'numero_certificacion_ancce' => $num_ancce,
                'estado' => $estado,
                'id_usuario' => $id_usuario_edicion
            ];

            if(Medico::update($id, $data)) {
                $_SESSION['message'] = "Médico actualizado exitosamente.";
            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados.';
                 unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al actualizar el médico. " . $error_detail;
                 header("Location: index.php?route=medicos/edit&id=" . $id);
                 exit;
            }
        } else {
            $_SESSION['error'] = "Datos no válidos o ID de médico no proporcionado.";
        }
        header("Location: index.php?route=medicos_index");
        exit;
    }

    /**
     * Elimina un médico existente.
     */
    public function delete() {
        session_start();
        if(!isset($_SESSION['user'])){
            header("Location: index.php?route=login");
            exit;
        }
        // Añadir permisos si es necesario

        if(isset($_GET['id'])) {
            $id = intval($_GET['id']);

            if(Medico::delete($id)) {
                $_SESSION['message'] = "Médico eliminado exitosamente.";
            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Error desconocido.';
                 unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al eliminar el médico. " . $error_detail;
            }
        } else {
            $_SESSION['error'] = "ID de médico no especificado.";
        }
        header("Location: index.php?route=medicos_index");
        exit;
    }
}
?>