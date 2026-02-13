<?php

	if(!session_id()) {session_start();}

	$pto_vta  = isset($_SESSION['pto_vta']) ? $_SESSION['pto_vta'] : null;

	/* 2️⃣  Incluir tus funciones propias */
	//require_once __DIR__ . '/funciones.php';   // <-- archivo funciones.php en la misma carpeta

    include ('../../WebClientPrint.php');
	
    use Neodynamic\SDK\Web\WebClientPrint;
    use Neodynamic\SDK\Web\Utils;
    use Neodynamic\SDK\Web\DefaultPrinter;
    use Neodynamic\SDK\Web\InstalledPrinter;
    use Neodynamic\SDK\Web\ClientPrintJob;

	/*
	$lottired_evolution_api_support = $_SESSION['funciones']."lottired_evolution_api_support.php";
	// Obtener la ruta base desde el servidor y eliminarla de la ruta completa
	$basePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); // Eliminar cualquier '/' final de la ruta base
	$relativePath = str_replace($basePath, "", $lottired_evolution_api_support);

	// Asegurarse de que la ruta comience con "/"
	if ($relativePath[0] !== '/') {
		$relativePath = '/' . $relativePath;  // Agregar "/" al principio si no existe
	}	

	$lottired_evolution_api_support = $relativePath;
	*/

	if (isset($_SESSION["lr_api_base"])) {
		unset($_SESSION["lr_api_base"]);
	}

	//2025-Nov-24 adiciono para lottired
	$_SESSION["lr_api_base"] = 'https://lottiredapiqa.loteriademedellin.com.co';

	//$_SESSION["lr_api_base"] = 'https://api.loteriademedellin.com.co';
	//FIN //2025-Nov-24 adiciono para lottired

	$retorna = imprimir();

	if ((!isset($pto_vta)) || (empty($pto_vta)))
	{
		//echo "<script type='text/javascript'>window.open('../logout.php','_top','');</script>";
	}
	
?>

