<?php
// app/controllers/EmpleadosController.php
require_once __DIR__ . '/../models/Empleado.php';
// Se incluye config.php para usar la función global check_permission()
require_once __DIR__ . '/../../config/config.php';

class EmpleadosController {

    /**
     * Muestra el formulario para crear un nuevo empleado.
     */
    public function create() {
        check_permission(); 
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']); 

        $pageTitle = 'Registrar Nuevo Empleado';
        $currentRoute = 'empleados/create';
        $contentView = __DIR__ . '/../views/empleados/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario de creación y guarda el nuevo empleado.
     */
    public function store() {
        check_permission();
        $_SESSION['form_data'] = $_POST;

        if(isset($_POST)) {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
            $apellido_materno = trim($_POST['apellido_materno'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $puesto = trim($_POST['puesto'] ?? '');
            $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');

            $errors = [];
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
            if (empty($email)) $errors[] = "El email es obligatorio.";
            if (empty($direccion)) $errors[] = "La dirección es obligatoria.";
            if (empty($telefono)) $errors[] = "El teléfono es obligatorio.";
            if (empty($puesto)) $errors[] = "El puesto es obligatorio.";
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email es inválido.";
            }
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }
            $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
            if (!empty($puesto) && !in_array($puesto, $puestos_permitidos)) {
                $errors[] = "El puesto seleccionado no es válido.";
            }
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
                               $errors[] = "La Fecha de Ingreso no puede ser anterior a " . date('d/m/Y', strtotime($minDateString)) . " (aprox. 40 años).";
                          }
                     }
                 }
            } else {
                $fecha_ingreso = null;
            }

            if (!empty($errors)) {
                 $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
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
                'fecha_ingreso' => $fecha_ingreso
            ];

            if(Empleado::store($data)) {
                $_SESSION['message'] = "Empleado creado exitosamente.";
                unset($_SESSION['form_data']);
            } else {
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al guardar el empleado. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al crear el empleado: " . $error_detail;
                header("Location: index.php?route=empleados/create"); 
                exit;
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=empleados/create"); 
            exit; 
        }

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
                $formData = $_SESSION['form_data'] ?? $empleado; 
                unset($_SESSION['form_data']); 
                
                $pageTitle = 'Editar Empleado';
                $currentRoute = 'empleados/edit';
                // CORREGIDO: Ruta de la vista "empleados/edit.php"
                $contentView = __DIR__ . '/../views/empleados/edit.php'; 
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
        
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { 
            $_SESSION['error'] = "ID de empleado inválido."; 
            header("Location: index.php?route=empleados_index"); 
            exit;
        }
        $_SESSION['form_data'] = $_POST; 

        if(isset($_POST)) {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido_paterno = trim($_POST['apellido_paterno'] ?? ''); 
            $apellido_materno = trim($_POST['apellido_materno'] ?? ''); 
            $email = trim($_POST['email'] ?? ''); 
            $direccion = trim($_POST['direccion'] ?? '');
            $telefono = trim($_POST['telefono'] ?? ''); 
            $puesto = trim($_POST['puesto'] ?? ''); 
            $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');

            $errors = [];
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "El apellido paterno es obligatorio.";
            if (empty($apellido_materno)) $errors[] = "El apellido materno es obligatorio.";
            if (empty($email)) $errors[] = "El email es obligatorio.";
            if (empty($direccion)) $errors[] = "La dirección es obligatoria.";
            if (empty($telefono)) $errors[] = "El teléfono es obligatorio.";
            if (empty($puesto)) $errors[] = "El puesto es obligatorio.";
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "El nombre contiene caracteres inválidos.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "El apellido paterno contiene caracteres inválidos.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "El apellido materno contiene caracteres inválidos.";
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El formato del email es inválido.";
            }
            if (!empty($telefono) && !preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe contener exactamente 10 dígitos numéricos.";
            }
            $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
            if (!empty($puesto) && !in_array($puesto, $puestos_permitidos)) {
                $errors[] = "El puesto seleccionado no es válido.";
            }
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

            if (!empty($errors)) {
                $_SESSION['error'] = "Se encontraron los siguientes problemas:<br>" . implode("<br>", $errors);
                header("Location: index.php?route=empleados/edit&id=" . $id); 
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
                'fecha_ingreso' => $fecha_ingreso
            ];

            if(Empleado::update($id, $data)) {
                $_SESSION['message'] = "Empleado actualizado exitosamente.";
                unset($_SESSION['form_data']);
            } else {
                $error_detail = $_SESSION['error_details'] ?? 'Error desconocido al actualizar el empleado. Verifique los datos o intente más tarde.';
                unset($_SESSION['error_details']);
                $_SESSION['error'] = "Error al actualizar el empleado: " . $error_detail;
                header("Location: index.php?route=empleados/edit&id=" . $id); 
                exit;
            }
        } else { 
            $_SESSION['error'] = "No se recibieron datos del formulario.";
            header("Location: index.php?route=empleados/edit&id=" . $id); 
            exit; 
        }

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