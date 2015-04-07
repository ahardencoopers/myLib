<?php
require_once("myMisc.php");
require_once("myDb.php");
require_once("myPw.php");
require_once("myQuery.php");
require_once("mySession.php");

//Este archivo contiene codigo para el manejo de archivos remotos.
//El directorio raiz del sistema de archivos remoto es el siguiente
// archivosRoot: directorio raiz del sistema, todos los archivos son parte de este directorio

//Funcion para subir un archivo al servidor
//Obtiene los datos del archivo del arreglo super global $_FILES.
//Tambien crea una entrada en la base de datos para el archivo que se acaba de subir.
//La informacion de la entrada en la base de datos del archivo es
//id => id unico del archivo
//creadorFk => id unico del usuario que subio el archivo
//visib => visibilidad del archivo, puede ser privado o publico
//fecha => fecha de cuando se subio el archivo
//nombre => nombre del archivo
//descri => descripcion corta del archivo (40 caracteres max)
//tipo	=> tipo de archivo (pdf, jpg, etc)
//tam => tamanio del archivo
//path => path completo al archivo incluyendo nombre
function subirArchivo()
{
	$conexion = conectarDb();

	haySesion();

	$creoEntrada = false;

	$idUsuario = $_SESSION["id"];
	$visiArchivo = $_POST["visiArchivo"];
	$nombreArchivo = basename($_FILES['archivo']['name']);
	$descArchivo = $_POST["descArchivo"];
	$tipoArchivo = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
	$tamArchivo = $_FILES['archivo']['size']/1024;
	$pathArchivo = "/var/www/html/serverfile/archivosRoot/".$nombreArchivo;

	//Checar que no haya otro archivo con el mismo nombre en la base de datos
	//Query para checar que no haya otro archivo con el mismo nombre
	$queryChecarArchivo = "SELECT nombre FROM Archivos where nombre = ?";

	if(prepararQuery($queryChecarArchivo, $stmtChecarArchivo, $conexion))
	{
		mysqli_stmt_bind_param($stmtChecarArchivo, "s", $nombreArchivo);
		mysqli_stmt_execute($stmtChecarArchivo);
		mysqli_stmt_bind_result($stmtChecarArchivo, $checarArchivo);
		mysqli_stmt_fetch($stmtChecarArchivo);

	}
	else
	{
		echoLine("Error al checar archivo");
		return false;
	}

	mysqli_stmt_store_result($stmtCrearArchivo);

	//Si no hay un archivo con el mismo nombre, crear entrada
	//en la base de datos.
	if($nombreArchivo != $checarArchivo)
	{
		$queryCrearArchivo = "INSERT INTO Archivos (creadorFk, visib, nombre, descr, tipo, tam, path) VALUES (?, ?, ?, ?, ?, ?, ?)"; 
		
		if($visiArchivo == "publico" || $visiArchivo == "")
		{
			$visiArchivo = 0;	
		}
		else
		{
			$visiArchivo = 1;
		}
		
		if(prepararQuery($queryCrearArchivo, $stmtCrearArchivo, $conexion))
		{
			mysqli_stmt_bind_param($stmtCrearArchivo, "iisssds", $idUsuario, $visiArchivo, $nombreArchivo, 
				$descArchivo, $tipoArchivo, $tamArchivo, $pathArchivo);
			mysqli_stmt_execute($stmtCrearArchivo);
			$creoEntrada = true;
		}
		else
		{
			echoLine("Error al insertar entrada de archivo en la base de datos");
			return false;
		}
	}
	
	mysqli_stmt_store_result($stmtCrearArchivo);

	if(!$creoEntrada)
	{
		echoLine("No se pudo crear entrada en la base de datos");
	}
	else
	{
		//Se quita los directorios que estan antes del nombre del archivo y se
		//le adjunta el directorio raiz del servidor remoto
		//$nombreArchivo = "archivosRoot/".basename($_FILES['archivo']['name']);

		//Si se puede mover el archivo de almacenamiento temporal a permanente, regresa true,
		//si no puede regresa false;
		if(move_uploaded_file($_FILES['archivo']['tmp_name'], $pathArchivo))
		{
			echoLine("El archivo ".$nombreArchivo." se ha subido exitosamente");
		}
		else
		{
			echoLine("Error al subir archivo");
		}
	}



	

}

//Funcion para desplegar forma para subir archivos.
//Recibe la pagina donde se hace la accion de la forma.
function crearFormaArchivo($accion)
{
	echo <<<OUT
	<form action="$accion" method="post" enctype="multipart/form-data">
		<h2>Subir Archivo</h2>
		<br>
		<b>Escoger Archivo:</b> <input type="file" name="archivo" size="25" />
		<br>
		<b>Descripcion:</b> <input type ="text" name="descArchivo" size="25">
		<br>
		<b>Visibilidad:</b> 
			<fieldset>
				<input type="radio" name="visiArchivo" value="publico">Publico
				<br>
				<input type="radio" name="visiArchivo" value="privado">Privado
				<br>
			</fieldset>
		<br>
		<input type="submit" name="subirArchivo" value="Subir Archivo">
	</form>
OUT;
}

//Funcion para desplegar forma para crear directorio.
//Recibe la pagina donde se hace la accion de la forma.
function crearFormaDirectorio($accion)
{

	echo <<<OUT
	<form action="$accion" method="post">
		<h2>Crear Carpeta</h2> 
		<br>
		<b>Nombre de carpeta:</b><input type="text" name="nombreDirectorio" size="25"/>
		<br>
		<input type="submit" name="crearCarpeta" value="Crear Carpeta">
	</form>
OUT;

}

//Funcion que crea un directorio en el servidor remoto utilizando exec con mkdir
//Recibe el $path donde se encuentre actualmente el usuario para crear la carpeta
//Los directorios se crean de manera relativa a $path y el input.
//$path debe de tener un slash al final o ser un valor en blanco.
//Si se da como input un path con multiples directorios, y no existen, se crean.
function subirDirectorio($path)
{
	//Se adjunta el path absoluto del servidor remoto al path relativo del usuario dentro del servidor remoto
	$pathArchivo = "/var/www/html/serverfile/archivosRoot/".$path;
	//Se obtiene el nombre del directorio
	$nombreDirectorio = $_POST['nombreDirectorio'];
	//Se crea el string del comando combinando el path del nuevo directorio con el nombre
	$stringComando = "mkdir -p ".$pathArchivo.$nombreDirectorio;
	//Se crea el directorio
	exec($stringComando);
}

?>
