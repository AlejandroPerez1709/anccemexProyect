<?php
// app/controllers/EjemplaresController.php
require_once __DIR__ . '/../models/Ejemplar.php';
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../../config/config.php';

class EjemplaresController {

    // ... (métodos handleSingle... y handleMultiple... se mantienen igual) ...
    private function handleSingleEjemplarDocUpload($fileInputName, $ejemplarId, $tipoDocumento, $userId) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 10 * 1024 * 1024; // 10 MB

            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, 'ejemplares');
            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'], 
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => null,
                    'ejemplar_id' => $ejemplarId,
                    'servicio_id' => null,
                    'id_usuario' => $userId,
                    'comentarios' => 'Documento maestro de ejemplar.'
                ];
                if (!Documento::store($docData)) {
                     $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB doc: " . htmlspecialchars($uploadResult['data']['originalName']) . ". ";
                }
            } else {
                 $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($_FILES[$fileInputName]['name']) . ": " . $uploadResult['message'] . ". ";
            }
        }
    }

    private function handleMultipleEjemplarPhotos($fileInputName, $ejemplarId, $userId) {
        if (isset($_FILES[$fileInputName]) && is_array($_FILES[$fileInputName]['name']) && !empty($_FILES[$fileInputName]['name'][0])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5 MB
            $fileCount = count($_FILES[$fileInputName]['name']);
            $subfolder = 'ejemplares' . DIRECTORY_SEPARATOR . $ejemplarId . DIRECTORY_SEPARATOR . 'fotos';

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES[$fileInputName]['error'][$i] === UPLOAD_ERR_OK) {
                    $singleFileArray = [
                        'name' => $_FILES[$fileInputName]['name'][$i],
                        'type' => $_FILES[$fileInputName]['type'][$i],
                        'tmp_name' => $_FILES[$fileInputName]['tmp_name'][$i],
                        'error' => $_FILES[$fileInputName]['error'][$i],
                        'size' => $_FILES[$fileInputName]['size'][$i]
                    ];
                    $uploadResult = Documento::handleUpload('dummy_name', $allowedTypes, $maxFileSize, $subfolder, ['dummy_name' => $singleFileArray]);

                    if ($uploadResult['status'] === 'success') {
                        $docData = [
                            'tipoDocumento' => 'FOTO_IDENTIFICACION',
                            'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                            'rutaArchivo' => $uploadResult['data']['savedPath'],
                            'mimeType' => $uploadResult['data']['mimeType'],
                            'sizeBytes' => $uploadResult['data']['size'],
                            'socio_id' => null,
                            'ejemplar_id' => $ejemplarId,
                            'servicio_id' => null,
                            'id_usuario' => $userId,
                            'comentarios' => 'Foto ID.'
                        ];
                        if (!Documento::store($docData)) {
                             $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB foto: " . htmlspecialchars($singleFileArray['name']) . ". ";
                        }
                    } else {
                        $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($singleFileArray['name']) . ": " . $uploadResult['message'] . ". ";
                    }
                }
            }
        }
    }

    public function index() {
         check_permission();
         
         $searchTerm = '';
         if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
             $searchTerm = trim($_GET['search']);
         }

         $ejemplares = Ejemplar::getAll($searchTerm);

         $pageTitle = 'Listado de Ejemplares'; 
         $currentRoute = 'ejemplares_index'; 
         $contentView = __DIR__ . '/../views/ejemplares/index.php'; 
         require_once __DIR__ . '/../views/layouts/master.php';
    }

    // ... (métodos create y store se mantienen igual) ...
    public function create() {
        check_permission();
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        $sociosList = Socio::getActiveSociosForSelect();
        if (empty($sociosList)) { 
            $_SESSION['warning'] = "No hay socios activos. Por favor, registre uno primero.";
        }
        
        $pageTitle = 'Registrar Nuevo Ejemplar';
        $currentRoute = 'ejemplares/create';
        $contentView = __DIR__ . '/../views/ejemplares/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    public function store() {
        check_permission();
        $userId = $_SESSION['user']['id_usuario'];
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
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
            if($ejemplarId) {
                $_SESSION['message'] = "Ejemplar registrado con ID: " . $ejemplarId . "."; 
                unset($_SESSION['form_data']);

                $this->handleSingleEjemplarDocUpload('pasaporte_file', $ejemplarId, 'PASAPORTE_DIE', $userId);
                $this->handleSingleEjemplarDocUpload('adn_file', $ejemplarId, 'RESULTADO_ADN', $userId);
                $this->handleSingleEjemplarDocUpload('cert_lg_file', $ejemplarId, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                $this->handleMultipleEjemplarPhotos('fotos_file', $ejemplarId, $userId);
                
                header("Location: index.php?route=ejemplares_index"); 
                exit;
            } else { 
                $_SESSION['error'] = "Error al registrar ejemplar: " . ($_SESSION['error_details'] ?? 'Error desconocido.'); 
                unset($_SESSION['error_details']);
                header("Location: index.php?route=ejemplares/create");
                exit; 
            }
        }
    }
    
    // ... (método edit se mantiene igual) ...
     public function edit($id = null) {
        check_permission();
        $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($ejemplarId) { 
            $ejemplar = Ejemplar::getById($ejemplarId);
            if ($ejemplar) { 
                $formData = $_SESSION['form_data'] ?? $ejemplar;
                unset($_SESSION['form_data']);

                $sociosList = Socio::getActiveSociosForSelect();
                $documentosEjemplar = Documento::getByEntityId('ejemplar', $ejemplarId, true); 
                $pageTitle = 'Editar Ejemplar'; 
                $currentRoute = 'ejemplares/edit'; 
                $contentView = __DIR__ . '/../views/ejemplares/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return; 
            }
        }
        $_SESSION['error'] = "Ejemplar no encontrado o ID inválido.";
        header("Location: index.php?route=ejemplares_index"); 
        exit;
    }

    // ... (método update se mantiene igual) ...
    public function update() {
        check_permission(); 
        $userId = $_SESSION['user']['id_usuario'];
        $id = filter_input(INPUT_POST, 'id_ejemplar', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de ejemplar inválido.";
            header("Location: index.php?route=ejemplares_index"); 
            exit;
        }
        
        $_SESSION['form_data'] = $_POST;
        if(isset($_POST)) {
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
                'estado' => trim($_POST['estado'] ?? ''), 
                'id_usuario' => $userId 
            ];

            if(Ejemplar::update($id, $data)) {
                $_SESSION['message'] = "Ejemplar actualizado.";
                unset($_SESSION['form_data']);

                $this->handleSingleEjemplarDocUpload('pasaporte_file', $id, 'PASAPORTE_DIE', $userId);
                $this->handleSingleEjemplarDocUpload('adn_file', $id, 'RESULTADO_ADN', $userId);
                $this->handleSingleEjemplarDocUpload('cert_lg_file', $id, 'CERTIFICADO_INSCRIPCION_LG', $userId);
                $this->handleMultipleEjemplarPhotos('fotos_file', $id, $userId);
            } else { 
                $_SESSION['error'] = "Error al actualizar. " . ($_SESSION['error_details'] ?? 'Error desconocido.');
                unset($_SESSION['error_details']);
                header("Location: index.php?route=ejemplares/edit&id=" . $id);
                exit; 
            }
        }
        
        header("Location: index.php?route=ejemplares_index");
        exit;
    }

    // *** INICIO DE LA MODIFICACIÓN ***
    public function delete($id = null) {
         check_permission();
         $ejemplarId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
         $razon = $_POST['razon'] ?? '';

         if (empty($razon)) {
            $_SESSION['error'] = "La razón de desactivación es obligatoria.";
            header("Location: index.php?route=ejemplares_index");
            exit;
         }

         if ($ejemplarId) { 
             if (Ejemplar::delete($ejemplarId, $razon)) { 
                 $_SESSION['message'] = "Ejemplar desactivado.";
             } else { 
                 $_SESSION['error'] = "Error al desactivar. " . ($_SESSION['error_details'] ?? 'Puede que tenga registros asociados.'); 
                 unset($_SESSION['error_details']);
             } 
         } else { 
             $_SESSION['error'] = "ID inválido.";
         } 
         header("Location: index.php?route=ejemplares_index"); 
         exit;
    }
    // *** FIN DE LA MODIFICACIÓN ***
}