<?php
	if ($retorna =='0') //nuevo para que no salga la ventana de DEMO en la impresion
	{
?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8">
		<?php
			include($_SESSION['bd']."conexion.php");
			include($_SESSION['funciones']."include.php");
		?>	

		<script src="js/funciones.js?v=<?php echo filemtime('js/funciones.js'); ?>"></script>	

		<!-- Google Fonts -->
		<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

		<!-- Estilos principales -->
		<link rel="stylesheet" href="css/estilos.css?v=<?php echo filemtime('css/estilos.css'); ?>" type="text/css" />
		<link rel="stylesheet" href="css/otros_estilos.css?v=<?php echo filemtime('css/otros_estilos.css'); ?>" type="text/css" />

		<script language="javascript">						
			function fn_sale_del_programa()
			{
				var ruta_salida="<?php echo '../logout.php';?>";
				
				window.open(ruta_salida,'_top','');
			}
		</script>		
		
  		<script language="javascript">						
			function cancelar() {
				location.href="index.php";
			}		
		</script> 		
		
	</head>
  
	<body onLoad="inicio()">
		<div class="container">
		
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist">
				<li>
					<a href="#nuevo" role="tab" data-toggle="tab">
						<icon class="fa fa-barcode"></icon> Ventas
					</a>
				</li>

				<li>
					<a href="#reporte" role="tab" data-toggle="tab">
						<icon class="fa fa-barcode"></icon> Reporte
					</a>
				</li>

			</ul>		
		
			<!-- Tab panes -->
			<div class="tab-content">
				<div class="tab-pane fade active in" id="nuevo">
					<div class="row">
						<div class="col-lg-8 col-md-8 col-sm-8">
							<div class="panel panel-primary">
								<div class="panel-body">
									<div class="venta-header">LOTERIA EN LINEA</div>
									<div class="number-input-container">
										<div class="row">
											<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
												<div class="form-group">
													<label>Lotería</label>
													<div id="list_loterias_ltr">
														<select class="form-control" id="cboLoterias_ltr">
															<option value="16">MEDELLIN</option>
														</select>
													</div>
												</div>
											</div>

											<div class="col-xs-7 col-sm-7 col-md-7 col-lg-7">
												<div class="form-group">
													<div class="row">
														<div class="col-xs-12">
															<label>Digita el número o genéralo automáticamente</label>
														</div>
													</div>

													<div class="row">
														<div class="col-xs-8">
															<div class="row">
																<div class="col-xs-3 number-input-digit-wrapper">
																	<input type="text" id="cfr1" name="cfr1" class="form-control input-lg number-input-digit numeric-input" maxlength="1">
																</div>
																<div class="col-xs-3 number-input-digit-wrapper">
																	<input type="text" id="cfr2" name="cfr2" class="form-control input-lg number-input-digit numeric-input" maxlength="1">
																</div>
																<div class="col-xs-3 number-input-digit-wrapper">
																	<input type="text" id="cfr3" name="cfr3" class="form-control input-lg number-input-digit numeric-input" maxlength="1">
																</div>
																<div class="col-xs-3 number-input-digit-wrapper">
																	<input type="text" id="cfr4" name="cfr4" class="form-control input-lg number-input-digit numeric-input" maxlength="1">
																</div>
															</div>
														</div>
														<div class="col-xs-4 number-buttons-container">
															<div class="row">
																<div class="col-xs-6 number-button-wrapper-generate">
																	<button id="genera_numero" class="btn btn-info btn-lg btn-block number-button">
																		<i class="fa fa-random"></i>
																	</button>
																</div>
																<div class="col-xs-6 number-button-wrapper-clear">
																	<button id="limpiar-numero-btn" class="btn btn-danger btn-lg btn-block number-button">
																		<i class="fa fa-times"></i>
																	</button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="form-group">
											<div id="div_series_disponibles"></div>
										</div>

										<div class="table-container-scroll">
											<table id="tbl_ltr" class="table table-bordered table-condensed table-striped table-hover">
												<thead>
													<tr>
														<th style="text-align: left; width: 40%;">Lotería</th>
														<th class="text-center">Sorteo</th>
														<th class="text-center">Número</th>
														<th class="text-center">Serie</th>
														<th class="text-center">Fracc</th>
														<th class="text-right" style="width: 20%;">Valor</th>
														<th class="text-center"><i class="fa fa-trash"></i></th>
													</tr>
												</thead>
												<tbody>
												</tbody>
												<tfoot>
													<tr>
														<td colspan="7" style="text-align: right;">
															<button id="cancelar-tbl-ltr" style="background-color: #dc3545; color: white; border: none; border-radius: 4px; padding: 8px 24px; font-size: 16px; font-weight: bold; cursor: pointer; float: right;">
																Cancelar
															</button>
														</td>
													</tr>
												</tfoot>
											</table>
											<!-- coloca el boton cancelar aqui -->
										</div>
										
										<div class="datos-sorteo-row"></div>
									</div>
								</div>
							</div>

							<!-- ====================== MODAL BUSCAR TRANSACCIONES ====================== -->
							<div id="modal-buscar-transacciones" class="modal" tabindex="-1" role="dialog"
								aria-labelledby="modalBuscarTransaccionesLabel">
								<div class="modal-dialog" role="document" style="width:43%; height:70%; margin:0; position:fixed; left:17%; top:40%; transform:translateY(-50%);">
									<div class="modal-content" style="height:90%; border-radius:0;">
										<!-- Header del modal (azul) -->
										<div class="modal-header" style="background:#337ab7; color:#fff;">
											<h4 class="modal-title" id="modalBuscarTransaccionesLabel">CONSULTA DE VENTAS POR CLIENTE</h4>
										</div>

										<!-- Body del modal (por ahora sin inputs) -->
										<div class="modal-body">

											<!-- Contenedor para los filtros (separado de la tabla) -->
											<div class="row" style="margin-bottom: 15px;">
												<div class="col-xs-12 col-sm-6 col-md-4">
													<div class="form-group">
														<label for="modal-loteria-select">Loterías</label>
														<div id="list_modal-loteria-select">
															<select id="modal-loteria-select" class="form-control">
																<option value="">Lotería</option>
																<!-- Opciones a cargar dinámicamente o estáticas -->
															</select>
														</div>
													</div>
												</div>
												<div class="col-xs-12 col-sm-6 col-md-2">
													<div class="form-group">
														<label for="modal-sorteo-input">Sorteo</label>
														<input type="text"
															id="modal-sorteo-input"
															class="form-control numeric-input"
															maxlength="4"
															placeholder="0000"
															pattern="\d{4}"
															title="Solo se permiten 4 dígitos numéricos">
													</div>
												</div>
												<div class="col-xs-12 col-sm-12 col-md-5">
													<div class="form-group">
														<label for="con-nombre-cliente">Nombre Cliente</label>
														<input type="text"
															id="con-nombre-cliente"
															class="form-control"
															disabled>
													</div>
												</div>
											</div>

											<div class="pager" style="position: relative; overflow: hidden;"> 

												<!-- Botón de buscar a la izquierda -->
												<div style="float: left;">
													<button type="button" id="btn-buscar-ventas" class="btn btn-primary"> 
														<i class="fa fa-search"></i>Buscar
													</button>
												</div>

												<img src="<?php echo $_SESSION['java'].'css/images/first.png'?>" class="first"/> 
												<img src="<?php echo $_SESSION['java'].'css/images/prev.png'?>" class="prev"/> 
												<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
												<img src="<?php echo $_SESSION['java'].'css/images/next.png'?>" class="next"/> 											
												<img src="<?php echo $_SESSION['java'].'css/images/last.png'?>" class="last"/> 											
												<select class="pagesize" title="Select page size"> 
													<option selected="selected" value="10">10</option> 
													<option value="20">20</option> 
													<option value="30">30</option> 
													<option value="40">40</option> 
												</select>
												<select class="gotoPage" title="Select page number"></select>

												<!-- Botón cerrar al final -->
												<div style="float: right; margin-left: 15px;">
													<button type="button" class="btn btn-danger" data-dismiss="modal">
														<i class="fa fa-times"></i> Cerrar
													</button>
												</div>

											</div>

											<table class="table table-bordered table-condensed table-striped table-hover tablesorter"
													id="tbl_consulta_ventas">
													<thead>
														<tr>
															<th class="text-center">Venta</th>
															<th class="text-center">Fecha</th>
															<th class="text-center">Lotería</th>
															<th class="text-center">Sorteo</th>
															<th class="text-center">F. Sorteo</th>
															<th class="text-center">Billete</th>
															<th class="text-center">Serie</th>
															<th class="text-center">Frac.</th>
															<th class="text-center">Acción</th>
														</tr>
													</thead>
													<colgroup>
														<!-- Puedes ajustar anchos si lo deseas -->
														<col style="width:10%">
														<col style="width:10%">
														<col style="width:30%">
														<col style="width:3%">
														<col style="width:10%">
														<col style="width:2%">
														<col style="width:2%">
														<col style="width:2%">
														<col style="width:2%">
													</colgroup>
													<tbody>
														<!-- Las filas se cargarán dinámicamente vía AJAX -->
													</tbody>

											</table>
										</div>

									</div>
								</div>
							</div>
							<!-- ===================================================================== -->

							<!-- Panel de Premios de Lotería -->
							<div class="panel panel-default" style="margin-top: 20px;">
								<div class="premio-header">PREMIOS LOTERIA</div>
								<div class="panel-body" style="border: 1px solid #ddd; border-top: 0;">
									<div class="row" style="padding: 20px;">
										<div class="col-md-4">
											<div class="form-group">
												<label for="barcode">Código</label>
												<input type="text" id="barcode" class="form-control numeric-input" maxlength="11" placeholder="00000000000">
											</div>
										</div>

										<div class="col-md-8" style="text-align: right; margin-top: 24px;">
											<button type="button"
												id="buscar-transacciones-btn"
												class="btn btn-success btn-lg"
												style="padding: 8px 16px; font-size: 16px; font-weight: bold;"
												title="Buscar ventas por cédula del cliente">
												<i class="fa fa-search"></i> Ventas por Cédula
											</button>
										</div>

									</div>

									<div class="table-container-scroll" style="margin: 0 20px 20px 20px;">
										<table id= "tbl_premios_ltr" class="table table-bordered table-condensed table-striped table-hover">
											<thead>
												<tr>
													<th class="text-center">Sorteo</th>
													<th class="text-center">Numero</th>
													<th class="text-center">Serie</th>
													<th style="width: 55%; text-align: left;">Premio</th>
													<th class="text-right" style="width: 20%;">Valor</th>
													<th class="text-center"><i class="fa fa-trash"></i></th>
												</tr>
											</thead>
											<tbody>
												<!-- Las filas de premios se agregarán aquí dinámicamente -->
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<!-- Panel de Datos del Cliente -->
							<div class="panel panel-default">
								<div class="cliente-header">DATOS DEL CLIENTE</div>
								<div class="panel-body" style="border: 1px solid #ddd; border-top: 0; padding: 15px;">
									<form class="form-horizontal">
										<div class="form-group">
											<label for="cliente-cedula" class="col-sm-3 control-label">Cédula</label>
											<div class="col-sm-9">
												<input type="text" id="cliente-cedula" class="form-control numeric-input" maxlength="10" value="">
											</div>
										</div>
										<div class="form-group">
											<label for="cliente-nombres" class="col-sm-3 control-label">Nombres</label>
											<div class="col-sm-9">
												<input type="text" id="cliente-nombres" class="form-control alpha-input">
											</div>
										</div>
										<div class="form-group">
											<label for="cliente-apellidos" class="col-sm-3 control-label">Apellidos</label>
											<div class="col-sm-9">
												<input type="text" id="cliente-apellidos" class="form-control alpha-input">
											</div>
										</div>
										<div class="form-group">
											<label for="cliente-celular" class="col-sm-3 control-label">Celular</label>
											<div class="col-sm-9">
												<input type="text" id="cliente-celular" class="form-control numeric-input phone-input" maxlength="12">
											</div>
										</div>
										<div class="form-group">
											<label for="cliente-direccion" class="col-sm-3 control-label">Dirección</label>
											<div class="col-sm-9">
												<input type="text" id="cliente-direccion" class="form-control address-input">
											</div>
										</div>
										<div class="form-group">
											<label for="cliente-email" class="col-sm-3 control-label">Email</label>
											<div class="col-sm-9">
												<input type="email" id="cliente-email" class="form-control email-input">
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-12 clearfix" style="margin-top: 20px;">
												<button type="button" id="btn-grabar-cliente" class="btn btn-primary pull-right">
													<i class="fa fa-save"></i> Grabar
												</button>
												<!-- <button type="button" id="btn-cancelar-cliente" class="btn btn-danger pull-left" onclick="limpiarCliente()"> -->
												<button type="button" id="btn-cancelar-cliente" class="btn btn-danger pull-left" onclick="limpiarCliente()">
													<i class="fa fa-times"></i> Cancelar
												</button>
											</div>
										</div>
									</form>
								</div>
							</div>

							<div class="panel panel-success">
								<div class="panel-heading">
									<h4 class="panel-title">TOTALES</h4>
								</div>
								<div class="panel-body">
									<div class="resumen-venta">
										<div class="fila-resumen">
											<span>Total Venta:</span>
											<strong id="total-venta-valor">$0</strong>
										</div>
										<div class="fila-resumen">
											<span>Total Premios:</span>
											<strong id="total-premios-valor">$0</strong>
										</div>
										<hr>
										<div class="fila-resumen total-final">
											<span>Valor a Pagar:</span>
											<strong id="valor-pagar-valor">$0</strong>
										</div>
										<div class="fila-resumen" style="padding-top: 10px;">
											<label for="ingrese-efectivo">Ingrese Efectivo:</label>
											<input type="text" class="form-control" id="ingrese-efectivo" placeholder="$0" style="width: 120px;">
										</div>

										<!-- Nuevo campo añadido justo debajo del anterior -->
										<div class="fila-resumen" style="padding-top: 10px;">
											<label for="id_venta">ID Venta:</label>
											<input type="text" class="form-control" id="id_venta" placeholder="ID" style="width: 120px;">
										</div>

										<div class="totales-actions" style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; text-align: center;">
											<button type="button" id="btn-cancelar-venta" class="btn btn-danger btn-lg pull-left" style="margin-right: 20px;">
												<i class="fa fa-times"></i> Cancelar
											</button>
											<button type="button" id="btn-grabar-venta" class="btn btn-success btn-lg pull-right">
												<i class="fa fa-check"></i> Grabar
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="id_usu" id="id_usu" value="<?php echo $_SESSION['id_usu']?>">	
					<input type="hidden" name="pto_vta" id="pto_vta" value="<?php echo $pto_vta?>">
				</div>

				<div class="tab-pane fade" id="reporte">

					<div class="container-fluid">
					
						<form method="" action="" autocomplete="off">
						
							<div class="col-lg-12 col-lg-offset-0">
								<div class="panel panel-primary">
									<div class="panel-heading text-center"><h4>REPORTE DE VENTAS</h4></div>

									<div class="panel-body">
										<div class="row">
											<div class="form-group">
												
												<div class="col-md-2 col-lg-2">
													<label>Punto</label>
													<div id="list_pto_venta_con">
														<select id="cboPtoventa_con" name="cboPtoventa_con" class="form-control" style="background-color: #87CEFA;">
															<option value="<?php echo $pto_vta; ?>">Pto Vta</option>
														</select>
													</div>
												</div>
												
												<div class="col-md-4 col-lg-4">
													<label>Loteria</label>
													<div class="form-group">
														<div id="list_loterias">								 								
															<select id="cboLoterias" name="cboLoterias" class="form-control">
																<option value="99">Todas</option>
															</select>
														</div>																									
													</div>
												</div> 												
												
												<div class="col-md-2 col-lg-2">
													<label>Sorteo</label>
													<input type="text" name="num_sor" id="num_sor" 
															class="form-control">
												</div> 																								

												<div class="col-md-4 col-lg-4">
													<label>Fec. Sorteo</label>
													<input type="text" name="rango_fechas_sorteo" id="rango_fechas_sorteo" 
															class="form-control">
												</div>	

											</div>	
											
										</div>	

 										<div class="row" style = ""> 

											<div class="form-group">

												<div class="col-md-3 col-lg-3">
													<label>Fec. Venta</label>
													<input type="text" name="rango_fechas_con" id="rango_fechas_con" 
															class="form-control">
												</div>

												<div class="col-md-3 col-lg-3">
													<label>Fec. Validacion Premio</label>
													<input type="text" name="rango_fechas_val_pre" id="rango_fechas_val_pre" 
															class="form-control">
												</div>													
												
												<div class="col-md-3 col-lg-3">
													<label>Cedula</label>
													<input type="text" name="cedula" id="cedula" 
															class="form-control">
												</div>

												<!-- <div class="col-md-3 col-lg-3 col-lg-offset-3 col-md-offset-3 pull-right"> -->
												<div class="col-md-3 col-lg-3 pull-right">
													<label>.</label>												 													
													<div class="btn-toolbar" role="toolbar">
														<div class="btn-group" role="group">
															<button id="consulta_con" class="btn btn-info fa fa-search fa-lg"></button>
														</div>
														
														<div class="btn-group" role="group">
															<button id="rep_excell" class="btn btn-success fa fa-file-excel-o fa-lg" onclick="generarArchivosExcel()">
															</button>
														</div>														
														
														<div class="btn-group" role="group">
															<button id="rep_print" class="btn btn-warning fa fa-print fa-lg" title="Imprimir reporte"></button>
														</div>

														<div class="btn-group" role="group">
															<button type="button" class="btn btn-danger fa fa-ban fa-lg cancelar" onClick="cancelar(event)"></button>
														</div>                          
													</div>                          
												</div> 

											</div> 												                                               
										</div>
										
									</div>

									<div id="spinner" align="center" style="display: none;" >								
										<i class="fa fa-spinner fa-spin" style="font-size:80px" ></i>
									</div>									

								</div>
							</div>

							<div class="col-lg-12 col-lg-offset-0">
								<div class="panel panel-primary">
									<div class="panel-body">
										<div id='div_productos'></div>
									</div>
								</div>
							</div>

						</form>

					</div>

				</div>
			</div>
		</div>

		<div style='display:none' id='div_detalle'></div>
		<div style='display:none' id='div_totales'></div>
		
		<?php
			//Specify the ABSOLUTE URL to the php file that will create the ClientPrintJob object
			//In this case, this same page
			echo WebClientPrint::createScript(Utils::getRoot().'/formas/venta_terceros/index.php');
		?>

	</body>
</html>
<?php
	}
