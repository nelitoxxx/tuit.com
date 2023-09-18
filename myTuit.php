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

    $conn = conexionDB();   //Alvergar la conexión con la DB.

    /*  
    Verificación de que el usuario este logueado.
    */
    if (!isset($_SESSION["User"])) {
        
        header("Location: Login.php");  //Redireccionar al Login.
    }

    /*
    validación y asignación de qué exista el índice del último tweet.
    */
    if (isset($_POST["formulario"])) {

        parse_str($_POST["formulario"], $datos);    //Convertir el string del $.ajax en una cadena de petición Url.

        $last_tweet = 8 * intval($datos["last_tweet"]);
        $next_tweet = $datos["last_tweet"] + 1;

        $elements_to_charge = myTuitsWriteDB($conn, $_SESSION["id_users"], $last_tweet); 
        $num_elements_to_charge_my_tweets = count($elements_to_charge);

        if ($num_elements_to_charge_my_tweets) {
            
            foreach ($elements_to_charge as $tweets) {
                
                $is_public = "";    //Variable String para cambiar de público a no público.
                
                /*  
                Verificación al escribir si es público o no público.
                */
                if($tweets["Is_public"]==1) {
    
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
                                    <label name="lblFecha_1" class="lblFecha_1">' . $tweets["Create_at"] . '</label>
                                </div>
                            </div>
                            <div class="div-down">
                                <label name="lblTexto_1" class="lblTexto_1">' . $tweets["Tweet"] . '</label>';
                                
                                
                    if ($tweets["image_tweet"] != 'none') {
                        
                        echo '<img name="lblImage_1" class="lblImage_1" src="./assets/images/' . $tweets["image_tweet"] .'" alt="">';
                    }
                echo '         
                                <div class="div_My_tweets_bottom">
                                    <div id="' . $tweets["id_tweets"] .'" class="icon_image_points" onclick="buttom_my_tweets(event)">
                                        <img id="' . $tweets["id_tweets"] .'" class="image_points" src="./assets/header/icon_points.png" alt="">
                                        <form class="form_my_tweets" id="form_'. $tweets["id_tweets"] .'" action="" method="post" style="display: none;">
                                            <input type="hidden" name="txtTexto_1_my_tweets" class="txtTexto_1_my_tweets" value="' . $tweets["Tweet"] . '">
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
                    <input type="hidden" class="last_tweet" class="last_tweet" name="last_tweet" value="' . $next_tweet . '"</input>
                </form>
            ';
        }
    }
?>