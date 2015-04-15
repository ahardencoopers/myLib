<?php
require_once("myMisc.php");
require_once("myDb.php");
require_once("myPw.php");
require_once("myQuery.php");
require_once("mySession.php");


function iniciarDirectorio()
{
	$_SESSION["directorioActual"] = "archivosRoot/";

}

function cambiarDirectorio($nuevoDirectorio)
{
	if($nuevoDirectorio == "..")
	{
		echoLine("Regresar directorio");
	}
	else
	{
		$_SESSION["directorioActual"] = $_SESSION["directorioActual"].$nuevoDirectorio."/";
	}
	
}

function mostrarArchivos()
{
	$conexion = conectarDb();

	haySesion();
	validarSesion();

	$nombreUsuario = $_SESSION["nombre"];
	$idUsuario = $_SESSION["id"];

	$directorioActual = $_SESSION["directorioActual"];
	$stringComando = "ls ".$directorioActual;
	exec($stringComando, $outputComando);

	$queryMostrarArchivos = "SELECT creadorFk, fecha, nombre, descr, tipo, tam, visib 
		FROM Archivos WHERE nombre = ?";

	//Preparar para despliegue de archivos en html
	//Abrir tabla donde se pondran los archivos
echo <<<OUT
		<table class="table table-bordered table-hover" data-toggle="table" data-url="data1.json" data-cache="false" data-height="299">
			<thead>
				<tr>
					<th data-field="nombreArchivo">Nombre</th>
					<th data-field="descrArchivo">Descripcion</th>
					<th data-field="ownerArchivo">Owner</th>
					<th data-field="fechaArchivo">Fecha de Creacion</th>
					<th data-field="tamArchivo">Tamanio</th>
					<th data-field="tipoArchivo">Tipo</th>
				</tr>
OUT;

	for($i = 0; $i < sizeof($outputComando); $i++)
	{
		if(prepararQuery($queryMostrarArchivos, $stmtMostrarArchivos, $conexion))
		{
			mysqli_stmt_bind_param($stmtMostrarArchivos, "s", $outputComando[$i]);
			mysqli_stmt_execute($stmtMostrarArchivos);
			mysqli_stmt_bind_result($stmtMostrarArchivos, $fkArchivo, $fechaArchivo, 
				$nombreArchivo, $descrArchivo, $tipoArchivo, $tamArchivo, $visiArchivo);
			mysqli_stmt_fetch($stmtMostrarArchivos);
		}
		else
		{
			echoLine("error al mostrar Archivos");
			return false;
		}
	
		mysqli_stmt_store_result($stmtMostrarArchivos);

		$queryOwnerArchivo = "SELECT nombre FROM Usuarios WHERE id = ?";

		if(prepararQuery($queryOwnerArchivo, $stmtOwnerArchivo, $conexion))
		{
			mysqli_stmt_bind_param($stmtOwnerArchivo, "i", $fkArchivo);
			mysqli_stmt_execute($stmtOwnerArchivo);
			mysqli_stmt_bind_result($stmtOwnerArchivo, $ownerArchivo);
			mysqli_stmt_fetch($stmtOwnerArchivo);
		}
		else
		{
			echoLine("Error al conseguir deunio de archivo");
		}

		mysqli_stmt_store_result($stmtOwnerArchivo);
		
		if(($visiArchivo == 0) || ($idUsuario == $fkArchivo))
		{
			$path = $directorioActual.$nombreArchivo;
	
			if($tipoArchivo != "dir")
			{
echo <<<OUT
				<tr>
					<td><a href="$path" target="_blank">$nombreArchivo</a></td>
					<td>$descrArchivo</td>
					<td>$ownerArchivo</td>
					<td>$fechaArchivo</td>
					<td>$tamArchivo</td>
					<td>$tipoArchivo</td>
				</tr>
OUT;
			}
			else
			{
echo <<<OUT
				<tr>
					<td >
						<form action="fileHome.php" method="post">
							<input type="hidden" name="nuevoDirectorio" value="$nombreArchivo">
							<input class="btn-link" type="submit" 
								name="submitNuevoDirectorio" value="Ir a carpeta: $nombreArchivo">
						</form>
					</td>

					<td>$descrArchivo</td>
					<td>$ownerArchivo</td>
					<td>$fechaArchivo</td>
					<td>$tamArchivo</td>
					<td>$tipoArchivo</td>
				</tr>
OUT;

			}

		}

	}
}


?>
