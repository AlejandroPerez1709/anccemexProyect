<?php
// app/controllers/EmpleadosController.php
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../../config/config.php';

class EmpleadosController {

    public function index() {
        check_permission();
        
        $searchTerm = '';
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $searchTerm = trim($_GET['search']);
        }

        $empleados = Empleado::getAll($searchTerm);

        $pageTitle = 'Listado de Empleados';
        $currentRoute = 'empleados_index';
        $contentView = __DIR__ . '/../views/empleados/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function create() {
        check_permission(); 
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); 

        $pageTitle = 'Registrar Nuevo Empleado';
        $currentRoute = 'empleados/create';
        $contentView = __DIR__ . '/../views/empleados/create.php';
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
                'email' => trim($_POST['email'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'puesto' => trim($_POST['puesto'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'fecha_ingreso' => trim($_POST['fecha_ingreso'] ?? '')
            ];
            if(Empleado::store($data)) {
                $_SESSION['message'] = "Empleado creado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=empleados_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al crear el empleado: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=empleados/create"); 
                exit;
            }
        }
    }

    public function edit($id = null) {
        check_permission();
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            $empleado = Empleado::getById($empleadoId);
            if($empleado) {
                $formData = $_SESSION['form_data'] ?? $empleado; 
                unset($_SESSION['form_data']); 
                
                $pageTitle = 'Editar Empleado';
                $currentRoute = 'empleados/edit';
                $contentView = __DIR__ . '/../views/empleados/edit.php'; 
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return;
            }
        }
        $_SESSION['error'] = "Empleado no encontrado.";
        header("Location: index.php?route=empleados_index"); 
        exit;
    }

    public function update() {
        check_permission();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de empleado inválido.";
            header("Location: index.php?route=empleados_index"); 
            exit;
        }
        
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''), 
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''), 
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'email' => trim($_POST['email'] ?? ''), 
                'direccion' => trim($_POST['direccion'] ?? ''), 
                'telefono' => trim($_POST['telefono'] ?? ''),
                'puesto' => trim($_POST['puesto'] ?? ''), 
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'fecha_ingreso' => trim($_POST['fecha_ingreso'] ?? '')
            ];
            if(Empleado::update($id, $data)) {
                $_SESSION['message'] = "Empleado actualizado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=empleados_index"); 
                exit;
            } else {
                $_SESSION['error'] = "Error al actualizar el empleado: " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=empleados/edit&id=" . $id); 
                exit;
            }
        }
    }

    public function delete($id = null) {
        check_permission();
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $razon = $_POST['razon'] ?? '';

        if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=empleados_index");
            exit;
        }

        if($empleadoId) {
            if(Empleado::delete($empleadoId, $razon)) { 
                $_SESSION['message'] = "Empleado desactivado.";
            } else { 
                $_SESSION['error'] = "Error al desactivar. " . ($_SESSION['error_details'] ?? 'Puede tener registros asociados.'); 
                unset($_SESSION['error_details']);
            }
        } else { 
            $_SESSION['error'] = "ID inválido.";
        }
        header("Location: index.php?route=empleados_index"); 
        exit;
    }
}