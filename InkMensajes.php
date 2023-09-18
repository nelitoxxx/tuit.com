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
    $ip = getIp();   //Alvergar la IP del equipo actual.
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
    Verificación de que el usuario seleccionó el Input de crear un Mensaje.
    */
    if (isset($_POST["formulario"])) {

        parse_str($_POST["formulario"], $datos);    //Convertir el string del $.ajax en una cadena de petición Url.  
        
        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        if (postInputsMensaje($datos)) {
        
            $id_chat = isNewChat($conn, $datos["amp;id_chat"]);

            if ($id_chat === 0) {
                
                exit;
                
            }elseif ($id_chat === false) {
                
                echo $datos["amp;id_chat"];
                $id_chat = $datos["amp;id_chat"];
            }

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
    
                messageDB($conn, $id_chat, $datos["txtMensaje_Messages_crear"], $name_archivo, 
                            $datos["amp;id_other_user"], $_SESSION["id_users"]);
                //Ingresar mensaje en la DB.
                
            }
        }
    }

    $_SESSION["csrf"] = random_int(1000, 9999);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tuit: Mensajes Recibidos</title>
        <link rel="stylesheet" href="css/style.css?12.0">
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
            <div class="main_mensajes">
                <div class="paquete_personas" id="paquete_personas">
                    <div class="div_title_chat">
                        <h2 class="title_chat">Tús Chats!!</h2>
                        <div class="div_search">
                            <img class="img_search" src="./assets/header/icons8-búsqueda-32.png" alt="">
                            <form id="form_search_chat" class="form_search_chat" action="">
                                <input type="text" class="search_chat" id="search_chat" name="search_chat" placeholder="Ingresa el nombre de la persona">
                            </form>
                        </div>
                    </div>
                    <div class="paquete_chats" id="paquete_chats">
                        <?php  
                            $chats = ChatUsersDB($conn, $_SESSION["id_users"]);   //Alvergar los usuarios de la DB.

                            foreach ($chats as $chat) {
                                $users = explode("/", $chat["users"]);   
                                $user1 = intval($users[0]);
                                $user2 = intval($users[1]);                             
                                $last_message_chat = lastMessage($conn, $users[0], $users[1]);
                                
                                if ($user1 == $_SESSION["id_users"]) {
                                    
                                    $id_other_user_chat = $user2;
                                }else {
                                    
                                    $id_other_user_chat = $user1;
                                }

                                if ($chat === reset($chats)) {
                                    
                                    $first_chat = $id_other_user_chat;
                                    $_SESSION["usuarioChat"] = $first_chat;
                                    $chat_users = $chat["id_chat"];
                                }

                                $other_user_chat = otherUserChat($conn, $id_other_user_chat);

                                echo '
                                    <form class="form_paquete_personas" id="form_chat_' . $chat["id_chat"] . '" onclick="changeChatUser(event)" method="post">
                                        <div class="persona_mensajes">
                                            <div class="border_image_perfil">
                                                <img class="perfil_mensajes" id="' . $chat["id_chat"] . '" src="./assets/images/' . $other_user_chat["Photo_url"] .'">
                                            </div>
                                            <div class="conteiner_buttom">
                                                <input type="hidden" name="id_chat" id="id_chat" value = "' . $chat["id_chat"] . '">
                                                <input type="hidden" name="id_other_user_chat" id="id_other_user_chat" value = "' . $id_other_user_chat . '">
                                                <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                                <label type="text" class="BtnPersona" name="BtnPersona" id="' . $chat["id_chat"] . '"">
                                                ' . $other_user_chat["nombre_completo"] . '</label>
                                ';

                                if ($last_message_chat["Sender_user_id"] == $_SESSION["id_users"]) {
                                    
                                    echo '
                                                <div class="div_you">
                                                    <p class="p_you" id="' . $chat["id_chat"] . '">You: </p>
                                                    <label type="text" id="' . $chat["id_chat"] . '" class="last_message">' . $last_message_chat["message_users"] .'</label>
                                                </div>
                                        ';
                                }else {
                                    
                                    echo        '<label type="text" class="last_message" id="' . $chat["id_chat"] . '">' . $last_message_chat["message_users"] .'</label>';
                                }

                                echo '
                                            </div>
                                        </div>
                                    </form>
                                ';
                            }
                        ?> 
                    </div>
                    <div class="lista_users" id="lista_users">
                        
                    </div>
                    <div class="onload_div" id="onload_div">
                        <img class="gif_onload" src="./assets/header/icons8-carga.gif" alt="">                   
                    </div>                            
                </div>
                <div class="main_chat" id="main_chat">
                    <div id="div_all_messages_Create">
                        <div id="div_title">
                            <?php 

                                if (isset($first_chat)) {
                                    /*
                                    Alvergar nombre de la persona del chat.
                                    */
                                    $sessionUserChat = personaChatDB($conn, $first_chat);

                                    echo '
                                        <img id="perfil_chat" src="./assets/images/' . $sessionUserChat["Photo_url"] .'">
                                        <h1 id="titulo">' . $sessionUserChat["nombre_completo"] . '</h1>
                                    ';   
                                }                     
                            ?>
                        </div>
                        <div id="div_messages_chat">
                            <div id="div_messages_chat_background">
                                <?php
                                    if (isset($first_chat)) {
                                        
                                        /*
                                        Creación de variables de chat.
                                        */
                                        $all_message_chats = myMessagesDB($conn, $_SESSION["id_users"], $first_chat);   //Alvergar los mensajes del chat.                               

                                        $date_canstant_sent = 1;    //Variable de control del css al mensaje de envio.
                                        $date_canstant_received = 1;    //Variable de control del css al mensaje que se recibe.
                                    
                                        $write_div_send = "";   //Variable html para iniciar un caja de envio de mensaje.
                                        $write_div_received = "";   //Variable html para iniciar un caja de recepción de mensaje.

                                        /*
                                        Recorrer la variable de mensajes e Imprimir cada mensaje.
                                        */
                                        foreach ($all_message_chats as $all_message_chat) {
                                            
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
                                    }else {
                                        
                                        echo '
                                            <div class="div_void_chat">
                                                <h2 clas="void_chat">Busca Amigos para Chatear '. $_SESSION["First_name"] . '!!</h2>
                                            </div>
                                        ';
                                    }
                                ?>
                            </div>
                            <script>
                                $('#div_messages_chat_background').scrollTop( $('#div_messages_chat_background').prop('scrollHeight') );
                            </script>
                            <div id="div_messages_chat_send">
                                <?php
                                    if (isset($first_chat)) {
                                        
                                        echo '
                                            <form id="form_send_message" method="post" enctype="multipart/form-data" onsubmit="mostrarMensaje(event);">
                                                <div id="div_attached_file">
                                                    <p id="area_button_attached">
                                                        <img id="img_button_attached" src="./assets/header/file_adjunto.png" alt="">
                                                        <input type="file" name="fulAdjunto" id="fulAdjunto" value="">
                                                    </p>
                                                </div>
                                                <div id="div_send_message">
                                                    <input type="text" name="txtMensaje_Messages_crear" id="txtMensaje_Messages_crear" placeholder="Aa" autocomplete="off">
                                                </div>
                                                <div id="button_send">
                                                    <p id="area_button">
                                                        <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                                        <input type="hidden" name="id_chat" id="id_chat" value = "' . $chat_users . '">
                                                        <input type="hidden" name="id_other_user" id="id_other_user" value = "' . $first_chat . '">
                                                        <input type="submit" name="btnEnviar_message_crear" id="btnEnviar_message_crear" value="holis">
                                                    </p>                     
                                                </div>
                                            </form>
                                        ';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="onload_chat_div" id="onload_chat_div">
                        <img class="gif_chat_onload" src="./assets/header/icons8-carga.gif" alt="">                   
                    </div>  
                </div>
            </div>
        </main>
    </body>
    <script>
        chat = 1;
        on_interval();
        document.getElementById("search_chat").addEventListener("keyup", keyUp);
        document.getElementById("search_chat").addEventListener("input", search_void);
    </script>
</html>