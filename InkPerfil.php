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
        
            header("Location: Index.php");  //Recarga la página.
            exit;   //Terminar ejecución.
        }
    }


    /*  
    Verificación de que el usuario este logueado.
    */
    if (!isset($_SESSION["User"])) {
        
        header("Location: Login.php");  //Redireccionar al Login.
    }

    if (isset($_POST["logOut"])) {

        if (writeIpDB($conn, $ip, $_SESSION["User"], 0)) {
            
            session_destroy();
            header("Location: Login.php");
        } 
    }


    /*  
    Verificación de que el usuario seleccionó el Input de cambiar los datos de su perfil.
    */
    if (isset($_POST["btnActualizar"])) {

        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        if (postInputsActualizar($_POST)) {

            $nombre_Archivo = imagen($_FILES["fullfoto"]);  //Devuelve un String si se actualizó la foto de perfil.
    
            /*  
            Validar si se actualizó la foto de perfil.
            */
            if(is_string($nombre_Archivo)){
                
                $registro = updateDataDB($conn, $_POST["txtNombre"], $_POST["txtApellido"], $_POST["txtCorreo"], 
                            $_POST["txtDir"], $_POST["txtNumHijos"], $_POST["txtEstCilvil"], 
                            $nombre_Archivo, $_SESSION["id_users"]);    //Alverga un True si actualizó la DB, sino un False.
                
                /*  
                Validar si se actualizó el perfil.
                */
                if ($registro) {

                    header("Location: Index.php");
                }else {
                    
                    echo '<script>alert("No se pudo actualizar sus datos");</script>';
                }
            }else {
                              
                $registro = updateDataDB($conn, $_POST["txtNombre"], $_POST["txtApellido"], $_POST["txtCorreo"], 
                            $_POST["txtDir"], $_POST["txtNumHijos"], $_POST["txtEstCilvil"], 
                            $_SESSION["Photo_url"], $_SESSION["id_users"]); //Alverga un True si actualizó la DB, sino un False.
                
                /*  
                Validar si se actualizó el perfil.
                */
                if ($registro) {
                    
                    header("Location: Index.php");
                }else {
                    
                    echo '<script>alert("No se pudo actualizar sus datos");</script>';
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
        <title>Index</title>
        <link rel="stylesheet" href="css/style.css?19.0">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" 
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
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
                <form id="form_perfil" action="" method="post" enctype="multipart/form-data">
                    <div id="div_foto_perfil" for="fullFoto">
                        <div id="div_foto_perfil_background">
                            <img id="foto_perfil_change" src="<?php echo './assets/images/' . $_SESSION["Photo_url"];?>" alt="">
                            <input type="file" id="fullFoto_perfil" name="fullfoto" onchange="previewImage(event, '#foto_perfil_change')">  
                        </div>
                        <h2>Actualiza tus Datos!</h2>
                    </div>             
                    <div id="div_grid_perfil">
                        <div>
                            <input type="text" name="txtNombre" id="txtNombre" pattern="[A-Za-z ]{2,}" value="<?php echo $_SESSION["First_name"];?>" required>
                        </div>
                        <div>
                            <input type="text" name="txtApellido" id="txtApellido" pattern="[A-Za-z ]{2,}" value="<?php echo $_SESSION["Last_name"];?>" required>
                        </div>
                        <div>
                            <input type="email" name="txtCorreo" id="txtCorreo" value="<?php echo $_SESSION["Email"];?>" required>
                        </div>
                        <div>
                            <input type="text" name="txtDir" id="txtDir" value="<?php echo $_SESSION["Address_house"];?>" required>
                        </div>
                        <div>
                            <input type="number" name="txtNumHijos" id="txtNumHijos" value="<?php echo $_SESSION["Childs"];?>" required>
                        </div>
                        <div>
                            <select name="txtEstCilvil" id="txtEstCilvil">
                                <option value="casado">Casado</option>
                                <option value="soltero">Soltero</option>
                                <option value="union_marital">Unión marital de hecho</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="csrf" id="csrf" value = "<?php echo $_SESSION["csrf"];?>">
                    <div id="div_bottom_perfil">
                        <input type="submit" name="btnActualizar" id="btnActualizar" value="Enviar">
                        <a href="CambioClave.php" style="color: #b3b5ff;">Cambia tú Contraseña</a>
                    </div>
                </form>
            </div>    
        </main>
    </body>
</html>