?>

<?php

	/**
	 * Función que procesa la petición de impresión enviada desde el
	 * cliente mediante WebClientPrint y devuelve el flujo de impresión.
	 *
	 * @return string  '1' si la impresión se envió correctamente,
	 *                 '0' en caso contrario.
	 */

	function imprimir()
	{
		$retorna = '0';	
		
		/*
		error_log('Error al decodificar JSON en imprimir()', 3,
					'C:/mercapos/htdocs/formas/pruebas/debug.log');
		*/
		
		if(!session_id()) {session_start();}	

		//include ('../../bd/conexion.php');

		// Conexión a la base de datos (ruta relativa al proyecto)
    	include __DIR__ . '/../../bd/conexion.php';

		//localhost
		require_once '/../ventas/funciones_lottired.php';
		
		//producion
		//include __DIR__ . '../ventas/funciones_lottired.php';
	
		// ---------------------------------------------------------
		// 1️⃣  Obtener la query‑string completa de la URL
		// ---------------------------------------------------------		
		$urlParts = parse_url($_SERVER['REQUEST_URI']);
		
		// Nombre de la impresora (vacío → usar la predeterminada)
    	$printerName = '';		

		// ---------------------------------------------------------
		// 2️⃣  Verificar que exista la cadena de consulta
		// ---------------------------------------------------------
		if (!isset($urlParts['query'])) {
			return $retorna; // nada que imprimir
		}
		

   		$rawQuery = $urlParts['query'];

		// ---------------------------------------------------------
		// 3️⃣  Detectar la señal de WebClientPrint
		// ---------------------------------------------------------
		// La constante WebClientPrint::CLIENT_PRINT_JOB debe estar
		// definida por la librería de impresión que estés usando.
		if (empty($rawQuery[WebClientPrint::CLIENT_PRINT_JOB])) {
			return $retorna; // no es una petición de impresión
		}

		// ---------------------------------------------------------
		// 4️⃣  Parsear los parámetros enviados desde JavaScript
		// ---------------------------------------------------------
		parse_str($rawQuery, $qs);

		// Los datos llegan codificados en URL → decodificamos y
		// convertimos a arrays asociativos.
		// Después (compatible con PHP 5.3)
		$clienteJson   = isset($qs['cliente'])   ? urldecode($qs['cliente'])   : '';
		$recibidosJson = isset($qs['recibidos']) ? urldecode($qs['recibidos']) : '';
		$totalesJson   = isset($qs['totales'])   ? urldecode($qs['totales'])   : '';
		
		//Construir los comandos ESC/POS 
		$esc      = '0x1B'; // ESC byte en notación hexadecimal
		$newLine  = '0x0A'; // LF byte en notación hexadecimal
		$cmds     = $esc . '@'; // Reset de la impresora (ESC @)

		//si se cumple esta condicion es porque vamos a imprirmir totales
		if (trim($clienteJson) === '' || trim($recibidosJson) === '' || trim($totalesJson) === '') {

			$total_venta   = isset($qs['total_venta'])   ? $qs['total_venta']   : 0;
			$total_premios = isset($qs['total_premios']) ? $qs['total_premios'] : 0;
			$id_usu        = isset($qs['id_usu'])        ? $qs['id_usu']        : '';
			$pto_vta       = isset($qs['pto_vta']) ? (int)$qs['pto_vta'] : 0;

			error_log(
				"total_venta: " . $total_venta . "\n" .
				"total_premios: " . $total_premios . "\n" .
				"id_usu: " . $id_usu . "\n" .
				"pto_vta: " . $pto_vta . "\n",
				3,
				__DIR__ . '/debug.log'
			);

			$cmds .= generar_reporte_totales($db, $esc, $newLine, $total_venta, $total_premios, $id_usu, $pto_vta);

		} else {

			$arre_cliente   = json_decode($clienteJson, true);
			$arre_recibidos = json_decode($recibidosJson, true);
			$arre_totales   = json_decode($totalesJson, true);

			/*
			// -----------------------------------------------------------------
			// 5️⃣  Registrar los tres arreglos en debug.log (para depuración)
			// -----------------------------------------------------------------
			$logFile = 'C:/mercapos/htdocs/formas/venta_terceros/debug.log';

			// Formateamos la salida para que sea legible en el log
			$logMsg  = "=== DEBUG - ARRAYS RECIBIDOS ===\n";
			$logMsg .= "Cliente   : " . print_r($arre_cliente, true)   . "\n";
			$logMsg .= "Recibidos : " . print_r($arre_recibidos, true) . "\n";
			$logMsg .= "Totales   : " . print_r($arre_totales,   true) . "\n";
			$logMsg .= "-------------------------------\n";

			// error_log escribe en el archivo indicado (crea el archivo si no existe)
			error_log($logMsg, 3, $logFile);
			*/

			// Si alguna decodificación falla, abortamos.
			if (json_last_error() !== JSON_ERROR_NONE) {
				error_log('Error al decodificar JSON en imprimir()', 3,
						'C:/mercapos/htdocs/formas/pruebas/debug.log');
				return $retorna;
			}
		
			// ---------------------------------------------------------
			// 5️⃣  Construir los comandos ESC/POS (ejemplo básico)
			// ---------------------------------------------------------

			/*
			$esc      = '0x1B'; // ESC byte en notación hexadecimal
			$newLine  = '0x0A'; // LF byte en notación hexadecimal
			$cmds     = $esc . '@'; // Reset de la impresora (ESC @)
			*/

			$premios = '0';
			$cmds .= generar_encabezado_recibo($db,$esc, $newLine, $arre_recibidos, $arre_cliente,$premios);

			if (is_array($arre_totales)) {
				// Salto de línea antes de los totales
				$cmds .= $newLine;

				// 1️⃣  Separador inicial
				//$cmds .= str_repeat('-', 46);
				//$cmds .= $newLine;

				// Agregar título centrado antes de los totales
				$cmds .= $esc . 'a' . chr(1); // Centrado

				// Cambiar a fuente B y activar negrilla
				$cmds .= $esc . 'M' . chr(1); // Fuente B
				$cmds .= $esc . 'E' . chr(1); // Negrilla

				// Aumentar tamaño de letra SOLO para este texto (doble alto)
				$cmds .= chr(29) . '!' . chr(1); // Doble alto
				$cmds .= "RESUMEN DE VENTA";
				$cmds .= chr(29) . '!' . chr(0); // Volver a tamaño normal
				$cmds .= $newLine;
				// Volver a fuente A y desactivar negrilla para el resto
				$cmds .= $esc . 'M' . chr(0); // Fuente A
				$cmds .= $esc . 'E' . chr(0); // Negrilla off
				// Volver a alinear a la izquierda para el resto
				//$cmds .= $esc . 'a' . chr(0); // Izquierda
				$cmds .= str_repeat('-', 46);
				$cmds .= $newLine;

				// 2️⃣  Total de la venta
				$cmds .= formatear_linea_total("TOTAL VENTA:", $arre_totales['totalVenta']);
				$cmds .= $newLine;

				// 3️⃣  Total de premios
				$cmds .= formatear_linea_total("TOTAL PREMIOS:", $arre_totales['totalPremios']);
				$cmds .= $newLine;

				// 4️⃣  Separador visual
				$cmds .= str_repeat('-', 46);
				$cmds .= $newLine;

				// 5️⃣  Valor a pagar
				$cmds .= formatear_linea_total("VALOR A PAGAR:", $arre_totales['valorPagar']);
				$cmds .= $newLine;

				// 6️⃣  Efectivo entregado
				$cmds .= formatear_linea_total("EFECTIVO:", $arre_totales['efectivo']);
				$cmds .= $newLine;

				// 7️⃣  Cambio (efectivo - valor a pagar)
				$cambio = $arre_totales['efectivo'] - $arre_totales['valorPagar'];
				$cmds .= formatear_linea_total("CAMBIO:", $cambio);
				$cmds .= $newLine;

				// 8️⃣  Separador final
				$cmds .= str_repeat('-', 46);
				$cmds .= $newLine;
			}

			$id_venta = isset($arre_recibidos['id_venta']) ? $arre_recibidos['id_venta'] : 0;
			
			$sql = "SELECT mov.*, 
					mae.nombre_corto AS Loteria,
					cli.nombres||' '||cli.apellidos AS cliente
					FROM movlottired mov
					INNER JOIN maelote mae ON mov.cod_lot = mae.cod_lot
					LEFT JOIN clientes cli ON mov.cedula = cli.cedula
					WHERE mov.id_venta = :id_venta OR mov.id_venta_premio = :id_venta";

			$stm = $db->prepare($sql);	
			$stm->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
			$stm->execute();
			$arre = $stm->fetchAll(PDO::FETCH_ASSOC);
			$stm->closeCursor();

			$esc = chr(27);
			$newLine = "\n";

			// Filtrar solo los registros donde 'tipo' es igual a 'VENTA'
			$arre_ventas = array_filter($arre, function($item) {
				return isset($item['tipo']) && $item['tipo'] === 'VENTA';
			});

			// Si quieres reindexar el array (opcional)
			$arre_ventas = array_values($arre_ventas);

			if (!empty($arre_ventas)) {
				//require_once 'funciones_lottired.php';

				foreach($arre_ventas as $row) 						
				{
				
					$cmds .= $newLine;
					// Cambiar a fuente B y activar negrilla
					$cmds .= $esc . 'M' . chr(1); // Fuente B
					$cmds .= $esc . 'E' . chr(1); // Negrilla

					// Aumentar tamaño de letra SOLO para este texto
					$cmds .= chr(29) . '!' . chr(1); // Doble alto
					$cmds .= "VENTA DE LOTERIA EN LINEA";
					$cmds .= chr(29) . '!' . chr(0); // Volver a tamaño normal
					$cmds .= $newLine;
					// Volver a fuente A y desactivar negrilla para el resto
					$cmds .= $esc . 'M' . chr(0); // Fuente A
					$cmds .= $esc . 'E' . chr(0); // Negrilla off

					// Volver a alinear a la izquierda para el resto
					$cmds .= $esc . 'a' . chr(0); // Izquierda
					$cmds .= "----------------------------------------------";
					$cmds .= $newLine;
					$cmds .= $esc . 'E' . chr(1); // Activar negrilla
					$cmds .= 'Codigo: '.$row['barcode'].' '.$row['loteria'];
					$cmds .= $esc . 'E' . chr(0); // Desactivar negrilla
					$cmds .= $newLine;
					$cmds .= "----------------------------------------------";

					$cmds .= $newLine;
					$cmds .= "Sorteo   Numero   Serie   Fracciones     Valor";
					$cmds .= $newLine;												
					$cmds .= "----------------------------------------------"; //esta linea mide 33				

					// Llamar a la función fn_consulta_venta_lottired
					$resultado = fn_consulta_venta_lottired($row['id_venta']); // Asegúrate de que 'id_venta' sea el campo correcto

					// Procesar los datos de la venta retornados por la función
					$arre_venta = $resultado['arre_venta'];
		
					// Verificar que $arre_venta y $arre_venta['soldNumbers'] existan y sean un array
					if (isset($arre_venta['soldNumbers']) && is_array($arre_venta['soldNumbers'])) {
						// Filtrar los datos

						$filtered = array_filter($arre_venta['soldNumbers'], function ($item) use ($row) {
							return isset($item['barcode']) && isset($row['barcode']) && $item['barcode'] === $row['barcode'];
						});

						// Verificar si hay elementos en el array filtrado
						if (!empty($filtered)) {
							// Obtener el primer elemento del array filtrado
							$firstItem = reset($filtered);

							// Extraer los valores de drawDate y drawJackpot
							$drawDate = isset($firstItem['drawDate']) ? $firstItem['drawDate'] : null;
							$drawJackpot = isset($firstItem['drawJackpot']) ? $firstItem['drawJackpot'] : null;


							$formattedDate = date('Y-m-d', strtotime($drawDate)); 
							$formattedJackpot = number_format($drawJackpot, 0, ',', '.');
						}
					}								

					$cmds .= $newLine;
					$cmds .= $row['num_sor'].'      '.$row['num_bil'].'     '.$row['num_ser'].'        '.$row['fracciones'].'         '.number_format($row['vlr_fracciones']);
					$cmds .= $newLine;
					$cmds .= $newLine;

					$cmds .= $esc . 'M' . chr(1); // Cambiar a Fuente B
					$cmds .= $esc . chr(4); // Activar Cursiva (Itálica) - Puede no ser soportado por todas las impresoras

					// Activar negrilla y tamaño más grande para la fecha y premio mayor
					$cmds .= $esc . 'E' . chr(1); // Activar negrilla
					//$cmds .= chr(29) . '!' . chr(16); // Ancho normal, doble alto	
					$cmds .= chr(29) . '!' . chr(1); // Doble altura (GS ! 1)
					$cmds .= 'Fecha Sorteo: '.$formattedDate.'     Premio Mayor: $'.$formattedJackpot;
					$cmds .= chr(29) . '!' . chr(0); // Volver a tamaño normal (GS ! 0)
					$cmds .= $esc . 'E' . chr(0); // Negrilla OFF

					$cmds .= $esc . chr(5); // Desactivar Cursiva (Itálica)
					$cmds .= $esc . 'M' . chr(0); // Volver a Fuente A

					$cmds .= $newLine;

					//numero anexo
					if (!empty($filtered) && isset($filtered[0]['includesAnnexNumber']) && $filtered[0]['includesAnnexNumber'] === true) {
						$cmds .= "----------------------------------------------";
						$cmds .= $newLine;
						$cmds .= $esc . 'E' . chr(1);
						$cmds .= "Anexo No: ".$filtered[0]['annexNumber']['number'];
						$cmds .= $newLine;
						$cmds .= "Detalle: ".$filtered[0]['annexNumber']['description'];
						$cmds .= $esc . 'E' . chr(0);
						$cmds .= $newLine;
					}

					//promotional
					if (!empty($filtered) && isset($filtered[0]['promotional']['winner']) && $filtered[0]['promotional']['winner'] === true) {
						$cmds .= "----------------------------------------------";
						$cmds .= $newLine;
						//$cmds .= $esc . 'E' . chr(1); // Activa negrita
						// Activar estilos y centrado para el título
						$cmds .= $esc . '!' . chr(24); // Activa doble altura y se asegura que la negrita también esté.
						$cmds .= $esc . 'a' . chr(1);  // <<<--- AÑADIDO: Activa la alineación centrada
						$cmds .= "G A N A S T E  P R E M I O";
						$cmds .= $esc . '!' . chr(0);  // Desactiva doble altura y negrita
						$cmds .= $newLine;
						$cmds .= $newLine;
						$cmds .= $esc . 'E' . chr(1); // Negrilla OFF
						$cmds .= $esc . 'a' . chr(0);  // <<<--- AÑADIDO: Desactiva el centrado (vuelve a alineación izquierda)
						//$cmds .= "Premio: ".$filtered[0]['promotional']['prizeDescription'];
						$cmds .= "Premio: ".$firstItem['promotional']['prizeDescription'][0];
						$cmds .= $newLine;
						$cmds .= "Cantidad de Premios: ".$filtered[0]['promotional']['prizeQuantity'];
						$cmds .= $newLine;
						if (!empty($filtered[0]['promotional']['replacementLottery'])) {
							$cmds .= "Loteria Recambio: ".$filtered[0]['promotional']['replacementLottery'];
							$cmds .= $newLine;
						}
						$cmds .= "Contrasena: ".$filtered[0]['promotional']['prizePasswordEncrypt'];
						$cmds .= $esc . 'E' . chr(0);
						$cmds .= $newLine;
					}
				}
				$cmds .= "----------------------------------------------";
				//$cmds .= $newLine;
			}

			//2025-may-22
			// Filtrar solo los registros donde 'tipo' es igual a 'PREMIO'
			$arre_premios = array_filter($arre, function($item) {
				return isset($item['tipo']) && $item['tipo'] === 'PREMIO';
			});

			// Si quieres reindexar el array (opcional)
			$arre_premios = array_values($arre_premios);

			// Llamar a la nueva función para generar los comandos de la sección de premios Lottired
			$cmds .= generar_seccion_premios_lottired($esc, $newLine, $arre_premios);						

			//2025-may-22 si existen premios lottired, entonces se imprime el tiquete para enviar a la oficina
			if (!empty($arre_premios)) {

				$cmds .= $newLine;
				$cmds .= $newLine;
				//$cmds .= $esc . 'a' . '0x1';  // Centrado											
				$cmds .= '**TODOS LOS SERVICIOS, UN SOLO PUNTO***';			
				$cmds .= $newLine;$newLine;
				$cmds .= "***GRACIAS POR SU COMPRA***";
				
				for( $i= 0 ; $i <= 4 ; $i++ )
				{
					$cmds .= $newLine;
				}								
				//corta el papel
				$cmds .= chr(29) . 'V' . chr(0); // 0x1D 0x56 0x00

				$premios = '1';
				
				$cmds .= generar_encabezado_recibo($db,$esc, $newLine, $arre_recibidos, $arre_cliente, $premios);
				$cmds .= $esc . 'a' . chr(1); // Center align
				$cmds .= $esc . '!' . '0x18'; // Emphasized + Double-height
				$cmds .= $newLine;
				$cmds .= "SOPORTE PARA MERCALOTERIAS";
				$cmds .= $esc . '!' . '0x00'; // Normal text
				$cmds .= $newLine;							
				$cmds .= generar_seccion_premios_lottired($esc, $newLine, $arre_premios);
				// Inicio de la sección DATOS DEL CLIENTE
				$cmds .= $newLine;
				$cmds .= $esc . 'E' . chr(1); // Negrilla ON
				$cmds .= "DATOS DEL CLIENTE:";
				$cmds .= $esc . 'E' . chr(0); // Negrilla OFF
				$cmds .= $newLine;

				$cmds .= "----------------------------------------------";
				$cmds .= $newLine; // Corresponde a <tr><td>.</td></tr>
				$cmds .= $newLine; // Corresponde a <tr><td>.</td></tr>

				$cmds .= $esc . 'E' . chr(1); // Negrilla ON
				$cmds .= "NOMBRES...:";
				$cmds .= $esc . 'E' . chr(0); // Negrilla OFF
				$cmds .= $newLine;
				$cmds .= $newLine; $cmds .= $newLine; // Espacio para que el cliente escriba

				$cmds .= $esc . 'E' . chr(1); // Negrilla ON
				$cmds .= "No. CEDULA:";
				$cmds .= $esc . 'E' . chr(0); // Negrilla OFF
				$cmds .= $newLine;
				$cmds .= $newLine; $cmds .= $newLine; // Espacio para que el cliente escriba

				$cmds .= $esc . 'E' . chr(1); // Negrilla ON
				$cmds .= "TELEFONO..:";
				$cmds .= $esc . 'E' . chr(0); // Negrilla OFF
			}
			//FIN 2025-may-22 si existen premios lottired, entonces se imprime el tiquete para enviar a la oficina

			$cmds .= $newLine;
			$cmds .= $newLine;

			$cmds .= $esc . 'a' . '0x1';  //center titulo												
			$cmds .= '***TODOS LOS SERVICIOS, UN SOLO PUNTO***';				
			$cmds .= $newLine;$newLine;
			//$cmds .= $esc . '!' . '0x00'; //Character font A selected (ESC ! 0)												
			//$cmds .= $esc . 'a' . '0x1';  //center titulo					
			$cmds .= "***GRACIAS POR SU COMPRA***";

			// Extraer el celular (si no existe, usar cadena vacía)
			$celular = isset($arre_cliente['celular']) ? $arre_cliente['celular'] : '';

			if ($celular != '')
			{
				// --- INICIO: Almacenar comandos limpios en la base de datos ---
				
				// Se trabaja sobre una copia para no alterar la variable $cmds original que va a la impresora.
				$cleaned_cmds = $cmds;

				// 1. Normalizar saltos de línea (reemplaza '0x0A' y chr(10) por \n).
				$cleaned_cmds = str_ireplace('0x0A', "\n", $cleaned_cmds);
				$cleaned_cmds = str_replace(chr(0x0A), "\n", $cleaned_cmds);

				// 2. Definir patrones para eliminar secuencias de control completas (ESC y GS).
				$esc_pattern = '(?:0x1B|' . preg_quote(chr(0x1B)) . ')';
				$gs_pattern = '(?:0x1D|' . preg_quote(chr(0x1D)) . ')';

				$patterns_to_remove = array(
					// Patrón genérico para comandos ESC (como !, a, M, E) seguido de un parámetro.
					'/' . $esc_pattern . '.{1}/s',
					
					// Patrón genérico para comandos GS (como V para cortar) seguido de un parámetro.
					'/' . $gs_pattern . '.{1,2}/s',

					// Eliminar cualquier otro código hexadecimal en formato de texto que pudiera quedar.
					'/0x[0-9a-f]{1,2}/i',

					// Eliminar cualquier otro carácter de control no imprimible (excepto el salto de línea).
					'/[\x00-\x09\x0B-\x1F\x7F]/'
				);

				// 3. Aplicar la limpieza.
				$cleaned_cmds = preg_replace($patterns_to_remove, '', $cleaned_cmds);

				if ($cleaned_cmds === null) {
					// Manejo de error para PHP 5.3 (preg_last_error_msg no existe).
					$error_code = preg_last_error();
					error_log("Error en preg_replace al limpiar comandos. Código: " . $error_code . ". Comandos (parcial): " . substr($cmds, 0, 500));
					$cleaned_cmds = "Error al procesar los comandos de impresión.";
				}

				try {
					// Se utiliza la variable limpia ($cleaned_cmds) para la base de datos.
					$stmt = $db->prepare("
						UPDATE print_commands_log
						SET print_command_data = :print_command_data,
							celular    = :celular_wsp
						WHERE id_venta = :id_venta
					");
					$stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
					$stmt->bindParam(':print_command_data', $cleaned_cmds, PDO::PARAM_STR);
					$stmt->bindParam(':celular_wsp', $celular_wsp, PDO::PARAM_STR);
					$stmt->execute();

					if ($stmt->rowCount() == 0) {
						$stmt = $db->prepare("
							INSERT INTO print_commands_log (id_venta, print_command_data, celular)
							VALUES (:id_venta, :print_command_data, :celular_wsp)
						");
						$stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
						$stmt->bindParam(':print_command_data', $cleaned_cmds, PDO::PARAM_STR);
						$stmt->bindParam(':celular_wsp', $celular_wsp, PDO::PARAM_STR);
						$stmt->execute();
					}
				} catch (PDOException $e) {
					error_log("Error al insertar/reemplazar comandos en print_commands_log: " . $e->getMessage());
				}
			}					
			
			for( $i= 0 ; $i <= 3 ; $i++ )
			{
				$cmds .= $newLine;
			}
			
		}

		//corta el papel
		$cmds .= '0x1D0x560x00';

		// ---------------------------------------------------------
		// 6️⃣  Preparar el objeto ClientPrintJob
		// ---------------------------------------------------------
		$cpj = new ClientPrintJob();
		$cpj->printerCommands   = $cmds;
		$cpj->formatHexValues   = true;

		// Determinar la impresora a usar:
		// $useDefaultPrinter debe estar definido en tu entorno.
		// Si no lo está, asumimos que se usará la predeterminada.
		$useDefaultPrinter = 'A';//$useDefaultPrinter ?? true;

		if ($useDefaultPrinter || $printerName === 'null') {
			$cpj->clientPrinter = new DefaultPrinter();
		} else {
			$cpj->clientPrinter = new InstalledPrinter($printerName);
		}

		// ---------------------------------------------------------
		// 7️⃣  Enviar el trabajo de impresión al cliente
		// ---------------------------------------------------------
		try {
			// Limpiar cualquier salida previa
			ob_start();
			ob_clean();

			// Enviar el flujo binario que entiende WebClientPrint
			echo $cpj->sendToClient();

			// Vaciar el buffer y enviarlo al navegador
			ob_end_flush();

			// Indicar éxito
			$retorna = '1';
		} catch (Exception $e) {
			// Registrar el error para depuración
			$logMsg = ">>> ERROR en imprimir(): " . $e->getMessage() . "\n";
			error_log($logMsg, 3, 'C:/mercapos/htdocs/formas/pruebas/debug.log');

			// Mantener $retorna = '0' (fallo)
		}

		return $retorna;
	}

	/**
	 * Genera el encabezado del recibo para impresión ESC/POS.
	 *
	 * @param PDO    $db               Conexión a la base de datos.
	 * @param string $esc              Código ESC (0x1B).
	 * @param string $newLine          Código LF (0x0A).
	 * @param array  $arre_recibidos   Datos de la venta (debe contener al menos
	 *                                 'id_venta', 'pto_vta' y 'id_usu').
	 * @param array  $arre_cliente    Datos del cliente (nombre, cedula, celular,
	 *                                 direccion, etc.).
	 *
	 * @return string  Cadena de comandos ESC/POS lista para enviarse a la
	 *                 impresora.
	 */
	function generar_encabezado_recibo($db, $esc, $newLine, $arre_recibidos, $arre_cliente)
	{
		// -----------------------------------------------------------------
		// 1️⃣  Obtener datos de la venta (id_venta, punto de venta, vendedor)
		// -----------------------------------------------------------------
		$id_venta = isset($arre_recibidos['id_venta']) ? $arre_recibidos['id_venta'] : '';
		$cod_pto  = isset($arre_recibidos['pto_vta']) ? $arre_recibidos['pto_vta'] : '';
		$id_usu   = isset($arre_recibidos['id_usu'])  ? $arre_recibidos['id_usu']  : '';

		// Nombre del punto de venta
		$nom_pto = '';
		if ($cod_pto !== '') {
			$sql = "SELECT pto.nom_pto
					FROM pto_vta pto
					WHERE pto.cod_pto = :cod_pto";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':cod_pto', $cod_pto, PDO::PARAM_STR);
			$stmt->execute();
			$nom_pto = $stmt->fetchColumn() ?: '';
		}

		// Nombre del vendedor
		$nom_vendedor = '';
		if ($id_usu !== '') {
			$sql = "SELECT nom_usu
					FROM usuarios
					WHERE id_usu = :id_usu";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':id_usu', $id_usu, PDO::PARAM_STR);
			$stmt->execute();
			$nom_vendedor = $stmt->fetchColumn() ?: '';
		}

		// -----------------------------------------------------------------
		// 2️⃣  Obtener datos del cliente (cliente, cedula, celular, direccion)
		// -----------------------------------------------------------------
		$cliente = trim(
        	(isset($arre_cliente['nombres'])   ? $arre_cliente['nombres']   : '') . ' ' .
        	(isset($arre_cliente['apellidos']) ? $arre_cliente['apellidos'] : '')
    	);
		$cedula    = isset($arre_cliente['cedula'])    ? $arre_cliente['cedula']    : '';
		$celular   = isset($arre_cliente['celular'])   ? $arre_cliente['celular']   : '';
		$direccion = isset($arre_cliente['direccion']) ? $arre_cliente['direccion'] : '';
		// -----------------------------------------------------------------
		// 3️⃣  Construir el encabezado ESC/POS
		// -----------------------------------------------------------------
		$cmds  = '';
		$cmds .= $esc . '!' . '0x18';               // negrita + doble altura
		$cmds .= $esc . 'a' . '0x1';                // centrado
		$cmds .= 'Mercaloterias S.A';
		$cmds .= $esc . '!' . '0x00';               // fuente normal
		$cmds .= $newLine;
		$cmds .= "Nit 805013949-0";
		$cmds .= $newLine;

		if ($premios === '0') {
			$cmds .= $esc . '!' . '0x18';               // énfasis + doble altura
			$cmds .= "INGRESOS RECIBIDOS PARA TERCEROS";
			$cmds .= $esc . '!' . '0x00';
			$cmds .= $newLine;
		}

		// Alinear a la izquierda para el resto del texto
		$cmds .= $esc . 'a' . '0x00';

		$cmds .= "Recibo No " . $id_venta . '  Fecha: ' . date('Y-m-d H:i:s');
		$cmds .= $newLine;

		$cmds .= "Punto: " . $nom_pto;
		$cmds .= $newLine;

		$cmds .= "Vendedor: " . $nom_vendedor;
		$cmds .= $newLine . $newLine;

		$cmds .= "Cliente: " . $cliente;
		$cmds .= $newLine;

		/*
		if (!empty($cedula)) {
			$cmds .= "Cédula: " . $cedula;
			$cmds .= $newLine;
		}

		if (!empty($celular)) {
			$cmds .= "Celular: " . $celular;
			$cmds .= $newLine;
		}

		if (!empty($direccion)) {
			$cmds .= $direccion;
			$cmds .= $newLine;
		}
		*/

		$cmds .= $esc . '!' . '0x00';               // reset de estilo
		//$cmds .= $newLine;

		return $cmds;
	}

	function generar_seccion_premios_lottired($esc, $newLine, $arre_premios) {
		$cmds_seccion = '';
		if (!empty($arre_premios)) {
			$cmds_seccion .= $newLine;
			$cmds_seccion .= $esc . 'a' . chr(1); // Centrado

			// Cambiar a fuente B y activar negrilla
			$cmds_seccion .= $esc . 'M' . chr(1); // Fuente B
			$cmds_seccion .= $esc . 'E' . chr(1); // Negrilla

			// Aumentar tamaño de letra SOLO para este texto
			$cmds_seccion .= chr(29) . '!' . chr(1); // Doble alto
			$cmds_seccion .= "PREMIOS DE LOTERIA EN LINEA";
			$cmds_seccion .= chr(29) . '!' . chr(0); // Volver a tamaño normal

			$cmds_seccion .= $newLine;

			// Volver a fuente A y desactivar negrilla para el resto
			$cmds_seccion .= $esc . 'M' . chr(0); // Fuente A
			$cmds_seccion .= $esc . 'E' . chr(0); // Negrilla off

			// Volver a alinear a la izquierda para el resto
			$cmds_seccion .= $esc . 'a' . chr(0); // Izquierda

			$cmds_seccion .= "----------------------------------------------";
			$cmds_seccion .= $newLine;
			$cmds_seccion .= "Sorteo   Numero   Serie   Fracciones    Premio";
			$cmds_seccion .= $newLine;
			$cmds_seccion .= "----------------------------------------------"; //esta linea mide 33
			$cmds_seccion .= $newLine;
			foreach ($arre_premios as $row) {
				$cmds_seccion .= $esc . 'E' . chr(1); // Activar negrilla
				$cmds_seccion .= 'Codigo: ' . $row['barcode'] . ' ' . $row['loteria'];
				$cmds_seccion .= $esc . 'E' . chr(0); // Desactivar negrilla
				$cmds_seccion .= $newLine;
				$cmds_seccion .= "----------------------------------------------";
				$cmds_seccion .= $newLine;
				$cmds_seccion .= $row['num_sor'] . '      ' . $row['num_bil'] . '     ' . $row['num_ser'] . '        ' . $row['fracciones'] . '         ' . number_format($row['vlr_premio']);
				$cmds_seccion .= $newLine;
				$cmds_seccion .= $row['nom_pre'];
				$cmds_seccion .= $newLine;
				$cmds_seccion .= $newLine;
			}
			$cmds_seccion .= "----------------------------------------------";
		}
		return $cmds_seccion;
	}

	// Función auxiliar para formatear líneas de totales con ancho completo
	function formatear_linea_total($etiqueta, $valor, $ancho_total = 46) {
		$valor_formateado = number_format($valor, 0, ',', '.');
		$espacios = $ancho_total - strlen($etiqueta) - strlen($valor_formateado);
		return $etiqueta . str_repeat(' ', max(1, $espacios)) . $valor_formateado;
	}

	function generar_reporte_totales($db, $esc, $newLine, $total_venta, $total_premios, $id_usu, $cod_pto) {

		$nom_pto = '';
		if ($cod_pto !== '') {
			$sql = "SELECT pto.nom_pto
					FROM pto_vta pto
					WHERE pto.cod_pto = :cod_pto";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':cod_pto', $cod_pto, PDO::PARAM_STR);
			$stmt->execute();
			$nom_pto = $stmt->fetchColumn() ?: '';
		}

		// Nombre del vendedor
		$nom_vendedor = '';
		if ($id_usu !== '') {
			$sql = "SELECT nom_usu
					FROM usuarios
					WHERE id_usu = :id_usu";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':id_usu', $id_usu, PDO::PARAM_STR);
			$stmt->execute();
			$nom_vendedor = $stmt->fetchColumn() ?: '';
		}

		error_log(
			"nom_pto: " . $nom_pto . "\n" .
			"nom_vendedor: " . $nom_vendedor . "\n" ,
			3,
			__DIR__ . '/debug.log'
		);

	}
?>

