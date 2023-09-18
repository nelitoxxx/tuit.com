<?php
    require_once ("../vendor/autoload.php");
    require_once ("../libs/tools.php");
    require_once ("../libs/db.php");

    use Firebase\JWT\JWT;

    $key = 'my_secret_key';
    $time = time();
    limpiarEntradas();
    $conn = conexionDB();


    $jwt = $_SERVER["HTTP_AUTHORIZATION"];

    if(substr($jwt, 0, 6) === "Bearer") {

        $jwt = str_replace("Bearer ", "", $jwt);

        try {
                
            $data = JWT::decode($jwt, $key, array("HS256"));
            $id_usuario = $data -> data -> id;
        } catch (\Throwable $th) {
            
            echo "Credenciales erroneas";
            http_response_code(401);
            exit;
        }
    }
    else {

        echo "Acceso no autorizado";
        http_response_code(401);
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        
        $datos = TuitsWriteApiDB($conn);
        header("HTTP/1.1 200 OK");
        echo json_encode($datos);
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        if (postInputsTuitApi($_POST)) {
            
            $pid = tuitsDB($conn, $_POST["txtMensaje_create_tweets"], $_POST["chkPublico_create_tweets"], $id_usuario);
            $datos = ["id" => $pid];
            header("HTTP/1.1 200 OK");
            echo json_encode($datos);
            exit;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "PUT") {
        
        if (isset($_GET["txtTexto_1_my_tweets"]) && isset($_GET["btnPublicar_1"])) {
            
            $pid = publicarTuitDB($conn, $id_usuario, $_GET["txtTexto_1_my_tweets"]);
            $datos = ["id" => $pid];
            header("HTTP/1.1 200 OK");
            echo json_encode($datos);
            exit;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "PUT") {
        
        if (isset($_GET["txtTexto_1_my_tweets"]) && isset($_GET["btnDespublicar_1"])) {
            
            $pid = despublicarTuitDB($conn, $id_usuario, $_GET["txtTexto_1_my_tweets"]);
            $datos = ["id" => $pid];
            header("HTTP/1.1 200 OK");
            echo json_encode($datos);
            exit;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
        
        if (isset($_GET["txtTexto_1_my_tweets"]) && isset($_GET["btnBorrar_1"])) {
            
            $pid = borrarTuitDB($conn, $id_usuario, $_GET["txtTexto_1_my_tweets"]);
            $datos = ["id" => $pid];
            header("HTTP/1.1 200 OK");
            echo json_encode($datos);
            exit;
        }
    }
?>