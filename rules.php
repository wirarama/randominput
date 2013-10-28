<?php
function head(){
	$out = price_date_range();
	return $out;
}
function js(){
	global $out;
?>
	<script>
	$(function() {
		var icons = {
			header: "ui-icon-circle-arrow-e",
			activeHeader: "ui-icon-circle-arrow-s"
		};
		$("#sliderprice").slider({
			value:<?php echo $out[1]; ?>,
			min: 0,
			max: <?php echo $out[0]; ?>,
			step: 10,
			slide: function( event, ui ) {
				$("#price").val(ui.value);
			}
		});
		$("#sliderprice").slider("value",$("#price").val());
		$("#sliderdate").slider({
			value:<?php echo $out[3]; ?>,
			min: 0,
			max: <?php echo $out[2]; ?>,
			slide: function( event, ui ) {
				$("#date").val(ui.value);
			}
		});
		$("#sliderdate").slider("value",$("#date").val());
		$("#slidercluster").slider({
			value:0,
			min: 0,
			max: 50,
			slide: function( event, ui ) {
				$("#cluster").val(ui.value);
			}
		});
		$("#slidercluster").slider("value",$("#cluster").val());
		$( "#accordion" ).accordion({
			heightStyle: "content",
			icons: icons,
			collapsible: true,
			active : 'none'
		});
		$( "#toggle" ).button().click(function() {
			if ( $( "#accordion" ).accordion( "option", "icons" ) ) {
				$( "#accordion" ).accordion( "option", "icons", null );
			} else {
				$( "#accordion" ).accordion( "option", "icons", icons );
			}
		});
	});
	</script>
	<?php
}
function main(){
	global $out;
	$price = mysql_fetch_row(mysql_query("SELECT MIN(price),MAX(price) FROM product"));
	$date = mysql_fetch_row(mysql_query("SELECT MIN(date),MAX(date),UNIX_TIMESTAMP(MIN(date)),UNIX_TIMESTAMP(MAX(date)) FROM product"));
	$price_range = $out[1];
	if(!empty($_GET['price'])) $price_range = $_GET['price'];
	$date_range = $out[3];
	if(!empty($_GET['date'])) $date_range = $_GET['date'];
	$cluster_range = 0;
	if(!empty($_GET['cluster'])) $cluster_range = $_GET['cluster'];
	$date_range_unix = $date_range*(24*3600);
	$select = array();
	if(!empty($_GET['select'])) $select = explode(',',$_GET['select']);
	//--------------------------------------------------------------------------------------------------
	//	range section
	//--------------------------------------------------------------------------------------------------
	if($_GET['rulestype']=='range'){
		$price_cluster = array();
		$price_pointer_low = null;
		$price_pointer_high = null;
		for($i=$price[0];$i<=$price[1];$i+=$price_range){
			if($i!=$price[0]){ $price_pointer_low = $i+1; }else{ $price_pointer_low = $i; }
			$price_pointer_high = $i+$price_range;
			$valuelabel = 'price:'.$price_pointer_low.':'.$price_pointer_high;
			$range_result = select_value1("SELECT id FROM product WHERE price BETWEEN '".$price_pointer_low."' AND '".$price_pointer_high."'",$valuelabel);
			$price_range_level = array($price_pointer_low,$price_pointer_high,$range_result[0]);
			array_push($price_cluster,$price_range_level);
		}
		$date_cluster = array();
		$date_pointer_low = null;
		$date_pointer_high = null;
		for($i=$date[2];$i<=$date[3];$i+=$date_range_unix){
			if($i!=$date[2]){ $date_pointer_low = $i+(24*3600); }else{ $date_pointer_low = $i; }
			$date_pointer_high = $i+$date_range_unix;
			$valuelabel = 'date:'.date('Y-m-d',$date_pointer_low).':'.date('Y-m-d',$date_pointer_high);
			$range_result = select_value1("SELECT id FROM product WHERE UNIX_TIMESTAMP(date) BETWEEN '".$date_pointer_low."' AND '".$date_pointer_high."'",$valuelabel);
			$date_range_level = array(date('Y-m-d',$date_pointer_low),date('Y-m-d',$date_pointer_high),$range_result[0],$date_pointer_low,$date_pointer_high);
			array_push($date_cluster,$date_range_level);
		}
		$combiy = array();
		foreach($price_cluster AS $pc1){
			foreach($date_cluster AS $dc1){
				$valuelabel = 'price:'.$pc1[0].':'.$pc1[1].';date:'.$dc1[0].':'.$dc1[1];
				$range_result = select_value1("SELECT id FROM product WHERE (price BETWEEN '".$pc1[0]."' AND '".$pc1[1]."') AND (UNIX_TIMESTAMP(date) BETWEEN '".$dc1[3]."' AND '".$dc1[4]."')",$valuelabel);
				$combix = array($pc1,$dc1,$range_result);
				array_push($combiy,$combix);
			}
		}
		$rulescombinum = sizeof($combiy);
		$alldata = mysql_num_rows(mysql_query("SELECT id FROM product"));
		$alldiv = abs($alldata/$rulescombinum);
		if($_GET['cluster']!=0){
			$clustersum = abs($alldata/$_GET['cluster']);
		}
	//--------------------------------------------------------------------------------------------------
	//	data section
	//--------------------------------------------------------------------------------------------------
	}elseif($_GET['rulestype']=='data'){
		$data = array();
		$dstyle = array();
		$rules = array();
		$q = mysql_query("SELECT a.id,a.name,b.name,a.price,a.date,a.category,UNIX_TIMESTAMP(a.date) FROM product AS a,category AS b WHERE a.category=b.id ORDER BY id ASC");
		while($d = mysql_fetch_row($q)){
			if(in_array($d[0],$select)){
				$style = 'style="background-color:#f9ff54;"';
			}elseif($d[0]==$_GET['main']){
				$style = 'style="background-color:#f59042;"';
			}else{
				$style = null;
			}
			$same_category = select_value("SELECT id FROM product WHERE category='".$d[5]."' AND id!='".$d[0]."'",$d[0]);
			$same_price = select_value("SELECT id FROM product WHERE price='".$d[3]."' AND id!='".$d[0]."'",$d[0]);
			$same_date = select_value("SELECT id FROM product WHERE date='".$d[4]."' AND id!='".$d[0]."'",$d[0]);
			$same_exact = select_value("SELECT id FROM product WHERE category='".$d[5]."' AND price='".$d[3]."' AND date='".$d[4]."' AND id!='".$d[0]."'",$d[0]);
			$price_high = $d[3]+$price_range;
			$price_low = $d[3]-$price_range;
			if($price_low<0) $price_low=0;
			$date_high = $d[6]+$date_range_unix;
			$date_low = $d[6]-$date_range_unix;
			$same_relative = select_value("SELECT id FROM product WHERE category='".$d[5]."' AND (price BETWEEN '".$price_low."' AND '".$price_high."') AND (UNIX_TIMESTAMP(date) BETWEEN '".$date_low."' AND '".$date_high."') AND id!='".$d[0]."'",$d[0]);
			if($same_relative[1]>=1){
				$rulesx = array($d[2],$price_low.' to '.$price_high,date('Y-m-d',$date_low).' to '.date('Y-m-d',$date_high),$same_relative[0]);
				array_push($rules,$rulesx);
			}
			$datax = array($d[0],$d[1],$d[2],$d[3],$d[4],$same_category[0],$same_price[0],$same_date[0],$same_exact[0],$same_relative[0]);
			array_push($data,$datax);
			array_push($dstyle,$style);
		}
	}
	?>
	<h2>Constanta</h2>
	<form action="" method="GET">
	<table width="100%" cellpadding="8" cellspacing="1">
		<tr>
			<th width="100px">Value</th>
			<th width="100px">Min</th>
			<th width="100px">Max</th>
			<th>Range</th>
		</tr>
		<tr>
			<td>Price</td>
			<td><?php echo $price[0]; ?></td>
			<td><?php echo $price[1]; ?></td>
			<td><div id="sliderprice"></div><input type="text" name="price" id="price" size="3" style="border:none;" value="<?php echo $price_range; ?>"> USD</td>
		</tr>
		<tr>
			<td>Date</td>
			<td><?php echo $date[0]; ?></td>
			<td><?php echo $date[1]; ?></td>
			<td><div id="sliderdate"></div><input type="text" name="date" size="3" id="date" style="border:none;" value="<?php echo $date_range; ?>"> days</td>
		</tr>
		<tr>
			<td colspan="3">Target Cluster <i>(0=no target)</i></td>
			<td><div id="slidercluster"></div><input type="text" name="cluster" size="3" id="cluster" style="border:none;" value="<?php echo $cluster_range; ?>"> cluster</td>
		</tr>
	</table>
	<div align="right" style="margin:0px 0px 50px 0px;">
		<select name="rulestype" class="span2" style="margin:2px 0px 0px 0px;">
			<?php
			$rtvalue = array('data','range');
			$rtlabel = array('By Data','By Range');
			for($i=0;$i<=1;$i++){
				if($_GET['rulestype']==$rtvalue[$i]){
					$selected = ' selected';
				}else{
					$selected = null;
				}
			?>
			<option value="<?php echo $rtvalue[$i]; ?>"<?php echo $selected; ?>><?php echo $rtlabel[$i]; ?></option>
			<?php } ?>
		</select>
		<input type="hidden" name="p" value="rules">
		<button type="submit">Re Populate</button>
	</div>
	</form>
	<?php if(!empty($_GET['label'])) writelabel(); ?>
	<div id="accordion">
		<?php
		if(!empty($_GET['select'])){
		$selecti = "'".implode("','",$select)."'";
		$qselect = mysql_query("SELECT a.id,a.name,b.name,a.price,a.date,a.category,UNIX_TIMESTAMP(a.date) FROM product AS a,category AS b WHERE a.category=b.id AND a.id in(".$selecti.") ORDER BY id ASC");
		$selectionnum = mysql_num_rows($qselect);
		?>
		<h3>Selection (<?php echo $selectionnum; ?>)</h3>
		<div>
			<table width="100%" cellpadding="8" cellspacing="1">
				<tr>
					<th>id</th>
					<th>product</th>
					<th>category</th>
					<th>price</th>
					<th>date</th>
				</tr>
				<?php
				while($dselect = mysql_fetch_row($qselect)){
				?>
				<tr>
					<td><?php echo $dselect[0]; ?></td>
					<td><?php echo $dselect[1]; ?></td>
					<td><?php echo $dselect[2]; ?></td>
					<td><?php echo $dselect[3]; ?></td>
					<td><?php echo $dselect[4]; ?></td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<?php } ?>
		<?php
		if($_GET['rulestype']=='range'){
		?>
		<h3>Population (<?php echo $rulescombinum; ?>/avg:<?php echo $alldiv; ?><?php if(!empty($_GET['cluster'])){ ?>/cluster:<?php echo $clustersum; } ?>)</h3>
		<div>
			<table width="100%" cellpadding="8" cellspacing="1">
				<tr>
					<th colspan="2">Price</th>
					<th colspan="2">Date</th>
					<th rowspan="2">Result</th>
				</tr>
				<tr>
					<th>Min</th>
					<th>Max</th>
					<th>Min</th>
					<th>Max</th>
				</tr>
				<?php
				$total = 0;
				foreach($combiy AS $combiy1){
				$total += $combiy1[2][1];
				if($alldiv<$combiy1[2][1]){
					$rowclass=' class="success"';
				}else{
					$rowclass=null;
				}
				?>
				<tr<?php echo $rowclass; ?>>
					<td><?php echo $combiy1[0][0]; ?></td>
					<td><?php echo $combiy1[0][1]; ?></td>
					<td><?php echo $combiy1[1][0]; ?></td>
					<td><?php echo $combiy1[1][1]; ?></td>
					<td><?php echo $combiy1[2][0]; ?></td>
				</tr>
				<?php
				}
				?>
				<tr>
					<td colspan="5">Total : <b><?php echo $total; ?></b></td>
				</tr>
			</table>
		</div>
		<?php
		$rulespricenum = sizeof($price_cluster);
		?>
		<h3>Population by Price (<?php echo $rulespricenum; ?>)</h3>
		<div>
			<table width="100%" cellpadding="8" cellspacing="1">
				<tr>
					<th>Min</th>
					<th>Max</th>
					<th>Result</th>
				</tr>
				<?php
				foreach($price_cluster AS $price_cluster1){
				?>
				<tr>
					<?php
					foreach($price_cluster1 AS $price_cluster2){
					?>
					<td><?php echo $price_cluster2; ?></td>
					<?php
					}
					?>
				</tr>
				<?php
				}
				?>
			</table>
		</div>
		<?php
		$rulesdatenum = sizeof($date_cluster);
		?>
		<h3>Population by Date (<?php echo $rulesdatenum; ?>)</h3>
		<div>
			<table width="100%" cellpadding="8" cellspacing="1">
				<tr>
					<th>Min</th>
					<th>Max</th>
					<th>Result</th>
				</tr>
				<?php
				foreach($date_cluster AS $date_cluster1){
				?>
				<tr>
					<?php
					for($i=0;$i<=2;$i++){
					?>
					<td><?php echo $date_cluster1[$i]; ?></td>
					<?php
					}
					?>
				</tr>
				<?php
				}
				?>
			</table>
		</div>
		<?php
		}
		?>
		<?php
		if($_GET['rulestype']=='data'){
		$rulesnum = sizeof($rules);
		?>
		<h3>Population (<?php echo $rulesnum; ?>)</h3>
		<div>
			<table width="100%" cellpadding="8" cellspacing="1">
				<tr>
					<th>category</th>
					<th>price</th>
					<th>date</th>
					<th>Result</th>
				</tr>
				<?php
				for($i=0;$i<$rulesnum;$i++){
				?>
				<tr>
					<?php
					foreach($rules[$i] AS $rulesx){
					?>
					<td><?php echo $rulesx; ?></td>
					<?php
					}
					?>
				</tr>
				<?php
				}
				?>
			</table>
		</div>
		<?php
		$datanum = sizeof($data);
		?>
		<h3>Data (<?php echo $datanum; ?>)</h3>
		<div>
			<table width="100%" cellpadding="8" cellspacing="1">
				<tr>
					<th rowspan="2">id</th>
					<th rowspan="2">product</th>
					<th rowspan="2">category</th>
					<th rowspan="2">price</th>
					<th rowspan="2">date</th>
					<th colspan="5">match</th>
				</tr>
				<tr>
					<th>category</th>
					<th>price</th>
					<th>date</th>
					<th>all</th>
					<th>relative</th>
				</tr>
				<?php
				for($i=0;$i<$datanum;$i++){
				?>
				<tr>
					<?php
					foreach($data[$i] AS $datax){
					?>
					<td <?php echo $dstyle[$i]; ?>><?php echo $datax; ?></td>
					<?php
					}
					?>
				</tr>
				<?php
				}
				?>
			</table>
		</div>
		<?php } ?>
	</div>
<?php
}
function price_date_range(){
	$price = mysql_fetch_row(mysql_query("SELECT MIN(price),MAX(price) FROM product"));
	$price_range = ceil($price[1]-$price[0]);
	$price_range_inc = ceil($price_range/4);
	$date = mysql_fetch_row(mysql_query("SELECT UNIX_TIMESTAMP(MIN(date)),UNIX_TIMESTAMP(MAX(date)) FROM product"));
	$date_range = ceil(($date[1]-$date[0])/(24*3600));
	$date_range_inc = ceil($date_range/4);
	$out = array($price_range,$price_range_inc,$date_range,$date_range_inc);
	return $out;
}
function select_value($query,$main,$label=null){
	$num = mysql_num_rows(mysql_query($query));
	if($num!=0){
		$q = mysql_query($query);
		$a = array();
		while($d = mysql_fetch_row($q)){
			array_push($a,$d[0]);
		}
		$l = implode(',',$a);
		$link = '<a href="index.php?p=rules&rulestype='.$_GET['rulestype'].'&price='.$_GET['price'].'&date='.$_GET['date'].'&main='.$main.'&select='.$l.'&label='.$label.'">'.$num.'</a>';
	}else{
		$link = $num;
	}
	$out = array($link,$num);
	return $out;
}
function select_value1($query,$label=null){
	$num = mysql_num_rows(mysql_query($query));
	if($num!=0){
		$q = mysql_query($query);
		$a = array();
		while($d = mysql_fetch_row($q)){
			array_push($a,$d[0]);
		}
		$l = implode(',',$a);
		$link = '<a href="index.php?p=rules&rulestype='.$_GET['rulestype'].'&price='.$_GET['price'].'&date='.$_GET['date'].'&select='.$l.'&label='.$label.'">'.$num.'</a>';
	}else{
		$link = $num;
	}
	$out = array($link,$num);
	return $out;
}
function writelabel(){
	?>
	<table width="100%" cellpadding="8" cellspacing="1">
		<tr>
			<th>Value</th>
			<th>Min</th>
			<th>Max</th>
		</tr>
	<?php
	$d = explode(';',$_GET['label']);
	foreach($d AS $d1){
		$d2 = explode(':',$d1);
		?>
		<tr>
			<?php foreach($d2 AS $d3){ ?>
			<td><?php echo $d3; ?></td>
			<?php } ?>
		</tr>
		<?php
	}
	?>
	</table>
	<?php
}
?>
