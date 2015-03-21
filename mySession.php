<?php
require_once("myMisc.php");
require_once("myDb.php");
require_once("myPw.php");
require_once("myQuery.php");

//Este archivo contiene funciones para facilitar el uso y manejo de sesiones de PHP
//y el del arreglo super global $_SESSION[], por lo tanto si se usan estas funciones 
//se deben de seguir ciertas reglas de nomenclatura para los indices de $_SESSION[]. 
//1. Para los siguientes datos que corresponden a las credenciales del usuario:
//Nombre de usuario, hash de su password y su id unico, se deberan de almacenar en
//$_SESSION[] utilizando los siguientes nombres/valores para los indices. 
// "nombre" => nombre de usuario
// "hash" => hash del usuario
// "id" => id unico del usuario

//Conectarse a la base de datos
//e iniciar una sesion de PHP
$conexion = conectarDb();

function haySesion()
{
	if(session_id() == "")
	{
		session_start();
	}
}

//Funcion que recibe 3 strings que tienen las credenciales del usuario
//y los coloca en el arreglo super global $_SESSION[] con sus nombres
//corresopondientes. 
// "nombre" => $nombreUsuario
// "hash" => $hashUsuario
// "id" => $idUsuario
function iniciarSesion($nombreUsuario, $hashUsuario, $idUsuario)
{
	haySesion();
	$_SESSION["nombre"] = $nombreUsuario;
	$_SESSION["hash"] = $hashUsuario;
	$_SESSION["id"] = $idUsuario;
}


//Funcion que valida que las credenciales de usuario que se encuentran en el arreglo
//super global $_SESSION[] sean validas comparando con las credenciales en la base de datos.
//Recibe 3 strings que contienen las credenciales de usuario y regresa true si son credenciales 
//validas, si no lo son, regresa false.
function validarSesion()
{
	haySesion();
	$conexion = conectarDb();
	$queryChecarUsuario = "SELECT nombre,id FROM Usuarios WHERE nombre = ?";
	
	if(prepararQuery($queryChecarUsuario, $stmtChecarUsuario, $conexion))
	{
		mysqli_stmt_bind_param($stmtChecarUsuario, "s", $_SESSION["nombre"]);
		mysqli_stmt_execute($stmtChecarUsuario);
		mysqli_stmt_bind_result($stmtChecarUsuario, $checarNombre, $checarId);
		mysqli_stmt_fetch($stmtChecarUsuario);

		mysqli_stmt_store_result($stmtChecarUsuario);	

		if($_SESSION["nombre"] == $checarNombre && $_SESSION["id"] == $checarId && $checarNombre != "")
		{
			$queryChecarPassword = "SELECT password FROM Passwords WHERE usuarioFK = ?";

			if(prepararQuery($queryChecarPassword, $stmtChecarPassword, $conexion))
			{
				mysqli_stmt_bind_param($stmtChecarPassword, "i", $_SESSION["id"]);
				mysqli_stmt_execute($stmtChecarPassword);
				mysqli_stmt_bind_result($stmtChecarPassword, $checarPassword);
				mysqli_stmt_fetch($stmtChecarPassword);
				mysqli_stmt_store_result($stmtChecarPassword);	

				if($_SESSION["hash"] == $checarPassword)	
				{
					return true;
				}
				else
				{
					return false;
				}

			}
			else
			{
				return false;
			}
			
		}
	}
	else
	{
		return false;
	}
}

//Funcion para terminar y destruir una sesion.

function terminarSesion()
{
	$_SESSION = array();
	
	//Seccion de codigo tomada de stackoverflow.
	//http://tinyurl.com/q2r4egt
	//Destruye por completo la cookie de la sesion.
	if (ini_get("session.use_cookies")) 
	{
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]);

	}	
	//Fin de codigo tomado de stackoverflow

	//Destruir la sesion
	session_destroy();
}

?>
