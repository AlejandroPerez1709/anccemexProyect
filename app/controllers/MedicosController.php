<?php
// app/controllers/MedicosController.php
require_once __DIR__ . '/../models/Medico.php';

class MedicosController {

    public function create() {
        check_permission();

        $pageTitle = 'Registrar Nuevo Médico';
        $currentRoute = 'medicos/create';
        $contentView = __DIR__ . '/../views/medicos/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();

        if(isset($_POST)) {
            // ... (Lógica de validación y guardado sin cambios) ...
            $data = [
                'nombre' => trim($_POST['nombre']),
                'apellido_paterno' => trim($_POST['apellido_paterno']),
                'apellido_materno' => trim($_POST['apellido_materno']),
                'especialidad' => trim($_POST['especialidad'] ?? '') ?: null,
                'telefono' => trim($_POST['telefono'] ?? '') ?: null,
                'email' => trim($_POST['email'] ?? '') ?: null,
                'numero_cedula_profesional' => trim($_POST['numero_cedula_profesional'] ?? '') ?: null,
                'entidad_residencia' => trim($_POST['entidad_residencia'] ?? '') ?: null,
                'numero_certificacion_ancce' => trim($_POST['numero_certificacion_ancce'] ?? '') ?: null,
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $_SESSION['user']['id_usuario']
            ];
            // (Aquí iría el bloque de validaciones)
            
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

    public function index() {
        check_permission();

        $medicos = Medico::getAll();
        $pageTitle = 'Listado de Médicos';
        $currentRoute = 'medicos_index';
        $contentView = __DIR__ . '/../views/medicos/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function edit($id = null) {
        check_permission();
        
        $id = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if($id) {
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

    public function update() {
        check_permission();

        if(isset($_POST['id_medico'])) {
            $id = intval($_POST['id_medico']);
            // ... (Lógica de validación y actualización sin cambios) ...
            $data = [
                'nombre' => trim($_POST['nombre']),
                'apellido_paterno' => trim($_POST['apellido_paterno']),
                'apellido_materno' => trim($_POST['apellido_materno']),
                'especialidad' => trim($_POST['especialidad'] ?? '') ?: null,
                'telefono' => trim($_POST['telefono'] ?? '') ?: null,
                'email' => trim($_POST['email'] ?? '') ?: null,
                'numero_cedula_profesional' => trim($_POST['numero_cedula_profesional'] ?? '') ?: null,
                'entidad_residencia' => trim($_POST['entidad_residencia'] ?? '') ?: null,
                'numero_certificacion_ancce' => trim($_POST['numero_certificacion_ancce'] ?? '') ?: null,
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $_SESSION['user']['id_usuario']
            ];
            // (Aquí iría el bloque de validaciones)
            
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

    public function delete($id = null) {
        check_permission();

        $id = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if($id) {
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