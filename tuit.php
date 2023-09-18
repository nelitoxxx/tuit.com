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

        $elements_to_charge = tuitsWriteDB($conn, $last_tweet); 
        $num_elements_to_charge = count($elements_to_charge);

        if ($num_elements_to_charge) {
            
            foreach ($elements_to_charge as $tweets) {
                
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
                <form id="id_tweet_form" method="post">
                    <input type="hidden" class="last_tweet" class="last_tweet" name="last_tweet" value="' . $next_tweet . '"</input>
                </form>
            ';
        }
    }
?>