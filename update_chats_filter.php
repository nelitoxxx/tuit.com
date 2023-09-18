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

    /*  
    Verificación de que el usuario seleccionó el Input de crear un Mensaje.
    */
    if (isset($_POST["formulario"])) {

        parse_str($_POST["formulario"], $datos);    //Convertir el string del $.ajax en una cadena de petición Url.  
        
        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
        //if (postInputsMensaje($datos)) {   

            $list_filter = listUsers($conn, $datos["search_chat"]);
 
            if (count($list_filter) >= 1) {

                foreach ($list_filter as $list) {
            
                    echo '
                        <form class="form_lista_personas" id="form_chat_' . $list["id_users"] . '" onclick="changeChatUser(event)" method="post">
                            <div class="lista_personas_chat">
                                <div class="border_image_perfil_filter">
                                    <img class="perfil_mensajes_filter" id="' . $list["id_users"] . '" src="./assets/images/' . $list["Photo_url"] .'">
                                </div>
                                <div class="conteiner_buttom_list">
                                    <input type="hidden" name="id_other_user_chat" id="id_other_user_chat" value = "' . $list["id_users"] . '">
                                    <input type="hidden" name="csrf" id="csrf" value = "' . $_SESSION["csrf"] . '">
                                    <label type="text" class="BtnPersona" name="BtnPersona" id="' . $list["id_users"] . '">' . $list["nombre_completo"] . '</label>                     
                                </div>
                            </div>
                        </form>
                    ';
                }
            }else {
                
                echo '
                        <form class="form_lista_personas" method="post">
                            <h2 class="no_registros">No se encontró a ningún usuario...</h2>
                        </form>
                    ';
            }
        //}
    }
?>