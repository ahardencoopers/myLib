<?php

//Funcion para desplegar string y newline
function echoLine($str)
{
	echo "$str";
	echo "<br>";
}

//Funcion para revisar si un arreglo contiene por lo menos
//un elemento con el string vacio (donde vacio es el string sin ningun caracter "" o solo caracteres de whitespace)
//En caso que el arreglo no tenga elementos vacios regresa true, de lo contrario false
function hayVacios($arr)
{
	//Se obtiene tamano del arreglo
	$tamArr = sizeof($arr);

	//Ciclo foreach para recorrer el arreglo
	foreach($arr as $actualStr)
	{
		//String vacio para comparaciones
		//y string actual a comparar
		$vacioStr = "";

		//Se quitan todos los whitespaces del string actual
		//del arreglo con una expresion regular
		//La expresion regular /\s+/ hara match con 1 o mas
		//instancias de cualquier caracter whitespace 
		$actualStr = preg_replace('/\s+/', '', $actualStr);
		
		//Si despues de quitar todos los whitespaces
		//Del string actual es igual al string vacio,
		//es un elemento vacio
		if($vacioStr == $actualStr)
		{
			return true;
		}
		
	}

	//Si termina el ciclo, no hay ningun elemento vacio en el arreglo
	return false;
}

//Funcion para convertir un arreglo asociativo a un arreglo tradicional
//(arreglo que solo tiene indices numericos ordenados de manera ascendente y 
//el primer elemento se encuentra en el indice 0), no tiene valor de retorno
//pero se recibe una variable donde se pondra el arreglo tradicional.
//Recibe: Un arreglo.
function convertirArreglo($arrAsociativo, &$arrTradicional)
{
	//Se declara explicitamente que arrTradicional es un arreglo.
	$arrTradicional = array(0 => "");

	//Contador para saber cual es el numero del indice actual
	//al que se mapeara el elemento del arreglo asociativo.
	$i = 0;

	//Ciclo foreach para recorrer cada elemento
	//del arreglo asociativo.
	foreach($arrAsociativo as $elemento)
	{
		//Asignar cada elemento al arreglo tradicional.
		$arrTradicional[$i] = $elemento;
		$i++;
	}
}


?>
