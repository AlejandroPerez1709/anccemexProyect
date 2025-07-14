<?php
// app/controllers/EmpleadosController.php
require_once __DIR__ . '/../models/Empleado.php';
// Se incluye config.php para usar la función global check_permission()
require_once __DIR__ . '/../../config/config.php';

class EmpleadosController {

    // El método checkSession() fue eliminado previamente y reemplazado por check_permission()

    /**
     * Muestra el formulario para crear un nuevo empleado.
     */
    public function create() {
        check_permission(); // Se usa la función estándar.
        // Recuperar datos del formulario si hubo un error de validación previo para repoblar
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); // Limpiar después de recuperarlos

        $pageTitle = 'Registrar Nuevo Empleado';
        $currentRoute = 'empleados/create';
        $contentView = __DIR__ . '/../views/empleados/create.php';
        // Pasar $formData a la vista a través del layout
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario de creación y guarda el nuevo empleado.
     */
    public function store() {
        check_permission();
        
        // Guardar todos los datos POST en sesión al inicio para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $puesto = trim($_POST['puesto'] ?? '');
            $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? ''); // Fecha puede venir vacía

            // --- Validaciones del Lado del Servidor ---
            $errors = [];

            // Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
            if (empty($email)) $errors[] = "El email es obligatorio.";
            if (empty($direccion)) $errors[] = "La dirección es obligatoria.";
            if (empty($telefono)) $errors[] = "El teléfono es obligatorio.";
            if (empty($puesto)) $errors[] = "El puesto es obligatorio.";

