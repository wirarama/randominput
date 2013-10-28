<?php
function head(){
	if(isset($_POST['submit'])){
		mysql_connect('localhost','root','');
		mysql_select_db('onlineshop');
		if(!empty($_POST['empty'])){
			mysql_query("TRUNCATE TABLE category");
			mysql_query("TRUNCATE TABLE product");
		}
		if(!empty($_POST['category'])){
			$lastid = mysql_fetch_row(mysql_query("SELECT MAX(id) FROM category"));
			if(!empty($lastid[0])){
				$lastid1 = $lastid[0]+1;
				$max = $_POST['category']+$lastid1;
			}else{
				$lastid1 = 1;
				$max = $_POST['category'];
			}
			for($i=$lastid1;$i<=$max;$i++){
				mysql_query("INSERT INTO category VALUES(null,'category".$i."')") or die(mysql_error());
			}
		}
		if(!empty($_POST['product'])){
			$lastid = mysql_fetch_row(mysql_query("SELECT MAX(id) FROM product"));
			if(!empty($lastid[0])){
				$lastid1 = $lastid[0]+1;
				$max = $_POST['product']+$lastid1;
			}else{
				$lastid1 = 1;
				$max = $_POST['product'];
			}
			list($yf,$mf,$df) = explode('-',$_POST['datefrom']);
			list($yt,$mt,$dt) = explode('-',$_POST['dateto']);
			$datefrom = mktime(0,0,0,$mf,$df,$yf);
			$dateto = mktime(23,50,50,$mt,$dt,$yt);
			for($i=$lastid1;$i<=$max;$i++){
				$category = mysql_fetch_row(mysql_query("SELECT id FROM category ORDER BY RAND() LIMIT 0,1"));
				list($pricefrom,$priceto) = explode('-',$_POST['price']);
				$price = intval(rand($pricefrom,$priceto)/10)*10;
				$date = rand($datefrom,$dateto);
				mysql_query("INSERT INTO product VALUES(null,'".$category[0]."','product".$i."','".$price."','".date('Y-m-d',$date)."')") or die(mysql_error());
			}
		}
		header('location:index.php?p=rules&rulestype=range');
	}
}
function js(){
	?>
	<script>
	var categorydatapercent = 0;
	var categorydatapercent1 = 0;
	$(function() {
		$(".datepicker").datepicker();
		$(".datepicker").datepicker("option","dateFormat","yy-mm-dd");
		$(".datepicker").datepicker({
			beforeShow:function(input) {
				$(input).css({
					"position": "relative",
					"z-index": 999999
				});
			}
		});
		$("#slidercategory").slider({
			value:10,
			min: 0,
			max: 50,
			slide: function( event, ui ) {
				$("#category").val(ui.value);
			}
		});
		$("#category").val($("#slidercategory").slider("value"));
		$("#sliderproduct").slider({
			value:200,
			min: 0,
			max: 500,
			step: 50,
			slide: function( event, ui ) {
				$("#product").val(ui.value);
			}
		});
		$("#product").val($("#sliderproduct").slider("value"));
		$("#slider-range").slider({
			range: true,
			min: 50,
			max: 50000,
			values: [50,5000],
			step: 50,
			slide: function( event, ui ) {
				$("#price").val(ui.values[0]+"-"+ui.values[1]);
			}
		});
		$("#price").val($("#slider-range").slider("values",0)+"-"+$("#slider-range").slider("values",1));
		$("#categorytotal span").text(categorydatapercent);
		$("#categorydataadd").click(function() {
			var newcategorypercent = parseInt($("#categorydatapercent").val(), 10);
			categorydatapercent1 = categorydatapercent+newcategorypercent;
			if(categorydatapercent1>=100){
				newcategorypercent = 100-categorydatapercent;
				categorydatapercent = 100;
			}else{
				categorydatapercent = categorydatapercent1;
			}
			$("#categorydata").append('<div class="subdata">'+$("#categorydatainput").val()+' '+newcategorypercent+'%</div>');
			$("#categorytotal span").text(categorydatapercent);
			$("#categorydatainput").val('');
			$("#categorydatapercent").val('10');
			$("#categorytotal span").text(categorydatapercent);
		});
		$("#categorydatapercent").spinner({
			min: 10,
			max: 100,
			step: 10,
			start: 10,
		});
	});
	</script>
	<?php
}
function formtemplete($label,$input){
	?>
	<div>
		<div class="label"><?php echo $label; ?></div>
		<div class="field"><?php echo $input; ?></div>
		<div style="clear:both;"></div>
	</div>
	<?
}
function main(){
?>
<form action="" method="POST">
	<?php
	formtemplete('Category','<div id="slidercategory"></div><input type="text" name="category" id="category" size="3" style="border:none;"> data');
	formtemplete('Category Data','<div id="categorytotal">total : <span></span>%</div><div id="categorydata"></div><input type="text" id="categorydatainput"> <input id="categorydatapercent" size="3" value="10" />% <button id="categorydataadd" type="button">add</button>');
	formtemplete('Product','<div id="sliderproduct"></div><input type="text" name="product" id="product" size="3" style="border:none;"> data');
	formtemplete('Price range','<div id="slider-range"></div><input type="text" name="price" id="price" size="10" style="border:none;">');
	formtemplete('Date range','<input type="text" name="datefrom" class="datepicker" placeholder="From"> <input type="text" name="dateto" class="datepicker" placeholder="To">');
	formtemplete('Empty Tables','<input type="checkbox" name="empty" value="1">');
	formtemplete('&nbsp;','<button type="submit" name="submit">Input</button>');	
	?>
</form>
<?php } ?>
