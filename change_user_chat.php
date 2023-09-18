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


    /*  
    Verificación de que el usuario este logueado.
    */
    if (!isset($_SESSION["User"])) {
        
        header("Location: Login.php");  //Redireccionar al Login.
    }


    /*  
    Verificación de que el usuario seleccionó el Input de crear un Mensaje.
    */
    if (isset($_POST["formulario"])) {

        parse_str($_POST["formulario"], $datos);    //Convertir el string del $.ajax en una cadena de petición Url.  
        
        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        if (postChangeMensaje($datos)) {

            $_SESSION["usuarioChat"] = $datos["amp;id_other_user_chat"];

            echo '
                <div id="div_all_messages_Create">
                    <div id="div_title">
            ';
                            /*
                            Alvergar nombre de la persona del chat.
                            */
                            $sessionUserChat = personaChatDB($conn, $_SESSION["usuarioChat"]);

                            echo '
                                <img id="perfil_chat" src="./assets/images/' . $sessionUserChat["Photo_url"] .'">
                                <h1 id="titulo">' . $sessionUserChat["nombre_completo"] . '</h1>
                            ';
            echo '                        
                    </div>
                    <div id="div_messages_chat">
                        <div id="div_messages_chat_background">
            ';
                                /*
                                Creación de variables de chat.
                                */
                                $all_message_chats = myMessagesDB($conn, $_SESSION["id_users"], $_SESSION["usuarioChat"]);   //Alvergar los mensajes del chat.                               

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
            echo '
                        </div>
                        <script>
                            $("#div_messages_chat_background").scrollTop( $("#div_messages_chat_background").prop("scrollHeight") );
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
                                    <input type="text" name="txtMensaje_Messages_crear" id="txtMensaje_Messages_crear" placeholder="Aa" autocomplete="off">
                                </div>
                                <div id="button_send">
                                    <p id="area_button">
                                        <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                        <input type="hidden" name="id_chat" id="id_chat" value = "' . $datos["id_chat"] . '">
                                        <input type="hidden" name="id_other_user" id="id_other_user" value = "' . $_SESSION["usuarioChat"] . '">
                                        <input type="submit" name="btnEnviar_message_crear" id="btnEnviar_message_crear" value="holis">
                                    </p>                     
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            ';
        } elseif (postChangeMensajesNoChat($datos)) {
            
            $searchChat1 =  $datos["id_other_user_chat"] . '/' . $_SESSION["id_users"];
            $searchChat2 =  $_SESSION["id_users"] . '/' . $datos["id_other_user_chat"];

            $chat = FoundChatDB($conn, $searchChat1, $searchChat2);

            if ($chat) {
                
                $_SESSION["usuarioChat"] = $datos["id_other_user_chat"];

                echo '
                    <div id="div_all_messages_Create">
                        <div id="div_title">
                ';
                                /*
                                Alvergar nombre de la persona del chat.
                                */
                                $sessionUserChat = personaChatDB($conn, $_SESSION["usuarioChat"]);

                                echo '
                                    <img id="perfil_chat" src="./assets/images/' . $sessionUserChat["Photo_url"] .'">
                                    <h1 id="titulo">' . $sessionUserChat["nombre_completo"] . '</h1>
                                ';
                echo '                        
                        </div>
                        <div id="div_messages_chat">
                            <div id="div_messages_chat_background">
                ';
                                    /*
                                    Creación de variables de chat.
                                    */
                                    $all_message_chats = myMessagesDB($conn, $_SESSION["id_users"], $_SESSION["usuarioChat"]);   //Alvergar los mensajes del chat.                               

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
                echo '
                            </div>
                            <script>
                                $("#div_messages_chat_background").scrollTop( $("#div_messages_chat_background").prop("scrollHeight") );
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
                                        <input type="text" name="txtMensaje_Messages_crear" id="txtMensaje_Messages_crear" placeholder="Aa" autocomplete="off">
                                    </div>
                                    <div id="button_send">
                                        <p id="area_button">
                                            <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                            <input type="hidden" name="id_chat" id="id_chat" value = "' . $chat["id_chat"] . '">
                                            <input type="hidden" name="id_other_user" id="id_other_user" value = "' . $_SESSION["usuarioChat"] . '">
                                            <input type="submit" name="btnEnviar_message_crear" id="btnEnviar_message_crear" value="holis">
                                        </p>                     
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                ';
            }else {
                
                $_SESSION["usuarioChat"] = $datos["id_other_user_chat"];

                echo '
                    <div id="div_all_messages_Create">
                        <div id="div_title">
                ';
                                /*
                                Alvergar nombre de la persona del chat.
                                */
                                $sessionUserChat = personaChatDB($conn, $_SESSION["usuarioChat"]);

                                echo '
                                    <img id="perfil_chat" src="./assets/images/' . $sessionUserChat["Photo_url"] .'">
                                    <h1 id="titulo">' . $sessionUserChat["nombre_completo"] . '</h1>
                                ';
                echo '                        
                        </div>
                        <div id="div_messages_chat">
                            <div id="div_messages_chat_background">
                ';
                                    
                echo '
                            </div>
                            <script>
                                $("#div_messages_chat_background").scrollTop( $("#div_messages_chat_background").prop("scrollHeight") );
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
                                        <input type="text" name="txtMensaje_Messages_crear" id="txtMensaje_Messages_crear" placeholder="Aa" autocomplete="off">
                                    </div>
                                    <div id="button_send">
                                        <p id="area_button">
                                            <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                            <input type="hidden" name="id_chat" id="id_chat" value = "' . $searchChat2 . '">
                                            <input type="hidden" name="id_other_user" id="id_other_user" value = "' . $_SESSION["usuarioChat"] . '">
                                            <input type="submit" name="btnEnviar_message_crear" id="btnEnviar_message_crear" value="holis">
                                        </p>                     
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                ';
            }        
        }else {
            

        }
    }
?>