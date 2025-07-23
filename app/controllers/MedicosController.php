<?php
// app/controllers/MedicosController.php
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../../config/config.php';

class MedicosController {

    public function index() {
        check_permission();
        
        $searchTerm = '';
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $searchTerm = trim($_GET['search']);
        }

        $medicos = Medico::getAll($searchTerm);

        $pageTitle = 'Listado de Médicos';
        $currentRoute = 'medicos_index';
        $contentView = __DIR__ . '/../views/medicos/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function create() {
        check_permission();
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        $pageTitle = 'Registrar Nuevo Médico';
        $currentRoute = 'medicos/create';
        $contentView = __DIR__ . '/../views/medicos/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $_SESSION['form_data'] = $_POST;
        
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'especialidad' => trim($_POST['especialidad'] ?? '') ?: null,
                'telefono' => trim($_POST['telefono'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'numero_cedula_profesional' => trim($_POST['numero_cedula_profesional'] ?? ''),
                'entidad_residencia' => trim($_POST['entidad_residencia'] ?? ''),
                'numero_certificacion_ancce' => trim($_POST['numero_certificacion_ancce'] ?? '') ?: null,
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $_SESSION['user']['id_usuario']
            ];

            if(Medico::store($data)) {
                $_SESSION['message'] = "Médico registrado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=medicos_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al registrar el médico: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=medicos/create");
                exit;
            }
        }
    }

    public function edit($id = null) {
        check_permission();
        $medicoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($medicoId) {
            $medico = Medico::getById($medicoId);
            if($medico) {
                $formData = $_SESSION['form_data'] ?? $medico;
                unset($_SESSION['form_data']);
                
                $pageTitle = 'Editar Médico';
                $currentRoute = 'medicos/edit';
                $contentView = __DIR__ . '/../views/medicos/edit.php'; 
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return;
            }
        }
        $_SESSION['error'] = "Médico no encontrado.";
        header("Location: index.php?route=medicos_index"); 
        exit;
    }

    public function update() {
        check_permission();
        $id = filter_input(INPUT_POST, 'id_medico', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de médico inválido.";
            header("Location: index.php?route=medicos_index");
            exit;
        }

        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'especialidad' => trim($_POST['especialidad'] ?? '') ?: null,
                'telefono' => trim($_POST['telefono'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'numero_cedula_profesional' => trim($_POST['numero_cedula_profesional'] ?? ''),
                'entidad_residencia' => trim($_POST['entidad_residencia'] ?? ''),
                'numero_certificacion_ancce' => trim($_POST['numero_certificacion_ancce'] ?? '') ?: null,
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $_SESSION['user']['id_usuario']
            ];

            if(Medico::update($id, $data)) {
                $_SESSION['message'] = "Médico actualizado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=medicos_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al actualizar el médico: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=medicos/edit&id=" . $id);
                exit;
            }
        }
    }

    public function delete($id = null) {
        check_permission();
        $medicoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $razon = $_POST['razon'] ?? '';

        if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=medicos_index");
            exit;
        }

        if($medicoId) {
            if(Medico::delete($medicoId, $razon)) { 
                $_SESSION['message'] = "Médico desactivado exitosamente.";
            } else { 
                $_SESSION['error'] = "Error al desactivar el médico. " . ($_SESSION['error_details'] ?? 'Puede que tenga servicios asociados.'); 
                unset($_SESSION['error_details']);
            }
        } else { 
            $_SESSION['error'] = "ID de médico no especificado.";
        }
        header("Location: index.php?route=medicos_index"); 
        exit;
    }
}