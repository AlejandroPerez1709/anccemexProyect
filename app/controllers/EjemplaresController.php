<?php
// app/controllers/EjemplaresController.php
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php';

class EjemplaresController {

    // CORREGIDO: Se eliminan los helpers 'checkSession', 'handleSingle...' y 'handleMultiple...'
    // La lógica de subida de archivos se mantiene, pero la de sesión ya no es necesaria aquí.
    private function handleSingleEjemplarDocUpload($fileInputName, $ejemplarId, $tipoDocumento, $userId) { /* ...código sin cambios... */ }
    private function handleMultipleEjemplarPhotos($fileInputName, $ejemplarId, $userId) { /* ...código sin cambios... */ }

    public function create() {
        check_permission(); // Se usa la función estándar

        $sociosList = Socio::getActiveSociosForSelect();
        if (empty($sociosList)) { $_SESSION['warning'] = "No hay socios activos registrados."; }
        $pageTitle = 'Registrar Nuevo Ejemplar';
        $currentRoute = 'ejemplares/create';
        $contentView = __DIR__ . '/../views/ejemplares/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        
        if(isset($_POST)) {
            // ... (Toda la lógica de validación y guardado permanece igual) ...
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'raza' => trim($_POST['raza'] ?? '') ?: null,
                'fechaNacimiento' => trim($_POST['fechaNacimiento'] ?? '') ?: null,
                'socio_id' => filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT),
                'sexo' => trim($_POST['sexo'] ?? ''),
                'codigo_ejemplar' => trim($_POST['codigo_ejemplar'] ?? '') ?: null,
                'capa' => trim($_POST['capa'] ?? '') ?: null,
                'numero_microchip' => trim($_POST['numero_microchip'] ?? '') ?: null,
                'numero_certificado' => trim($_POST['numero_certificado'] ?? '') ?: null,
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $userId
            ];

            $ejemplarId = Ejemplar::store($data);

            if($ejemplarId !== false) {
                $_SESSION['message'] = "Ejemplar registrado con ID: " . $ejemplarId . ".";
                unset($_SESSION['form_data']);
                
                $this->handleSingleEjemplarDocUpload('pasaporte_file', $ejemplarId, 'PASAPORTE_DIE', $userId);
                $this->handleSingleEjemplarDocUpload('adn_file', $ejemplarId, 'RESULTADO_ADN', $userId);
                $this->handleSingleEjemplarDocUpload('cert_lg_file', $ejemplarId, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                $this->handleMultipleEjemplarPhotos('fotos_file', $ejemplarId, $userId);
                
                header("Location: index.php?route=ejemplares_index");
                exit;
            } else {
                $_SESSION['error'] = "Error al registrar ejemplar. " . ($_SESSION['error_details'] ?? 'Verifique datos.');
                unset($_SESSION['error_details']);
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?route=ejemplares/create");
                exit;
            }
        }
    }

    public function index() {
        check_permission();
        
        $ejemplares = Ejemplar::getAll();
        $pageTitle = 'Listado de Ejemplares';
        $currentRoute = 'ejemplares_index';
        $contentView = __DIR__ . '/../views/ejemplares/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function edit($id = null) {
        check_permission();
        
        $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($ejemplarId) {
            $ejemplar = Ejemplar::getById($ejemplarId);
            if ($ejemplar) {
                $sociosList = Socio::getActiveSociosForSelect();
                $documentosEjemplar = Documento::getByEntityId('ejemplar', $ejemplarId, true);
                $pageTitle = 'Editar Ejemplar';
                $currentRoute = 'ejemplares/edit';
                $contentView = __DIR__ . '/../views/ejemplares/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php';
                return;
            } else { $_SESSION['error'] = "Ejemplar no encontrado."; }
        } else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=ejemplares_index");
        exit;
    }

    public function update() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];

        if(isset($_POST['id_ejemplar'])) {
            $id = filter_input(INPUT_POST, 'id_ejemplar', FILTER_VALIDATE_INT);
            if (!$id) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=ejemplares_index"); exit; }

            // ... (Lógica de validación y actualización permanece igual) ...
             $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'raza' => trim($_POST['raza'] ?? '') ?: null,
                'fechaNacimiento' => trim($_POST['fechaNacimiento'] ?? '') ?: null,
                'socio_id' => filter_input(INPUT_POST, 'socio_id', FILTER_VALIDATE_INT),
                'sexo' => trim($_POST['sexo'] ?? ''),
                'codigo_ejemplar' => trim($_POST['codigo_ejemplar'] ?? '') ?: null,
                'capa' => trim($_POST['capa'] ?? '') ?: null,
                'numero_microchip' => trim($_POST['numero_microchip'] ?? '') ?: null,
                'numero_certificado' => trim($_POST['numero_certificado'] ?? '') ?: null,
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $userId
            ];

            if(Ejemplar::update($id, $data)) {
                 $_SESSION['message'] = "Ejemplar actualizado.";
                 $this->handleSingleEjemplarDocUpload('pasaporte_file', $id, 'PASAPORTE_DIE', $userId);
                 $this->handleSingleEjemplarDocUpload('adn_file', $id, 'RESULTADO_ADN', $userId);
                 $this->handleSingleEjemplarDocUpload('cert_lg_file', $id, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                 $this->handleMultipleEjemplarPhotos('fotos_file', $id, $userId);
            } else {
                 $_SESSION['error'] = "Error al actualizar. " . ($_SESSION['error_details'] ?? 'Error al guardar.');
                 unset($_SESSION['error_details']);
                 header("Location: index.php?route=ejemplares/edit&id=" . $id);
                 exit;
            }
        } else {
            $_SESSION['error'] = "Datos inválidos.";
        }
        header("Location: index.php?route=ejemplares_index");
        exit;
    }

    public function delete($id = null) {
        check_permission();
        
        $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($ejemplarId) {
            if (Ejemplar::delete($ejemplarId)) { $_SESSION['message'] = "Ejemplar eliminado."; }
            else { $_SESSION['error'] = "Error al eliminar. " . ($_SESSION['error_details'] ?? ''); unset($_SESSION['error_details']); }
        }
        else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=ejemplares_index");
        exit;
    }
}