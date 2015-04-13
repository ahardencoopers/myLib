<?php
require_once("myLib/myDb.php"); //Codigo para manejar conexion a base da datos.
require_once("myLib/myPw.php"); //Codigo para manejo de passwords.
require_once("myLib/myQuery.php"); //Codigo para manejo de queries. 
require_once("myLib/myMisc.php"); //Codigo misc. (Output con newline, crear hyperlinks, etc) 


function agregarUsuario($arrDatos)
{
	$conexion = conectarDb();
	
	//1. Checar que no haya otro usuario con el mismo nombre.
	//Query para verificar que no haya otro usuario con el mismo nombre.
	$queryChecarUsuario = "SELECT nombre FROM Usuarios WHERE nombre = ?";

	if(prepararQuery($queryChecarUsuario, $stmtChecarUsuario, $conexion))
	{
		mysqli_stmt_bind_param($stmtChecarUsuario, "s", $arrDatos[0]);
		mysqli_stmt_execute($stmtChecarUsuario);
		mysqli_stmt_bind_result($stmtChecarUsuario, $checarNombre);
		mysqli_stmt_fetch($stmtChecarUsuario);

	}
	else
	{
		echoLine("Error al checar usuarios");
	}
	mysqli_stmt_store_result($stmtChecarUsuario);


	//2. Si no hay un usuario con el mismo nombre, crear al usuario.
	//Query para crear un nuevo usuario en la base de datos.
	$queryCrearUsuario =  "INSERT INTO Usuarios (nombre, tipo) VALUES (?, ?)";

	//Verificar si las passwords son iguales y no hay otro usuario con
	//el mismo nombre.
	//Si lo anterior se cumple, crear al usuario.
	if($arrDatos[1] == $arrDatos[2] && $arrDatos[0] != $checarNombre)
	{
		//Asignar el tipo de usuario correspondiente.
		if($arrDatos[3] == "normal")
		{
			$arrDatos[3] = 1;
		}
		else
		{
			$arrDatos[3] = 0;
		}
		

		//Si se logra preparar query, seguir ejecucion para crear usuario.
		if(prepararQuery($queryCrearUsuario, $stmtCrearUsuario, $conexion))
		{
			//Pasar valores a y ejecutar query.
			mysqli_stmt_bind_param($stmtCrearUsuario, "si", $arrDatos[0], $arrDatos[3]);
			mysqli_stmt_execute($stmtCrearUsuario);
		}
		else
		{
			echoLine("Error al preparar query.");
		}

		mysqli_stmt_store_result($stmtCrearUsuario);

		return true;
	}
	else
	{
		echoLine("Las passwords no coinciden o hay un usuario con el mismo nombre. 
		Vuelve a ingresar los datos");

		return false;
	}

}


function agregarPassword($arrDatos)
{
	//Crear hash de la password y borrar 
	//password de confirmacion.
	$arrDatos[1] = hashPassword($arrDatos[1]);
	$arrDatos[2] = "";


	$conexion = conectarDb();	

	$queryObtenerId = "SELECT id FROM Usuarios WHERE nombre = ?";

	if(prepararQuery($queryObtenerId, $stmtObtenerId, $conexion))
	{
		mysqli_stmt_bind_param($stmtObtenerId, "s", $arrDatos[0]);
		mysqli_stmt_execute($stmtObtenerId);
		mysqli_stmt_bind_result($stmtObtenerId, $idUsuario);
		mysqli_stmt_fetch($stmtObtenerId);
	}
	else
	{
		echoLine("Error al obtener id unico del usuario.");

	}

	//Despues de ejecutar una query que regresa un set de resultados,
	//si no se termina de hacer fetch de todos los resultados del fetch
	//cualquier query subsequente que se quiera ejecutar dara un
	//"Command out of sync error", lo cual indica que aun quedan resultados
	//a los cuales hacer fetch antes de seguir procesando queries.
	//Para esto se debe de procesar todos los resultados para seguir
	//ejecutando queries nuevas. Esta comando "limpia" todos los resultados
	//sobre los que se no se hizo un fetch.
	mysqli_stmt_store_result($stmtObtenerId);

	//3.b Ingresar la hash del password y id unico del usuario en la tabla
	//de passwords.
	//Query para insertar hash del password en la tabla de passwords.
	$queryInsertarPassword = "INSERT INTO Passwords (usuarioFK, password) VALUES (?, ?)";


	if(prepararQuery($queryInsertarPassword, $stmtInsertarPassword, $conexion))
	{
		mysqli_stmt_bind_param($stmtInsertarPassword, "is", $idUsuario, $arrDatos[1]);
		mysqli_stmt_execute($stmtInsertarPassword);
		echoLine("Sign up exitoso, bienvenido $arrDatos[0]");
		$loginUrl = "iniciarSesion.php";
		echoLine("Pasar a <a href=$loginUrl>Log in</a>");

		return true;
	}
	else
	{
		echoLine("Error al Ingresar password en la base de datos");

		return false;
	}

}

function existeUsuario($arrDatos, &$nombreUsuario, &$idUsuario)
{
	$conexion = conectarDb();

	$queryChecarUsuario = "SELECT nombre,id FROM Usuarios WHERE nombre = ?";

	if(prepararQuery($queryChecarUsuario, $stmtChecarUsuario, $conexion))
	{
		mysqli_stmt_bind_param($stmtChecarUsuario, "s", $arrDatos[0]);
                mysqli_stmt_execute($stmtChecarUsuario);
                mysqli_stmt_bind_result($stmtChecarUsuario, $checarNombre, $checarId);
                mysqli_stmt_fetch($stmtChecarUsuario);	

		if($arrDatos[0] == $checarNombre)
		{
			$nombreUsuario = $checarNombre;
			$idUsuario = $checarId;
			return true;
		}
		else
		{
			echoLine("El usuario no existe en la base de datos.");
			return false;
		}

	}
	else
	{
			echoLine("Error al preparar query (existeUsuario).");
			return false;
	}

	//Purgar resultados
	mysqli_stmt_store_result($stmtChecarUsuario);	

}

function existePassword($arrDatos, &$hashUsuario)
{
	$queryChecarPassword = "SELECT password FROM Passwords WHERE usuarioFK = ?";
	
	if(prepararQuery($queryChecarPassword, $stmtChecarPassword, $conexion))
	{
		mysqli_stmt_bind_param($stmtChecarPassword, "i",  $idUsuario);
	        mysqli_stmt_execute($stmtChecarPassword);
	        mysqli_stmt_bind_result($stmtChecarPassword, $checarPassword);
	        mysqli_stmt_fetch($stmtChecarPassword);
	
		//3.
		if(password_verify($arrDatos[1], $checarPassword))
		{
			$hashUsuario = $checarPassword;	
			return true;
		}
		else
		{
			echoLine("Credenciales invalidas");
			return false;
		}

		mysqli_stmt_store_result($stmtChecarUsuario);	

	}

}

?>
