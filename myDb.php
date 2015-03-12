<?php
function conectarDb()
{
	//Definir conexion como variable estatica para evitar multiples conexiones

	static $conexion;

	//Conectarse a la base de datos solo si no se ha establecido una conexion

	if(!isset($conexion))
	{
		//Cargar credenciales de conexion a base de datos
		//Las credenciales se cargan en un arreglo
		$credenciales = 
			parse_ini_file('/var/www/html/serverfile/config.ini');

		//Conexion a base de datos
		//Se utilizan los datos del arreglo asociativo previamente definido
		$conexion = 
			mysqli_connect('localhost', $credenciales['usuario'], 
			$credenciales['password'], $credenciales['dbnombre']);

	}
	
	//Manejo de error de conexion
	if($conexion == false)
	{
		return mysqli_connect_error();
	} 
	

	return $conexion;
}


?>
