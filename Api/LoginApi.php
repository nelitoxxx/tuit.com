<?php
    require_once ("autoload.php");
    require_once ("libs/tools.php");
    require_once ("libs/db.php");

    use Firebase\JWT\JWT;

    limpiarEntradas();
    $conn = conexionDB();
    $key = 'my_secret_key';
    $time = time();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        if (postInputsLoginApi($_POST)) {
            
            $logueo = loginApiDB($conn, $_POST["txtUsuario"], $_POST["txtClave"]);
            if ($logueo) {
                
                $data = array(
                    "iat" => $time,
                    "exp" => $time + (60*60),
                    "data" => [
                        "usuario" => $logueo["User"],
                        "First_name" => $logueo["First_name"],
                        "Last_name" => $logueo["Last_name"],
                        "Email" => $logueo["Email"],
                        "Address_house" => $logueo["Address_house"],
                        "Childs" => $logueo["Childs"],
                        "Marital_Status" => $logueo["Marital_Status"],
                        "Photo_url" => $logueo["Photo_url"],
                        "id" => $logueo["id_users"]
                    ]
                );

                $jwt = JWT::encode($data, $key);
                echo $jwt;
                header("HTTP/1.1 200 OK");
                exit();
            }
            else {
                
                echo "Acceso no autorizado";
                http_response_code(401);
                exit();
            }
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
            
        $jwt = $_SERVER["HTTP_AUTHORIZATION"];

        if(substr($jwt, 0, 6) === "Bearer") {

            $jwt = str_replace("Bearer ", "", $jwt);

            try {
                
                $data = JWT::decode($jwt, $key, array("HS256"));
                echo json_encode($data);
                http_response_code(200);
                exit;
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
    }
?>