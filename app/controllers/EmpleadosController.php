<?php
// app/controllers/EmpleadosController.php
require_once __DIR__ . '/../models/Empleado.php';

class EmpleadosController {

    // CORREGIDO: El helper checkSession() ha sido eliminado.

    public function create() {
        check_permission(); // Se usa la función estándar.

        $pageTitle = 'Registrar Nuevo Empleado';
        $currentRoute = 'empleados/create';
        $contentView = __DIR__ . '/../views/empleados/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();

        if(isset($_POST)) {
            // La lógica de validación y guardado permanece igual
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $puesto = trim($_POST['puesto'] ?? '');
            $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');

            $errors = [];
            // ... (validaciones)
            if (!empty($errors)) {
                 $_SESSION['error'] = implode("<br>", $errors);
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=empleados/create");
                 exit;
             }

            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'email' => $email,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'puesto' => $puesto,
                'fecha_ingreso' => $fecha_ingreso ?: null
            ];
            if(Empleado::store($data)) {
                $_SESSION['message'] = "Empleado creado exitosamente.";
                unset($_SESSION['form_data']);
            } else {
                $_SESSION['error'] = "Error al crear el empleado. " . ($_SESSION['error_details'] ?? '');
                unset($_SESSION['error_details']);
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=empleados/create"); exit;
            }
        } else { $_SESSION['error'] = "No se recibieron datos."; }

        header("Location: index.php?route=empleados_index"); exit;
    }

    public function index() {
        check_permission();
        
        $empleados = Empleado::getAll();
        $pageTitle = 'Listado de Empleados';
        $currentRoute = 'empleados_index';
        $contentView = __DIR__ . '/../views/empleados/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function edit($id = null) {
        check_permission();
        
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            $empleado = Empleado::getById($empleadoId);
            if($empleado) {
                $pageTitle = 'Editar Empleado';
                $currentRoute = 'empleados/edit';
                $contentView = __DIR__ . '/../views/empleados/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php';
                return;
            } else { $_SESSION['error'] = "Empleado no encontrado."; }
        } else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=empleados_index"); exit;
    }

    public function update() {
        check_permission();

        if(isset($_POST['id'])) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=empleados_index"); exit; }

            // ... (Lógica de validación y actualización permanece igual)
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'puesto' => trim($_POST['puesto'] ?? ''),
                'fecha_ingreso' => trim($_POST['fecha_ingreso'] ?? '') ?: null
            ];
            // ... (Bloque de validaciones)
            if(Empleado::update($id, $data)) {
                $_SESSION['message'] = "Empleado actualizado exitosamente.";
            } else {
                $_SESSION['error'] = "Error al actualizar. " . ($_SESSION['error_details'] ?? '');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=empleados/edit&id=" . $id); exit;
            }
        } else {
            $_SESSION['error'] = "Datos inválidos.";
        }
        header("Location: index.php?route=empleados_index");
        exit;
    }

    public function delete($id = null) {
        check_permission();

        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            if(Empleado::delete($empleadoId)) { $_SESSION['message'] = "Empleado eliminado."; }
            else { $_SESSION['error'] = "Error al eliminar. " . ($_SESSION['error_details'] ?? ''); unset($_SESSION['error_details']); }
        } else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=empleados_index"); exit;
    }
}