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

    /*  
    Verificación de que el usuario este logueado.
    */
    if (!isset($_SESSION["User"])) {
        
        header("Location: Login.php");  //Redireccionar al Login.
    }

    $chats = ChatUsersDB($conn, $_SESSION["id_users"]);   //Alvergar los chats del Uusuario.

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
                        <label type="text" class="BtnPersona" name="BtnPersona" id="' . $chat["id_chat"] . '">
                        ' . $other_user_chat["nombre_completo"] . '</label>
        ';

        if ($last_message_chat["Sender_user_id"] == $_SESSION["id_users"]) {
            
            echo '
                        <div class="div_you">
                            <p class="p_you" id="' . $chat["id_chat"] . '">You: </p>
                            <label type="text" class="last_message" id="' . $chat["id_chat"] . '">' . $last_message_chat["message_users"] .'</label>
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
