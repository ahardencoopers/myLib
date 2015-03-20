<?php
require_once("myMisc.php");

//Funcion que se encarga de preparar un statement con una query parametrizada.
//La funcion regresa false si ocurre un error, si se ejecuta sin errores regresa true.
//El 2ndo paramentro ($stmtQuery) se pasa por referencia para que se modifique dentro de la funcion.
//Recibe: string con query parametrizada, variable que contendra el statement y la conexion de la base de datos.
function prepararQuery($query, &$stmtQuery, $conexion)
{
	//Inicializar mysqli statement 
	$stmtQuery = mysqli_stmt_init($conexion);

	//Preparar statement para su ejecucion cargandolo
	//con la query parametrizada
	if(mysqli_stmt_prepare($stmtQuery, $query))
	{
		return true;	
	}
	else
	{
		return false;
	}
}

?>
