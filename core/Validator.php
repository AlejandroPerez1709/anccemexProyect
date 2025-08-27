<?php
// core/Validator.php

class Validator {
    
    /**
     * @var array Almacena los errores de validación.
     */
    private $errors = [];

    /**
     * @var array Almacena los mensajes de error predeterminados para cada regla.
     */
    private $messages = [
        'required'    => 'El campo :field es obligatorio.',
        'email'       => 'El campo :field debe ser una dirección de correo válida.',
        'min'         => 'El campo :field debe tener al menos :rule caracteres.',
        'text'        => 'El campo :field solo puede contener letras y espacios.',
        'alphanumeric'=> 'El campo :field solo puede contener letras y números.',
        'numeric'     => 'El campo :field debe ser numérico.',
        'length'      => 'El campo :field debe tener exactamente :rule caracteres.',
        'unique'      => 'El valor del campo :field ya existe en la base de datos.'
    ];

    /**
     * @var mysqli Conexión a la base de datos.
     */
    private $db;

    /**
     * Constructor que establece la conexión a la base de datos.
     */
    public function __construct() {
        $this->db = dbConnect();
    }

    /**
     * Método estático principal para ejecutar la validación.
     *
     * @param array $data Los datos a validar (ej. $_POST).
     * @param array $rules Las reglas de validación para los datos.
     * @return array Un array de errores. Vacío si la validación es exitosa.
     */
    public static function validate($data, $rules) {
        $instance = new self();
        return $instance->runValidation($data, $rules);
    }

    /**
     * Ejecuta el ciclo de validación.
     *
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function runValidation($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $rulesArray = explode('|', $fieldRules);

            // --- INICIO DE LA CORRECCIÓN CLAVE ---
            // Si el campo está vacío y NO es requerido, no se aplican más reglas a este campo.
            // Esto permite que campos opcionales (como la contraseña al editar) pasen la validación si se dejan en blanco.
            if (empty(trim((string)$value)) && !in_array('required', $rulesArray)) {
                continue; // Saltar al siguiente campo del formulario.
            }
            // --- FIN DE LA CORRECCIÓN CLAVE ---

            foreach ($rulesArray as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramStr) = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $methodName = 'validate' . ucfirst($rule);
                
                if (method_exists($this, $methodName)) {
                    if (!$this->$methodName($field, $value, $params)) {
                        $this->addError($field, $rule, $params);
                        break; 
                    }
                }
            }
        }
        return $this->errors;
    }

    /**
     * Añade un mensaje de error al array de errores.
     *
     * @param string $field
     * @param string $rule
     * @param array $params
     */
    private function addError($field, $rule, $params = []) {
        $message = $this->messages[$rule] ?? 'Error de validación en el campo :field.';
        $message = str_replace(':field', ucfirst(str_replace('_', ' ', $field)), $message);
        if (!empty($params)) {
            $message = str_replace(':rule', $params[0], $message);
        }
        $this->errors[$field][] = $message;
    }

    // --- MÉTODOS DE VALIDACIÓN INDIVIDUALES ---

    private function validateRequired($field, $value, $params) {
        return !empty(trim((string)$value));
    }

    private function validateEmail($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin($field, $value, $params) {
        return strlen(trim((string)$value)) >= $params[0];
    }

    private function validateText($field, $value, $params) {
        return preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $value);
    }

    private function validateAlphanumeric($field, $value, $params) {
        return preg_match('/^[a-zA-Z0-9]+$/', $value);
    }
    
    private function validateNumeric($field, $value, $params) {
        return is_numeric($value);
    }

    private function validateLength($field, $value, $params) {
        return strlen(trim((string)$value)) == $params[0];
    }
    
    private function validateUnique($field, $value, $params) {
        if (!$this->db) return false;

        $table = $params[0];
        $column = $params[1] ?? $field;
        $ignoreId = $params[2] ?? null;

        $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` = ?";
        $types = "s";
        $queryParams = [$value];

        if ($ignoreId) {
            // Asumimos que la columna del ID es 'id_TABLA' por convención.
            $idColumn = 'id_' . rtrim($table, 's');
            // Corregimos para el caso especial de usuarios
            if ($table === 'usuarios') {
                $idColumn = 'id_usuario';
            }
            $sql .= " AND `{$idColumn}` != ?";
            $types .= "i";
            $queryParams[] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$queryParams);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result['count'] == 0;
    }

    /**
     * Destructor para cerrar la conexión a la base de datos.
     */
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}