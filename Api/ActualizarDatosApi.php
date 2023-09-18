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
            $array_usuario = array(
                "txtNombre" => $data -> data -> First_name,
                "txtApellido" => $data -> data -> Last_name,
                "txtCorreo" => $data -> data -> Email,
                "txtDir" => $data -> data -> Address_house,
                "txtNumHijos" => $data -> data -> Childs,
                "txtEstCilvil" => $data -> data -> Marital_Status,
                "fullFoto" => $data -> data -> Photo_url
            );
        } catch (\Throwable $th) {
            
            echo "Credenciales erroneas";
            echo $th;
            http_response_code(401);
            exit;
        }
    }
    else {

        echo "Acceso no autorizado";
        http_response_code(401);
        exit;
    }

    $_PUT = array();
    parse_str(file_get_contents("php://input"), $_PUT);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        $nombres_post = [];
        $nombres_comparar = ["txtNombre", "txtApellido", "txtCorreo", "txtDir", "txtNumHijos",
                            "txtEstCilvil", "fullFoto"];
        $estadoCivil_permitidos = ["casado", "soltero", "union_marital"];

        foreach ($_POST as $key => $value) {
            
            array_push($nombres_post, $key);
        }

        foreach ($nombres_post as $key => $value) {
            
            if (!in_array($value, $nombres_comparar)) {
                
                echo "Acceso denegado";
                http_response_code(401);
                exit;
            }
        }

        foreach ($nombres_post as $key => $value) {          
            foreach ($array_usuario as $key1 => $value1) {           
                if ($key1 == $value) {
                    
                    $array_usuario[$key1] = $_POST[$value];
                }
            }
        }

        if (isset($_POST["txtEstCilvil"])) {           
            if (!in_array($_POST["txtEstCilvil"], $estadoCivil_permitidos)) {
                
                echo "Acceso denegado";
                http_response_code(401);
                exit;
            }
            
        }

        if (isset($_POST["fullFoto"])) {
            
            $ruta = base64_to_jpeg($_POST["fullFoto"]);

            if (is_string($ruta)) {
                
                $array_usuario["fullFoto"] = $ruta;
            }
            else {
                
                echo "Acceso denegado";
                http_response_code(401);
                exit;
            }
        }

        updateDataDB($conn, $array_usuario["txtNombre"], $array_usuario["txtApellido"], $array_usuario["txtCorreo"], 
                    $array_usuario["txtDir"], $array_usuario["txtNumHijos"], $array_usuario["txtEstCilvil"], 
                    $array_usuario["fullFoto"], $id_usuario);
        
        $key = 'my_secret_key';
        $data = array(
            "iat" => $time,
            "exp" => $time + (60*60),
            "data" => [
                "usuario" => $usuario,
                "First_name" => $array_usuario["txtNombre"],
                "Last_name" => $array_usuario["txtApellido"],
                "Email" => $array_usuario["txtCorreo"],
                "Address_house" => $array_usuario["txtDir"],
                "Childs" => $array_usuario["txtNumHijos"],
                "Marital_Status" => $array_usuario["txtEstCilvil"],
                "Photo_url" => $array_usuario["fullFoto"],
                "id" => $id_usuario
            ]
        );

        $jwt = JWT::encode($data, $key);
        echo $jwt;
        header("HTTP/1.1 200 OK");
        exit();       
    }
?>