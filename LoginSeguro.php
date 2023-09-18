<?php
    /*
    Acceso a las funciones de las librerias propias.
    */
    require_once("libs/tools.php");
    require_once ("libs/db.php");
	

    /*
    Llamado a las funciones de seguridad.
    */
	sesionSegura();
    limpiarEntradas();


    /*
    Creación de variables locales.
    */
    $conn = conexionDB();   //Alvergar la conexión con la DB.
    $ip= getIp();   //Alvergar la IP del equipo actual.


    /*  
    Verificación de variables para anti CSRF.
    */
    if (isset($_POST["csrf"])) {
        
        /*  
        Comparar la variable de la sesión con la enviada.
        */
        if ($_POST["csrf"] != $_SESSION["csrf"]) {
        
            header("Location: Index.php");  //Recarga la página.
            exit;   //Terminar ejecución.
        }
    }

    if (!isset($_SESSION["User_Validate"])) {
        
        header("location: Login.php");
    }

    if (isset($_POST["cancel"])) {
        
        unset($_SESSION["User_Validate"]);
        unset($_SESSION["Password_Validate"]);
        header("location: Login.php");

    }

    if (isset($_POST["continue"])) {
        
        $_SESSION["validar"] = 1;

        if(loginDB($conn, $_SESSION["User_Validate"], $_SESSION["Password_Validate"], $_SESSION["validar"])) {

            unset($_SESSION["User_Validate"]);
            unset($_SESSION["Password_Validate"]);

            header("location: Index.php");
        }
        else {
            
            header("location: Login.php");
        }
    }

    $ipUsuario = IpDB($conn, $_SESSION["User_Validate"]);

    if ($ip == $ipUsuario["IP"]) {
        
        $typeAlert = 1;
    }
    else {
        
        $typeAlert = 2;
    }

    $_SESSION["csrf"] = random_int(1000, 9999);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Validar Login</title>
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
                    <p id="text_warning">
                        <?php
                            if ($typeAlert == 1) {
                                
                                echo "Usted ya ha iniciado sesión en un dispositivo con la misma dirección IP. ¿Desea igualmente
                                    iniciar sesión en este dispositivo?";
                            }
                            else {

                                echo "Usted ya tiene iniciado sesión en un dispositivo con una dirección IP distinta a la actual.
                                    ¿Desea abrir sesión en este dispositivo con <br> diferente dirección IP? teniendo encuenta que su sesión
                                    en el <br> anterior dispositivo se cerrará";
                            }
                        ?>
                    </p>
                    <div id="form_warning">
                        <form method="post">
                            <input type="hidden" id="csrf" name="csrf" value="<?php echo $_SESSION["csrf"];?>">
                            <input type="submit" id="continue" name="continue" value="Continuar"></input>
                            <input type="submit" id="cancel" name="cancel" value="Cancelar"></input>
                        </form>
                    </div>
                </div> 
            </div>
        </div>  
    </body>
</html>