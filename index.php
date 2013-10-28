<?php
	mysql_connect('localhost','root','');
	mysql_select_db('onlineshop');
	if(!empty($_GET['p'])){
		include($_GET['p'].'.php');
	}else{
		include('welcome.php');
	}
	$out = head();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>random input</title>
		<link rel="stylesheet" href="css/jquery-ui-1.10.3.custom.min.css">
		<link rel="stylesheet" href="css/style.css">
		<script src="js/jquery-1.9.1.min.js"></script>
		<script src="js/jquery-ui-1.10.3.custom.min.js"></script>
		<script type="text/javascript" src="js/jqsimplemenu.js"></script>
		<link rel="stylesheet" href="css/jqsimplemenu.css" type="text/css" media="screen" />
		<script type="text/javascript">
			$(document).ready(function () {
				$('.menu').jqsimplemenu();
			});
		</script>
		<?php js(); ?>
	</head>
	<body>
		<div align="center">
			<div id="container">
				<?php nav(); ?>
				<div style="margin-top:80px;">
				<?php main(); ?>
				</div>
			</div>
		</div>
	</body>
</html>
<?php
function nav(){
	?>
	<ul class="menu">
		<li><a href="#">Rules</a>
			<ul>
				<li><a href="index.php?p=rules&rulestype=data">By Data</a></li>
				<li><a href="index.php?p=rules&rulestype=range">By Ranges</a></li>
			</ul>
		</li>
		<li><a href="index.php?p=randominput">Random Input</a></li>
	</ul>
	<div style="clear:both;border-bottom:1px solid #BBB;padding-top:4px;"></div>
	<?php
}
?>
