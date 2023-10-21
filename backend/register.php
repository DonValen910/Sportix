<?php

/**
 * Este archivo maneja el registro de usuarios. 
 * Recibe los datos del formulario de registro, valida los datos y crea un nuevo usuario en la base de datos.
 * Si hay errores en la validación, se almacenan en la sesión y se redirige al usuario a la página de registro.
 * Si el registro es exitoso, se envía un correo electrónico de confirmación y se redirige al usuario a la página de inicio.
 */

if($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibe los datos del formulario de registro
    $username   = $_POST["username"];
    $password   = $_POST["password"];
    $confPass   = $_POST["confPass"];
    $email      = $_POST["email"];

    try {
        require_once 'includes/dbconn.inc.php';
        require_once 'register_model.php';
        require_once 'register_control.php';

        
        // Manejadores de Errores
        $errors = [];

        // Valida los datos del formulario
        if (is_input_empty($username, $password, $confPass, $email)) {
            $errors["empty_input"] = "Llena todos los campos!";
        } 
        if (is_email_invalid($email)) 
        {
            $errors["invalid_email"] = "Email invalido!";
        } 
        if (is_username_taken($mysqli, $username)) 
        {
            $errors["username_taken"] = "Nombre de usuario ya existe!";
        }
        if (is_email_registered($mysqli, $email)) {
            $errors["email_registered"] = "Email ya usado!";
        }
        if ($password !== $confPass) {
            $errors["password_mismatch"] = "Contraseñas no coinciden!";
        }

        require_once 'includes/config_session.inc.php';

        // Escapa los datos del formulario para prevenir inyección de SQL
        $username = mysqli_real_escape_string($mysqli, $username);
        $password = mysqli_real_escape_string($mysqli, $password);
        $confPass = mysqli_real_escape_string($mysqli, $confPass);
        $email    = mysqli_real_escape_string($mysqli, $email);

        // Si hay errores en la validación, se almacenan en la sesión y se redirige al usuario a la página de registro.
        if ($errors) {
            $_SESSION["errors_register"] = $errors;

            $regData = [
                "username" => $username,
                "password" => $password,
                "confPass" => $confPass,
                "email"    => $email
            ];
            $_SESSION["reg_data"] = $regData;

            header("Location: ../register.php");
            die();
        }

        // Crea un nuevo usuario en la base de datos
        create_user($mysqli, $username, $password, $email);
        // Enviar mail de confirmacion
        send_email($mysqli, $email, $username);

        // Si el registro es exitoso, se envía un correo electrónico de confirmación y se redirige al usuario a la página de inicio.
        header("Location: ../index.php?register=success");

        $mysqli = null;
        $stmt = null;

        die();
    } catch (mysqli_sql_exception $e) {
        die("Query failed: " . $e->getMessage());
    }

} else {
    header("Location: ../register.php");
    die();
}
?>