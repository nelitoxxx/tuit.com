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
            $usuario = $data -> data -> usuario;
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
        
        $datos = listaPersonasApiDB($conn, $id_usuario);
        header("HTTP/1.1 200 OK");
        echo json_encode($datos);
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        if (postInputsMensajeApi($_POST)) {
            
            if ($_POST["fulAdjunto"] != "") {
                
                $ruta =base64($_POST["fulAdjunto"]);
            }
            else {
                
                $ruta = "Sin archivo adjunto";
            }
            
            if (is_string($ruta)) {
                
                $pid = messageDB($conn, $_POST["txtMensaje_Messages_crear"], $ruta, $_POST["cmbDestino"], $id_usuario);
                $datos = ["id" => $pid];
                header("HTTP/1.1 200 OK");
                echo json_encode($datos);
                exit;
            }
        }
        else {

            echo "Error";
            http_response_code(401);
            exit;
        }
    }
?>