<?php
// app/controllers/EmpleadosController.php
require_once __DIR__ . '/../models/Empleado.php';

class EmpleadosController {

    // Helper para verificar sesión
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user'])) { header("Location: index.php?route=login"); exit; }
        // Opcional: Verificar Rol si solo admin gestiona empleados
        // if ($_SESSION['user']['rol'] !== 'superusuario') { ... }
    }

    /**
     * Muestra el formulario para crear un nuevo empleado.
     */
    public function create() {
        $this->checkSession();
        $pageTitle = 'Registrar Nuevo Empleado';
        $currentRoute = 'empleados/create';
        $contentView = __DIR__ . '/../views/empleados/create.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Procesa los datos del formulario de creación y guarda el nuevo empleado.
     */
    public function store() {
        $this->checkSession();

        if(isset($_POST)) {
            // Recoger datos
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
            if (empty($nombre)) $errors[] = "El nombre es obligatorio.";
            if (empty($apellido_paterno)) $errors[] = "Apellido paterno obligatorio.";
            if (empty($apellido_materno)) $errors[] = "Apellido materno obligatorio.";
            if (empty($puesto)) $errors[] = "El puesto es obligatorio.";

            // Validación de nombres (letras, espacios, acentos, ñ)
            if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "Nombre inválido.";
            if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "Apellido paterno inválido.";
            if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "Apellido materno inválido.";

            /// Validación de email (obligatorio y válido)
            if (empty($email)) {
                $errors[] = "El campo email es obligatorio.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email inválido.";
            }

            // Validación de teléfono (obligatorio y 10 dígitos)
            if (empty($telefono)) {
                $errors[] = "El campo teléfono es obligatorio.";
            } elseif (!preg_match('/^\d{10}$/', $telefono)) {
                $errors[] = "El teléfono debe tener exactamente 10 dígitos numéricos.";
            }

            // Validación de dirección (obligatoria)
            if (empty($direccion)) {
                $errors[] = "El campo dirección es obligatorio.";
            } else {
                // Si planeas mostrar esta dirección en HTML más adelante:
                $direccion = htmlspecialchars($direccion);
            }

            // Validación de puesto
            $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador'];
            if (!empty($puesto) && !in_array($puesto, $puestos_permitidos)) {
                $errors[] = "Puesto inválido.";
            }

            // Validación de fecha de ingreso
            if (!empty($fecha_ingreso)) {
                 if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
                      $errors[] = "Formato de Fecha de Ingreso inválido (Usar AAAA-MM-DD).";
                 } else {
                     list($y, $m, $d) = explode('-', $fecha_ingreso);
                     if (!checkdate($m, $d, $y)) {
                          $errors[] = "La Fecha de Ingreso no es una fecha válida.";
                     } else {
                          $hoy = date('Y-m-d');
                          if ($fecha_ingreso > $hoy) {
                              $errors[] = "La Fecha de Ingreso no puede ser futura.";
                          }
                          // Validacion de fecha minima 
                          $minDateString = date('Y-m-d', strtotime('-40 years')); // 40 años atrás
                          if ($fecha_ingreso < $minDateString) {
                               $errors[] = "La Fecha de Ingreso no puede ser anterior a " . date('d/m/Y', strtotime($minDateString)) . " (aprox. 40 años).";
                          }
                          
                     }
                 }
            } else {
                $fecha_ingreso = null; // Permitir NULL si no se ingresa
            }

            // --- Fin Validaciones ---

             if (!empty($errors)) {
                 $_SESSION['error'] = implode("<br>", $errors);
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=empleados/create");
                 exit;
             }

            // Preparar datos para guardar
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
                $_SESSION['error'] = "Error al crear el empleado. " . ($_SESSION['error_details'] ?? '');
                unset($_SESSION['error_details']);
                 $_SESSION['form_data'] = $_POST;
                 header("Location: index.php?route=empleados/create"); exit;
            }
        } else { $_SESSION['error'] = "No se recibieron datos."; }

        header("Location: index.php?route=empleados_index"); exit;
    }

    /**
     * Muestra la lista de todos los empleados.
     */
    public function index() {
        $this->checkSession();
        // Pasar $empleados a la vista master/content include
        $empleados = Empleado::getAll();
        $pageTitle = 'Listado de Empleados'; $currentRoute = 'empleados_index';
        $contentView = __DIR__ . '/../views/empleados/index.php';
        require_once __DIR__ . '/../views/layouts/master.php';
    }

    /**
     * Muestra el formulario para editar un empleado existente.
     */
    public function edit($id = null) {
        $this->checkSession();
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            // Pasar $empleado a la vista
            $empleado = Empleado::getById($empleadoId);
            if($empleado) {
                $pageTitle = 'Editar Empleado'; $currentRoute = 'empleados/edit';
                $contentView = __DIR__ . '/../views/empleados/edit.php';
                require_once __DIR__ . '/../views/layouts/master.php'; return;
            } else { $_SESSION['error'] = "Empleado no encontrado."; }
        } else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=empleados_index"); exit;
    }


    /**
     * Procesa los datos del formulario de edición y actualiza el empleado.
     */
    public function update() {
        $this->checkSession();
        if(isset($_POST['id'])) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) { $_SESSION['error'] = "ID inválido."; header("Location: index.php?route=empleados_index"); exit; }

            // Recoger datos
            $nombre = trim($_POST['nombre'] ?? ''); $apellido_paterno = trim($_POST['apellido_paterno'] ?? ''); $apellido_materno = trim($_POST['apellido_materno'] ?? ''); $email = trim($_POST['email'] ?? ''); $direccion = trim($_POST['direccion'] ?? ''); $telefono = trim($_POST['telefono'] ?? ''); $puesto = trim($_POST['puesto'] ?? ''); $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');

            // --- Validaciones (Igual que en store) ---
             $errors = [];
             if (empty($nombre)) $errors[] = "Nombre obligatorio."; if (empty($apellido_paterno)) $errors[]="Ap Paterno obligatorio."; if (empty($apellido_materno)) $errors[]="Ap Materno obligatorio."; if (empty($puesto)) $errors[] = "Puesto obligatorio.";
             if (!empty($nombre) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $nombre)) $errors[] = "Nombre inválido."; if (!empty($apellido_paterno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_paterno)) $errors[] = "Ap Paterno inválido."; if (!empty($apellido_materno) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', $apellido_materno)) $errors[] = "Ap Materno inválido.";
             if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Email inválido."; } elseif (empty($email)) { $email = null; }
             if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) { $errors[] = "Teléfono 10 dígitos."; } elseif (empty($telefono)) { $telefono = null; }
             if (empty($direccion)) $direccion = null;
             $puestos_permitidos = ['Administrativo', 'Mensajero', 'Gerente', 'Medico', 'Secretaria', 'Organizador']; if (!in_array($puesto, $puestos_permitidos)) { $errors[] = "Puesto inválido."; }
             // Validación de fecha de ingreso (incluyendo mínima)
             if (!empty($fecha_ingreso)) {
                  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) { $errors[] = "Formato Fecha Inválido."; }
                  else { list($y, $m, $d) = explode('-', $fecha_ingreso); if (!checkdate($m, $d, $y)) { $errors[] = "Fecha Inválida."; }
                  else { $hoy = date('Y-m-d'); if ($fecha_ingreso > $hoy) { $errors[] = "Fecha no puede ser futura."; }
                  // ****** NUEVA VALIDACIÓN DE FECHA MÍNIMA ******
                  $minDateString = date('Y-m-d', strtotime('-40 years'));
                  if ($fecha_ingreso < $minDateString) { $errors[] = "Fecha no puede ser anterior a " . date('d/m/Y', strtotime($minDateString)) . "."; }
                  // ****** FIN NUEVA VALIDACIÓN ******
                  }}
             } else { $fecha_ingreso = null; }
             // --- Fin Validaciones ---

             if (!empty($errors)) {
                 $_SESSION['error'] = implode("<br>", $errors);
                 header("Location: index.php?route=empleados/edit&id=" . $id); exit;
             }

            // Preparar datos
            $data = [
                'nombre' => $nombre, 'apellido_paterno' => $apellido_paterno, 'apellido_materno' => $apellido_materno,
                'email' => $email, 'direccion' => $direccion, 'telefono' => $telefono,
                'puesto' => $puesto, 'fecha_ingreso' => $fecha_ingreso
            ];

            if(Empleado::update($id, $data)) {
                $_SESSION['message'] = "Empleado actualizado exitosamente.";
            } else {
                $_SESSION['error'] = "Error al actualizar. " . ($_SESSION['error_details'] ?? ''); unset($_SESSION['error_details']);
                header("Location: index.php?route=empleados/edit&id=" . $id); exit; // Volver a edit en error
            }
        } else {
            $_SESSION['error'] = "Datos inválidos.";
        }
        // Redirigir al listado tras éxito
        header("Location: index.php?route=empleados_index"); exit;
    }

    /**
     * Elimina un empleado existente.
     */
    public function delete($id = null) {
        $this->checkSession();
        $empleadoId = $id ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if($empleadoId) {
            if(Empleado::delete($empleadoId)) { $_SESSION['message'] = "Empleado eliminado."; }
            else { $_SESSION['error'] = "Error al eliminar. " . ($_SESSION['error_details'] ?? ''); unset($_SESSION['error_details']); }
        } else { $_SESSION['error'] = "ID inválido."; }
        header("Location: index.php?route=empleados_index"); exit;
    }
} // Fin clase
?>