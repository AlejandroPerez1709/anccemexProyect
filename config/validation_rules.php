<?php
// config/validation_rules.php

/**
 * Almacena todos los conjuntos de reglas de validación para la aplicación.
 * La clave del array es el nombre del conjunto de reglas (ej. 'crear_socio').
 * El valor es un array asociativo donde la clave es el nombre del campo del formulario
 * y el valor es un string con las reglas separadas por '|'.
 *
 * Reglas disponibles:
 * - required: El campo no puede estar vacío.
 * - email: Debe ser un formato de email válido.
 * - min:x: Longitud mínima de x caracteres.
 * - text: Solo letras y espacios.
 * - alphanumeric: Solo letras y números, sin espacios.
 * - numeric: Debe ser un valor numérico.
 * - length:x: Debe tener exactamente x caracteres.
 * - unique:tabla,columna: El valor debe ser único en la `columna` de la `tabla` especificada.
 */

return [
    'crear_usuario' => [
        'nombre'           => 'required|text',
        'apellido_paterno' => 'required|text',
        'apellido_materno' => 'required|text',
        'email'            => 'required|email|unique:usuarios,email',
        'username'         => 'required|min:4|unique:usuarios,username',
        'password'         => 'required|min:8',
        'rol'              => 'required',
        'estado'           => 'required'
    ],
    
    'actualizar_usuario' => [
        'nombre'           => 'required|text',
        'apellido_paterno' => 'required|text',
        'apellido_materno' => 'required|text',
        'email'            => 'required|email', // La regla 'unique' se añadirá dinámicamente en el controlador
        'username'         => 'required|min:4',   // La regla 'unique' se añadirá dinámicamente
        'password'         => 'min:8',           // No es requerido, pero si se envía, debe tener mínimo 8 caracteres
        'rol'              => 'required',
        'estado'           => 'required'
    ],

    'crear_socio' => [
        'nombre'                      => 'required|text',
        'apellido_paterno'            => 'required|text',
        'apellido_materno'            => 'required|text',
        'email'                       => 'required|email|unique:socios,email',
        'codigoGanadero'              => 'required|alphanumeric|unique:socios,codigoGanadero',
        'telefono'                    => 'required|numeric|length:10',
        'identificacion_fiscal_titular' => 'required' // Podríamos crear una regla 'rfc' si fuera necesario
    ],

    'actualizar_socio' => [
        'nombre'                      => 'required|text',
        'apellido_paterno'            => 'required|text',
        'apellido_materno'            => 'required|text',
        'email'                       => 'required|email',
        'codigoGanadero'              => 'required|alphanumeric',
        'telefono'                    => 'required|numeric|length:10',
        'identificacion_fiscal_titular' => 'required'
    ],

    'crear_medico' => [
        'nombre'                    => 'required|text',
        'apellido_paterno'          => 'required|text',
        'apellido_materno'          => 'required|text',
        'email'                     => 'required|email|unique:medicos,email',
        'numero_cedula_profesional' => 'required|alphanumeric|unique:medicos,numero_cedula_profesional',
        'telefono'                  => 'required|numeric|length:10',
        'entidad_residencia'        => 'required'
    ],

    'actualizar_medico' => [
        'nombre'                    => 'required|text',
        'apellido_paterno'          => 'required|text',
        'apellido_materno'          => 'required|text',
        'email'                     => 'required|email',
        'numero_cedula_profesional' => 'required|alphanumeric',
        'telefono'                  => 'required|numeric|length:10',
        'entidad_residencia'        => 'required'
    ],

    'crear_ejemplar' => [
        'nombre'   => 'required',
        'socio_id' => 'required|numeric',
        'sexo'     => 'required'
    ],

    'actualizar_ejemplar' => [
        'nombre'   => 'required',
        'socio_id' => 'required|numeric',
        'sexo'     => 'required'
    ],

    'crear_servicio' => [
        'tipo_servicio_id' => 'required|numeric',
        'socio_id'         => 'required|numeric',
        'ejemplar_id'      => 'required|numeric',
        'fechaSolicitud'   => 'required'
    ],
    'crear_empleado' => [
        'nombre'           => 'required|text',
        'apellido_paterno' => 'required|text',
        'apellido_materno' => 'required|text',
        'email'            => 'required|email|unique:empleados,email',
        'direccion'        => 'required',
        'telefono'         => 'required|numeric|length:10',
        'puesto'           => 'required',
        'fecha_ingreso'    => 'required'
    ],

    'actualizar_empleado' => [
        'nombre'           => 'required|text',
        'apellido_paterno' => 'required|text',
        'apellido_materno' => 'required|text',
        'email'            => 'required|email', // La regla 'unique' se añade dinámicamente
        'direccion'        => 'required',
        'telefono'         => 'required|numeric|length:10',
        'puesto'           => 'required',
        'fecha_ingreso'    => 'required'
    ]

    
];