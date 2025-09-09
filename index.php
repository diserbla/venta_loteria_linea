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

		<!-- 🎯 ESTILOS EXTERNOS CON VERSIÓN DINÁMICA (ANTI-CACHE) -->
		<link rel="stylesheet" href="css/estilos.css?v=<?php echo filemtime('css/estilos.css'); ?>" type="text/css" />


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
							<div class="col-lg-11">
								<div class="panel panel-primary">
									<div class="panel-heading text-center"><h4>VENTAS</h4></div>
									
									<div class="panel-body">
										<div class="form-group">
											<div class="row">									
												<div class="col-md-2 col-lg-2 pull-right">
													<div class="btn-toolbar" role="toolbar">
														<div class="btn-group" role="group">
															<button id="genera_mensajes" title="EXCELL" class="btn btn-info fa fa-search fa-sm"></button>
														</div>													
														<div class="btn-group" role="group">
															<button class="btn btn-danger fa fa-eraser fa-lg" aria-hidden="true"></button>
														</div>							  														
													</div>											
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

		<script src="funciones.js?v=<?php echo filemtime('funciones.js'); ?>"></script>	
	</body>
</html>
<?php
	}
?>