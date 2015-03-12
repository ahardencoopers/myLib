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


function prepararBind(&$stmtQuery, $formatoValores)
{

	/*$args = func_get_args();
	$i = 0;
	$arrNombres = array();
	$arrValores = array();
	$scriptCrearBind = "./crearBind.bsh ";

	foreach($args as $valor)
	{
		if($i > 1)
		{
			$iTemp = $i-2;
			$arrNombres[$iTemp] = '\$arrValores'.$iTemp;
			$arrValores[$iTemp] = $valor;
			$scriptCrearBind = $scriptCrearBind.$arrNombres[$iTemp]." ";
		}
	
		$i++;
	}*/
	$scriptCrearBind = "./crearBind ";
	$scriptCrearBind = crearNombreComando($scriptCrearBind);
	exec($scriptCrearBind);
}

function ejecutarQuery(&$stmtQuery)
{
	mysqli_stmt_execute($stmtQuery);
}


?>
