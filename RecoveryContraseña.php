<?php
    /*
    Acceso a las funciones de las librerias propias y composer.
    */
    require_once ("libs/tools.php");
    require_once ("libs/db.php");
    require_once ("vendor/autoload.php");


    /*
    Llamado a la libreria JSON Web Tokens.
    */
    use Firebase\JWT\JWT;

    /*
    Llamado a las funciones de seguridad.
    */
    sesionSegura();
    limpiarEntradas();


    /*  
    Verificación de variables para anti CSRF.
    */
    if (isset($_POST["csrf"])) {
        
        /*  
        Comparar la variable de la sesión con la enviada.
        */
        if ($_POST["csrf"] != $_SESSION["csrf"]) {
        
            header("Location: Login.php");  //Redireccionar al Login.
            exit;
        }
    }


    /*  
    Verificación de que el usuario este logueado.
    */
    if (isset($_SESSION["User"])) {
        
        header("location: Index.php");  //Redireccionar al Login.
    }


    /*  
    Verificación de que el usuario seleccionó el Input para recuperar su cuenta.
    */
    if (isset($_POST["btnRecuperar"])) {
        
        /*  
        Validar los caracteres del usuario y el correo del array $_POST.
        */
        if (postInputsVerificarMensaje($_POST)) {
            
            $conn = conexionDB();   //Alvergar la conexión con la DB.
            
            /*  
            Validar si no ha hecha la petición seguidamente.
            */
            if (verifyTimeRecoveryDB($conn, $_POST["txtUsuario"])) {
                
                /*  
                Confirmar el Usuario y Correo en el DB luego enviar el mensaje al correo, 
                sino mandar un mensaje anunciando que no los datos ingresados no son validos.
                */
                if (is_array($arrayRecovery = verifyEmailDB($conn, $_POST["txtUsuario"], $_POST["txtCorreo"]))) {
                    
                    /*  
                    Generar código y variables para el uso del JWT.
                    */
                    $time = time() + (60*5);    //Tiempo de duración del código.
                    $key = "my_secret_key";     //Llave necesario para abrir el JWT.
                    $codigo = random_int(1000, 9999);   //Código a enviar al correo para la recuperación.
                    $data = array(
                        "data" => [
                            "tiempo" => $time,
                            "codigo" => $codigo
                        ]
                    );  //Creación del objeto para codificar con la información.

                    $jwt = JWT::encode($data, $key);    //Creación del JWT con toda la información encriptada.

                    /*  
                    Validar la creación del código encriptado.
                    */
                    if (isset($jwt)) {
                        
                        /*  
                        Escribir en la DB el tiempo para que la cuenta pueda volver hacer esta misma petición.
                        */
                        $time_recovery = time() + (60*2);    //Tiempo para volver a hacer la petición.
                        writeTimeRecoveryDB($conn, $time_recovery, $_POST["txtUsuario"]);//Escribir en el DB el tiempo.

                        /*  
                        Validar la escritura del código en la DB.
                        */
                        if (writeCodeRecoveryDB($conn, $jwt, $_POST["txtUsuario"])) {
                            

                            /*  
                            Validar el envio del correo.
                            */
                            if (sendEmail($codigo, $_POST["txtCorreo"], $arrayRecovery["Full_name"])) {

                                header("location: IngresarCodigo.php");  //Redirección a la página de ingreso del código.
                            }else {
                                
                                echo '<script>alert("No se pudo generar la recuperación");</script>';
                            }
                        }else {
                            
                            echo '<script>alert("No se pudo generar la recuperación");</script>';
                        }
                    }else {
                        
                        echo '<script>alert("No se pudo generar la recuperación");</script>';
                    }
                }else {
                    
                    echo '<script>alert("NO coinciden las credenciales");</script>';
                }
            }else {
                
                echo '<script>alert("NO existe el usuario o ya ha hecho esta petición seguidamente.");</script>';
            }
        }else {
            
            echo '<script>alert("Carácteres no validos");</script>';
        }
    }


    /*
    Crear variable anti CSRF.
    */
    $_SESSION["csrf"] = random_int(1000, 9999);
?>


<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro</title>
        <link rel="stylesheet" href="css/style.css?19.0">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" 
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src = perfil.js></script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    </head>
    <body>
    <header>    
    </header>
        <main id="main_login">
            <div id="back_login">
                <div id="div_login_left">
                    <div id="box_login_left">
                        <h1 id="logo_login">TUIT.COM</h1>
                        <p id="paragraph_login">Recupera tú Contraseña Inmediatamente!!!</p>
                    </div>
                </div>
                <div id="div_register_right">
                    <div id="recovery_password">
                        <form id="form_register" action="" method="post" enctype="multipart/form-data">
                            <h2 id="title_register">Recupera tú Cuenta en un Segundo!</h2>
                            <p id="sentence_register">Te Extrañamos!!</p>

                            <input type="text" name="txtUsuario" id="txtUsuario" pattern="[^' ']+[A-Za-z0-9]{3,15}" required
                            placeholder="Digite su Usuario"><br>

                            <input type="email" name="txtCorreo" id="txtCorreo" required
                            placeholder="Digite su Correo"><br>

                            <input type="hidden" name="csrf" id="csrf" value = "<?php echo $_SESSION["csrf"];?>">

                            <input type="submit" name="btnRecuperar" id="btnRecuperar" value="Recuperar">

                            <p style="color: white;  margin: 16px 0 0 0;">¿Ya tienes Cuenta? <a href="Login.php" style="color: #b3b5ff;">Ingresa Aquí</a></p>
                        </form>
                    </div>
                </div>
            </div>  
        </main>
    </body>
</html>