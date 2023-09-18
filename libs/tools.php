<?php
	require_once ("db.php");
	require_once ("vendor/autoload.php");

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

    /**
	*Asegura la cookie hasta el cierre de sesión.
	*
	*/
    function sesionSegura(){

		$cookieParams = session_get_cookie_params();
		$path = $cookieParams["path"];

		$secure = true;
		$httponly = true;
		$samesite = "strict";

		session_set_cookie_params([

			"lifetime" => $cookieParams["lifetime"],
			"path" => $path,
			"domain" => $_SERVER["HTTP_HOST"],
			"secure" => $secure,
			"httponly" => $httponly,
			"samesite" => $samesite
		]);

		session_start();
		session_regenerate_id();

		if (isset($_SESSION["User"])) {
			
			if (isset($_SESSION['start']) && (time() - $_SESSION['start'] > 1800)) {

				$conn = conexionDB();   //Alvergar la conexión con la DB.
				$ip= getIp();   //Alvergar la IP del equipo actual.
				
				if (writeIpDB($conn, $ip, $_SESSION["User"], 0)) {
					
					session_unset(); 
					session_destroy();
				} 
			}
		}
		$_SESSION['start'] = time();
    }



	/**
	*Conocerla IP del usuario.
	*
	*/
	function getIp() {

		foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
				
			if ( array_key_exists( $key, $_SERVER ) ) {
	
				foreach (array_map('trim', explode(",", $_SERVER[$key])) as $ip) {

					return $ip;
					/*	
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
						return $ip;
					}
					*/
				}
			}
		}
	
		return '?';
	} 



	/**
	*Discrimina el archivo subido para que sólo permita el deseado y lo guarda en el servidor.
	*
	*@param $imagen: Array de la subida del archivo con toda la información.
	*/
    function imagen($imagen){

		if (isset($imagen) && $imagen["error"] === UPLOAD_ERR_OK) {
			
			$fileTmpPath = $imagen["tmp_name"];
			$filName = $imagen["name"];

			$filNameCamps = explode(".", $filName);
			$fileExtension = strtolower(end($filNameCamps));
			$permitidas = ["jpg", "gif", "png", "jpeg"];

			if (in_array($fileExtension, $permitidas)) {
				
				$uploadFileDir = "./assets/images/"; //Carpeta destino

				$filName = md5(time() . $filName) . '.' . $fileExtension;
				$dest_path = $uploadFileDir . $filName;

				if (move_uploaded_file($fileTmpPath, $dest_path)) {
					
					$img = imagecreatefromjpeg($dest_path);
					imagejpeg($img, $dest_path, 100);
					imagedestroy($img);

					return $filName;
				}else {
					
					echo '<script>alert("Error al subir el archivo");</script>';
					return false;
				}
			}
			else {
				
				echo '<script>alert("Archivo no permitido");</script>';
				return false;
			}
		}else {
			
			echo '<script>alert("Error al subir el archivo");</script>';
			return false;
		}
    }


	/**
	*Discrimina el archivo subido para que sólo permita el deseado y lo guarda en el servidor.
	*
	*@param $imagen: Array de la subida del archivo con toda la información.
	*/
    function archivo($archivo){

		if (isset($archivo) && $archivo["error"] === UPLOAD_ERR_OK) {
			
			$fileTmpPath = $archivo["tmp_name"];
			$filName = $archivo["name"];

			$filNameCamps = explode(".", $filName);
			$fileExtension = strtolower(end($filNameCamps));
			$permitidas = ["jpg", "gif", "png", "jpeg", "pdf", "xls", "rar", "docx", "pptx"];

			if (in_array($fileExtension, $permitidas)) {
				
				$uploadFileDir = "./assets/archivos/"; //Carpeta destino

				$filName = md5(time() . $filName) . '.' . $fileExtension;
				$dest_path = $uploadFileDir . $filName;

				if (move_uploaded_file($fileTmpPath, $dest_path)) {

					return $filName;
				}else {
					
					echo '<script>alert("Error al subir el archivo");</script>';
					return false;
				}
			}
			else {
				
				echo '<script>alert("Archivo no permitido");</script>';
				return false;
			}
		}else {
			
			echo '<script>alert("Error al subir el archivo");</script>';
			return false;
		}
    }
	
	

	/**
	*Asegura que no inyecten scripts en cada valor del array POST.
	*
	*@param $cadena: Array POST con todos los valores.
	*/
    function limpiarCadena($cadena){

		$patron = array('/<script>.*\/script>/');
		$cadena = preg_replace($patron, "", $cadena);
		$cadena = htmlspecialchars($cadena);
		return $cadena;
    }



	/**
	*Limpia todos los valores del array POST usando la función limpiarCadena().
	*
	*/
    function limpiarEntradas(){

		if(isset($_POST)) {
			
			foreach($_POST as $key => $value) {
				
				$_POST[$key] = limpiarCadena($value);
			}
		}
		elseif (isset($_GET)) {
			
			foreach($_GET as $key => $value) {
				
				$_GET[$key] = limpiarCadena($value);
			}
		}
    }



	/**
	*Reconstruye un archivo en base64.
	*
	*@param $base64: archivo codificaddo en base64.
	*/
	function base64_to_jpeg($base64) {

		$data = explode(',', $base64);

		if (!isset($data[1])) {
			
			return false;
		}
    
		$uploadFileDir = "../assets/images/"; 

		$filName = md5(time() . random_int(1000, 9999)) . ".jpg";
		$dest_path = $uploadFileDir . $filName;

		$ifp = fopen( $dest_path, 'wb' );
			
		fwrite($ifp, base64_decode($data[1]));
		fclose($ifp); 
		
		try {
			
			@getimagesize($dest_path) or die($error = "Archivo no valido");

			if (!isset($error)) {
				
				return $filName;
			}
			else {
				
				unlink($dest_path);
				return false;
			}
		} catch (\Throwable $th) {
			
			unlink($dest_path);
			return false;
		}
        
    }



	/**
	*Reconstruye un archivo en base64.
	*
	*@param $base64: archivo codificaddo en base64.
	*@param $ruta: Ruta de terminación dela rchivo.
	*/
	function base64($base64) {

		$data = explode(',', $base64);

		if (!isset($data[1])) {
			
			return false;
		}
    
		$uploadFileDir = "../assets/archivos/"; 

		$filName = md5(time() . random_int(1000, 9999)) . ".jpg";
		$dest_path = $uploadFileDir . $filName;

		$ifp = fopen( $dest_path, 'wb' );
			
		fwrite($ifp, base64_decode($data[1]));
		fclose($ifp); 
		
		try {
			
			@getimagesize($dest_path) or die($error = "Archivo no valido");

			if (!isset($error)) {
				
				return $filName;
			}
			else {
				
				unlink($dest_path);
				return false;
			}
		} catch (\Throwable $th) {
			
			unlink($dest_path);
			return false;
		}
        
    }



	/**
	*Envía un correo con un código de recuperación.
	*
	*@param $email: Email del usuario.
	*@param $Full_name: nombre completo del usuario.
	*@param $codigo: Código de recuperación
	*/
	function sendEmail($codigo, $email, $Full_name) {
    
		$mail = new PHPMailer(true);

		try {
			
			//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->isSMTP();
			$mail->Host = "smtp.gmail.com";
			$mail->SMTPAuth = true;
			$mail->Username = "lasnalguitas28@gmail.com";
			$mail->Password = "oedpvhdtdkxxfitz";
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = 465;

			$mail->setFrom("lasnalguitas28@gmail.com", "lasNalguitas");
			$mail->addAddress($email, $Full_name);
			
			$mail->isHTML(true);
			$mail->Subject = "Recuperacion de contrasena TUIT.COM";
			$mail->Body = "El codigo para recuperar su cuenta es --" . $codigo . "--";

			if ($mail->send()) {
				
				return true;
			}else {
				
				return false;
			}
		}catch (Exception $e) {
			
			echo "Mensaje" . $mail->ErrorInfo;
    	}
	}



	/**
	*Envia un correo con un enlace para activar la cuenta recien registrada.
	*
	*@param $email: Email del usuario.
	*@param $Full_name: nombre completo del usuario.
	*@param $codigo: Código de recuperación.
	*/
	function sendEmailActiveAccount($codigo, $email, $Full_name) {
    
		$mail = new PHPMailer(true);

		try {
			
			//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->isSMTP();
			$mail->Host = "smtp.gmail.com";
			$mail->SMTPAuth = true;
			$mail->Username = "lasnalguitas28@gmail.com";
			$mail->Password = "oedpvhdtdkxxfitz";
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = 465;

			$mail->setFrom("lasnalguitas28@gmail.com", "lasNalguitas");
			$mail->addAddress($email, $Full_name);
			
			$mail->isHTML(true);
			$mail->Subject = "Activación de Cuenta TUIT.COM";
			$mail->Body = "El siguiente enlace es necesario par activar tú cuenta <br>
							-- https://localhost/Parcial/ActivarCuenta.php?codigo=" . $codigo . " --";

			if ($mail->send()) {
				
				return true;
			}else {
				
				return false;
			}
		}catch (Exception $e) {
			
			echo "Mensaje" . $mail->ErrorInfo;
    	}
	}



	/**
	*Limitar caracteres
	*
	*@param $usuario: cadena de caracteres del usuario.
	*/
	function usuarioLimitar($usuario){

		if (strlen($usuario) >= 4 && strlen($usuario) < 15) {

			if (strpos($usuario, ' ') == 0) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $correo: cadena de caracteres del correo.
	*/
	function correoLimitar($correo){

		if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			
			return false;
		}
		else {
			
			return true;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $apellido: cadena de caracteres del apellido.
	*/
	function apellidoLimitar($apellido){

		if (strlen($apellido) < 4 || strlen($apellido) > 40) {

			return false;
		}
		else {
			
			return true;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $nombre: cadena de caracteres del nombre.
	*/
	function nombreLimitar($nombre){

		if (strlen($nombre) < 4 || strlen($nombre) > 40) {

			return false;
		}
		else {
			
			return true;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $direccion: cadena de caracteres del correo.
	*/
	function direccionLimitar($direccion){

		if (strlen($direccion) < 6 || strlen($direccion) > 45) {

			return false;
		}
		else {
			
			return true;
		}
	}



	/**
	*Limitar a entero
	*
	*@param $hijos: entero de los hijos.
	*/
	function hijosLimitar($hijos){

		$hijos = intval($hijos);

		if (!is_string($hijos)) {
			
			if ($hijos < 15) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $estado: cadena de caracteres del estado.
	*/
	function estadoCivilLimitar($estado){

		$estadoCivil_permitidos = ["casado", "soltero", "union_marital"];
		
		if (!in_array($estado, $estadoCivil_permitidos)) {

			return false;
		}
		else {
			
			return true;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $clave: cadena de caracteres de la clave.
	*/
	function claveLimitar($clave){

		if (strlen($clave) > 6 && strlen($clave) < 30) {

			if (strpos($clave, ' ') == 0) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Limitar caracteres del codigo
	*
	*@param $codigo: cadena de caracteres del codigo de recuperación.
	*/
	function codigoLimitar($codigo){

		$codigo = intval($codigo);

		if (!is_string($codigo)) {
			
			if ($codigo >= 1000 && $codigo <= 9999) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $id: entero del id del receptor.
	*/
	function mensajePersonaLimitar($id){

		$id = intval($id);

		if (!is_string($id)) {
			
			return true;
		}
		else {
			
			return false;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $mensaje: cadena de caracteres del mensaje.
	*/
	function mensajeLimitar($mensaje){

		if (strlen($mensaje) < 2 || strlen($mensaje) > 150) {

			return false;
		}
		else {
			
			return true;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $check: entero del id del receptor.
	*/
	function tuitPublicoLimitar($check){

		$check = intval($check);

		if (!is_string($check)) {
			
			return true;
		}
		else {
			
			return false;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $tuit: cadena de caracteres del tuit.
	*/
	function tuitLimitar($tuit){

		if (strlen($tuit) < 2 || strlen($tuit) > 150) {

			return false;
		}
		else {
			
			return true;
		}
	}



	/**
	*Limitar caracteres
	*
	*@param $tuit: cadena de caracteres del tuit.
	*/
	function idChatLimitar($id){


		if (($id > 0 && $id < 99999)) {
			
			return true;
		}
		else {
			
			$find = explode("/", $id); 
			if (isset($find[1])) {
				
				if (($find[1] > 0 && $find[1] < 9999) && ($find[1] > 0 && $find[1] < 9999)) {
					
					return true;
				}else {
					
					return false;
				}
			}else {
				
				return false;
			}
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsRegistrar($post){

		$nombres_comparar = ["txtNombre", "txtApellido", "txtCorreo", "txtDir", "txtNumHijos",
							"txtEstCilvil", "txtUsuario", "txtClave", "btnRegistrar", "csrf"];
		
		if (count($post) == 10) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al registrar");</script>';
					return false;
				}
			}

			if (usuarioLimitar($post["txtUsuario"]) && correoLimitar($post["txtCorreo"]) && nombreLimitar($post["txtNombre"])
				&& apellidoLimitar($post["txtApellido"]) && direccionLimitar($post["txtDir"]) && hijosLimitar($post["txtNumHijos"])
				&& estadoCivilLimitar($post["txtEstCilvil"]) && claveLimitar($post["txtClave"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al registrar");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al registrar");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $usuario: cadena de caracteres del usuario.
	*/
	function postInputsActualizar($post){

		$nombres_comparar = ["txtNombre", "txtApellido", "txtCorreo", "txtDir", "txtNumHijos",
							"txtEstCilvil", "btnActualizar", "csrf"];
		
		if (count($post) == 8) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al actualizar");</script>';
					return false;
				}
			}

			if (correoLimitar($post["txtCorreo"]) && nombreLimitar($post["txtNombre"])
				&& apellidoLimitar($post["txtApellido"]) && direccionLimitar($post["txtDir"]) && hijosLimitar($post["txtNumHijos"])
				&& estadoCivilLimitar($post["txtEstCilvil"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al actualizar");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al actualizar");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsActualizarClave($post){

		$nombres_comparar = ["txtAnterior", "txtNueva", "txtRepetir", "csrf", "btnActualizar"];
		
		if (count($post) == 5) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al actualizar la clave");</script>';
					return false;
				}
			}

			if (claveLimitar($post["txtAnterior"]) && claveLimitar($post["txtNueva"]) && claveLimitar($post["txtRepetir"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al actualizar la clave");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al actualizar la clave");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsLogin($post){

		$nombres_comparar = ["txtUsuario", "txtClave", "btningresar", "csrf"];
		
		if (count($post) == 4) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al ingresar 1");</script>';
					return false;
				}
			}

			if (usuarioLimitar($post["txtUsuario"]) && claveLimitar($post["txtClave"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al ingresar");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al ingresar");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsTuit($post){

		$nombres_comparar = ["txtMensaje_create_tweets", "chkPublico_create_tweets", "csrf", "btnCrear"];

		if (count($post) <= 4) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al crear tuit");</script>';
					return false;
				}
			}

			if (tuitLimitar($post["txtMensaje_create_tweets"]) && tuitPublicoLimitar($post["chkPublico_create_tweets"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al crear tuit");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al crear tuit");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsMensaje($post){

		$nombres_comparar = ["amp;id_other_user", "amp;id_chat", "txtMensaje_Messages_crear", "amp;csrf"];
		
		if (count($post) == 4) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al crear el mensaje");</script>';
					return false;
				}
			}

			if (mensajeLimitar($post["txtMensaje_Messages_crear"]) && idChatLimitar($post["amp;id_chat"]) && 
				idChatLimitar($post["amp;id_other_user"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al crear el mensaje");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al crear el mensaje");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postChangeMensaje($post){

		$nombres_comparar = ["id_chat", "amp;id_other_user_chat", "amp;csrf"];
		
		if (count($post) == 3) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					return false;
				}
			}

			if (idChatLimitar($post["id_chat"]) && idChatLimitar($post["amp;id_other_user_chat"])) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postChangeMensajesNoChat($post){

		$nombres_comparar = ["id_other_user_chat", "amp;csrf"];
		
		if (count($post) == 2) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					return false;
				}
			}

			if (idChatLimitar($post["id_other_user_chat"])) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Conocer las llaves del post para abrir el chat del usuario deseado
	*
	*@param $post: array POST del PHP.
	*/
	function postInterfasMensaje($post){

		$nombres_comparar = ["csrf", "BtnPersona"];
		
		if (count($post) == 2) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al encontrar el usuario");</script>';
					return false;
				}
			}

			if (mensajePersonaLimitar($post["BtnPersona"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al encontrar el usuario");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al encontrar el usuario");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post para la Api.
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsLoginApi($post){

		$nombres_comparar = ["txtUsuario", "txtClave"];
		
		if (count($post) == 2) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al ingresar");</script>';
					return false;
				}
			}

			if (usuarioLimitar($post["txtUsuario"]) && claveLimitar($post["txtClave"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al ingresar");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al ingresar");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post para la Api.
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsRegistrarApi($post){

		$nombres_comparar = ["txtNombre", "txtApellido", "txtCorreo", "txtDir", "txtNumHijos",
							"txtEstCilvil", "txtUsuario", "txtClave", "fullFoto"];
		
		if (count($post) == 9) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al registrar");</script>';
					return false;
				}
			}

			if (usuarioLimitar($post["txtUsuario"]) && correoLimitar($post["txtCorreo"]) && nombreLimitar($post["txtNombre"])
				&& apellidoLimitar($post["txtApellido"]) && direccionLimitar($post["txtDir"]) && hijosLimitar($post["txtNumHijos"])
				&& estadoCivilLimitar($post["txtEstCilvil"]) && claveLimitar($post["txtClave"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al registrar");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al registrar");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post para la Api.
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsTuitApi($post){

		$nombres_comparar = ["txtMensaje_create_tweets", "chkPublico_create_tweets"];
		
		if (count($post) == 2) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al crear tuit");</script>';
					return false;
				}
			}

			if (tuitLimitar($post["txtMensaje_create_tweets"]) && tuitPublicoLimitar($post["chkPublico_create_tweets"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al crear tuit");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al crear tuit");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsMensajeApi($post){

		$nombres_comparar = ["cmbDestino", "txtMensaje_Messages_crear", "fulAdjunto"];
		
		if (count($post) == 3) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					echo '<script>alert("Error al crear el mensaje 1");</script>';
					return false;
				}
			}

			if (mensajePersonaLimitar($post["cmbDestino"]) && mensajeLimitar($post["txtMensaje_Messages_crear"])) {
				
				return true;
			}
			else {
				
				echo '<script>alert("Error al crear el mensaje 2");</script>';
				return false;
			}
		}
		else {
			
			echo '<script>alert("Error al crear el mensaje 3");</script>';
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsVerificarMensaje($post){

		$nombres_comparar = ["txtUsuario", "txtCorreo", "csrf", "btnRecuperar"];

		if (count($post) == 4) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					return false;
				}
			}

			if (usuarioLimitar($post["txtUsuario"]) && correoLimitar($post["txtCorreo"])) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsVerificarCodigo($post){

		$nombres_comparar = ["txtUsuario", "txtCodigo", "csrf", "btnRecuperar"];

		if (count($post) == 4) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					return false;
				}
			}

			if (usuarioLimitar($post["txtUsuario"]) && codigoLimitar($post["txtCodigo"])) {
				
				return true;
			}else {
				
				return false;
			}
		}else {
			
			return false;
		}
	}



	/**
	*Conocer las llaves deL array Post
	*
	*@param $post: array POST del PHP.
	*/
	function postInputsRecuperarClave($post){

		$nombres_comparar = ["txtNueva", "txtRepetir", "csrf", "btnActualizar"];
		
		if (count($post) == 4) {
			
			foreach ($post as $key => $value) { 
				         
				if (!in_array($key, $nombres_comparar)) {
						
					return false;
				}
			}

			if (claveLimitar($post["txtNueva"]) && claveLimitar($post["txtRepetir"])) {
				
				return true;
			}
			else {
				
				return false;
			}
		}
		else {
			
			return false;
		}
	}



	/**
	*Comparar la fecha del mensaje con la actual.
	*
	*@param $date: Fecha del mensaje.
	*/
	function fechaComparative($date) {

		date_default_timezone_set('America/Bogota');
		$today = date("Y-m-d H:i:s");

		$firstToday = explode(" ", $today);
		$firstDate = explode(" ", $date);

		if ($firstDate[0] == $firstToday[0]) {
			
			return true;
		}
	}



	/**
	*Conocer si es una imágen.
	*
	*@param $nameImg: nombre del archivo.
	*/
	function imageMessage($nameImg) {

		$imageExplode = explode(".", $nameImg);
		$imgExplodeExtensión = strtolower(end($imageExplode));
		$permitidas = ["jpg", "gif", "png", "jpeg"];

		if(in_array($imgExplodeExtensión, $permitidas)) {

			return true;
		}
		else{

			return false;
		}
	}



	/**
	*Crear un nuevo chat.
	*
	*@param $datos: Array a identificar.
	*@param $conexion: Conexión con base de datos.
	*/
	function isNewChat($conexion, $chat){

		$id_chat = explode("/", $chat);   

		if (isset($id_chat[1])) {
			
			$user1 = intval($id_chat[0]);
			$user2 = intval($id_chat[1]);
			if ($user1 == $_SESSION["id_users"] || $user2 == $_SESSION["id_users"]) {
	
				$searchChat1 =  $user1 . '/' . $user2;
            	$searchChat2 =  $user2 . '/' . $user1;
				$chatSearch = FoundChatDB($conexion, $searchChat1, $searchChat2);

				if ($chatSearch) {
					
					return $chatSearch["id_chat"];
				}else {
					
					if (CreateChatDB($conexion, $chat)) {
						
						$newChat = FoundChatDB($conexion, $chat, $chat);
						return $newChat["id_chat"];
					}else {
						
						$chat = 0;
						return $chat;
					}
				}
			}else {

				$chat = 0;
				return $chat;
			}

		}else {
			
			$chat = false;
			return $chat;
		}
	}
?>