            // Validación de formato de nombres y apellidos (solo letras y espacios)
            // Se agregó el modificador 'u' para soporte UTF-8 (acentos, ñ)
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            
            // Validación de email (formato válido)
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email es inválido.";
            }

            // Validación de teléfono (exactamente 10 dígitos numéricos)
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }

            // Validación de puesto (solo valores permitidos)
            $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
            if (!empty($puesto) && !in_array($puesto, $puestos_permitidos)) {
                $errors[] = "El puesto seleccionado no es válido.";
            }

            // Validación de fecha de ingreso (formato, no futura, no muy antigua)
            if (!empty($fecha_ingreso)) {
                 if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
                      $errors[] = "El formato de la Fecha de Ingreso es inválido (debe ser AAAA-MM-DD).";
                 } else {
                     list($y, $m, $d) = explode('-', $fecha_ingreso);
                     if (!checkdate($m, $d, $y)) {
                          $errors[] = "La Fecha de Ingreso no es una fecha válida.";
                     } else {
                          $hoy = date('Y-m-d');
                          if ($fecha_ingreso > $hoy) {
                              $errors[] = "La Fecha de Ingreso no puede ser futura.";
                          }
                          $minDateString = date('Y-m-d', strtotime('-40 years')); // 40 años atrás
                          if ($fecha_ingreso < $minDateString) {
                               $errors[] = "La Fecha de Ingreso no puede ser anterior a " . date('d/m/Y', strtotime($minDateString)) . " (aprox. 40 años).";
                          }
                     }
                 }
            } else {
                $fecha_ingreso = null; // Si no se ingresa, se permite NULL en la BD
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                 $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                 header("Location: index.php?route=empleados/create");
                 exit;
             }

            // Preparar datos para guardar (todos los datos ya validados)
            $data = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'email' => $email,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'puesto' => $puesto,
                'fecha_ingreso' => $fecha_ingreso // Puede ser null
            ];

            // Intentar guardar el empleado. El modelo Empleado::store ya maneja errores de DB.
            if(Empleado::store($data)) {
                $_SESSION['message'] = "Empleado creado exitosamente.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión si todo sale bien
            } else {
                // Si hubo un error en el modelo (ej. duplicado de email, error de DB),
                // el modelo ya debería haber establecido un $_SESSION['error_details'].
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al guardar el empleado. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']); // Limpiar los detalles específicos del error del modelo
                $_SESSION['error'] = "Error al crear el empleado: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=empleados/create"); 
                exit;
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=empleados/create"); 
            exit; 
        }

        // Redirigir al listado si todo fue exitoso
        header("Location: index.php?route=empleados_index"); 
        exit;
    }

    /**
     * Muestra la lista de todos los empleados.
     */
    public function index() {
        check_permission();
        
        $empleados = Empleado::getAll();
        $pageTitle = 'Listado de Empleados';
        $currentRoute = 'empleados_index';
        $contentView = __DIR__ . '/../views/empleados/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un empleado existente.
     */
    public function edit($id = null) {
        check_permission();
        
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            $empleado = Empleado::getById($empleadoId);
            if($empleado) {
                // Si se cargan datos de un empleado, usarlos para repoblar el formulario
                // Si hubo un error previo en update(), $_SESSION['form_data'] tendrá prioridad
                $formData = $_SESSION['form_data'] ?? $empleado; // Repoblar con datos del empleado o de la sesión si hubo error
                unset($_SESSION['form_data']); // Limpiar después de usarlos
                
                $pageTitle = 'Editar Empleado';
                $currentRoute = 'empleados/edit';
                $contentView = __DIR__ . '/../views/views/empleados/edit.php'; // Notar la ruta corregida a la carpeta 'views'
                // Pasar $formData a la vista para repoblar el formulario
                require_once __DIR__ . '/../views/layouts/master.php'; 
                return;
            } else { 
                $_SESSION['error'] = "Empleado no encontrado."; 
            }
        } else { 
            $_SESSION['error'] = "ID inválido.";
        }
        header("Location: index.php?route=empleados_index"); 
        exit;
    }


    /**
     * Procesa los datos del formulario de edición y actualiza el empleado.
     */
    public function update() {
        check_permission();
        
        // Recuperar el ID al inicio para poder redirigir a la misma página de edición en caso de error
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de empleado inválido."; 
            header("Location: index.php?route=empleados_index"); // Redirigir a la lista si el ID es nulo o inválido
            exit;
        }
        // Guardar todos los datos POST en sesión para repoblar el formulario si hay errores
        $_SESSION['form_data'] = $_POST; 

        if(isset($_POST)) {
            // Recoger y limpiar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? ''); 
            $apellido_materno = trim($_POST['apellido_materno'] ?? ''); 
            $email = trim($_POST['email'] ?? ''); 
            $direccion = trim($_POST['direccion'] ?? '');
            $telefono = trim($_POST['telefono'] ?? ''); 
            $puesto = trim($_POST['puesto'] ?? ''); 
            $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');

            // --- Validaciones (igual que en store, adaptadas a edición) ---
            $errors = [];

            // Validaciones de campos obligatorios
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
            if (empty($email)) $errors[] = "El email es obligatorio.";
            if (empty($direccion)) $errors[] = "La dirección es obligatoria.";
            if (empty($telefono)) $errors[] = "El teléfono es obligatorio.";
            if (empty($puesto)) $errors[] = "El puesto es obligatorio.";

            // Validación de formato de nombres y apellidos
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            
            // Validación de email
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email es inválido.";
            }

            // Validación de teléfono
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }

            // Validación de puesto
            $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
            if (!empty($puesto) && !in_array($puesto, $puestos_permitidos)) {
                $errors[] = "El puesto seleccionado no es válido.";
            }

            // Validación de fecha de ingreso
            if (!empty($fecha_ingreso)) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
                    $errors[] = "El formato de la Fecha de Ingreso es inválido (debe ser AAAA-MM-DD).";
                } else {
                    list($y, $m, $d) = explode('-', $fecha_ingreso);
                    if (!checkdate($m, $d, $y)) {
                        $errors[] = "La Fecha de Ingreso no es una fecha válida.";
                    } else {
                        $hoy = date('Y-m-d');
                        if ($fecha_ingreso > $hoy) {
                            $errors[] = "La Fecha de Ingreso no puede ser futura.";
                        }
                        $minDateString = date('Y-m-d', strtotime('-40 years'));
                        if ($fecha_ingreso < $minDateString) {
                            $errors[] = "La Fecha de Ingreso no puede ser anterior a " . date('d/m/Y', strtotime($minDateString)) . ".";
                        }
                    }
                }
            } else {
                $fecha_ingreso = null;
            }

            // Si hay errores, guardarlos en sesión y redirigir
            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=empleados/edit&id=" . $id); 
                exit;
            }

            // Preparar datos para actualizar
            $data = [
                'nombre' => $nombre, 
                'apellido_paterno' => $apellido_paterno, 
                'apellido_materno' => $apellido_materno,
                'email' => $email, 
                'direccion' => $direccion, 
                'telefono' => $telefono,
                'puesto' => $puesto, 
                'fecha_ingreso' => $fecha_ingreso
            ];

            // Intentar actualizar el empleado. El modelo ya maneja errores de DB.
            if(Empleado::update($id, $data)) {
                $_SESSION['message'] = "Empleado actualizado exitosamente.";
                unset($_SESSION['form_data']); // Limpiar datos del formulario en sesión si todo sale bien
            } else {
                // Si hubo un error en el modelo, mostrar error genérico o específico
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el empleado. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al actualizar el empleado: " . $error_detail;
                // Mantener $_SESSION['form_data'] para que el formulario se repueble
                header("Location: index.php?route=empleados/edit&id=" . $id); 
                exit;
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=empleados/edit&id=" . $id); 
            exit; 
        }

        // Redirigir al listado tras éxito
        header("Location: index.php?route=empleados_index"); 
        exit;
    }

    /**
     * Elimina un empleado existente.
     */
    public function delete($id = null) {
        check_permission();
        
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            if(Empleado::delete($empleadoId)) { 
                $_SESSION['message'] = "Empleado eliminado.";
            } else { 
                $_SESSION['error'] = "Error al eliminar. " . ($_SESSION['error_details'] ?? 'Puede que el empleado tenga registros asociados o no exista.'); 
                unset($_SESSION['error_details']); 
            }
        } else { 
            $_SESSION['error'] = "ID inválido.";
        }
        header("Location: index.php?route=empleados_index"); 
        exit;
    }
}