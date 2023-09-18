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
        
            header("Location: Login.php");  //Recarga la página.
            exit;   //Terminar ejecución.
        }
    }


    /*
    Redireccionar al Login.
    */
    if (isset($_POST["continue"])) {
        
        header("location: Login.php");  //Redireccionar al Login.
    }


    /*
    Validar que exista la variable $_GET necesaria.
    */
    if (isset($_GET["codigo"])) {
        
        try {

            $key = "my_secret_key"; //Llave necesario para abrir el JWT.            
            $data = JWT::decode($_GET["codigo"], $key, array("HS256")); //Decodificar el JWT.               
            $usuario = $data -> usuario;   //Extraer el usuario del JWT.

            $conn = conexionDB();   //Alvergar la conexión con la DB.

            /*
            Validar la activación en la DB.
            */
            if ($activarCuenta = activeAccountDB($conn, $usuario)) {
                
                $error = false; //Determina el tipo de mensaje en el HTML.
            }else {
                
                echo '<script>alert("No se pudo conecta con la base de Datos, intente más tarde");</script>';
                header("location: Login.php");  //Redireccionar al Login.
            }
 
        } catch (\Throwable $th) {

            $error = true;  //Determina el tipo de mensaje en el HTML.
        }
    }else {
        
        header("location: Login.php");  //Redireccionar al Login.
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
        <title>Activación de Cuenta</title>
        <link rel="stylesheet" href="css/style.css?14.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    </head>
    <body>
        <div id="box_validate">
            <div id="box_primary">
                <div id="first_Box">
                    <h1 id="title_validate">TUIT.COM</h1>
                </div>
                <div id="second_Box">
                    <p id="textRecovery" style="color: black;">
                        <?php
                            if ($error == false) {
                                
                                echo "Se ha Activado tú cuenta, ya puedes Ingresar con tú usuario y contraseña a TUIT.COM";
                            }
                            else {

                                echo "El link recibido no es valido para activar la cuenta, porfavor intente de nuevo";
                            }
                        ?>
                    </p>
                    <div id="form_warning">
                        <form method="post">
                            <input type="hidden" id="csrf" name="csrf" value="<?php echo $_SESSION["csrf"];?>">
                            <input type="submit" id="continue" name="continue" value="Continuar"></input>
                        </form>
                    </div>
                </div> 
            </div>
        </div>  
    </body>
</html>