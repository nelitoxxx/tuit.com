<?php
    /*
    Acceso a las funciones de las librerias propias.
    */
    require_once ("libs/tools.php");
    require_once ("libs/db.php");


    /*
    Llamado a la funció de seguridad.
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
    Verificación de que el usuario este logueado y se seleccionó un chat de una persona.
    */
    if (!isset($_SESSION["User"]) && !isset($_SESSION["usuarioChat"])) {
        
        header("Location: Index.php");  //Redireccionar al Index.
    }


    /*  
    Verificación de que el usuario selecciono el Input de cerrar sesión.
    */
    if (isset($_POST["logOut"])) {  

        if (writeIpDB($conn, $ip, $_SESSION["User"], 0)) {
            
            session_destroy();
            header("Location: Login.php");
        } 
    }


    /*  
    Verificación de que el usuario seleccionó el Input de crear un Mensaje.
    */
    if (isset($_POST["formulario"])) {

        parse_str($_POST["formulario"], $datos);    //Convertir el string del $.ajax en una cadena de petición Url.  
        
        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        if (postInputsMensaje($datos)) {
        
            /*  
            Validar si existe un archivo adjunto enviado.
            */
            if(isset($_FILES["fulAdjunto"])) {
    
                $name_archivo = archivo($_FILES["fulAdjunto"]); //Retorna el nombre nuevo del archivo guardado en el servidor.
            }
            else {
                
                $name_archivo = "Sin archivo adjunto";  //Se describe en el mensaje que no hay arhivo adjunto.
            }
    
            /*  
            Validar si la variable de archivo adjunto es un String para enviar el mensaje a la DB.
            */
            if(is_string($name_archivo)) {
    
                //messageDB($conn, $datos["txtMensaje_Messages_crear"], $name_archivo, $_SESSION["usuarioChat"], $_SESSION["id_users"]);
                //Ingresar mensaje en la DB.
                
            }
        }
    }

    /*
    Alvergar nombre de la persona del chat.
    */
    $sessionUserChat = personaChatDB($conn, $_SESSION["usuarioChat"]);  


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
        <title>Tuit: Mensajes Enviados</title>
        <link rel="stylesheet" href="css/style.css?9.0">
        <script src = perfil.js></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" 
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>


        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">

        
        <script>
            setInterval(function(){
                ajax_chat();
            }, 500)
        </script>
    </head>
    <body onload="ajax();">
        <header id="main-header">
            <div id="opciones_header">
                <a href="Index.php"><h1 id="logo-header">TUIT.COM</h1></a>
                <div id="usuario-desplegable">
                    <div id="usuario" onclick="perfil();">
                        <p><?php echo $_SESSION["User"];?></p>
                        <?php echo '<img id="perfil" src="./assets/images/' . $_SESSION["Photo_url"] .'">'?>
                    </div>
                    <div id="usuario-perfil">
                        <ul id="usuario-perfil-UL" style="display: none;">
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
                <a href="InkMensajes.php"><img id="mensajes" src="./assets/header/pngegg.png" style="background-color: #fff;"></a>  
                <a href="Index.php"><img id="mensajes" src="./assets/header/pngwing.com.png"></a> 
            </div> 
        </header>
        <main>
            <nav id="box_all_messages_create">
                <div id="div_all_messages_Create">
                    <div id="div_title">
                        <?php echo '<img id="perfil_chat" src="./assets/images/' . $sessionUserChat["Photo_url"] .'">'?>
                        <h1 id="titulo"><?php echo $sessionUserChat["nombre_completo"];?></h1>
                    </div>
                    <div id="div_messages_chat">
                        <div id="div_messages_chat_background">
                            <?php
                                /*
                                Creación de variables de chat.
                                */
                                $all_message_chat = myMessagesDB($conn, $_SESSION["id_users"], $_SESSION["usuarioChat"]);   //Alvergar los mensajes del chat.                               

                                $date_canstant_sent = 1;    //Variable de control del css al mensaje de envio.
                                $date_canstant_received = 1;    //Variable de control del css al mensaje que se recibe.
                            
                                $write_div_send = "";   //Variable html para iniciar un caja de envio de mensaje.
                                $write_div_received = "";   //Variable html para iniciar un caja de recepción de mensaje.


                                /*
                                Recorrer la variable de mensajes e Imprimir cada mensaje.
                                */
                                foreach ($all_message_chat as $all_message_chat) {
                                    
                                    /*  
                                    Confirmar que el mensaje es del usuario, sino es un mensaje recibido.
                                    */
                                    if ($_SESSION["id_users"] == $all_message_chat["Sender_user_id"]) {
                            
                                        /*  
                                        Confirmar la fecha del mensaje para cerrar o abrir una caja del mensaje.
                                        */
                                        if (fechaComparative($all_message_chat["date_message"])) {
                                            
                                            /*  
                                            Confirmar cuando cerrar una etiqueta html "div". 
                                            */
                                            if ($write_div_send != "" || $write_div_received != "") {
                                                
                                                echo "</div>";  //Escribir etiqueta "div" para cerrar.
                            
                                                $write_div_send = "";
                                                $write_div_received = "";
                                                $date_canstant_sent = 1;
                                                $date_canstant_received = 1;
                                            }
                            
                                            $date_canstant_sent = 1;    //Alternar variable de fecha dependiendo abrir o cerrar una acaj de mensajes.
                            
                                        }
                                        elseif(!fechaComparative($all_message_chat["date_message"]) && $date_canstant_sent == 1){
                                            
                                            /*  
                                            Confirmar cuando cerrar una etiqueta html "div". 
                                            */
                                            if ($write_div_received != "") {
                                                
                                                echo "</div>";  //Escribir etiqueta "div" para cerrar.
                                                $write_div_received = "";
                                                $date_canstant_received = 1;
                                            }
                                            
                                            $date_canstant_sent = 0;    //Alternar variable de fecha dependiendo abrir o cerrar una acaj de mensajes.
                            
                                            $write_div_send = '<div id="message_group_sent">';  //Cambiar variable para abrir una caja de mensaje.
                            
                                            echo $write_div_send;   //Escribir variable.
                                        }
                                        
                                        echo '
                                                <div class="message_sent" id="message_sent">
                                                    <div class="message_sent_text">
                                                        ' . $all_message_chat["message_users"] . '
                                                    </div>
                                                </div>
                            
                                        ';  //Escribir mensaje.
                            
                                        /*  
                                        Confirmar si el mensaje enviado tiene un archivo adjunto. 
                                        */
                                        if ($all_message_chat["File_url"] != "Sin archivo adjunto") {
                                            
                                            echo '
                                                <div class="message_sent" id="message_sent">
                                                    <div class="message_sent_text">
                                            ';  //Escribir etiquetas de html de apertura.

                                            /*  
                                            Confirmar si el recurso es tipo imágen o otro tipo. 
                                            */
                                            if (imageMessage($all_message_chat["File_url"])) {
                                                
                                                echo '<img id="img_text" src="./assets/archivos/' . $all_message_chat["File_url"] . '">';   //Escribir etiqueta de imágen.
                                            }
                                            else {
                                                
                                                echo '<a href="./assets/archivos/' . $all_message_chat["File_url"] . '">' . $all_message_chat["File_url"] .'</a>';
                                                //Escribir etiqueta de link. 
                                            }

                                            echo '
                                                    </div>
                                                </div>
                                            ';  //Escribir etiquetas de html de cierre.
                                        }
                                    }
                                    else {
                                        
                                        /*  
                                        Confirmar cuando cerrar una etiqueta html "div". 
                                        */
                                        if (fechaComparative($all_message_chat["date_message"])) {
                                        
                                            /*  
                                            Confirmar cuando cerrar una etiqueta html "div". 
                                            */
                                            if ($write_div_send != "" || $write_div_received != "") {
                                                
                                                echo "</div>";  //Escribir etiqueta "div" para cerrar.
                            
                                                $write_div_send = "";
                                                $write_div_received = "";
                                                $date_canstant_sent = 1;
                                                $date_canstant_received = 1;
                                            }
                                            
                                            $date_canstant_received = 1;    //Alternar variable de fecha dependiendo abrir o cerrar una caja de mensajes.
                            
                                        }
                                        elseif(!fechaComparative($all_message_chat["date_message"]) && $date_canstant_received == 1){
                                            
                                            /*  
                                            Confirmar cuando cerrar una etiqueta html "div". 
                                            */
                                            if ($write_div_send != "") {
                                                
                                                echo "</div>";  //Escribir etiqueta "div" para cerrar.
                                                $write_div_send = "";
                                                $date_canstant_sent = 1;
                            
                                            }
                            
                                            $date_canstant_received = 0;    //Alternar variable de fecha dependiendo abrir o cerrar una caja de mensajes.
                            
                                            $write_div_received = '<div id="message_group_received">';  //Cambiar variable para abrir una caja de mensaje.
                            
                                            echo  $write_div_received;  //Escribir variable.
                                        }
                                        
                                        echo '
                                                <div class="message_received" id="message_received">
                                                    <div class="message_received_text">
                                                        ' . $all_message_chat["message_users"] . '
                                                    </div>
                                                </div>                  
                                        ';  //Escribir mensaje.
                            
                                        /*  
                                        Confirmar si el mensaje enviado tiene un archivo adjunto. 
                                        */
                                        if ($all_message_chat["File_url"] != "Sin archivo adjunto") {
                                            
                                            echo '
                                                <div class="message_received" id="message_received">
                                                    <div class="message_received_text">
                                            ';  //Escribir etiquetas de html de apertura.

                                            /*  
                                            Confirmar si el recurso es tipo imágen o otro tipo. 
                                            */
                                            if (imageMessage($all_message_chat["File_url"])) {
                                        
                                                echo '<img id="img_text" src="./assets/archivos/' . $all_message_chat["File_url"] . '">';   //Escribir etiqueta de imágen.
                                            }
                                            else {
                                                
                                                echo '<a href="./assets/archivos/' . $all_message_chat["File_url"] . '">' . $all_message_chat["File_url"] .'</a>';
                                                //Escribir etiqueta de link. 
                                            }

                                            echo '
                                                    </div>
                                                </div>
                                            ';  //Escribir etiquetas de html de cierre.
                                        }       
                            
                                    }
                                }

                                if ($date_canstant_received == 0 || $date_canstant_sent == 0) {
        
                                    echo "</div>";
                                }
                            ?>
                        </div>
                        <script>
                            $('#div_messages_chat_background').scrollTop( $('#div_messages_chat_background').prop('scrollHeight') );
                        </script>
                        <div id="div_messages_chat_send">
                            <form id="form_send_message" method="post" enctype="multipart/form-data" onsubmit="mostrarMensaje(event);">
                                <div id="div_attached_file">
                                    <p id="area_button_attached">
                                        <img id="img_button_attached" src="./assets/header/file_adjunto.png" alt="">
                                        <input type="file" name="fulAdjunto" id="fulAdjunto" value="">
                                    </p>
                                </div>
                                <div id="div_send_message">
                                    <input type="text" name="txtMensaje_Messages_crear" id="txtMensaje_Messages_crear">
                                </div>
                                <div id="button_send">
                                    <p id="area_button">
                                        <input type="hidden" name="csrf" id="csrf" value = "<?php echo $_SESSION["csrf"]  ?>">
                                        <img id="img_button_send" src="./assets/header/pngwing_send.png" alt="">
                                        <input type="submit" name="btnEnviar_message_crear" id="btnEnviar_message_crear" value="holis">
                                    </p>                     
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>
        </main>
    </body>
</html>