<?php
    /*
    Acceso a las funciones de las librerias propias.
    */
    require_once ("libs/tools.php");
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
    $dataIp = IpDB($conn, $_SESSION["User"]);   //Alvergar la ip de la DB.


    /*  
    Comparar la IP y la sesión iniciada alvergarda en la DB con la del equipo actual
    para continuar la sesión o destruirla.
    */
    if ($dataIp["IP"]!= $ip || $dataIp["is_Login"] != 1) {
        
        session_destroy(); //Destruir Sesión.
        header("Location: Login.php");  //Redireccionar al Login.
    }


    /*  
    Verificación de variables para anti CSRF.
    */
    if (isset($_POST["csrf"])) {
        
        /*  
        Comparar la variable de la sesión con la enviada.
        */
        if ($_POST["csrf"] != $_SESSION["csrf"]) {
        
            header("Location: CambioClave.php");    //Recarga la página.
            exit;   //Terminar ejecución.
        }
    }


    /*  
    Verificación de que el usuario este logueado.
    */
    if (!isset($_SESSION["User"])) {
        
        header("Location: Login.php");
    }

    if (isset($_POST["logOut"])) {

        if (writeIpDB($conn, $ip, $_SESSION["User"], 0)) {
            
            session_destroy();
            header("Location: Login.php");  //Redireccionar al Login.
        } 
    }


    /*  
    Verificación de que el usuario selecciono el Input de cambio de clave.
    */
    if (isset($_POST["btnActualizar"])) {

        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        if (postInputsActualizarClave($_POST)) { 
            
            /*  
            Confirmar el ingreso de los datos del cambio de clave en el DB, sino mandar un mensaje
            anunciando que no se pudó crear el cambio de clave.
            */
            if(changePasswordDB($conn, $_SESSION["User"], $_POST["txtAnterior"], $_POST["txtNueva"], $_POST["txtRepetir"])) {

                header("Location: Index.php");  //Redireccionar al Index.
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
        <title>Cambio de clave</title>
        <link rel="stylesheet" href="css/style.css?13.0">
        <script src = perfil.js></script>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    </head>
    <body>
        <header id="main-header">
            <div id="opciones_header">
                <div id="div_logo_header">
                    <a href="Index.php"><h1 id="logo-header">TUIT.COM</h1></a>
                </div>
                <div id="usuario-desplegable">
                    <div id="div_mensajes">
                        <div id="div_mensajes_contorno">
                            <a href="InkMensajes.php"><img id="mensajes" src="./assets/header/pngegg.png" alt=""></a>
                        </div>
                    </div>
                    <div id="usuario" onclick="perfil()">
                        <?php echo '<img id="perfil" src="./assets/images/' . $_SESSION["Photo_url"] .'">'?>
                    </div>
                    <div id="usuario-perfil" style="display: none;">
                        <div id="usuario-perfil_back">
                            <ul id="usuario-perfil-UL">
                                <a id="opciones-perfil" href="InkMisTweets.php"><li>Mis Tweets</li></a>
                                <a id="opciones-perfil" href="InkPerfil.php"><li>Perfil</li></a>
                                <a href="">
                                    <form id="form_logOut" name="form_logOut" method="post">
                                        
                                        <button type="submit" id="logOut" name="logOut" value="Log Out">Log Out</button>
                                    </form>
                                </a>
                            </ul>
                        </div>
                    </div>
                </div>   
            </div> 
        </header>
        <main>
            <div id="main_perfil">
                <form id="form_change_password" action="" method="post">
                    <div id="div_foto_change_password">
                        <div id="div_foto_perfil_background">
                            <img id="foto_perfil_change" src="<?php echo './assets/images/' . $_SESSION["Photo_url"];?>" alt=""> 
                        </div>
                        <h2>Actualiza tu Contraseña!</h2>
                    </div>   
                    <div id="div_change_password">
                        <input type="password" name="txtAnterior" id="txtAnterior" pattern="[^' ']+{2,}[A-Za-z0-9._%+-]{2,}" required
                        placeholder="Digite su Contraseña Anterior"><br>

                        <input type="password" name="txtNueva" id="txtNueva" pattern="[^' ']+[A-Za-z0-9._%+-]{2,}" required
                        placeholder="Digite su Contraseña Nueva"><br>

                        <input type="password" name="txtRepetir" id="txtRepetir" pattern="[A-Za-z0-9._%+-]{2,}" required
                        placeholder="Vuelva a Digitar su Contraseña Nueva"><br>

                        <input type="hidden" name="csrf" id="csrf" value = "<?php echo $_SESSION["csrf"];?>">
                        
                        <input type="submit" name="btnActualizar" id="btnActualizar" value="Actualizar">
                    </div>
                </form>
            </div>    
        </main>
    </body>
</html>



