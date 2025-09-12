<?php

	//https://listjs.com/examples/pagination/  

	if(!session_id()) {session_start();}

	$pto_vta  = isset($_SESSION['pto_vta']) ? $_SESSION['pto_vta'] : null;
	
	$fec_desp = date('d/m/Y');
	
	$fec_actual = date('d/m/Y');
	
	//imprimir(); //esta en la parte inferior de este programa
	$retorna = '0';

	$lottired_evolution_api_support = $_SESSION['funciones']."lottired_evolution_api_support.php";
	// Obtener la ruta base desde el servidor y eliminarla de la ruta completa
	$basePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); // Eliminar cualquier '/' final de la ruta base
	$relativePath = str_replace($basePath, "", $lottired_evolution_api_support);

	// Asegurarse de que la ruta comience con "/"
	if ($relativePath[0] !== '/') {
		$relativePath = '/' . $relativePath;  // Agregar "/" al principio si no existe
	}	

	$lottired_evolution_api_support = $relativePath;

	if ((!isset($pto_vta)) || (empty($pto_vta)))
	{
		echo "<script type='text/javascript'>window.open('../logout.php','_top','');</script>";
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
			</ul>		
		
			<!-- Tab panes -->
			<div class="tab-content">
				<div class="tab-pane fade active in" id="nuevo">
					<div class="row"> 
						<div class="col-lg-8 col-md-8 col-sm-8">
							<div class="panel panel-primary">
								<div class="panel-body" >
									<div class="venta-header">LOTERIA EN LINEA</div>
									<div class="number-input-container">
										<div class="row">
											<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
												<div class="form-group">
													<label>Lotería</label>
													<select class="form-control" id="loteria">
														<option value="MEDELLIN">MEDELLIN</option>
														<option value="BOGOTA">BOGOTA</option>
														<option value="CALI">CALI</option>
													</select>
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
																<div class="col-xs-3 number-input-digit-wrapper"><input type="text" id="cfr1" name="cfr1" class="form-control input-lg number-input-digit numeric-input" maxlength="1"></div>
																<div class="col-xs-3 number-input-digit-wrapper"><input type="text" id="cfr2" name="cfr2" class="form-control input-lg number-input-digit numeric-input" maxlength="1"></div>
																<div class="col-xs-3 number-input-digit-wrapper"><input type="text" id="cfr3" name="cfr3" class="form-control input-lg number-input-digit numeric-input" maxlength="1"></div>
																<div class="col-xs-3 number-input-digit-wrapper"><input type="text" id="cfr4" name="cfr4" class="form-control input-lg number-input-digit numeric-input" maxlength="1"></div>
															</div>
														</div>
														<div class="col-xs-4 number-buttons-container">
															<div class="row">
																<div class="col-xs-6 number-button-wrapper-generate">
																	<button class="btn btn-info btn-lg btn-block number-button"><i class="fa fa-random"></i></button>
																</div>
																<div class="col-xs-6 number-button-wrapper-clear">
																	<button id="limpiar-numero-btn" class="btn btn-danger btn-lg btn-block number-button"><i class="fa fa-times"></i></button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<table class="table table-bordered table-condensed table-striped table-hover">
											<thead>
												<tr>
													<th>Lotería</th>
													<th>Sorteo</th>
													<th>Número</th>
													<th>Serie</th>
													<th>Fracc</th>
													<th>Valor</th>
													<th>Borrar</th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>

										<div class="datos-sorteo-row">
											Total: <strong>$0</strong>
										</div>

										<div class="footer-buttons" style="text-align: right;">
											<button type="button" class="btn btn-danger">Cancelar</button>
											<button type="button" class="btn btn-primary">Registrar</button>
										</div>
									</div>
								</div>	
							</div>	
						</div>	

						<div class="col-lg-4 col-md-4 col-sm-4">
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
									</div>
								</div>
							</div> 
						</div>
					</div>
					
					<!-- Nueva fila para el panel de premios -->
					<div class="row">
						<div class="col-lg-8 col-md-8 col-sm-8">
							<div class="panel panel-default">
								<div class="premio-header">PREMIOS LOTERIA</div>
								<div class="panel-body" style="border: 1px solid #ddd; border-top: 0;">
									<div class="row" style="padding: 20px;">
										<div class="col-md-4">
											<label for="premio-cedula">Cédula</label>
											<div class="input-group">
												<input type="text" id="premio-cedula" class="form-control numeric-input" maxlength="10">
												<span class="input-group-addon" id="buscar-cliente-btn" style="cursor: pointer;">
													<i class="fa fa-search"></i>
												</span>
											</div>
										</div>
										<div class="col-md-5">
											<div class="form-group">
												<label for="premio-nombre-cliente">Nombre</label>
												<input type="text" id="premio-nombre-cliente" class="form-control" readonly>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="premio-codigo">Código</label>
												<input type="text" id="premio-codigo" class="form-control numeric-input" maxlength="11" placeholder="00000000000">
											</div>
										</div>
									</div>
								</div>
							</div>	
						</div>	
					</div> 
				</div>
			</div>
		</div>
	</body>
</html>
<?php
	}
?>