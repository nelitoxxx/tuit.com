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


    $conn = conexionDB();   //Alvergar la conexión con la DB.


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
        if (postInputsVerificarCodigo($_POST)) {

            /*  
            Traer de la DB el código de recuperación encriptado. 
            */
            if (is_array($codigoDB = verifyCodeDB($conn, $_POST["txtUsuario"]))) {
                
                /*  
                Verificar que el usuario tenga un código de recuperación en la DB.  
                */
                if (is_string($codigoDB["Code_recovery"])) {
                    
                    $key = "my_secret_key"; //Llave necesario para abrir el JWT.

                    try {

                        $data = JWT::decode($codigoDB["Code_recovery"], $key, array("HS256"));  
                        //Decodificar el código traido de la base de datos y guardarlo en una variable. 

                        /*  
                        Extraer la información del decodificada y especificar el tiempo actual.
                        */
                        $tiempo_exp = $data -> data -> tiempo;  //Alvergar en una variable la fecha a expedir del código.
                        $codigo_recovery = $data -> data -> codigo; //Aalvergar en un variable el código.
                        $tiempo = time();   //Alvergar en una variabel el tiempo actual.

                        /*  
                        Verificar que el código de recuperación no se haya expirado.  
                        */
                        if ($tiempo <  $tiempo_exp) {
                            
                            /*  
                            Verificar que los códigos de recuperación coincidan.  
                            */
                            if ($codigo_recovery == $_POST["txtCodigo"]) {
                                
                                $_SESSION["Recovery_user"] = $_POST["txtUsuario"];  //Guardar el usuario en la variable de $_SESSION.
                                header("location: IngresarCodigo.php");  //Recargar página.
                            }else {
                                
                                echo '<script>alert("El código ingesado no coincide");</script>';
                            }
                        }else {
                            
                            echo '<script>alert("Ha caducado la fecha del codigo de recuperación");</script>';
                        }

                    } catch (\Throwable $th) {

                        echo $th;
                    }
                }else {
                    
                    echo '<script>alert("El usuario no tiene código de recuperación");</script>';
                }
            }else {
                
                echo '<script>alert("NO coinciden las credenciales");</script>';
            }
        }else {
            
            echo '<script>alert("Carácteres no validos");</script>';
        }
    }


    /*  
    Verificación de que el usuario seleccionó el Input para cambiar contraseñas.
    */
    if (isset($_POST["btnActualizar"]) && isset($_SESSION["Recovery_user"])) {
        
        /*  
        Validar los caracteres de las contraseñas de las variables $_POST.
        */
        if (postInputsRecuperarClave($_POST)) {
            
            /*  
            Validar el cambio de clave en la base de datos.
            */    
            if (changePasswordRecoveryDB($conn, $_SESSION["Recovery_user"], $_POST["txtNueva"], $_POST["txtRepetir"])) {
                
                /*  
                Eliminar código y las variables de sesión usadas y redireccionar al login.
                */ 
                writeCodeRecoveryDB($conn, 0, $_SESSION["Recovery_user"]);  //Eliminamos le código usado en la DB.
                session_destroy();  //Destruimos la sesión.
                header("Location: Login.php");  //Redirección al Login.
            }else {
                
                echo '<script>alert("No se pudo acceder a la base de datos, Intente nuevamente más tarde");</script>';
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
        <title>Recuperar Contraseña</title>
        <link rel="stylesheet" href="css/style.css?10.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    </head>
    <body>
        <header id="main-header">
            <a href="Index.php"><h1 id="logo-header">TUIT.COM</h1></a>
        </header>
        <main>
            <h2 id="titulo-blog">RECUPERACIÓN DE CUENTA</h2>
            <?php
                if (isset($_SESSION["Recovery_user"])) {
                    
                    echo '
                        <div id="forms">
                            <form action="" method="post" enctype="multipart/form-data">
                                <label for="txtNueva">Digite su nueva clave porfavor</label><br>
                                <input type="password" name="txtNueva" id="txtNueva" pattern="[^' . "' '" . ']+[A-Za-z0-9._%+-]{2,}" required><br>
            
                                <label for="txtRepetir">Vuelva a digitar la clave de nuevo porfavor</label><br>
                                <input type="password" name="txtRepetir" id="txtRepetir" pattern="[A-Za-z0-9._%+-]{2,}" required><br>
            
                                <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                
                                <input type="submit" name="btnActualizar" id="btnActualizar" value="Actualizar">
                            </form>
                        </div> 
                    ';
                }else {
                    
                    echo '
                        <div id="boxRecovery">
                        <p id="textRecovery">
                            Porfavor ingrese el código que se la enviado al correo y el usuario para verificar su identificación, 
                            recuerde que el código tiene una validez de 5 minutos.
                        </p>
                        </div>
                        <div id="forms">
                            <form action="" method="post" enctype="multipart/form-data">
            
                                <label for="txtUsuario">Digite el usuario de nuevo</label><br>
                                <input type="text" name="txtUsuario" id="txtUsuario" pattern="[^' . "' '" . ']+[A-Za-z0-9]{3,15}" required><br>
            
                                <label for="txtCodigo">Digite el código enviado a su correo</label><br>
                                <input type="number" name="txtCodigo" id="txtCodigo" required><br>
            
                                <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                
                                <input type="submit" name="btnRecuperar" id="btnRecuperar" value="Recuperar">
                            </form>
                        </div>
                    ';
                }
            ?>   
        </main>
    </body>
</html>