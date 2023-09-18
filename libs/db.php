<?php

    require_once("tools.php");

    /**
    *Genera la conexión con la base de datos de tipo PDO.
    *
    */
    function conexionDB(){
		$servername = "localhost";
		$database = "tuit";
		$username = "root";
		$password = "1234";

		$sql = "mysql:host=$servername;dbname=$database;";
		$dsn_Options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

		try { 
			$my_Db_Connection = new PDO($sql, $username, $password, $dsn_Options);
			return $my_Db_Connection;
		} catch (PDOException $error) {
			echo 'Connection error: ' . $error->getMessage();
			return NULL;
		}
	}



    /**
	*Graba los datos de los usuarios en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $first_name: Nombre del usuario en string.
	*@param $second_name: Apellido dle usuario en string.
    *@param $email: Email de la persona.
	*@param $address_house: Dirección.
	*@param $childs: número de hijos.
    *@param $marital_status: Estado civil.
	*@param $photo_url: nombre de la imágen subida.
    *@param $usuario: Login del usuario en string.
	*@param $clave: Contraseña del usuario en string.
    *@param $code_verify_Account: JWT que alverga el codigo de activación de la cuenta.
	*/
    function grabarDB($conexion, $first_name, $second_name, $email, $address_house, $childs, 
                        $marital_status, $photo_url, $user, $password, $code_verify_Account){

		$password = password_hash($password, PASSWORD_DEFAULT);

        try {
            
            $comprobar_usuario = $conexion->prepare("SELECT User FROM users WHERE User = :User OR Email= :Email");
            $comprobar_usuario->bindParam(":User", $user, PDO::PARAM_STR);
            $comprobar_usuario->bindParam(":Email", $email, PDO::PARAM_STR);
            $comprobar_usuario->execute();
            $comprobar_usuario->fetchAll();
            $num_filas = $comprobar_usuario->rowCount();

            if($num_filas == 1) {

                echo '<script>alert("El Usuario o Correo ya existen");</script>';
                return false;
            }else {
                
                $registro = $conexion->prepare("INSERT INTO users (
                                                First_name,
                                                Last_name,
                                                Email,
                                                Address_house,
                                                Childs,
                                                Marital_Status,
                                                Photo_url,
                                                User,
                                                Password_user,
                                                Code_Verify_Account)

                                                VALUES (:First_name, :Last_name, :Email, :Address_house, :Childs, :Marital_Status, :Photo_url,
                                                        :User, :Password_user, :Code_Verify_Account)");
                
                $registro->bindParam(":First_name", $first_name, PDO::PARAM_STR);
                $registro->bindParam(":Last_name", $second_name, PDO::PARAM_STR);
                $registro->bindParam(":Email", $email, PDO::PARAM_STR);
                $registro->bindParam(":Address_house", $address_house, PDO::PARAM_STR);
                $registro->bindParam(":Childs", $childs, PDO::PARAM_INT);
                $registro->bindParam(":Marital_Status", $marital_status, PDO::PARAM_STR);
                $registro->bindParam(":Photo_url", $photo_url, PDO::PARAM_STR);
                $registro->bindParam(":User", $user, PDO::PARAM_STR);
                $registro->bindParam(":Password_user", $password, PDO::PARAM_STR);
                $registro->bindParam(":Code_Verify_Account", $code_verify_Account, PDO::PARAM_STR);
                
                $registro->execute();
                return true;
            }
        } catch (\Throwable $th) {
            
            echo $th;
        }
	}



    /**
	*Activa la cuenta del usuario recien registrado.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $usuario: Login Usuario.
	*/
    function activeAccountDB($conexion, $usuario){

        try {
            
            $Code_recovery = $conexion->prepare("UPDATE users SET Verify_account = 1, Code_Verify_Account = 0
                                                WHERE User = :User");
            $Code_recovery->bindParam(":User", $usuario);

            if ($Code_recovery->execute()) {
                
                return true;
            }
            else {
                
                return false;
            }
        } catch (\Throwable $th) {
            
            throw $th;
        }
	}



    /**
	*Carga clave y usuario en la sesion y verifica que exista en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $usuario: Login Usuario.
	*@param $password: Contraseña Usuario.
	*/
    function loginDB($conexion, $usuario, $password, $validar){

        $ip = getIp();

        try {
            
            $validar_usuario = $conexion->prepare("SELECT id_users, First_name, Last_name, Email, Address_house, Childs,
                                                Marital_Status, Photo_url, User, Password_user, is_Login, IP
                                                FROM users WHERE User = :User AND Verify_account = 1");
            $validar_usuario->bindParam(":User", $usuario, PDO::PARAM_STR);
            $validar_usuario->execute();
            $validar_password = $validar_usuario->fetch(PDO::FETCH_ASSOC);
            
            if (is_array($validar_password)) {
                
                if (password_verify($password, $validar_password["Password_user"])) {

                    if ($validar_password["is_Login"] == 1) {
                                              
                        if ($validar == 1) {
                            
                            if (writeIpDB($conexion, $ip, $usuario, 1)) {
                        
                                $_SESSION["User"] = $validar_password["User"];
                                $_SESSION["First_name"] = $validar_password["First_name"];
                                $_SESSION["Last_name"] = $validar_password["Last_name"];
                                $_SESSION["Email"] = $validar_password["Email"];
                                $_SESSION["Address_house"] = $validar_password["Address_house"];
                                $_SESSION["Childs"] = $validar_password["Childs"];
                                $_SESSION["Marital_Status"] = $validar_password["Marital_Status"];
                                $_SESSION["Photo_url"] = $validar_password["Photo_url"];
                                $_SESSION["id_users"] = $validar_password["id_users"];
                    
                                return true;
                            }
                            else {
                                
                                return false;
                            }
                        }
                        else {
                            
                            $_SESSION["User_Validate"] = $usuario;
                            $_SESSION["Password_Validate"] = $password;
                            header("location: LoginSeguro.php");
                            exit;
                        }  
                    }

                    if (writeIpDB($conexion, $ip, $usuario, 1)) {
                        
                        $_SESSION["User"] = $validar_password["User"];
                        $_SESSION["First_name"] = $validar_password["First_name"];
                        $_SESSION["Last_name"] = $validar_password["Last_name"];
                        $_SESSION["Email"] = $validar_password["Email"];
                        $_SESSION["Address_house"] = $validar_password["Address_house"];
                        $_SESSION["Childs"] = $validar_password["Childs"];
                        $_SESSION["Marital_Status"] = $validar_password["Marital_Status"];
                        $_SESSION["Photo_url"] = $validar_password["Photo_url"];
                        $_SESSION["id_users"] = $validar_password["id_users"];
            
                        return true;
                    }
                    else {
                        
                        return false;
                    }
                }else {
                    
                    return false;
                }
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {

            //throw $th;
        }
	}



    /**
	*Carga usuario y correo para verificar que existan y correspondan.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $usuario: Login Usuario.
	*@param $email: Email del usuario.
	*/
    function verifyEmailDB($conexion, $usuario, $email){

        try {
            
            $validar_usuario = $conexion->prepare("SELECT User, Email, concat (First_name, ' ', Last_name) AS Full_name 
                                                FROM users WHERE User = :User AND Email = :Email");
            $validar_usuario->bindParam(":User", $usuario, PDO::PARAM_STR);
            $validar_usuario->bindParam(":Email", $email, PDO::PARAM_STR);
            $validar_usuario->execute();
            $validar_email = $validar_usuario->fetch(PDO::FETCH_ASSOC);
            
            if (is_array($validar_email)) {

                return $validar_email;
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {

            //throw $th;
        }
	}



    /**
	*Carga codigo de recuperación.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $usuario: Login Usuario.
	*/
    function verifyCodeDB($conexion, $usuario){

        try {
            
            $validar_codigo = $conexion->prepare("SELECT Code_recovery 
                                                FROM users WHERE User = :User");
            $validar_codigo->bindParam(":User", $usuario, PDO::PARAM_STR);
            $validar_codigo->execute();
            $array_codigo = $validar_codigo->fetch(PDO::FETCH_ASSOC);
            
            if (is_array($array_codigo)) {

                return $array_codigo;
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {

            //throw $th;
        }
	}



    /**
	*Escribe el código encriptado para la recuperación por correo.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $code: Codigo de recuperación encriptado, eliminar el código.
    *@param $usuario: Cuenta del Usuario a anexar el código.
	*/
    function writeCodeRecoveryDB($conexion, $code, $usuario) {

        try {
            
            $Code_recovery = $conexion->prepare("UPDATE users SET Code_recovery = :Code_recovery WHERE User = :User");
            $Code_recovery->bindParam(":User", $usuario);
            $Code_recovery->bindParam(":Code_recovery", $code);

            if ($Code_recovery->execute()) {
                
                return true;
            }
            else {
                
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Escribe en la base de datos el tiempo a volver a pedir la recuperación de la cuenta.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $code: Codigo de recuperación encriptado, eliminar el código.
    *@param $usuario: Cuenta del Usuario a anexar el código.
	*/
    function writeTimeRecoveryDB($conexion, $time, $usuario) {

        try {
            
            $Code_recovery = $conexion->prepare("UPDATE users SET Time_recovery = :Time_recovery WHERE User = :User");
            $Code_recovery->bindParam(":User", $usuario);
            $Code_recovery->bindParam(":Time_recovery", $time);

            if ($Code_recovery->execute()) {
                
                return true;
            }
            else {
                
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Conocer si el usuario ya ha hecho esta petición seguidamente.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $usuario: Login Usuario.
	*/
    function verifyTimeRecoveryDB($conexion, $usuario){

        try {
            
            $validar_time_recovery = $conexion->prepare("SELECT Time_recovery 
                                                        FROM users WHERE User = :User");
            $validar_time_recovery->bindParam(":User", $usuario, PDO::PARAM_STR);
            $validar_time_recovery->execute();
            $array_time_recovery = $validar_time_recovery->fetch(PDO::FETCH_ASSOC);
            
            $time = time();

            if (is_array($array_time_recovery)) {
                
                if ($array_time_recovery["Time_recovery"] < $time) {

                    return true;
                }else {
                    
                    return false;
                }
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {

            //throw $th;
        }
	}



    /**
	*Extraer datos de la Ip actual en base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Usuario actual de la base de datos.
	*/
    function IpDB($conexion, $usuario) {

        try {
            
            $validarIp = $conexion->prepare("SELECT IP, is_Login FROM users WHERE User = :User");
            $validarIp->bindParam(":User", $usuario);
            $validarIp->execute();
            $compararIp = $validarIp->fetch(PDO::FETCH_ASSOC);

            return $compararIp;

        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Escribe la ip actual en la base de datos.
	*
    *@param $Ip: Conexión PDO para la base de datos.
	*/
    function writeIpDB($conexion, $ip, $usuario, $login) {

        try {
            
            $validarIp = $conexion->prepare("UPDATE users SET IP = :IP, is_Login = :is_Login WHERE User = :User");
            $validarIp->bindParam(":User", $usuario);
            $validarIp->bindParam(":IP", $ip);
            $validarIp->bindParam(":is_Login", $login);

            if ($validarIp->execute()) {
                
                return true;
            }
            else {
                
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Recibe una nueva clave y la confirmación para así poder cambiar la clave en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Usuario activo en la sesión.
	*@param $password_write: Clave en el formulario para comparar con el .txt
	*@param $new_clave: La nueva clave del usuario para cambiar.
	*@param $confirmacion_clave: Clave de confirmación que coincida con la nueva.
	*/
    function changePasswordDB($conexion, $usuario, $password_write, $new_clave, $confirmacion_clave){

        try {
            
            if ($new_clave == $confirmacion_clave) {
            
                $validar_usuario = $conexion->prepare("SELECT * FROM users WHERE User = :User");
                $validar_usuario->bindParam(":User", $usuario, PDO::PARAM_STR);
                $validar_usuario->execute();
                $validar_password = $validar_usuario->fetch(PDO::FETCH_ASSOC);
    
                if (password_verify($password_write, $validar_password["Password_user"])) {
                    
                    $new_clave = password_hash($new_clave, PASSWORD_DEFAULT);
    
                    $insert_password = $conexion->prepare("UPDATE users SET Password_user = :Password_user WHERE User = :User");
                    $insert_password->bindParam(":Password_user", $new_clave, PDO::PARAM_STR);
                    $insert_password->bindParam(":User", $usuario);
                    $insert_password->execute();
    
                    return true;
                }else {
                    
                    echo '<script>alert("Las claves no coinciden");</script>';
                    return false;
                }
            }else{
    
                echo '<script>alert("Las claves no coinciden");</script>';
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Recibe una nueva clave y la confirmación para así poder cambiar la clave en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $first_name: Primer nomnre del usuario.
    *@param $second_name: Segundo nomnre del usuario.
	*@param $email: Email del usuario.
	*@param $address_house: Dirección del usuario.
	*@param $childs: Número de hijos del usuario.
	*@param $marital_status: Estado civil del usuario.
	*@param $photo_url: Nombre de la foto.
	*@param $id: Id del usuario.
	*/
    function updateDataDB($conexion, $first_name, $second_name, $email, $address_house, $childs, 
                            $marital_status, $photo_url, $id){

        try {
            
            $new_data = $conexion->prepare("UPDATE users SET First_name = :First_name, Last_name = :Last_name, 
                                            Email = :Email, Address_house = :Address_house,
                                            Childs = :Childs, Marital_Status = :Marital_Status, Photo_url = :Photo_url
                                            WHERE id_users = :id_users");
        
            $new_data->bindParam(":First_name", $first_name, PDO::PARAM_STR);
            $new_data->bindParam(":Last_name", $second_name, PDO::PARAM_STR);
            $new_data->bindParam(":Email", $email, PDO::PARAM_STR);
            $new_data->bindParam(":Address_house", $address_house, PDO::PARAM_STR);
            $new_data->bindParam(":Childs", $childs, PDO::PARAM_INT);
            $new_data->bindParam(":Marital_Status", $marital_status, PDO::PARAM_STR);
            $new_data->bindParam(":Photo_url", $photo_url, PDO::PARAM_STR);
            $new_data->bindParam(":id_users", $id);

            if ($new_data->execute()) {
                
                $_SESSION["First_name"] = $first_name;
                $_SESSION["Last_name"] = $second_name;
                $_SESSION["Email"] = $email;
                $_SESSION["Address_house"] = $address_house;
                $_SESSION["Childs"] = $childs;
                $_SESSION["Marital_Status"] = $marital_status;
                $_SESSION["Photo_url"] = $photo_url;

                return true;
            }else {
                
                return false;
            } 
        } catch (\Throwable $th) {
            //throw $th;
        }
            
         
    }



    /**
	*Graba el tuit que los usuarios hagan en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $tuit: Tuit escrito por el usuario en string.
    *@param $image: Posible imágen que tenga el tuit.
    *@param $public: Valor para definir si se publica el tweet.
    *@param $usuario: id del Usuario activo en la sesión.
	*/
    function tuitsDB($conexion, $tuit, $image, $public, $usuario){

        try {
            
            $write_tuit = $conexion->prepare("INSERT INTO tweets (
                            user_tweet_id,
                            Tweet,
                            image_tweet,
                            Is_public)

                            VALUES (:user_tweet_id, :Tweet, :image_tweet, :Is_public)");

            $write_tuit->bindParam(":user_tweet_id", $usuario, PDO::PARAM_INT);
            $write_tuit->bindParam(":Tweet", $tuit, PDO::PARAM_STR);
            $write_tuit->bindParam(":image_tweet", $image, PDO::PARAM_STR);
            $write_tuit->bindParam(":Is_public", $public, PDO::PARAM_INT);

            if($write_tuit->execute()) {

                return true;
            }else {

                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Recoge los tuits de la base de datos y los escribe en un parrafo en html iterando en todos los tuits.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*/
    function tuitsWriteDB($conexion, $section){

        try {
            
            $get_tuit = $conexion->prepare("SELECT concat (u.First_name, ' ', u.Last_name) as creater_tweet, u.Photo_url, t.Tweet, 
                                            t.image_tweet, t.Create_at, t.id_tweets 
        
                                            FROM tweets t

                                            INNER JOIN users u on (t.user_tweet_id=u.id_users)
                                            WHERE t.Is_public='1'
                                            ORDER BY t.Create_at DESC
                                            LIMIT :section, 8");
            $get_tuit->bindParam(":section", $section, PDO::PARAM_INT);
            $get_tuit->execute();
            $array_get_tuit = $get_tuit->fetchAll(PDO::FETCH_ASSOC);

            return $array_get_tuit;
        } catch (\Throwable $th) {           
            //throw $th;
            exit;
        }
    }



    /**
	*Recoge los tuits propios del usuarios activo de la base de datos y los escribe en un parrafo en html iterando en todos los tuits.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $id_usuario: id del Usuario activo en la sesión.
	*/
    function myTuitsWriteDB($conexion, $id_usuario, $section){

        try {
            
            $get_tuit = $conexion->prepare("SELECT t.id_tweets, t.Tweet, t.Create_at, t.Is_public, t.image_tweet 
                                            from tweets t 
                                            inner join users u on (t.user_tweet_id=u.id_users) 
                                            WHERE u.id_users = :id_users
                                            ORDER BY t.Create_at DESC
                                            LIMIT :section, 8");
            $get_tuit->bindParam(":id_users", $id_usuario);
            $get_tuit->bindParam(":section", $section, PDO::PARAM_INT);
            $get_tuit->execute();
            $array_get_tuit = $get_tuit->fetchAll(PDO::FETCH_ASSOC);

            return $array_get_tuit;
        } catch (\Throwable $th) {
            //throw $th;
            exit;
        }
    }



    /**
	*Borra el tuit en especifico del usuario en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Usuario activo en la sesión.
	*@param $tuit: El tuit especifico que escribió el usuario.
	*/
	function borrarTuitDB($conexion, $usuario, $tuit){

        try {
            
            $erase_tuit = $conexion->prepare("DELETE FROM tweets WHERE Tweet = :Tweet AND user_tweet_id = :user_tweet_id");
            $erase_tuit->bindParam(":user_tweet_id", $usuario);
            $erase_tuit->bindParam(":Tweet", $tuit, PDO::PARAM_STR);
            
            if($erase_tuit->execute()) {

                return true;
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
	}



    /**
	*Publica el tuit en especifico del usuario en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Usuario activo en la sesión.
	*@param $tuit: El tuit especifico que escribió el usuario.
	*/
	function publicarTuitDB($conexion, $usuario, $tuit){

		try {
        
            $public_tuit = $conexion->prepare("UPDATE tweets SET Is_public='1' WHERE Tweet = :Tweet AND user_tweet_id = :user_tweet_id");
            $public_tuit->bindParam(":user_tweet_id", $usuario);
            $public_tuit->bindParam(":Tweet", $tuit, PDO::PARAM_STR);
            
            if($public_tuit->execute()) {

                return true;
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
	} 
    
    

    /**
	*Publica el tuit en especifico del usuario en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Usuario activo en la sesión.
	*@param $tuit: El tuit especifico que escribió el usuario.
	*/
	function despublicarTuitDB($conexion, $usuario, $tuit){

        try {
            
            $public_tuit = $conexion->prepare("UPDATE tweets SET Is_public='0' WHERE Tweet = :Tweet AND user_tweet_id = :user_tweet_id");
            $public_tuit->bindParam(":user_tweet_id", $usuario);
            $public_tuit->bindParam(":Tweet", $tuit, PDO::PARAM_STR);
            
            if($public_tuit->execute()) {

                return true;
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
	}
    
    

    /**
	*Lista los nombres de todas las persona .
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Id del usuario logueado.
	*/
    function ChatUsersDB($conexion, $usuario) {

        $strUsuario = strval($usuario);
        $strUsuario1 = '%/' . $strUsuario;
        $strUsuario2 = $strUsuario . '/%';

        try {
            
            $lista_chats = $conexion->prepare("SELECT date_last_message, users, id_chat
                                                FROM chats 
                                                WHERE users LIKE :id_users1 OR users LIKE :id_users2
                                                ORDER BY date_last_message DESC");
            $lista_chats->bindParam(":id_users1", $strUsuario1, PDO::PARAM_STR);
            $lista_chats->bindParam(":id_users2", $strUsuario2, PDO::PARAM_STR) ;
            $lista_chats->execute();
            $array_lista_chats = $lista_chats->fetchAll(PDO::FETCH_ASSOC);

            return $array_lista_chats;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    

    /**
	*Encontrar Chat del usuario.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario1: Id de los usuarios.
    *@param $usuario2: Id de los usuarios.
	*/
    function FoundChatDB($conexion, $usuario1, $usuario2) {

        try {
            
            $lista_chats = $conexion->prepare("SELECT date_last_message, users, id_chat
                                                FROM chats 
                                                WHERE users LIKE :id_users1 OR users LIKE :id_users2
                                                ORDER BY date_last_message DESC");
            $lista_chats->bindParam(":id_users1", $usuario1, PDO::PARAM_STR);
            $lista_chats->bindParam(":id_users2", $usuario2, PDO::PARAM_STR);
            $lista_chats->execute();
            $array_lista_chats = $lista_chats->fetch(PDO::FETCH_ASSOC);

            return $array_lista_chats;
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    /**
	*Encontrar Chat del usuario.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuarios: Usuarios del chat.
	*/
    function CreateChatDB($conexion, $usuarios) {

        try {
            
            $create_chat = $conexion->prepare("INSERT INTO chats(
                                                users, 
                                                date_last_message) 
                                                VALUES (:users, CURRENT_TIME)");
            $create_chat->bindParam(":users", $usuarios, PDO::PARAM_STR);

            if($create_chat->execute()) {

                return true;
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    /**
	*Trae el último mensaje de ese chat.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario1: Id de uno de los usuarios del chat.
    *@param $usuario2: Id de uno de los usuarios del chat.
	*/
    function lastMessage($conexion, $usuario1, $usuario2){

        try {
            $last_message = $conexion->prepare("SELECT message_users, date_message, Sender_user_id
                                                FROM messages
                                                WHERE (Sender_user_id = :id_users1 AND Recepter_user_id = :id_users2)
                                                    OR (Sender_user_id = :id_users2 AND Recepter_user_id = :id_users1)
                                                ORDER BY date_message DESC
                                                LIMIT 1");
            $last_message->bindParam(":id_users1", $usuario1, PDO::PARAM_INT);
            $last_message->bindParam(":id_users2", $usuario2, PDO::PARAM_INT) ;
            $last_message->execute();
            $array_last_message = $last_message->fetch(PDO::FETCH_ASSOC);

            return $array_last_message;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Trae la información del usuario del chat
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Id del usuario del chat.
	*/
    function otherUserChat($conexion, $usuario){

        try {
            $other_user_chat = $conexion->prepare("SELECT concat(First_name,  ' ', Last_name) AS nombre_completo, Photo_url 
                                                FROM users
                                                WHERE id_users = :id_users");
            $other_user_chat->bindParam(":id_users", $usuario, PDO::PARAM_INT);

            $other_user_chat->execute();
            $array_other_user_chat = $other_user_chat->fetch(PDO::FETCH_ASSOC);

            return $array_other_user_chat;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Trae la información del usuario del chat
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $first_name: Nombre del usuario a filtrar.
	*/
    function listUsers($conexion, $first_name){

        $first_name = '%' . $first_name . '%';
        try {
            $other_user_chat = $conexion->prepare("SELECT id_users, concat(First_name, ' ', Last_name) AS nombre_completo, Photo_url 
                                                    FROM users 
                                                    WHERE First_name LIKE :Firstname OR Last_name LIKE :Firstname
                                                    LIMIT 6");
            $other_user_chat->bindParam(":Firstname", $first_name, PDO::PARAM_STR);

            $other_user_chat->execute();
            $array_other_user_chat = $other_user_chat->fetchAll(PDO::FETCH_ASSOC);

            return $array_other_user_chat;
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    /**
	*Crea el mensaje entre los usuarios.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $mensaje: Texto del mensaje.
    *@param $archivo: Nombre dle archivo en el servidor.
    *@param $receptor: id del Usuario a enviar.
    *@param $emisor: id del Usuario que envia.
	*/
    function messageDB($conexion, $id_chat, $mensaje, $archivo, $receptor, $emisor){

        try {
            
            $message_create = $conexion->prepare("INSERT INTO messages (
                            Sender_user_id,
                            Recepter_user_id,
                            message_users,
                            File_url, 
                            id_chats)

                            VALUES (:Sender_user_id, :Recepter_user_id, :message_users, :File_url, :id_chats)");

            $message_create->bindParam(":Sender_user_id", $emisor, PDO::PARAM_INT);
            $message_create->bindParam(":Recepter_user_id", $receptor, PDO::PARAM_INT);
            $message_create->bindParam(":message_users", $mensaje, PDO::PARAM_STR);
            $message_create->bindParam(":File_url", $archivo, PDO::PARAM_STR);
            $message_create->bindParam(":id_chats", $id_chat, PDO::PARAM_INT);

            if($message_create->execute()) {

                $update_chat = $conexion->prepare("UPDATE chats 
                                                    SET date_last_message = CURRENT_TIME 
                                                    WHERE id_chat = :id_chats");
                $update_chat->bindParam(":id_chats", $id_chat, PDO::PARAM_INT);
                $update_chat->execute();

                return true;
            }else {

                return false;
            }
        } catch (\Throwable $th) {
            
            echo $th;
        }
    }



    /**
	*Recoge los mensajes recibidos del usuarios activo de la base de datos y los escribe en un parrafo en html iterando en todos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $id_usuario: id del Usuario activo en la sesión.
	*/
    function myMessagesDB($conexion, $id_usuario, $id_usuario_send){

        try {
            
            $received_message = $conexion->prepare("SELECT concat (u.First_name, ' ', u.Last_name) as nombre_completo, m.message_users, m.File_url, 
                                                    m.date_message, m.Sender_user_id, m.Recepter_user_id

                                                    FROM messages m

                                                    INNER JOIN users u on (m.Sender_user_id=u.id_users)
                                                    WHERE (m.Recepter_user_id = :Recepter_user_id AND m.Sender_user_id = :Sender_user_id) OR
                                                            (m.Recepter_user_id = :Sender_user_id AND m.Sender_user_id = :Recepter_user_id)
                                                    ORDER BY m.date_message ASC");
            $received_message->bindParam(":Recepter_user_id", $id_usuario);
            $received_message->bindParam(":Sender_user_id", $id_usuario_send);
            $received_message->execute();
            $array_received_message = $received_message->fetchAll(PDO::FETCH_ASSOC);

            return $array_received_message;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Recoge los mensajes enviados del usuarios activo de la base de datos y los escribe en un parrafo en html iterando en todos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $id_usuario: id del Usuario activo en la sesión.
	*/
    function myMessagesSendsDB($conexion, $id_usuario){

        try {
            
            $sent_message = $conexion->prepare("SELECT concat (u.First_name, ' ', u.Last_name) as nombre_completo, u.Photo_url, m.message_users, m.File_url, 
                                            m.date_message

                                            FROM messages m

                                            INNER JOIN users u on (m.Recepter_user_id=u.id_users)
                                            WHERE m.Sender_user_id = :Sender_user_id");
            $sent_message->bindParam(":Sender_user_id", $id_usuario);
            $sent_message->execute();
            $array_sent_message = $sent_message->fetchAll(PDO::FETCH_ASSOC);

            return $array_sent_message;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $id_usuario: id del Usuario activo en la sesión.
	*/
    function TuitsWriteApiDB($conexion){

        try {
            
            $get_tuit = $conexion->prepare("SELECT concat (u.First_name, ' ', u.Last_name) as creater_tweet, u.Photo_url, t.Tweet, t.Create_at
            
                                            FROM tweets t

                                            INNER JOIN users u on (t.user_tweet_id=u.id_users)
                                            WHERE t.Is_public='1'");
            $get_tuit->execute();
            $array_get_tuit = $get_tuit->fetchAll(PDO::FETCH_ASSOC);

        return $array_get_tuit;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Carga clave y usuario en la sesion y verifica que exista en la base de datos a travez de la API.
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*@param $usuario: Login Usuario.
	*@param $password: Contraseña Usuario.
	*/
    function loginApiDB($conexion, $usuario, $password){

		try {
            
            $validar_usuario = $conexion->prepare("SELECT id_users, First_name, Last_name, Email, Address_house, Childs,
                                                    Marital_Status, Photo_url, User, Password_user
                                                    FROM users WHERE User = :User");
            $validar_usuario->bindParam(":User", $usuario, PDO::PARAM_STR);
            $validar_usuario->execute();
            $validar_password = $validar_usuario->fetch(PDO::FETCH_ASSOC);
            
            if (is_array($validar_password)) {
                
                if (password_verify($password, $validar_password["Password_user"])) {
                    
                    return $validar_password;
                }
                else {
                    
                    return false;
                }
            }else {
                
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
	}



    /**
	*Recoge los mensajes recibidos del usuarios activo de la base de datos y los escribe en un parrafo en html iterando en todos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $id_usuario: id del Usuario activo en la sesión.
	*/
    function myMessagesApiDB($conexion, $id_usuario){

        try {
            
            $received_message = $conexion->prepare("SELECT concat (u.First_name, ' ', u.Last_name) as nombre_completo, u.Photo_url, m.message_users, m.File_url, 
                                            m.date_message

                                            FROM messages m

                                            INNER JOIN users u on (m.Sender_user_id=u.id_users)
                                            WHERE m.Recepter_user_id = :Recepter_user_id");
            $received_message->bindParam(":Recepter_user_id", $id_usuario);
            $received_message->execute();
            $array_received_message = $received_message->fetchAll(PDO::FETCH_ASSOC);

            return $array_received_message;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Recoge los mensajes enviados del usuarios activo de la base de datos y los escribe en un parrafo en html iterando en todos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $id_usuario: id del Usuario activo en la sesión.
	*/
    function myMessagesSendsApiDB($conexion, $id_usuario){

        try {
            
            $sent_message = $conexion->prepare("SELECT concat (u.First_name, ' ', u.Last_name) as nombre_completo, u.Photo_url, m.message_users, m.File_url, 
                                            m.date_message

                                            FROM messages m

                                            INNER JOIN users u on (m.Recepter_user_id=u.id_users)
                                            WHERE m.Sender_user_id = :Sender_user_id");
            $sent_message->bindParam(":Sender_user_id", $id_usuario);
            $sent_message->execute();
            $array_sent_message = $sent_message->fetchAll(PDO::FETCH_ASSOC);

            return $array_sent_message;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Recibe una nueva clave y la confirmación para así poder cambiar la clave en la base de datos.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $usuario: Usuario activo en la sesión.
	*@param $new_clave: La nueva clave del usuario para cambiar.
	*@param $confirmacion_clave: Clave de confirmación que coincida con la nueva.
	*/
    function changePasswordRecoveryDB($conexion, $usuario, $new_clave, $confirmacion_clave){

        try {
            
            if ($new_clave == $confirmacion_clave) {
    
                $new_clave = password_hash($new_clave, PASSWORD_DEFAULT);
    
                $insert_password = $conexion->prepare("UPDATE users SET Password_user = :Password_user WHERE User = :User");
                $insert_password->bindParam(":Password_user", $new_clave, PDO::PARAM_STR);
                $insert_password->bindParam(":User", $usuario);

                if ($insert_password->execute()) {

                    return true;
                }else {
                    
                    return false;
                }
            }else{
    
                echo '<script>alert("Las claves no coinciden");</script>';
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    /**
	*Lista los nombres de todas las persona .
	*
    *@param $conexion: Conexión PDO para la base de datos.
	*/
    function listaPersonasApiDB($conexion, $usuario) {

        try {
            
            $lista_personas = $conexion->prepare("SELECT id_users, concat (First_name, ' ', Last_name) as nombre_completo FROM users
                                                    WHERE id_users != :id_users");
            $lista_personas->bindParam(":id_users", $usuario);
            $lista_personas->execute();
            $array_lista_personas = $lista_personas->fetchAll(PDO::FETCH_ASSOC);

            return $array_lista_personas;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    /**
	*Traer el nombre completo del la persona del chat.
	*
    *@param $conexion: Conexión PDO para la base de datos.
    *@param $id_usuario_chat: Id de la persona del chat.
	*/
    function personaChatDB($conexion, $id_usuario_chat) {

        try {
            
            $received_message = $conexion->prepare("SELECT concat (First_name, ' ', Last_name) as nombre_completo, Photo_url
                                                    
                                                    FROM users
                                                    WHERE id_users = :id_users");
            $received_message->bindParam(":id_users", $id_usuario_chat);
            $received_message->execute();
            $array_received_message = $received_message->fetch(PDO::FETCH_ASSOC);

            return $array_received_message;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
?>