
<?php
// app/views/auth/login.php
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="login-body" >
    <div class="login-container">
        <img src="<?php echo BASE_URL; ?>/assets/img/ancceBack.png" alt="Logo ANCCEMEX" class="login-logo">
        <h1>Iniciar Sesión</h1>
        <?php
        
        if(isset($_SESSION['error'])){
            echo "<div class='alert alert-error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
            unset($_SESSION['error']); // Limpiar el error después de mostrarlo
        }
        
        ?>
        <form action="index.php?route=authenticate" method="POST">
            <label for="username">Usuario:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>