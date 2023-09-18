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


    /*  
    Verificación de que el usuario selecciono el Input de cerrar sesión.
    */
    if (isset($_POST["logOut"])) {

        /*  
        Anexar a la DB la IP con la que cerro sesión y que el usuario ha sido deslogueado.
        */
        if (writeIpDB($conn, $ip, $_SESSION["User"], 0)) {
            
            session_destroy();  //Destruir Sesión.
            header("Location: Login.php");  //Redireccionar al Login.
        } 
    }


    /*  
    Verificación de que el usuario seleccionó el Input de crear un Tuit.
    */
    if (isset($_POST["btnCrear"])) {

        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        if (postInputsTuit($_POST)) {

            $name_archivo = imagen($_FILES["fulAdjunto_tweet"]);

            if($name_archivo == '') {
    
                $name_archivo = "none"; //Retorna el nombre nuevo del archivo guardado en el servidor.
            }

            /*  
            Crear la variable para que el Tuit no sea público.
            */
            if (!isset($_POST["chkPublico_create_tweets"])) {
                
                $_POST["chkPublico_create_tweets"] = 0;
            }
            
            /*  
            Confirmar el ingreso de los datos del Tuit en el DB, sino mandar un mensaje
            anunciando que no se pudó crear el Tuit.
            */
            if(tuitsDB($conn, nl2br($_POST["txtMensaje_create_tweets"]), $name_archivo, $_POST["chkPublico_create_tweets"], $_SESSION["id_users"])) {
                
                header("Location: Index.php");  //Redireccionar al Index.
            }else {

                echo '<script>alert("No se pudo ingresar el tweet");</script>'; //Mensaje de no lograr el recurso.
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
                    <h1 style="color: white;">Los Tuits de Todos a la Mano!!</h1>
                </div>
            </nav>
            <nav id="box_create_tweets">
                <div id="buttom_transition" onclick="reveal_post()">
                    <div id="buttom_hover" onmouseover="buttom_post_over()" onmouseout="buttom_post_out()">
                        <img id="img_hover" src="./assets/header/pencil_white.png" alt="">
                    </div>
                    <div id="text_hover" onmouseover="buttom_post_over()" onmouseout="buttom_post_out()" style="width: 0px;">
                        <p id="text_hover_p">Pública lo que sientes Aquí!</p>
                    </div>
                </div>
                <form action="" method="post" enctype="multipart/form-data">  
                    <div id="div_create_tweets" style="display: none;">
                        <div id="div_up_create">
                            <div id="div_up_left">
                                <textarea maxlength="150" name="txtMensaje_create_tweets" id="txtMensaje_create_tweets" placeholder="Escribe Aquí :3"></textarea>
                            </div>
                            <div id="div_up_right">
                                <div id="div_up_right_image">
                                    <img id="img_prev" src="./assets/header/img_icon.png" alt="">
                                    <img id="erase_image" src="./assets/header/erase_image.png" onclick="remove_image()">
                                    <input name="fulAdjunto_tweet" id="fulAdjunto_tweet" type="file" accept="image/png, image/jpeg" name="fulAdjunto_tweet" onchange="previewImage(event, '#img_prev')">
                                </div>
                            </div> 
                        </div>
                        <div id="div_down_create">
                            <input type="hidden" name="csrf" id="csrf" value = "<?php echo $_SESSION["csrf"];?>">
                            <label name="es_publico_1_create" id="es_publico_1_create" for="">
                                Es público: <input type="checkbox" name="chkPublico_create_tweets" id="chkPublico_create_tweets" value="1" checked>
                            </label> 
                            <input type="submit" name="btnCrear" id="btnCrear" value="Publicar">                                                                 
                        </div>
                    </div>
                </form>
            </nav>
            <div id="father_colummns_count">
                <div id="colummns_count">
                    <?php 
                        $section = 0;
                        $tweets = tuitsWriteDB($conn, $section);  //Alvergar los Tuit de la DB.

                        /*
                        Recorrer la variable de Tuits e Imprimir cada Tuit.
                        */
                        foreach ($tweets as $tweets) {
                    
                            echo '
                                <div class="box_all_tweets">
                                    <div class="div_all_tweets">
                                        <div class="div-up">
                                            <div class="div-up-left">
                                                <img name="imgFotoAutor_1" class="imgFotoAutor_1" src="assets/images/' . $tweets["Photo_url"] . '">
                                                <label name="lblAutor_1" class="lblAutor_1" for="">' . $tweets["creater_tweet"] . '</label>
                                            </div>
                                            <div class="div-up-right">
                                                <label name="lblFecha_1" class="lblFecha_1">' . $tweets["Create_at"] . '</label>
                                            </div>
                                        </div>
                                        <div class="div-down">
                                            <label name="lblTexto_1" class="lblTexto_1">' . $tweets["Tweet"] . '</label>';
                                            
                                            
                                if ($tweets["image_tweet"] != 'none') {
                                    
                                    echo '<img name="lblImage_1" class="lblImage_1" src="./assets/images/' . $tweets["image_tweet"] .'" alt="">';
                                }
                            echo '                  
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
            </div>
            <br>
        </main>
    </body>
</html>