<?php
// app/controllers/SociosController.php
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php';

class SociosController {

    private function handleSocioDocumentUpload($fileInputName, $socioId, $tipoDocumento, $userId) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 10 * 1024 * 1024;

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

    public function create() {
        check_permission();

        $pageTitle = 'Registrar Nuevo Socio';
        $currentRoute = 'socios/create';
        $contentView = __DIR__ . '/../views/socios/create.php';
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
                 'nombre_ganaderia' => trim($_POST['nombre_ganaderia'] ?? '') ?: null,
                 'direccion' => trim($_POST['direccion'] ?? '') ?: null,
                 'codigoGanadero' => trim($_POST['codigoGanadero'] ?? ''),
                 'telefono' => trim($_POST['telefono'] ?? '') ?: null,
                 'email' => trim($_POST['email'] ?? '') ?: null,
                 'fechaRegistro' => trim($_POST['fechaRegistro'] ?? date('Y-m-d')) ?: date('Y-m-d'),
                 'estado' => trim($_POST['estado'] ?? 'activo'),
                 'id_usuario' => $_SESSION['user']['id_usuario'],
                 'identificacion_fiscal_titular' => trim($_POST['identificacion_fiscal_titular'] ?? '') ?: null
            ];
            // (Aquí iría el bloque de validaciones)

            $socioId = Socio::store($data);

            if($socioId !== false) {
                $_SESSION['message'] = "Socio registrado exitosamente con ID: " . $socioId . ".";
                unset($_SESSION['form_data']);

                $this->handleSocioDocumentUpload('id_oficial_file', $socioId, 'ID_OFICIAL_TITULAR', $_SESSION['user']['id_usuario']);
                $this->handleSocioDocumentUpload('rfc_file', $socioId, 'CONSTANCIA_FISCAL', $_SESSION['user']['id_usuario']);
                $this->handleSocioDocumentUpload('domicilio_file', $socioId, 'COMPROBANTE_DOM_GANADERIA', $_SESSION['user']['id_usuario']);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $socioId, 'TITULO_PROPIEDAD_RANCHO', $_SESSION['user']['id_usuario']);
                
                 header("Location: index.php?route=socios_index");
                 exit;
            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados (posible duplicado o campo faltante).';
                 unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al registrar el socio. " . $error_detail;
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=socios/create");
                 exit;
            }
        } else {
             $_SESSION['error'] = "No se recibieron datos del formulario.";
             header("Location: index.php?route=socios/create");
             exit;
        }
    }

    public function index() {
        check_permission();
        
        $socios = Socio::getAll();
        $pageTitle = 'Listado de Socios';
        $currentRoute = 'socios_index';
        $contentView = __DIR__ . '/../views/socios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function edit($id = null) {
        check_permission();

        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($socioId) {
            $socio = Socio::getById($socioId);
            if($socio) {
                $documentosSocio = Documento::getByEntityId('socio', $socioId, true);
                $pageTitle = 'Editar Socio'; $currentRoute = 'socios/edit';
                $contentView = __DIR__ . '/../views/socios/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php';
                return;
            } else { $_SESSION['error'] = "Socio no encontrado."; }
        } else { $_SESSION['error'] = "ID de socio no especificado o inválido."; }
        header("Location: index.php?route=socios_index"); exit;
    }

    public function update() {
        check_permission();
        
        if(isset($_POST['id_socio'])) {
            $id = filter_input(INPUT_POST, 'id_socio', FILTER_VALIDATE_INT);
            if (!$id) { $_SESSION['error'] = "ID de socio inválido."; header("Location: index.php?route=socios_index"); exit; }

            // ... (Lógica de validación y actualización permanece igual)
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'nombre_ganaderia' => trim($_POST['nombre_ganaderia'] ?? '') ?: null,
                'direccion' => trim($_POST['direccion'] ?? '') ?: null,
                'codigoGanadero' => trim($_POST['codigoGanadero'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? '') ?: null,
                'email' => trim($_POST['email'] ?? '') ?: null,
                'fechaRegistro' => trim($_POST['fechaRegistro'] ?? '') ?: null,
                'estado' => trim($_POST['estado'] ?? 'activo'),
                'id_usuario' => $_SESSION['user']['id_usuario'],
                'identificacion_fiscal_titular' => trim($_POST['identificacion_fiscal_titular'] ?? '') ?: null
            ];

            if(Socio::update($id, $data)) {
                $_SESSION['message'] = "Socio actualizado exitosamente.";
                $this->handleSocioDocumentUpload('id_oficial_file', $id, 'ID_OFICIAL_TITULAR', $_SESSION['user']['id_usuario']);
                $this->handleSocioDocumentUpload('rfc_file', $id, 'CONSTANCIA_FISCAL', $_SESSION['user']['id_usuario']);
                $this->handleSocioDocumentUpload('domicilio_file', $id, 'COMPROBANTE_DOM_GANADERIA', $_SESSION['user']['id_usuario']);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $id, 'TITULO_PROPIEDAD_RANCHO', $_SESSION['user']['id_usuario']);
                
                 header("Location: index.php?route=socios_index");
                 exit;

            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados.'; unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al actualizar el socio. " . $error_detail;
                 header("Location: index.php?route=socios/edit&id=" . $id); exit;
            }
        } else {
            $_SESSION['error'] = "Datos no válidos o ID de socio no proporcionado.";
            header("Location: index.php?route=socios_index"); exit;
        }
    }

    public function delete($id = null) {
        check_permission();
        
        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($socioId) {
            if(Socio::delete($socioId)) { $_SESSION['message'] = "Socio eliminado."; }
            else { $_SESSION['error'] = "Error al eliminar socio. " . ($_SESSION['error_details'] ?? ''); unset($_SESSION['error_details']); }
        } else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=socios_index"); exit;
    }
}