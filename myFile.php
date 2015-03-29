<?php
require_once("myMisc.php");
require_once("myDb.php");
require_once("myPw.php");
require_once("myQuery.php");

//Este archivo contiene codigo para el manejo de archivos remotos.
//El directorio raiz del sistema de archivos remoto es el siguiente
// archivosRoot: directorio raiz del sistema, todos los archivos son parte de este directorio

//Funcion para subir un archivo al servidor
//Obtiene los datos del archivo del arreglo super global $_FILES
function subirArchivo()
{
	//Se quita los directorios que estan antes del nombre del archivo y se
	//le adjunta el directorio raiz del servidor remoto
	$nombreArchivo = "archivosRoot/".basename($_FILES['archivo']['name']);

	//Si se puede mover el archivo de almacenamiento temporal a permanente, regresa true,
	//si no puede regresa false;
	if(move_uploaded_file($_FILES['archivo']['tmp_name'], $nombreArchivo))
	{
		return true;
	}
	else
	{
		return false;
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
