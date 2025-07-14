<?php
// public/captcha.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start(); // Inicia la sesión para acceder al código Captcha

// Asegurarse de que el código Captcha esté en la sesión
if (!isset($_SESSION['captcha_code'])) {
    header('Location: index.php?route=login');
    exit;
}

$captcha_code = $_SESSION['captcha_code'];

// Dimensiones de la imagen
$width = 150; // Ancho un poco mayor para letras más grandes
$height = 50; // Alto un poco mayor

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
$font_size_px = 28; // Puedes probar con 28, 30, 32, etc.

// Ángulo del texto (0 para horizontal)
$angle = 0;

// Calcular posición para centrar el texto
$textbox = imagettfbbox($font_size_px, $angle, $font_path, $captcha_code);

// --- CAMBIOS AQUI: Ajuste en el cálculo de 'y' para centrar mejor el texto ---
// $x ya lo calculaba bien horizontalmente
$x = ($width - ($textbox[2] - $textbox[0])) / 2;

// Ajuste de 'y' para mover el texto hacia arriba o hacia abajo.
// Restamos el punto 'y' más bajo del bounding box (caja del texto) para obtener la altura total del texto,
// y luego ajustamos. Un valor negativo en el final de la línea lo sube.
// Prueba con diferentes valores en el '- X' al final de la línea si no queda centrado.
$y = ($height / 2) + ($font_size_px / 2) - 5; // Un ajuste común, puedes probar -7, -10, etc.

// Dibuja el texto usando la fuente TrueType
imagettftext($image, $font_size_px, $angle, $x, $y, $text_color, $font_path, $captcha_code);


// Establecer el tipo de contenido como imagen PNG
header('Content-type: image/png');

// Enviar la imagen al navegador
imagepng($image);

// Liberar memoria
imagedestroy($image);
?>