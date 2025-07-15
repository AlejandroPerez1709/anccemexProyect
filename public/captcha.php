<?php
// public/captcha.php

// Asegúrate que no haya espacios en blanco ni líneas antes de este <?php

// DESACTIVAMOS TEMPORALMENTE LA VISUALIZACIÓN DE ERRORES para producción
// No se deben dejar estos ini_set para mostrar errores en un entorno real.
// Si necesitas depurar en vivo, usa error_log o revisa los logs del servidor.
ini_set('display_errors', 0); // Desactivar la muestra de errores en pantalla
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Mantener el reporte de errores para que se guarden en el log

session_start(); // Inicia la sesión para acceder al código Captcha

// Asegurarse de que el código Captcha esté en la sesión
if (!isset($_SESSION['captcha_code'])) {
    header('Location: index.php?route=login');
    exit;
}

$captcha_code = $_SESSION['captcha_code'];

// Dimensiones de la imagen
$width = 150; 
$height = 50; 

// Crear una imagen en blanco
$image = imagecreatetruecolor($width, $height);

// Colores
$background_color = imagecolorallocate($image, 255, 255, 255); // Blanco
$text_color = imagecolorallocate($image, 0, 0, 0); // Negro
$noise_color = imagecolorallocate($image, 150, 150, 150); // Gris

// Rellenar el fondo
imagefill($image, 0, 0, $background_color);

// Añadir ruido (puntos)
for ($i = 0; $i < 500; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Añadir ruido (líneas)
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noise_color);
}

// Ruta a tu archivo .ttf. Asegúrate que 'playfair.ttf' sea el nombre exacto que le diste.
$font_path = __DIR__ . '/fonts/playfair.ttf'; 

// Tamaño de la fuente en píxeles (puedes ajustar este número para hacerlo más grande o pequeño)
$font_size_px = 28; 

// Ángulo del texto (0 para horizontal)
$angle = 0;

// Calcular posición para centrar el texto
$textbox = imagettfbbox($font_size_px, $angle, $font_path, $captcha_code);

// CORREGIDO: Redondear los valores a enteros explícitamente para evitar la advertencia "Deprecated"
// y asegurar que las coordenadas sean siempre números enteros.
$x = (int) (($width - ($textbox[2] - $textbox[0])) / 2); // Conversión explícita a int
$y = (int) (($height / 2) + ($font_size_px / 2) - 5);    // Conversión explícita a int (línea 64)

// Dibuja el texto usando la fuente TrueType
imagettftext($image, $font_size_px, $angle, $x, $y, $text_color, $font_path, $captcha_code);


// Establecer el tipo de contenido como imagen PNG
header('Content-type: image/png'); // Línea 68

// Enviar la imagen al navegador
imagepng($image);

// Liberar memoria
imagedestroy($image);
?>