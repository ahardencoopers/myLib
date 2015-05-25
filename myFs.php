<?php
require_once("myMisc.php");
require_once("myDb.php");
require_once("myPw.php");
require_once("myQuery.php");
require_once("mySession.php");


function iniciarDirectorio()
{
	$_SESSION["directorioActual"] = "../serverfile/archivosRoot/";

}

function cambiarDirectorio($nuevoDirectorio)
{
	if($nuevoDirectorio == "..")
	{
		$directorioActual = $_SESSION["directorioActual"];

		if($directorioActual == "../serverfile/archivosRoot/")
		{
				return false;
		}
		else
		{
			$informacionPath = pathinfo($directorioActual);
			$directorioAtras = $informacionPath['dirname']."/";
			$_SESSION["directorioActual"] = $directorioAtras;

		}

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

	$queryMostrarArchivos = "SELECT creadorFk, fecha, nombre, descr, tipo, tam, visib, path 
		FROM Archivos WHERE nombre = ?";

	//Preparar para despliegue de archivos en html
	//Abrir tabla donde se pondran los archivos
echo <<<OUT
<div class="container" >

OUT;
	for($i = 0; $i < sizeof($outputComando); $i++)
	{
		if(prepararQuery($queryMostrarArchivos, $stmtMostrarArchivos, $conexion))
		{
			mysqli_stmt_bind_param($stmtMostrarArchivos, "s", $outputComando[$i]);
			mysqli_stmt_execute($stmtMostrarArchivos);
			mysqli_stmt_bind_result($stmtMostrarArchivos, $fkArchivo, $fechaArchivo, 
				$nombreArchivo, $descrArchivo, $tipoArchivo, $tamArchivo, $visiArchivo, $pathArchivo);
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
			$linkArchivo = "www.hardencooper.co/serverfile/".$pathArchivo;
	
			if($tipoArchivo != "dir")
			{
echo <<<OUT
    <div class="row browse-item">
        <div class="col-xs-2 text-center">
        </div>
        <div class="col-xs-3 text-center">
			<a href="$path" target="_blank"><img src="img/pdf17.png"></a>
   		     	
        </div>
        <div class="col-xs-6">
			<h4><a href="$path" target="_blank">$nombreArchivo</a></h4>
	        <p>$descrArchivo</p>
			<p>Tipo de archivo: $tipoArchivo</p>	
        </div> 
    </div>

OUT;
echoLine();
			}
		}
		if($tipoArchivo == "dir")
		{
echo <<<OUT
    <div class="row browse-item">
        <div class="col-xs-2 text-center">
        </div>
        <div class="col-xs-3 text-center">
			<a href="$path" target="_blank"><img src="img/file96.png"></a>
        </div>
        <div class="col-xs-6 text-left">
			<h4>
					<form action="fileHome.php"  method="post">
					<input type="hidden" name="nuevoDirectorio" value="$nombreArchivo">
					<input class="btn-link text-left" type="submit" 
					    name="submitNuevoDirectorio" value="Ir a carpeta: $nombreArchivo">
				    </form>
			</h4>
	        <p>$descrArchivo</p>
			<p>$linkArchivo</p>
			<p>Tipo de archivo: $tipoArchivo</p>	
        </div> 
    </div>

OUT;
echoLine();
			}
		}
echo <<<OUT
</div>
OUT;
	}


function mostrarEditarArchivo($nombreEditarArchivo)
{
	$conexion = conectarDb();

	haySesion();
	validarSesion();

	$nombreUsuario = $_SESSION["nombre"];
	$idUsuario = $_SESSION["id"];
	$directorioActual = $_SESSION["directorioActual"];

	$queryEditarArchivo = "SELECT nombre, descr, visib, tipo FROM Archivos WHERE nombre = ?";

	if(prepararQuery($queryEditarArchivo, $stmtEditarArchivo, $conexion))
		{
			mysqli_stmt_bind_param($stmtEditarArchivo, "s", $nombreEditarArchivo);
			mysqli_stmt_execute($stmtEditarArchivo);
			mysqli_stmt_bind_result($stmtEditarArchivo, $nombreArchivo, $descrArchivo, $visibArchivo, $tipoArchivo);
			mysqli_stmt_fetch($stmtEditarArchivo);
		}
		else
		{
			echoLine("error al mostrar Archivos");
			return false;
		}
	
	mysqli_stmt_store_result($stmtEditarArchivo);


echo <<<OUT
		<table class="table table-bordered table-hover" data-toggle="table" data-url="data1.json" data-cache="false" data-height="299">
			<thead>
				<tr>
					<th data-field="nombreArchivo">Nombre</th>
					<th data-field="descrArchivo">Descripcion</th>
					<th data-field="ownerArchivo">Visibilidad</th>
					<th data-field="cambiarUsuario">Cambiar dueño del archivo</th>
					<th data-field="opcionesArchivo">Opciones</th>
				</tr>
			</thead>

				<tr>
					<form action="editarArchivo.php" method="post">
						<input type="hidden" name="nombreOriginal" value="$nombreArchivo">
						<input type="hidden" name="tipoArchivo" value="$tipoArchivo">
						<td>
							<input type="text" name="editarNombre" value="$nombreArchivo">
						</td>
						<td>
							<input type="text" name="editarDescr" value="$descrArchivo">
						</td>
						<td>
							<select name="listaVisib">
								<option value="publico">
									Publico
								</option>
								<option value="privado">
									Privado
								</option>
							</select>
						</td>
						<td>
							<input type="text" name="cambiarUsuario" value="$nombreUsuario">
						</td>
						<td>
							<input class="btn btn-link" type="submit" name="submitEditarArchivo" value="Actualizar Archivo">
						</td>
						<td>
							<input type="submit" name="submitBorrarArchivo" value="Borrar Archivo">
						</td>
					</form>
				</tr>
OUT;

}

?>
