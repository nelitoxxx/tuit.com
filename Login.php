<?php
    //Acceso a las funciones de las librerias propias.
	require_once ("libs/tools.php");
    require_once ("libs/db.php");
	
    //Llamado a las funciones de seguridad.
	sesionSegura();
    limpiarEntradas();


    /*  
    Verificación de variables para anti CSRF.
    */
    if (isset($_POST["csrf"])) {
        
        /*  
        Comparar la variable de la sesión con la enviada.
        */
        if ($_POST["csrf"] != $_SESSION["csrf"]) {
        
            header("Location: Login.php"); //Redireccionar al Login.
            exit;   //Terminar ejecución.
        }
    }


    /*  
    Verificación de que el usuario este logueado.
    */
    if (isset($_SESSION["User"])) {
        
        header("location: Index.php");  //Redireccionar al Login.
    }


    /*  
    Verificación de que el usuario selecciono el Input de Iniciar Sesión.
    */
	if(isset($_POST["btningresar"])){
		
        /*  
        Validar los nombres y la cantidad del array $_POST.
        */
		if (postInputsLogin($_POST)) {
            
            $conn = conexionDB();   //Alvergar la conexión con la DB.
            $_SESSION["validar"] = 0;


            /*  
            Confirmar el ingreso de los datos del Usuario en el DB, sino mandar un mensaje
            anunciando que no los datos ingresados no son validos.
            */
            if(loginDB($conn, $_POST["txtUsuario"], $_POST["txtClave"], $_SESSION["validar"])){

                header("location: Index.php");  //Redireccionar al Index.
            }
            else{

                echo '<script>alert("Clave o Usuario no coinciden O su cuenta no ha sido activada");</script>'; //Mensaje de datos no validos.
            }
        }
    }


    /*
    Crear variable anti CSRF.
    */
    $_SESSION["csrf"] = random_int(1000, 9999);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="css/style.css?14.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    </head>
    <body>
        <header>    
        </header>
        <main id="main_login">
            <div id="back_login">
                <div id="div_login_left">
                    <div id="box_login_left">
                        <h1 id="logo_login">TUIT.COM</h1>
                        <p id="paragraph_login">Disfruta hablar con todos de todo!!!</p>
                    </div>
                </div>
                <div id="div_login_right">
                    <div id="login">
                        <form id="form_login" action="" method="post">
                            <label class="login_label" for="txtUsuario">Digite su Usuario</label>
                            <input type="text" name="txtUsuario" id="txtUsuario" pattern="[A-Za-z0-9]{2,}" required><br>

                            <label class="login_label" for="txtClave">Digite su Clave</label>
                            <input type="password" name="txtClave" id="txtClave" pattern="[A-Za-z0-9._%+-]{2,}" required><br>

                            <input type="hidden" name="csrf" id="csrf" value = "<?php echo $_SESSION["csrf"];?>">
                            
                            <input type="submit" name="btningresar" id="btningresar" value="Login"><br>

                            <p style="color: white;">¿No tienes Cuenta? <a href="Registro.php" style="color: #b3b5ff;">Registrate Ya Aquí!!</a></p>
                            <a href="RecoveryContraseña.php" style="color: #b3b5ff;">¿Olvidaste tú contraseña?</a>
                        </form>
                    </div>
                </div>
            </div>  
        </main>
    </body>
</html>