<?php
// app/controllers/TiposServiciosController.php
require_once __DIR__ . '/../models/TipoServicio.php';

class TiposServiciosController {

    // CORREGIDO: ELIMINADO el método privado checkAdmin() para usar la función global check_permission()

    public function index() {
        // CORREGIDO: Se usa la función estándar check_permission
        check_permission('superusuario'); 
    
        // Pasamos la variable $tiposServicios a la vista master/content include
        $tiposServicios = TipoServicio::getAll();
        $pageTitle = 'Catálogo de Tipos de Servicio';
        $currentRoute = 'tipos_servicios_index';
        $contentView = __DIR__ . '/../views/tipos_servicios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function create() {
        // CORREGIDO: Se usa la función estándar check_permission
        check_permission('superusuario');
        $pageTitle = 'Registrar Nuevo Tipo de Servicio';
        $currentRoute = 'tipos_servicios/create';
        $contentView = __DIR__ . '/../views/tipos_servicios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        // CORREGIDO: Se usa la función estándar check_permission
        check_permission('superusuario');
        if (isset($_POST)) {
            $nombre = trim($_POST['nombre']);
            $codigo_servicio = trim($_POST['codigo_servicio'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $requiere_medico = isset($_POST['requiere_medico']);
            // Checkbox presente o no
            // requiere_ejemplar ya no se envía/recibe
            $documentos_requeridos = trim($_POST['documentos_requeridos'] ?? '');
            $estado = trim($_POST['estado'] ?? 'activo');

            $errors = [];
            if (empty($nombre)) $errors[] = "El nombre del tipo de servicio es obligatorio.";
            if (!in_array($estado, ['activo', 'inactivo'])) $errors[] = "El estado seleccionado no es válido.";
            if (!empty($errors)) {
                 $_SESSION['error'] = implode("<br>", $errors);
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=tipos_servicios/create");
                 exit;
            }

            $data = [
                'nombre' => $nombre,
                'codigo_servicio' => $codigo_servicio,
                'descripcion' => $descripcion,
                'requiere_medico' => $requiere_medico,
                // requiere_ejemplar ya no se pasa al modelo
                'documentos_requeridos' => $documentos_requeridos,
                'estado' => $estado
            ];
            $result = TipoServicio::store($data);

            if ($result !== false) {
                $_SESSION['message'] = "Tipo de servicio creado exitosamente.";
                unset($_SESSION['form_data']);
                header("Location: index.php?route=tipos_servicios_index");
                exit;
            } else {
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al crear el tipo de servicio. " . $error_detail;
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?route=tipos_servicios/create");
                exit;
            }
        } else {
             $_SESSION['error'] = "No se recibieron datos.";
             header("Location: index.php?route=tipos_servicios/create");
             exit;
        }
    }

    public function edit($id = null) {
        // CORREGIDO: Se usa la función estándar check_permission
        check_permission('superusuario');
        $tipoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$tipoId) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=tipos_servicios_index"); exit; }

        $tipoServicio = TipoServicio::getById($tipoId);
        // $tipoServicio se pasará a la vista
        if (!$tipoServicio) { $_SESSION['error'] = "Tipo de servicio no encontrado."; header("Location: index.php?route=tipos_servicios_index"); exit; }

        $pageTitle = 'Editar Tipo de Servicio';
        $currentRoute = 'tipos_servicios/edit';
        $contentView = __DIR__ . '/../views/tipos_servicios/edit.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function update() {
        // CORREGIDO: Se usa la función estándar check_permission
        check_permission('superusuario');
        if (isset($_POST['id_tipo_servicio'])) {
            $id = filter_input(INPUT_POST, 'id_tipo_servicio', FILTER_VALIDATE_INT);
            if (!$id) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=tipos_servicios_index"); exit; }

            $nombre = trim($_POST['nombre']);
            $codigo_servicio = trim($_POST['codigo_servicio'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $requiere_medico = isset($_POST['requiere_medico']);
            // requiere_ejemplar ya no se envía/recibe
            $documentos_requeridos = trim($_POST['documentos_requeridos'] ?? '');
            $estado = trim($_POST['estado'] ?? 'activo');

             $errors = [];
             if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (!in_array($estado, ['activo', 'inactivo'])) $errors[] = "Estado inválido.";

             if (!empty($errors)) {
                 $_SESSION['error'] = implode("<br>", $errors);
                 header("Location: index.php?route=tipos_servicios/edit&id=" . $id);
                 exit;
             }

             $data = [
                'nombre' => $nombre,
                'codigo_servicio' => $codigo_servicio,
                'descripcion' => $descripcion,
                'requiere_medico' => $requiere_medico,
                // requiere_ejemplar ya no se pasa al modelo
                'documentos_requeridos' => $documentos_requeridos,
                'estado' => $estado
             ];
            if (TipoServicio::update($id, $data)) {
                 $_SESSION['message'] = "Tipo de servicio actualizado exitosamente.";
            } else {
                  $error_detail = $_SESSION['error_details'] ?? 'Error desconocido.';
                  unset($_SESSION['error_details']);
                  $_SESSION['error'] = "Error al actualizar el tipo de servicio. " . $error_detail;
                  header("Location: index.php?route=tipos_servicios/edit&id=" . $id);
                  exit;
            }

        } else {
             $_SESSION['error'] = "Falta ID del tipo de servicio.";
        }
        header("Location: index.php?route=tipos_servicios_index");
        exit;
    }

    public function delete($id = null) {
         // CORREGIDO: Se usa la función estándar check_permission
         check_permission('superusuario');
         $tipoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         if ($tipoId) {
             if (TipoServicio::delete($tipoId)) {
                 $_SESSION['message'] = "Tipo de servicio eliminado exitosamente.";
             } else {
                  $error_detail = $_SESSION['error_details'] ?? 'Error desconocido.';
                  unset($_SESSION['error_details']);
                  $_SESSION['error'] = "Error al eliminar el tipo de servicio. " . $error_detail;
             }
         } else {
             $_SESSION['error'] = "ID de tipo de servicio inválido.";
         }
         header("Location: index.php?route=tipos_servicios_index");
         exit;
    }

} // Fin clase TiposServiciosController
?>