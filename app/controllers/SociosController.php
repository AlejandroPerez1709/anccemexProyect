<?php
// app/controllers/SociosController.php
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php'; // <-- Asegúrate que la ruta es correcta

class SociosController {

    // Helper para procesar subida de un documento específico para un socio
    private function handleSocioDocumentUpload($fileInputName, $socioId, $tipoDocumento, $userId) {
        // Definir tipos permitidos y tamaño máximo (puedes ajustarlos)
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 10 * 1024 * 1024; // 10 MB

        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            // Guardar en subcarpeta 'socios'
            $uploadResult = Documento::handleUpload($fileInputName, $allowedTypes, $maxFileSize, 'socios');

            if ($uploadResult['status'] === 'success') {
                $docData = [
                    'tipoDocumento' => $tipoDocumento,
                    'nombreArchivoOriginal' => $uploadResult['data']['originalName'],
                    'rutaArchivo' => $uploadResult['data']['savedPath'],
                    'mimeType' => $uploadResult['data']['mimeType'],
                    'sizeBytes' => $uploadResult['data']['size'],
                    'socio_id' => $socioId, // Asociar al socio
                    'ejemplar_id' => null,
                    'servicio_id' => null,
                    'id_usuario' => $userId,
                    'comentarios' => 'Documento maestro de socio.'
                ];
                if (!Documento::store($docData)) {
                     error_log("Error BD al guardar doc {$tipoDocumento} para socio ID {$socioId}.");
                     // Usar ?? '' para evitar error si $_SESSION['warning'] no existe
                     $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error DB doc: " . htmlspecialchars($uploadResult['data']['originalName']) . ". ";
                }
                return true; // Se procesó el intento de subida
            } else {
                 $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error al subir " . htmlspecialchars($_FILES[$fileInputName]['name']) . ": " . $uploadResult['message'] . ". ";
            }
        } elseif (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_NO_FILE) {
             $_SESSION['warning'] = ($_SESSION['warning'] ?? '') . " Error PHP al subir " . $fileInputName . " (Code: " . $_FILES[$fileInputName]['error'] . "). ";
        }
         // Si no se subió archivo (UPLOAD_ERR_NO_FILE), simplemente no hacemos nada y retornamos false
         return false;
    }


    /**
     * Muestra el formulario para crear un nuevo socio.
     */
    public function create() {
        session_start();
        if(!isset($_SESSION['user'])){ header("Location: index.php?route=login"); exit; }

        $pageTitle = 'Registrar Nuevo Socio';
        $currentRoute = 'socios/create';
        $contentView = __DIR__ . '/../views/socios/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario y guarda el nuevo socio y sus documentos.
     */
    public function store() {
        session_start();
        if(!isset($_SESSION['user'])){ header("Location: index.php?route=login"); exit; }

        if(isset($_POST)) {
            // Recoger datos del formulario
            $nombre = trim($_POST['nombre']);
            $apellido_paterno = trim($_POST['apellido_paterno']);
            $apellido_materno = trim($_POST['apellido_materno']);
            $nombre_ganaderia = trim($_POST['nombre_ganaderia'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            //$numeroSocio = trim($_POST['numeroSocio'] ?? ''); // Eliminado
            $codigoGanadero = trim($_POST['codigoGanadero'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fechaRegistro = trim($_POST['fechaRegistro'] ?? date('Y-m-d'));
            $estado = trim($_POST['estado'] ?? 'activo');
            $identificacionFiscal = trim($_POST['identificacion_fiscal_titular'] ?? '');
            $id_usuario_registro = $_SESSION['user']['id_usuario'];

            // --- Validaciones ---
            $errors = [];
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
           // if (empty($numeroSocio)) $errors[] = "El número de socio es obligatorio."; // Eliminado
            if (empty($codigoGanadero)) $errors[] = "El código ganadero es obligatorio.";
            // ... resto validaciones ...

            if (!empty($errors)) {
                $_SESSION['error'] = implode("<br>", $errors);
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?route=socios/create");
                exit;
            }

            $data = [
                 'nombre' => $nombre, 'apellido_paterno' => $apellido_paterno, 'apellido_materno' => $apellido_materno,
                 'nombre_ganaderia' => $nombre_ganaderia ?: null, 'direccion' => $direccion ?: null,
                 /* 'numeroSocio' => $numeroSocio, // Eliminado */ 'codigoGanadero' => $codigoGanadero,
                 'telefono' => $telefono ?: null, 'email' => $email ?: null,
                 'fechaRegistro' => $fechaRegistro ?: date('Y-m-d'), 'estado' => $estado,
                 'id_usuario' => $id_usuario_registro, 'identificacion_fiscal_titular' => $identificacionFiscal ?: null
            ];

            $socioId = Socio::store($data); // Intentar guardar socio

            if($socioId !== false) {
                $_SESSION['message'] = "Socio registrado exitosamente con ID: " . $socioId . ".";
                unset($_SESSION['form_data']);

                // Procesar Documentos Subidos
                $this->handleSocioDocumentUpload('id_oficial_file', $socioId, 'ID_OFICIAL_TITULAR', $id_usuario_registro);
                $this->handleSocioDocumentUpload('rfc_file', $socioId, 'CONSTANCIA_FISCAL', $id_usuario_registro);
                $this->handleSocioDocumentUpload('domicilio_file', $socioId, 'COMPROBANTE_DOM_GANADERIA', $id_usuario_registro);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $socioId, 'TITULO_PROPIEDAD_RANCHO', $id_usuario_registro);

                 // Redirigir al índice después de éxito
                 header("Location: index.php?route=socios_index");
                 exit;

            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados (posible duplicado o campo faltante).';
                 unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al registrar el socio. " . $error_detail;
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=socios/create"); // Volver al formulario
                 exit;
            }
        } else {
             $_SESSION['error'] = "No se recibieron datos del formulario.";
             header("Location: index.php?route=socios/create"); // Volver al formulario
             exit;
        }
       // No debería llegar aquí si todo va bien, pero por si acaso:
       // header("Location: index.php?route=socios_index");
       // exit;
    }

    /**
     * Muestra la lista de todos los socios.
     */
    public function index() {
        session_start();
        if(!isset($_SESSION['user'])){ header("Location: index.php?route=login"); exit; }
        $socios = Socio::getAll();
        $pageTitle = 'Listado de Socios';
        $currentRoute = 'socios_index';
        $contentView = __DIR__ . '/../views/socios/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un socio y sus documentos asociados.
     */
    public function edit($id = null) {
        session_start();
        if(!isset($_SESSION['user'])){ header("Location: index.php?route=login"); exit; }
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


    /**
     * Procesa los datos del formulario de edición, actualiza el socio y maneja nuevas subidas de documentos.
     */
    public function update() {
        session_start();
        if(!isset($_SESSION['user'])){ header("Location: index.php?route=login"); exit; }
        $id_usuario_edicion = $_SESSION['user']['id_usuario']; // Usuario que edita

        if(isset($_POST['id_socio'])) { // Asegurarse que el ID viene del POST
            $id = filter_input(INPUT_POST, 'id_socio', FILTER_VALIDATE_INT);
             if (!$id) { $_SESSION['error'] = "ID de socio inválido."; header("Location: index.php?route=socios_index"); exit; }

            // Recoger datos (sin numeroSocio)
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $nombre_ganaderia = trim($_POST['nombre_ganaderia'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $codigoGanadero = trim($_POST['codigoGanadero'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fechaRegistro = trim($_POST['fechaRegistro'] ?? ''); // Recoger fecha
            $estado = trim($_POST['estado'] ?? 'activo');
            $identificacionFiscal = trim($_POST['identificacion_fiscal_titular'] ?? '');

             // Validaciones (sin numeroSocio)
             $errors = [];
             if (empty($nombre)) $errors[] = "Nombre obligatorio.";
             if (empty($apellido_paterno)) $errors[]="Ap Paterno obligatorio.";
             if (empty($apellido_materno)) $errors[]="Ap Materno obligatorio.";
             if (empty($codigoGanadero)) $errors[]="Código Ganadero obligatorio.";
             if (!empty($fechaRegistro) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRegistro)){ $errors[]="Formato Fecha Reg. inválido."; $fechaRegistro=null; } elseif(empty($fechaRegistro)) { $fechaRegistro = null; }
             // ... resto validaciones ...

             if (!empty($errors)) {
                 $_SESSION['error'] = implode("<br>", $errors);
                 header("Location: index.php?route=socios/edit&id=" . $id); exit;
             }

             // Preparar datos (sin numeroSocio)
            $data = [
                'nombre' => $nombre, 'apellido_paterno' => $apellido_paterno, 'apellido_materno' => $apellido_materno,
                'nombre_ganaderia' => $nombre_ganaderia ?: null, 'direccion' => $direccion ?: null,
                'codigoGanadero' => $codigoGanadero, 'telefono' => $telefono ?: null, 'email' => $email ?: null,
                'fechaRegistro' => $fechaRegistro, // Pasar fecha validada (puede ser null)
                'estado' => $estado, 'id_usuario' => $id_usuario_edicion,
                'identificacion_fiscal_titular' => $identificacionFiscal ?: null
            ];

            if(Socio::update($id, $data)) {
                $_SESSION['message'] = "Socio actualizado exitosamente.";
                // Procesar NUEVOS Documentos Subidos en Edición
                $this->handleSocioDocumentUpload('id_oficial_file', $id, 'ID_OFICIAL_TITULAR', $id_usuario_edicion);
                $this->handleSocioDocumentUpload('rfc_file', $id, 'CONSTANCIA_FISCAL', $id_usuario_edicion);
                $this->handleSocioDocumentUpload('domicilio_file', $id, 'COMPROBANTE_DOM_GANADERIA', $id_usuario_edicion);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $id, 'TITULO_PROPIEDAD_RANCHO', $id_usuario_edicion);

                 // --- ¡CAMBIO EN LA REDIRECCIÓN! ---
                 header("Location: index.php?route=socios_index"); // Redirigir al LISTADO
                 exit;
                 // --- FIN DEL CAMBIO ---

            } else {
                 $error_detail = $_SESSION['error_details'] ?? 'Verifique los datos ingresados.'; unset($_SESSION['error_details']);
                 $_SESSION['error'] = "Error al actualizar el socio. " . $error_detail;
                 header("Location: index.php?route=socios/edit&id=" . $id); exit; // Volver a edit en caso de error de BD
            }
        } else {
            $_SESSION['error'] = "Datos no válidos o ID de socio no proporcionado.";
            header("Location: index.php?route=socios_index"); exit; // Si no hay ID, ir al índice
        }
        // Esta línea ya no debería alcanzarse
        // header("Location: index.php?route=socios/edit&id=" . $id); exit;
    }

    /**
     * Elimina un socio existente.
     */
    public function delete($id = null) {
        session_start();
        if(!isset($_SESSION['user'])){ header("Location: index.php?route=login"); exit; }

        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($socioId) {
             // Considerar borrar documentos asociados si ON DELETE no es CASCADE
             // $docsToDelete = Documento::getByEntityId('socio', $socioId);
             // foreach($docsToDelete as $doc) { Documento::delete($doc['id_documento']); } // Podría ser peligroso si falla a la mitad
            if(Socio::delete($socioId)) { $_SESSION['message'] = "Socio eliminado."; }
            else { $_SESSION['error'] = "Error al eliminar socio. " . ($_SESSION['error_details'] ?? ''); unset($_SESSION['error_details']); }
        } else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=socios_index"); exit;
    }

} // Fin clase SociosController
?>