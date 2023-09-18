<?php
    require_once ("../libs/tools.php");
    require_once ("../libs/db.php");


    limpiarEntradas();
    $conn = conexionDB();


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        if (postInputsRegistrarApi($_POST)) {

            $estadoCivil_permitidos = ["casado", "soltero", "union_marital"];
            
            $ruta =base64_to_jpeg($_POST["fullFoto"]);

            if(is_string($ruta) && in_array($_POST["txtEstCilvil"], $estadoCivil_permitidos)) {

                if(grabarDB($conn, $_POST["txtNombre"], $_POST["txtApellido"], $_POST["txtCorreo"], $_POST["txtDir"],
                    $_POST["txtNumHijos"], $_POST["txtEstCilvil"], $ruta, $_POST["txtUsuario"], 
                    $_POST["txtClave"])) {
                    
                    header("HTTP/1.1 200 OK");
                    echo "registro exitoso";
                    exit;
                }
                else {
                    
                    echo "No se pudo registrar";
                    http_response_code(401);
                    exit;
                }
            }
            else {
                
                echo "Credenciales incorrectas";
                http_response_code(401);
                exit;
            }
        }
        else {
            
            http_response_code(401);
            exit;
        }
    }
?>