<?php
// app/controllers/SociosController.php
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../../config/config.php';

class SociosController {

    // ... (métodos index, create, store, edit, update, handleSocioDocumentUpload se mantienen igual) ...
    private function handleSocioDocumentUpload($fileInputName, $socioId, $tipoDocumento, $userId) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 10 * 1024 * 1024; // 10 MB

        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, 'socios');
            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'],
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => $socioId,
                    'ejemplar_id' => null,
                    'servicio_id' => null,
                    'id_usuario' => $userId,
                    'comentarios' => 'Documento maestro de socio.'
                ];
                if (!Documento::store($docData)) {
                     error_log("Error BD al guardar doc {$tipoDocumento} para socio ID {$socioId}.");
                     $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB doc: " . htmlspecialchars($uploadResult['data']['originalName']) . ". ";
                }
                return true;
            } else {
                 $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($_FILES[$fileInputName]['name']) . ": " . $uploadResult['message'] . ". ";
            }
        } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_NO_FILE) {
             $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error PHP al subir " . $fileInputName . " (Code: " . $_FILES[$fileInputName]['error'] . "). ";
        }
        return false;
    }

    public function index() {
        check_permission();
        
        $searchTerm = '';
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $searchTerm = trim($_GET['search']);
        }

        $socios = Socio::getAll($searchTerm);

        $pageTitle = 'Listado de Socios';
        $currentRoute = 'socios_index';
        $contentView = __DIR__ . '/../views/socios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function create() {
        check_permission();
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        $pageTitle = 'Registrar Nuevo Socio';
        $currentRoute = 'socios/create';
        $contentView = __DIR__ . '/../views/socios/create.php';
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
                 'nombre_ganaderia' => trim($_POST['nombre_ganaderia'] ?? ''),
                 'direccion' => trim($_POST['direccion'] ?? ''),
                 'codigoGanadero' => trim($_POST['codigoGanadero'] ?? ''),
                 'telefono' => trim($_POST['telefono'] ?? ''),
                 'email' => trim($_POST['email'] ?? ''),
                 'fechaRegistro' => trim($_POST['fechaRegistro'] ?? date('Y-m-d')),
                 'estado' => trim($_POST['estado'] ?? 'activo'),
                 'id_usuario' => $_SESSION['user']['id_usuario'],
                 'identificacion_fiscal_titular' => trim($_POST['identificacion_fiscal_titular'] ?? '')
            ];
            
            $socioId = Socio::store($data);
            if($socioId !== false) {
                $_SESSION['message'] = "Socio registrado exitosamente con ID: " . $socioId . ".";
                unset($_SESSION['form_data']);

                $this->handleSocioDocumentUpload('id_oficial_file', $socioId, 'ID_OFICIAL_TITULAR', $data['id_usuario']);
                $this->handleSocioDocumentUpload('rfc_file', $socioId, 'CONSTANCIA_FISCAL', $data['id_usuario']);
                $this->handleSocioDocumentUpload('domicilio_file', $socioId, 'COMPROBANTE_DOM_GANADERIA', $data['id_usuario']);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $socioId, 'TITULO_PROPIEDAD_RANCHO', $data['id_usuario']);
                
                header("Location: index.php?route=socios_index");
                exit;

            } else {
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al guardar el socio.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al registrar el socio: " . $error_detail;
                header("Location: index.php?route=socios/create");
                exit;
            }
        }
    }
    
    public function edit($id = null) {
        check_permission();
        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($socioId) {
            $socio = Socio::getById($socioId);
            if($socio) {
                $formData = $_SESSION['form_data'] ?? $socio;
                unset($_SESSION['form_data']);
                
                $documentosSocio = Documento::getByEntityId('socio', $socioId, true);
                $pageTitle = 'Editar Socio'; 
                $currentRoute = 'socios/edit';
                $contentView = __DIR__ . '/../views/socios/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php';
                return;
            }
        }
        $_SESSION['error'] = "Socio no encontrado.";
        header("Location: index.php?route=socios_index"); 
        exit;
    }

    public function update() {
        check_permission();
        $id = filter_input(INPUT_POST, 'id_socio', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de socio inválido.";
            header("Location: index.php?route=socios_index"); 
            exit;
        }

        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'nombre_ganaderia' => trim($_POST['nombre_ganaderia'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'codigoGanadero' => trim($_POST['codigoGanadero'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'fechaRegistro' => trim($_POST['fechaRegistro'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $_SESSION['user']['id_usuario'],
                'identificacion_fiscal_titular' => trim($_POST['identificacion_fiscal_titular'] ?? '')
            ];
            
            if(Socio::update($id, $data)) {
                $_SESSION['message'] = "Socio actualizado exitosamente.";
                unset($_SESSION['form_data']);

                $this->handleSocioDocumentUpload('id_oficial_file', $id, 'ID_OFICIAL_TITULAR', $data['id_usuario']);
                $this->handleSocioDocumentUpload('rfc_file', $id, 'CONSTANCIA_FISCAL', $data['id_usuario']);
                $this->handleSocioDocumentUpload('domicilio_file', $id, 'COMPROBANTE_DOM_GANADERIA', $data['id_usuario']);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $id, 'TITULO_PROPIEDAD_RANCHO', $data['id_usuario']);
                
                header("Location: index.php?route=socios_index");
                exit;
            } else {
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el socio.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al actualizar el socio: " . $error_detail;
                header("Location: index.php?route=socios/edit&id=" . $id);
                exit;
            }
        }
    }

    // *** INICIO DE LA MODIFICACIÓN ***
    public function delete($id = null) {
        check_permission();
        
        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $razon = $_POST['razon'] ?? ''; // Recibimos la razón desde POST

        if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=socios_index");
            exit;
        }

        if($socioId) {
            if(Socio::delete($socioId, $razon)) { 
                $_SESSION['message'] = "Socio desactivado exitosamente.";
            } else { 
                $_SESSION['error'] = "Error al desactivar socio. " . ($_SESSION['error_details'] ?? ''); 
                unset($_SESSION['error_details']);
            }
        } else { 
            $_SESSION['error'] = "ID de socio inválido.";
        }
        header("Location: index.php?route=socios_index"); 
        exit;
    }
    // *** FIN DE LA MODIFICACIÓN ***
}