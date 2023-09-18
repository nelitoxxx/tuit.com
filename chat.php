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


    /*
    Creación de variables locales.
    */
    $conn = conexionDB();   //Alvergar la conexión con la DB.
    $all_message_chat = myMessagesDB($conn, $_SESSION["id_users"], $_SESSION["usuarioChat"]);   //Alvergar los mensajes del chat.

    $date_canstant_sent = 1;    //Variable de control del css al mensaje de envio.
    $date_canstant_received = 1;    //Variable de control del css al mensaje que se recibe.

    $write_div_send = "";   //Variable html para iniciar un caja de envio de mensaje.   
    $write_div_received = "";   //Variable html para iniciar un caja de recepción de mensaje.       

    $count_overflow = "";


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

                $date_canstant_sent = 1; //Alternar variable de fecha dependiendo abrir o cerrar una acaj de mensajes.

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
?>