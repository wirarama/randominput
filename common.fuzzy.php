<?php
//------------------------------------------------------------------
//	fuzzy tinggi
//------------------------------------------------------------------
function fuzzy_tinggi($nilai,$toleransi,$nilai_puncak){
	if($nilai!=0){
		if($nilai>=$toleransi){
			$cache1 = $nilai-$toleransi;
			$cache2 = $nilai_puncak-$toleransi;
			$persen_tinggi = 0;
			if($cache1!=0 && $cache2!=0) $persen_tinggi=$cache1/$cache2;
		}elseif($nilai>=$nilai_puncak){
			$persen_tinggi=1;
		}else{
			$persen_tinggi=0;
		}
	}else{
		$persen_tinggi=0;
	}
	return $persen_tinggi;
}
//------------------------------------------------------------------
//	fuzzy sedang
//------------------------------------------------------------------
function fuzzy_sedang($nilai,$nilai_puncak,$toleransimin,$toleransimax){
	if(($nilai<$nilai_puncak)&&($toleransimin<=$nilai)){
		$persen_sedang=($nilai-$toleransimin)/($nilai_puncak-$toleransimin);
	}elseif(($nilai<=$toleransimax)&&($nilai_puncak<$nilai)){
		$persen_sedang=($toleransimax-$nilai)/($toleransimax-$nilai_puncak);
	}elseif($nilai==$nilai_puncak){
		$persen_sedang=1;
	}else{
		$persen_sedang=0;
	}
	return $persen_sedang;
}
//------------------------------------------------------------------
//	fuzzy rendah
//------------------------------------------------------------------
function fuzzy_rendah($nilai,$toleransi,$nilai_puncak){
	if($nilai<=$toleransi){
		$persen_rendah=($toleransi-$nilai)/($toleransi-$nilai_puncak);
	}elseif($nilai<=$nilai_puncak){
		$persen_rendah=1;
	}else{
		$persen_rendah=0;
	}
	return $persen_rendah;
}
//------------------------------------------------------------------
//	defuzzy rendah
//------------------------------------------------------------------
function defuzzy_rendah($nilai,$mid_inferensi=50){
	$defuzzy=$mid_inferensi-($nilai*$mid_inferensi);
	return $defuzzy;
}
//------------------------------------------------------------------
//	defuzzy tinggi
//------------------------------------------------------------------
function defuzzy_tinggi($nilai,$mid_inferensi=50){
	$defuzzy=($nilai*$mid_inferensi)+$mid_inferensi;
	return $defuzzy;
}
//------------------------------------------------------------------
//	defuzzy sedang
//------------------------------------------------------------------
function defuzzy_sedang($nilai,$q1_inferensi=25,$q2_inferensi=75,$mid_inferensi=50){
	$defuzzy1=($nilai*$q1_inferensi)+$mid_inferensi;
	$defuzzy2=$q2_inferensi-($nilai*$q1_inferensi);
	return $defuzzy1;
}
//------------------------------------------------------------------
//	tsukamoto
//------------------------------------------------------------------
function tsukamoto($data1,$data2,$max1,$max2){
	$nilai_median1=$max1/2;
	$nilai_median2=$max2/2;
	//Nilai Tinggi
	$persen_tinggi1=fuzzy_tinggi($data1,$nilai_median1,$max1);
	//Nilai Rendah
	$persen_rendah1=fuzzy_rendah($data1,$nilai_median1,0);
	//Nilai Tinggi
	$persen_tinggi2=fuzzy_tinggi($data2,$nilai_median2,$max2);
	//Nilai Rendah
	$persen_rendah2=fuzzy_rendah($data2,$nilai_median2,0);
	
	$mid_inferensi = 50;
	//R1=Jika Nilai1 rendah dan nilai2 rendah maka hasil rendah
	if($persen_rendah1<$persen_rendah2){
		$R1=$persen_rendah1;
	}else{
		$R1=$persen_rendah2;
	}
	$R1_nilai=defuzzy_rendah($R1,$mid_inferensi);
	//R2=Jika Nilai1 tinggi dan nilai2 tinggi maka hasil rendah
	if($persen_tinggi1<$persen_tinggi2){
		$R2=$persen_tinggi1;
	}else{
		$R2=$persen_tinggi2;
	}
	$R2_nilai=defuzzy_tinggi($R2,$mid_inferensi);
	//R3=Jika Nilai1 rendah dan nilai2 sedang maka hasil rendah
	if($persen_rendah1<$persen_tinggi2){
		$R3=$persen_rendah1;
	}else{
		$R3=$persen_tinggi2;
	}
	$R3_nilai=defuzzy_rendah($R3,$mid_inferensi);
	//R4=Jika Nilai1 tinggi dan nilai2 rendah maka hasil sedang
	if($persen_tinggi1<$persen_rendah2){
		$R4=$persen_tinggi1;
	}else{
		$R4=$persen_rendah2;
	}
	$R4_nilai=defuzzy_tinggi($R4,$mid_inferensi);
	//Nilai Inferensi Tsukamoto san!!
	$jumlah_R=$R1+$R2+$R3+$R4;
	$jumlah_R_nilai=($R1*$R1_nilai)+($R2*$R2_nilai)+($R3*$R3_nilai)+($R4*$R4_nilai);
	$inferensi = 0;
	if($jumlah_R_nilai!=0 && $jumlah_R!=0) $inferensi=$jumlah_R_nilai/$jumlah_R;
	$out = array($persen_tinggi1,$persen_rendah1,$persen_tinggi2,$persen_rendah2,
	$R1_nilai,$R2_nilai,$R3_nilai,$R4_nilai,$inferensi);
	return $out;
}
?>
