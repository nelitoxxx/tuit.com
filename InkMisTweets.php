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
    Verificación de que el usuario seleccionó el Input de borrar un Tuit.
    */
    if (isset($_POST["btnBorrar_1"])) {
        
        /*  
        Validar si se borró el tuit en la DB.
        */
        if(!borrarTuitDB($conn, $_SESSION["id_users"], $_POST["txtTexto_1_my_tweets"])) {

            echo '<script>alert("No se pudo borrar el tweet");</script>';
        }
    }


    /*  
    Verificación de que el usuario seleccionó el Input de publicar un Tuit.
    */
    if (isset($_POST["btnPublicar_1"])) {
        
        /*  
        Validar si se publicó el tuit en la DB.
        */
        if(!publicarTuitDB($conn, $_SESSION["id_users"], $_POST["txtTexto_1_my_tweets"])) {
            
            echo '<script>alert("No se pudo publicar el tweet");</script>';
        }
    }


    /*  
    Verificación de que el usuario seleccionó el Input de despublicar un Tuit.
    */
    if (isset($_POST["btnDespublicar_1"])) {
        
        /*  
        Validar si se despublicó el tuit en la DB.
        */
        if(!despublicarTuitDB($conn, $_SESSION["id_users"], $_POST["txtTexto_1_my_tweets"])) {

            echo '<script>alert("No se pudo despublicar el tweet");</script>';
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
    <body onload="loaded();">
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
            <nav id="box_opciones">
                <div id="opciones_tweets">
                    <h1 style="color: white;">Tus Publicaciones hasta la fecha!!</h1>
                </div>
            </nav>
            <div id="father_colummns_count">
                <div id="colummns_count">
                    <?php 
                        $section = 0;
                        $myTweets = myTuitsWriteDB($conn, $_SESSION["id_users"], $section);   //Alvergar los Tuit propios del usuario en la DB.

                        /*
                        Recorrer la variable de tuits propios e Imprimir cada Tuit.
                        */
                        foreach ($myTweets as $myTweets) {

                            $is_public = "";    //Variable String para cambiar de público a no público.
                            
                            /*  
                            Verificación al escribir si es público o no público.
                            */
                            if($myTweets["Is_public"]==1) {
                
                                $is_public = "Si";
                            }else {
                                
                                $is_public = "No";
                            }
                            
                            echo '
                                <div class="box_all_tweets">
                                    <div class="div_all_tweets">
                                        <div class="div-up">
                                            <div class="div-up-left">
                                                <img name="imgFotoAutor_1" class="imgFotoAutor_1" src="assets/images/' . $_SESSION["Photo_url"] . '">
                                                <label name="lblAutor_1" class="lblAutor_1" for="">' . $_SESSION["First_name"] . ' ' . $_SESSION["Last_name"] .'</label>
                                            </div>
                                            <div class="div-up-right">
                                                <label name="lblFecha_1" class="lblFecha_1">' . $myTweets["Create_at"] . '</label>
                                            </div>
                                        </div>
                                        <div class="div-down">
                                            <label name="lblTexto_1" class="lblTexto_1">' . $myTweets["Tweet"] . '</label>';
                                            
                                            
                                if ($myTweets["image_tweet"] != 'none') {
                                    
                                    echo '<img name="lblImage_1" class="lblImage_1" src="./assets/images/' . $myTweets["image_tweet"] .'" alt="">';
                                }
                            echo '         
                                            <div class="div_My_tweets_bottom">
                                                <div id="' . $myTweets["id_tweets"] .'" class="icon_image_points" onclick="buttom_my_tweets(event)">
                                                    <img id="' . $myTweets["id_tweets"] .'" class="image_points" src="./assets/header/icon_points.png" alt="">
                                                    <form class="form_my_tweets" id="form_'. $myTweets["id_tweets"] .'" action="" method="post" style="display: none;">
                                                        <input type="hidden" name="txtTexto_1_my_tweets" class="txtTexto_1_my_tweets" value="' . $myTweets["Tweet"] . '">
                                                        <input type="hidden" name="csrf" class="csrf" value = "' . $_SESSION["csrf"] . '">
                                                        <div class="div_btnPublicar_1">
                                                            <img class="icon_btnPublicar_1" src="./assets/header/icons8-transferencia-entre-usuarios-64.png" alt="">
                                                            <input type="submit" name="btnPublicar_1" class="btnPublicar_1" value="Publicar">
                                                        </div>
                                                        <div class="div_btnDespublicar_1">
                                                            <img class="icon_btnPublicar_1" src="./assets/header/icons8-cancelar-2-100.png" alt="">
                                                            <input type="submit" name="btnDespublicar_1" class="btnDespublicar_1" value="Despublicar">
                                                        </div>
                                                        <div class="div_btnBorrar_1">
                                                            <img class="icon_btnPublicar_1" src="./assets/header/icons8-basura-52.png" alt="">
                                                            <input type="submit" name="btnBorrar_1" class="btnBorrar_1" value="Borrar">
                                                        </div>
                                                    </form>
                                                </div> 
                                            </div>      
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                        echo '
                            <form class="id_tweet_form" method="post">
                                <input type="hidden" class="last_tweet" class="last_tweet" name="last_tweet" value="1"</input>
                            </form>
                        ';
                    ?>
                </div>
            </div><br>
        </main>
    </body>
</html>