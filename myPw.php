<?php 

function hashPassword($password)
{
	//Se crea un hash de 60 caracteres utilizando el algoritmo Blowfish con 
	//bcrypt y prefijo $2y.
	//PASSWORD_DEFAULT es el algoritmo actual mas seguro recomendado por PHP, esto puede
	//cambiar automaticamente futuras versiones de PHP.
	return password_hash($password, PASSWORD_DEFAULT, ['cost' => 11]);
}

function verificarPassword($str, $password)
{
	//Se verifica que un string dado sea igual al hash de una password.
	//Regresa true si str crea el mismo hash que password, false si no.
	return password_verify($str, $password);
}


?>
