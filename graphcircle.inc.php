<?php

// $Id: graphcircle.inc.php,v 0.01 2017/03/18

function plugin_graphcircle_convert()
{
	return "#graphcircle: You MUST use svggraphs plugin.";

}

function plugin_graphcircle_draw($argg, $lib)
{
	global $vars;

	// 描画領域 初期値。
	$cw=100;	$ch=100;		// キャンバスサイズデフォルト
	$offx = 20;	$offy =10;	// キャンバス上のグラフ開始座標。

	//グラフタイトル
	$gtitle = "";	// タイトル文字列
	$tx = round($cw/3); $ty=20;	//タイトル座標デフォルト

	// グラフ座標軸関連	
	$cx= ($cw-$offx)/2;
	$cy= ($ch-$offy)/2;

	$r = ($cw>$ch)? $ch/3 : $cw/3;

	// グラフ項目変数
	$data = array(); //実データ
	$color = array(); // 色

	$noshow_target = "";
	$noshow = FALSE;

	// 引数処理
	foreach( $argg as $key => $arg){
		////////tomoseDBG("arg[".$key."][".$arg."]");

		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			//$argss = array_map('trim', $argss); 
			switch ($argss[0])
			{
				case 'w': //幅指定
					$cw = ctype_digit($argss[1])? $argss[1]: $cw;
					break;
				case 'h': //高さ指定
					$ch = ctype_digit($argss[1])? $argss[1]: $ch;
					break;
				case 'offx': //オフセット位置指定
					$offx = ctype_digit($argss[1])? $argss[1]: $offx;
					break;
				case 'offy': //オフセット位置指定
					$offy = ctype_digit($argss[1])? $argss[1]: $offy;
					break;

				case 'tx': //タイトル座標ｘ
					$tx = ctype_digit($argss[1])? $argss[1]: $tx;
					break;
				case 'ty': //タイトル座標ｙ
					$ty = ctype_digit($argss[1])? $argss[1]: $ty;
					break;
				case 'title':
					$gtitle=htmlsc($argss[1]);
					break;

				case 'data':
					// dataは "name:1,2,3,..." という構造。
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	{
						$data[htmlsc($datas[0])] = $datas[1];
					}
					break;
				case 'color':
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	{
						//array_push($data,$argss[1]);
						$color[htmlsc($datas[0])] = $datas[1];
					}
					////////tomoseDBG("push: key[".$datas[0]."][".$datas[1]."]");
					break;

				case 'noshow':
					$noshow_target = htmlsc($argss[1]);
					break;

				default:
					// 知らないコマンド。捨てる。
					break;
			
			}
		}else{
			// 入力行に = がないケース。
			// pukiwiki表/cvsを想定して、データとみなす。
			// この場合、最初の有効要素をキーとみなす。
			$datas=$lib->trimexplode(',',$arg);
			// 最初の1つ目は必ず捨てる。
			$tmp = array_shift($datas);
			$tmp = array_shift($datas);
			if(! $tmp=="") $data[$tmp] = implode(",",$datas);

		}
	}

	// 引数解析が終わった。データ行の数をチェックする。
	if(count($data)<=0) return "#graphcircle: No Data";

	// グラフ座標 再計算
	$cx= ($cw-$offx)/2;
	$cy= ($ch-$offy)/2;

	$r = ($cw>$ch)? $ch/3 : $cw/3;

	// データ構造の解析。
	$dcount =0;
	$dtable = array();

	foreach($data as $key => $value){
		//////tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // 各行にtrim()をかける
		$dtable[$key] = $tmp[0];
	}

	// 比率を決めるために、検証処理。
	$tval = 0;
	$dcount=0;
	foreach($dtable as $key => $value){
		$tvalue =$value+$tvalue;
//		////tomoseDBG("curent item[".$value."],total[".$tvalue."]");
		$dcount++;
	}



	if($dcount<1) return "#graphcircle: to few item in one-line.";

	// キャンバスサイズを基準に、グラフ領域を決める
	//$eow = $cw-10;	$eoh = $ch-15;
	//$clipx = $eow-$offx; $clipy=$eoh - $offy;

	$html = '';
//<p>Camvas Size($cw,$ch), Drawsize($eow,$eoh)</p>
//<circle cx="$cx" cy="$cy" r="$r" stroke="black" stroke-width="1" />

$html =<<<EOD
<svg xmlns="http://www.w3.org/2000/svg" width="$cw" height="$ch" viewBox="0 0 $cw $ch">
<circle cx="$cx" cy="$cy" r="$r" stroke="black" stroke-width="2" />

EOD;
	$ctotal=0;
	$precol='lightgrey';
	foreach($dtable as $key => $value){
		$sangle= 360*$ctotal/$tvalue;
		$startx= intval($cx + $r * sin($sangle / 180 * pi()));
		$starty= intval($cy - $r * cos($sangle / 180 * pi()));

		$eangle= 360*($ctotal+$value)/$tvalue;
		$endx= intval($cx + $r * sin($eangle / 180 * pi()));
		$endy= intval($cy - $r * cos($eangle / 180 * pi()));
		$mode = (($eangle-$sangle)>180)?1:0;

		$ccolor= (!$color[$k]=="")? $color[$k]:$lib->getnextcolor($precol);
		$precol=$ccolor;

		$tmp='<path d="M'.$cx.' '.$cy.' L'.$startx.' '.$startY.' L'.$endx.' '.$endy.' Z" />';
			////tomoseDBG("|point item[".$tmp."]");
		

$html .=<<<EOD
<path d="M$cx $cy L$startx $starty A$r $r 0 $mode 1 $endx $endy Z" stroke="gray" stroke-width="1" fill="$ccolor" />

EOD;
		$ctotal+=$value;

	}

	$ctotal=0;

	foreach($dtable as $key => $value){
		if( $noshow_target==$key ) $noshow=TRUE;
		if( $noshow ) continue;

		$tangle= 360*($ctotal+$value/2)/$tvalue;
		$txx= intval($cx + $r/2 * sin($tangle / 180 * pi()) -10);
		$txy= intval($cy - $r/2 * cos($tangle / 180 * pi()));
		if( $tangle<45 || $tangle>315 ){	$txy-=20;	}
		if( $tangle>=45 && $tangle<135 ){	$txx+=10;	}
		if( $tangle>=135 && $tangle<225 ){	$txy+=20;	}
		if( $tangle>=225 && $tangle<315 ){	$txx-=10;	}


		$tmptxt1 = $key;
		$tmptxt2 = round((($value/$tvalue)*100),2)."%";
		$html .= '<text x="'.$txx.'" y="'.($txy-6).'" fill="black" font-size="11">'.$tmptxt1.'</text>';
		$html .= '<text x="'.$txx.'" y="'.($txy+5).'" fill="black" font-size="11">'.$tmptxt2.'</text>';
		$ctotal+=$value;

	}



	// タイトル

	if(! $gtitle==""){
		$html .='<text x="'.$tx.'" y="'.$ty.'" fill="black">'.$gtitle.'</text>';
	}	




$html .='</svg>';
	
	$tbltxt="";
	foreach($data as $key => $value){
	
		$tbltxt .= ",".$key.",".$value."\n";
	}
//	$html .= convert_html($tbltxt);

	return $html;
}


?>
