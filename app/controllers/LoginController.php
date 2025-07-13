<?php
// app/controllers/LoginController.php

class LoginController {

    public function index() {
        require_once '../app/views/login/index.php';
    }

    public function authenticate() {
        session_start();
        
        if(isset($_POST['username']) && isset($_POST['password'])){
            $username = $_POST['username'];
            $password = $_POST['password'];

            $conn = dbConnect();

            // Consulta segura para obtener el usuario activo
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? AND estado = 'activo' LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Comparación directa de contraseñas (sin hash) para pruebas
                if($password === $user['password']) {
                    $_SESSION['user'] = $user;
                    
                    // Actualiza el campo ultimo_login
                    $updateStmt = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?");
                    $updateStmt->bind_param("i", $user['id_usuario']);
                    $updateStmt->execute();

                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Credenciales incorrectas.";
                }
            } else {
                $error = "Usuario no encontrado o inactivo.";
            }
            $stmt->close();
            $conn->close();
        } else {
            $error = "Por favor, ingresa username y password.";
        }

        $_SESSION['error'] = $error;
        header("Location: login.php");
        exit;
    }
}
