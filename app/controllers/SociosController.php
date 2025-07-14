<?php
// app/controllers/SociosController.php
require_once __DIR__ . '/../models/Socio.php';
require_once __DIR__ . '/../models/Documento.php'; // Asegúrate que la ruta es correcta
// Se incluye config.php para usar la función global check_permission()
require_once __DIR__ . '/../../config/config.php';

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
        check_permission(); // Se usa la función global para verificar permisos.
        // Recuperar datos del formulario si hubo un error de validación previo para repoblar
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); // Limpiar después de recuperarlos

        $pageTitle = 'Registrar Nuevo Socio';
        $currentRoute = 'socios/create';
        $contentView = __DIR__ . '/../views/socios/create.php';
        // Pasar $formData a la vista
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario y guarda el nuevo socio y sus documentos.
     */
    public function store() {
        check_permission(); // Se usa la función global para verificar permisos.
        
        // Guardar todos los datos POST en sesión al inicio para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $nombre_ganaderia = trim($_POST['nombre_ganaderia'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $codigoGanadero = trim($_POST['codigoGanadero'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fechaRegistro = trim($_POST['fechaRegistro'] ?? date('Y-m-d'));
            $estado = trim($_POST['estado'] ?? 'activo');
            $identificacionFiscal = trim($_POST['identificacion_fiscal_titular'] ?? '');
            $id_usuario_registro = $_SESSION['user']['id_usuario'];

            // --- Validaciones del Lado del Servidor (MEJORADAS) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre del titular es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno del titular es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno del titular es obligatorio.";
            if (empty($telefono)) $errors[] = "El teléfono de contacto es obligatorio.";
            if (empty($email)) $errors[] = "El email de contacto es obligatorio.";
            if (empty($identificacionFiscal)) $errors[] = "El RFC del titular es obligatorio.";
            if (empty($nombre_ganaderia)) $errors[] = "El nombre de la ganadería es obligatorio.";
            if (empty($direccion)) $errors[] = "La dirección de la ganadería es obligatoria.";
            if (empty($codigoGanadero)) $errors[] = "El Código Ganadero es obligatorio.";
            if (empty($fechaRegistro)) $errors[] = "La fecha de registro es obligatoria.";

            // 2. Validación de formato de nombres y apellidos (letras y espacios)
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre del titular contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno del titular contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno del titular contiene caracteres inválidos.";
            
            // 3. Validación de email (formato válido)
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email de contacto es inválido.";
            }

            // 4. Validación de teléfono (exactamente 10 dígitos numéricos)
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }

            // 5. Validación de RFC (letras, números y guiones)
            if (!empty($identificacionFiscal) && !preg_match('/^[A-Za-z0-9\-]+$/', $identificacionFiscal)) {
                $errors[] = "El RFC contiene caracteres no válidos.";
            }

            // 6. Validación de nombre de ganadería y dirección (letras, números, espacios, etc.)
            if (!empty($nombre_ganaderia) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\#\°\,\-]+$/u', $nombre_ganaderia)) {
                $errors[] = "El nombre de la ganadería contiene caracteres inválidos.";
            }
            if (!empty($direccion) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\#\°\,\-]+$/u', $direccion)) {
                $errors[] = "La dirección de la ganadería contiene caracteres inválidos.";
            }

            // 7. Validación de Código Ganadero (solo letras y números)
            if (!empty($codigoGanadero) && !preg_match('/^[A-Za-z0-9]+$/', $codigoGanadero)) {
                $errors[] = "El Código Ganadero solo debe contener letras y números.";
            }
            // Puedes añadir una validación para duplicidad de codigoGanadero aquí si el modelo lo permite

            // 8. Validación de Fecha de Registro (formato, no futura)
            if (!empty($fechaRegistro)) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRegistro)) {
                    $errors[] = "El formato de la Fecha de Registro es inválido (debe ser AAAA-MM-DD).";
                } else {
                    list($y, $m, $d) = explode('-', $fechaRegistro);
                    if (!checkdate($m, $d, $y)) {
                        $errors[] = "La Fecha de Registro no es una fecha válida.";
                    } else {
                        $hoy = date('Y-m-d');
                        if ($fechaRegistro > $hoy) {
                            $errors[] = "La Fecha de Registro no puede ser futura.";
                        }
                    }
                }
            }
            
            // 9. Validación de estado (solo valores permitidos)
            $estados_permitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estados_permitidos)) {
                $errors[] = "El estado seleccionado no es válido.";
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=socios/create");
                exit;
            }

            // Preparar datos para guardar (todos los datos ya validados)
            $data = [
                 'nombre' => $nombre,
                 'apellido_paterno' => $apellido_paterno,
                 'apellido_materno' => $apellido_materno,
                 'nombre_ganaderia' => $nombre_ganaderia ?: null,
                 'direccion' => $direccion ?: null,
                 'codigoGanadero' => $codigoGanadero,
                 'telefono' => $telefono,
                 'email' => $email,
                 'fechaRegistro' => $fechaRegistro,
                 'estado' => $estado,
                 'id_usuario' => $id_usuario_registro,
                 'identificacion_fiscal_titular' => $identificacionFiscal ?: null
            ];

            // Intentar guardar el socio. El modelo Socio::store ya maneja errores de DB.
            $socioId = Socio::store($data);

            if($socioId !== false) {
                $_SESSION['message'] = "Socio registrado exitosamente con ID: " . $socioId . ".";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión si todo sale bien

                // Procesar Documentos Subidos (Si hay warnings, se añadirán a la sesión)
                $this->handleSocioDocumentUpload('id_oficial_file', $socioId, 'ID_OFICIAL_TITULAR', $id_usuario_registro);
                $this->handleSocioDocumentUpload('rfc_file', $socioId, 'CONSTANCIA_FISCAL', $id_usuario_registro);
                $this->handleSocioDocumentUpload('domicilio_file', $socioId, 'COMPROBANTE_DOM_GANADERIA', $id_usuario_registro);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $socioId, 'TITULO_PROPIEDAD_RANCHO', $id_usuario_registro);
                
                // Redirigir al índice después de éxito
                header("Location: index.php?route=socios_index");
                exit;

            } else {
                // Si hubo un error en el modelo (ej. duplicado, error de DB),
                // el modelo ya debería haber establecido un $_SESSION['error_details'].
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al guardar el socio. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']); // Limpiar los detalles específicos del error del modelo
                $_SESSION['error'] = "Error al registrar el socio: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=socios/create"); 
                exit; // Volver al formulario de creación con errores
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=socios/create"); 
            exit; 
        }
    }

    /**
     * Muestra la lista de todos los socios.
     */
    public function index() {
        check_permission(); // Se usa la función global para verificar permisos.
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
        check_permission(); // Se usa la función global para verificar permisos.
        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($socioId) {
            $socio = Socio::getById($socioId);
            if($socio) {
                // Si se cargan datos de un socio, usarlos para repoblar el formulario
                // Si hubo un error previo en update(), $_SESSION['form_data'] tendrá prioridad
                $formData = $_SESSION['form_data'] ?? $socio; // Repoblar con datos del socio o de la sesión si hubo error
                unset($_SESSION['form_data']); // Limpiar después de usarlos
                
                $documentosSocio = Documento::getByEntityId('socio', $socioId, true);
                $pageTitle = 'Editar Socio'; 
                $currentRoute = 'socios/edit';
                $contentView = __DIR__ . '/../views/socios/edit.php';
                // Pasar $formData y $documentosSocio a la vista
                require_once __DIR__ . '/../views/layouts/master.php';
                return;
            } else { 
                $_SESSION['error'] = "Socio no encontrado."; 
            }
        } else { 
            $_SESSION['error'] = "ID de socio no especificado o inválido.";
        }
        header("Location: index.php?route=socios_index"); 
        exit;
    }


    /**
     * Procesa los datos del formulario de edición, actualiza el socio y maneja nuevas subidas de documentos.
     */
    public function update() {
        check_permission(); // Se usa la función global para verificar permisos.
        $id_usuario_edicion = $_SESSION['user']['id_usuario']; // Usuario que edita

        // Recuperar el ID al inicio para poder redirigir a la misma página de edición en caso de error
        $id = filter_input(INPUT_POST, 'id_socio', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de socio inválido."; 
            header("Location: index.php?route=socios_index"); 
            exit;
        }
        // Guardar todos los datos POST en sesión para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST; 

        if(isset($_POST)) {
            // Recoger y limpiar datos
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

            // --- Validaciones (similar a store) ---
            $errors = [];

            // 1. Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre del titular es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno del titular es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno del titular es obligatorio.";
            if (empty($telefono)) $errors[] = "El teléfono de contacto es obligatorio.";
            if (empty($email)) $errors[] = "El email de contacto es obligatorio.";
            if (empty($identificacionFiscal)) $errors[] = "El RFC del titular es obligatorio.";
            if (empty($nombre_ganaderia)) $errors[] = "El nombre de la ganadería es obligatorio.";
            if (empty($direccion)) $errors[] = "La dirección de la ganadería es obligatoria.";
            if (empty($codigoGanadero)) $errors[] = "El Código Ganadero es obligatorio.";
            if (empty($fechaRegistro)) $errors[] = "La fecha de registro es obligatoria.";

            // 2. Validación de formato de nombres y apellidos (letras y espacios)
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre del titular contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno del titular contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno del titular contiene caracteres inválidos.";
            
            // 3. Validación de email (formato válido)
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email de contacto es inválido.";
            }

            // 4. Validación de teléfono (exactamente 10 dígitos numéricos)
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }

            // 5. Validación de RFC (letras, números y guiones)
            if (!empty($identificacionFiscal) && !preg_match('/^[A-Za-z0-9\-]+$/', $identificacionFiscal)) {
                $errors[] = "El RFC contiene caracteres no válidos.";
            }

            // 6. Validación de nombre de ganadería y dirección (letras, números, espacios, etc.)
            if (!empty($nombre_ganaderia) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\#\°\,\-]+$/u', $nombre_ganaderia)) {
                $errors[] = "El nombre de la ganadería contiene caracteres inválidos.";
            }
            if (!empty($direccion) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.\#\°\,\-]+$/u', $direccion)) {
                $errors[] = "La dirección de la ganadería contiene caracteres inválidos.";
            }

            // 7. Validación de Código Ganadero (solo letras y números)
            if (!empty($codigoGanadero) && !preg_match('/^[A-Za-z0-9]+$/', $codigoGanadero)) {
                $errors[] = "El Código Ganadero solo debe contener letras y números.";
            }

            // 8. Validación de Fecha de Registro (formato, no futura)
            if (!empty($fechaRegistro)) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRegistro)) {
                    $errors[] = "El formato de la Fecha de Registro es inválido (debe ser AAAA-MM-DD).";
                } else {
                    list($y, $m, $d) = explode('-', $fechaRegistro);
                    if (!checkdate($m, $d, $y)) {
                        $errors[] = "La Fecha de Registro no es una fecha válida.";
                    } else {
                        $hoy = date('Y-m-d');
                        if ($fechaRegistro > $hoy) {
                            $errors[] = "La Fecha de Registro no puede ser futura.";
                        }
                    }
                }
            } else {
                $fechaRegistro = null; // Si se permite NULL en la BD y no es obligatorio
            }
            
            // 9. Validación de estado (solo valores permitidos)
            $estados_permitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estados_permitidos)) {
                $errors[] = "El estado seleccionado no es válido.";
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=socios/edit&id=" . $id); 
                exit;
            }

            // Preparar datos para actualizar
            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'nombre_ganaderia' => $nombre_ganaderia ?: null,
                'direccion' => $direccion ?: null,
                'codigoGanadero' => $codigoGanadero,
                'telefono' => $telefono,
                'email' => $email,
                'fechaRegistro' => $fechaRegistro,
                'estado' => $estado,
                'id_usuario' => $id_usuario_edicion,
                'identificacion_fiscal_titular' => $identificacionFiscal ?: null
            ];

            // Intentar actualizar el socio. El modelo ya maneja errores de DB.
            if(Socio::update($id, $data)) {
                $_SESSION['message'] = "Socio actualizado exitosamente.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión

                // Procesar Documentos Subidos (Si hay warnings, se añadirán a la sesión)
                $this->handleSocioDocumentUpload('id_oficial_file', $id, 'ID_OFICIAL_TITULAR', $id_usuario_edicion);
                $this->handleSocioDocumentUpload('rfc_file', $id, 'CONSTANCIA_FISCAL', $id_usuario_edicion);
                $this->handleSocioDocumentUpload('domicilio_file', $id, 'COMPROBANTE_DOM_GANADERIA', $id_usuario_edicion);
                $this->handleSocioDocumentUpload('titulo_propiedad_file', $id, 'TITULO_PROPIEDAD_RANCHO', $id_usuario_edicion);
                
                // Redirigir al listado tras éxito
                header("Location: index.php?route=socios_index");
                exit;

            } else {
                // Si hubo un error en el modelo, mostrar error genérico o específico
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el socio. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al actualizar el socio: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=socios/edit&id=" . $id); 
                exit; // Volver al formulario de edición con errores
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=socios/edit&id=" . $id); 
            exit; 
        }
    }

    /**
     * Elimina un socio existente.
     */
    public function delete($id = null) {
        check_permission(); // Se usa la función global para verificar permisos.
        
        $socioId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($socioId) {
            if(Socio::delete($socioId)) { 
                $_SESSION['message'] = "Socio eliminado.";
            } else { 
                $_SESSION['error'] = "Error al eliminar socio. " . ($_SESSION['error_details'] ?? 'Puede que el socio tenga registros asociados o no exista.'); 
                unset($_SESSION['error_details']); 
            }
        } else { 
            $_SESSION['error'] = "ID inválido.";
        }
        header("Location: index.php?route=socios_index"); 
        exit;
    }
}