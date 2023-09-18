<?php
    /*
    Acceso a las funciones de las librerias propias.
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
        
            header("Location: Registro.php");   //Recargar página.
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
    Verificación de que el usuario seleccionó el Input de crear un nuevo Usuario.
    */
    if (isset($_POST["btnRegistrar"])) {
        
        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        if (postInputsRegistrar($_POST)) {
                   
            $nombre_Archivo = imagen($_FILES["fullFoto"]);  //Alverga un String si foto es valida.
            
            /*  
            Validar si la foto es valida.
            */
            if(is_string($nombre_Archivo)){
            
                /*  
                Crear las variables para el uso del JWT.
                */
                $key = "my_secret_key"; //Llave necesario para abrir el JWT.
                $data = $arrayName = array(
                    "usuario" => $_POST["txtUsuario"] 
                );  //Creación del objeto para codificar con la información.

                $jwt = JWT::encode($data, $key);    //Creación del JWT con toda la información encriptada.

                if (isset($jwt)) {
                    
                    $conn = conexionDB();   //Alvergar la conexión con la DB. 
                    $registro = grabarDB($conn, $_POST["txtNombre"], $_POST["txtApellido"], $_POST["txtCorreo"], 
                                        $_POST["txtDir"], $_POST["txtNumHijos"], $_POST["txtEstCilvil"], 
                                        $nombre_Archivo, $_POST["txtUsuario"], $_POST["txtClave"], $jwt); 
                    //Alverga un True si registró el usuario en la DB, sino un False.
                    
                    /*  
                    Validar si el registro fue exitoso.
                    */ 
                    echo $jwt . "<br>";          
                    if ($registro) {
                        
                        /*  
                        Validar el envio del correo.
                        */
                        if (sendEmailActiveAccount($jwt, $_POST["txtCorreo"], $_POST["txtNombre"] . $_POST["txtApellido"])) {
                            
                            header("Location: Login.php");  //Redireccionar al Login.
                        }else {
                            
                            echo '<script>alert("No se pudó enviar el correo de verificación");</script>';
                        }
                    }else {
                        
                        echo '<script>alert("No se pudó generar el registro");</script>';
                    }
                }else {
                    
                    echo '<script>alert("No se pudó generar el código de activación, vuelve a registrarte más tarde");</script>'; 
                }
                
            }
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
                        <p id="paragraph_login">Disfruta hablar con todos de todo!!!</p>
                    </div>
                </div>
                <div id="div_register_right">
                    <div id="register">
                        <form id="form_register" action="" method="post" enctype="multipart/form-data">
                            <h2 id="title_register">Registrate Ya!</h2>
                            <p id="sentence_register">Rápido y Sencillo</p>

                            <input type="text" name="txtNombre" id="txtNombre" pattern="[A-Za-z ]{2,}" required
                            placeholder="Digite su nombre"><br>

                            <input type="text" name="txtApellido" id="txtApellido" pattern="[A-Za-z ]{2,}" required
                            placeholder="Digite su Apellido"><br>

                            <input type="email" name="txtCorreo" id="txtCorreo" required
                            placeholder="Digite su Correo"><br>

                            <input type="text" name="txtDir" id="txtDir" required
                            placeholder="Digite su Dirección"><br>

                            <input type="number" name="txtNumHijos" id="txtNumHijos" required
                            placeholder="Digite su cantidad de hijos"><br>

                            <select name="txtEstCilvil" id="txtEstCilvil">
                                <option value="casado">Casado</option>
                                <option value="soltero">Soltero</option>
                                <option value="union_marital">Unión marital de hecho</option>
                            </select><br>

                            <label id="label_foto" for="fullFoto">Seleccione su Foto</label>
                            <input type="file" name="fullFoto" id="fullFoto" onchange="change_label_Image()"><br>

                            <input type="text" name="txtUsuario" id="txtUsuario" pattern="[^' ']+[A-Za-z0-9]{3,15}" required
                            placeholder="Digite su Usuario"><br>

                            <input type="password" name="txtClave" id="txtClave" pattern="[^' ']+[A-Za-z0-9._%+-]{6,}" required
                            placeholder="Digite su Contraseña"><br>

                            <input type="hidden" name="csrf" id="csrf" value = "<?php echo $_SESSION["csrf"];?>">
                            
                            <input type="submit" name="btnRegistrar" id="btnRegistrar" value="Enviar">

                            <p style="color: white;  margin: 16px 0 0 0;">¿Ya tienes Cuenta? <a href="Login.php" style="color: #b3b5ff;">Ingresa Aquí</a></p>
                        </form>
                    </div>
                </div>
            </div>  
        </main>
    </body>
</html>