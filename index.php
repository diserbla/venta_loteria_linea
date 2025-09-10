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
					<div class="container">
						<form id="frm_procesar" method="" action="" autocomplete="off">
							<div class="col-lg-12	col-md-12 col-sm-12">
								<div class="panel panel-primary">
									<div class="panel-heading text-center"><h4>VENTAS</h4></div>
									
									<div class="panel-body" style="padding: 0;">
										<!-- Nueva sección de ventas -->
										<div class="venta-container-left-full">
											<div class="venta-header">LOTERIA EN LINEA</div>
											<div class="venta-body">

												<div class="row">
													<div class="col-xs-7 col-sm-7 col-md-7">
														<div class="form-group">
															<label>Lotería</label>
															<select class="form-control" id="loteria">
																<option value="MEDELLIN">MEDELLIN</option>
																<option value="BOGOTA">BOGOTA</option>
																<option value="CALI">CALI</option>
															</select>
														</div>
													</div>
													<div class="col-xs-5 col-sm-5 col-md-5">
														<div class="form-group">
															<label>Cédula</label>
															<input type="text" class="form-control" id="cedula" value="323-284-1619" readonly>
														</div>
													</div>
												</div>

												<div class="form-group">
													<div class="row">
														<div class="col-md-12">
															<div style="text-align: center; margin: 10px 0;">
																<span>Digita el número o genéralo automáticamente</span>
																<button class="btn btn-sm btn-info"><i class="fa fa-random"></i></button>
																<button class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>
															</div>
														</div>
													</div>
												</div>

												<div class="table-responsive">
													<table class="table table-bordered">
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
															<tr>
																<td colspan="7" style="text-align: center;">No hay números seleccionados</td>
															</tr>
														</tbody>
													</table>
												</div>

												<div class="total-row">
													Total: <strong>$0</strong>
												</div>

												<div class="footer-buttons">
													<button type="button" class="btn btn-success">CONFIRME LOS NÚMEROS SELECCIONADOS</button>
													<button type="button" class="btn btn-primary">Registrar</button>
													<button type="button" class="btn btn-danger">Cancelar</button>
												</div>
											</div>
										</div>
									</div>	

								</div>	
							</div>	
						</form>	
					</div>
				</div>
			</div>
		</div>

		<script src="js/funciones.js?v=<?php echo filemtime('js/funciones.js'); ?>"></script>	
	</body>
</html>
<?php
	}
?>