<?php

// $Id: graphradar.inc.php,v 0.10 2023/11/4

function plugin_graphradar_convert()
{
	return "#graphradar: You MUST use svggraphs plugin.";

}

function plugin_graphradar_draw($argg, $lib)
{
	global $vars;

	// 描画領域 初期値。
	$cw=100;	$ch=100;		// キャンバスサイズデフォルト
	$offx = 0;	$offy =0;	// キャンバス上のグラフ開始座標。

	//グラフタイトル
	$gtitle = "";	// タイトル文字列
	$tx = round($cw/3); $ty=20;	//タイトル座標デフォルト
	$titlestyle=array(0 => 'black'); // タイトル用のスタイル指定データ

	// グラフ座標軸関連	
	$cx= ($cw+$offx)/2;
	$cy= ($ch+$offy)/2;
	$r = ($cw>$ch)? $ch/2.5 : $cw/2.5;

	// グラフの最高値
	$vmax = 100;

	// 補助線情報
	$scales = array();

	// グラフ項目変数
	$data = array(); //実データ
	$keyname = array(); // 軸の名称
	$keyoffset = array(); //軸表示文字列の位置補正
	$linemarker = array(); //マーカーの指定データ

	$color = array(); // 色
	$fillcolor = array(); // 塗りつぶし色
	

	$datacount = 0; // テスト用パラメータ。将来はdata countとかを使う

	$legend="";
	$legendx =0;	$legendy=0;
	$legendw =0;	$legendh=0;

	// 引数処理
	foreach( $argg as $key => $arg){
		// 明示的なコメント除外。これがないとコメントに"="をかけないので。 
		if( mb_substr($arg,0,2)=="//") continue;
		if( mb_substr($arg,0,1)=="#") continue;

		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			switch ($argss[0])
			{
				case 'w': //幅指定
					$cw = ctype_digit($argss[1])? $argss[1]: $cw;
					break;
				case 'h': //高さ指定
					$ch = ctype_digit($argss[1])? $argss[1]: $ch;
					break;
				case 'offx': //オフセット位置指定
					$offx = is_numeric($argss[1])? $argss[1]: $offx;
					break;
				case 'offy': //オフセット位置指定
					$offy = is_numeric($argss[1])? $argss[1]: $offy;
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
				case 'titlestyle':
					$titlestyle= $lib->trimexplode(',',$argss[1]);
					break;

				case 'keyname':
					// keyは "key1,key2,key3,..." という文字列構造。
					$keyname= $lib->trimexplode(',',$argss[1]);
					break;

				case 'keyoffset':
					// keyoffsetは "name:offx,offy" という文字列構造。
					// まず名前チェック。 
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	$tmpname=htmlsc($datas[0]);

					//オフセットの2値のチェック。
					$datas=$lib->trimexplode(',',$datas[1]);
					$keyoffset[$tmpname]=array();
					$keyoffset[$tmpname][0]=is_numeric($datas[0])? $datas[0]: 0;
					$keyoffset[$tmpname][1]=is_numeric($datas[1])? $datas[1]: 0;
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
					break;

				case 'fillcolor':
					// データ名:色名[,塗りつぶし率」とする。
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	{
						$fillcolor[htmlsc($datas[0])] = $datas[1];
					}
					break;

				case 'marker':
					//$linestyle= $lib->trimexplode(',',$argss[1]);
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	{
						$linemarker[htmlsc($datas[0])] = $datas[1];
					}

					break;


				case 'vmax':
					$vmax = ctype_digit($argss[1])? $argss[1]: $vmax;
					//array_push($noshow_target,htmlsc($argss[1]));

					break;

				case 'scale':
					// scalesは "sc1,sc2,sc3,..." という数値の列。
					$datas= $lib->trimexplode(',',$argss[1]);
					foreach( $datas as $v ){

						$scales[htmlsc($v)]=$v;
					}
					break;

				case 'legend':
					$legend=htmlsc($argss[1]);
					break;

				case 'legendx': //タイトル座標ｘ
					$legendx = is_numeric($argss[1])? $argss[1]: $legendx;
					break;
				case 'legendy': //タイトル座標ｙ
					$legendy = is_numeric($argss[1])? $argss[1]: $legendy;
					break;

				default:
					// 知らないコマンド。捨てる。
					break;
			
			}
		}else{
			// 入力行に = がないケース。
			// 先頭にカンマがある場合、pukiwiki表/cvsによるデータとみなす。
			if( mb_substr($arg,0,1)!=",") continue;

			// この場合、最初の有効要素をキーとみなす。
			$datas=$lib->trimexplode(',',$arg);
			// 最初の1つ目は必ず捨てる。
			$tmp = array_shift($datas);
			$tmp = array_shift($datas);
			if(! $tmp=="") $data[$tmp] = implode(",",$datas);
		}
	}

	// 引数解析が終わった。データ行の数をチェックする。
	if(count($data)<=0) return "#graphradar: No Data";

	// グラフ座標 再計算
	$cx= ($cw+$offx)/2;
	$cy= ($ch+$offy)/2;

	$r = ($cw>$ch)? $ch/2.5 : $cw/2.5;

	// データ構造の解析。
	$dcount =0;
	$dtable = array();

	$datacount = count($keyname);
	foreach($data as $key => $value){
		//////tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // 各行にtrim()をかける
		if(count($tmp)<$datacount){
			return "#graphradar: to few item in one-line.";
		}
		$dcount = (count($tmp)>$dcount)?count($tmp):$dcount;
		$dtable[$key] = $tmp;
	}
	
	// キャンバスサイズを基準に、グラフ領域を決める
	//$eow = $cw-10;	$eoh = $ch-15;
	//$clipx = $eow-$offx; $clipy=$eoh - $offy;

	$html = '';
	$html2 = '';

//<p>Camvas Size($cw,$ch), Drawsize($eow,$eoh)</p>
//<circle cx="$cx" cy="$cy" r="$r" stroke="black" stroke-width="1" />

$html =<<<EOD
<svg xmlns="http://www.w3.org/2000/svg" width="$cw" height="$ch" viewBox="0 0 $cw $ch">
<!--
<circle cx="$cx" cy="$cy" r="$r" stroke="none" stroke-width="1" fill="white"/>
-->
EOD;
	$precol='lightgrey';

	// 軸
	$stroke="";
	for($i=0 ; $i<$datacount; $i++){
		$sangle= $i*360/$datacount;

		$endx= intval($cx + $r * sin($sangle / 180 * pi()));
		$endy= intval($cy - $r * cos($sangle / 180 * pi()));


		$stroke=$stroke." ".$endx.",".$endy;
		$tmpname= $keyname[$i];

		$textx= intval($cx -(8*mb_strlen($tmpname)) + 1.1*$r * sin($sangle / 180 * pi()));

		$texty= intval($cy +6 - 1.1*$r * cos($sangle / 180 * pi()));
		if(array_key_exists(0,$keyoffset[$tmpname] )) $textx+=$keyoffset[$tmpname][0];
		if(array_key_exists(1,$keyoffset[$tmpname] )) $texty+=$keyoffset[$tmpname][1];

$html2 .=<<<EOD
<line x1="$cx " y1="$cy" x2="$endx" y2="$endy" stroke="gray" stroke-width="1"/>
<text x="$textx" y="$texty" fill="black">$tmpname</text>
EOD;

	}

$html .=<<<EOD

<polygon points="$stroke" stroke="black" fill="white" />

EOD;

	$html .= $html2;

	// スケールデータ
	foreach($scales as $keys => $values){
		$yy = intval($cy  - $values*$r/$vmax) ;
		$html .='<text x="'.($cx+5).'" y="'.$yy.'" fill="black">'.$keys.'</text>';

		$stroke ="";
		for($i=0 ; $i<$datacount; $i++){
			$sangle= $i*360/$datacount;
			$endx= intval($cx + $r*($values/$vmax) * sin($sangle / 180 * pi()));
			$endy= intval($cy - $r*($values/$vmax) * cos($sangle / 180 * pi()));

			$stroke=$stroke." ".$endx.",".$endy;
		}
$html .=<<<EOD

<polygon points="$stroke" stroke="LightGray" fill="none" />

EOD;

	}

	// レーダー用のデータ作成
	foreach( $dtable as $lname =>$tline){
		$stroke ="";
		$i=0;
		$lstyle="";

		foreach($tline as $key => $value){

			$sangle= $i*360/$datacount;

			$endx= intval($cx + $r*($value/$vmax) * sin($sangle / 180 * pi()));
			$endy= intval($cy - $r*($value/$vmax) * cos($sangle / 180 * pi()));

			$stroke=$stroke." ".$endx.",".$endy;
			//$lcolor = $color[$key];
			$i=$i+1;

		}
		$lcolor = ($color[$lname])?$color[$lname]:"black";
		$wcolor=$lib->trimexplode(',',($fillcolor[$lname]));
		$wcolor[1]=is_numeric($wcolor[1])?$wcolor[1]:0.2;

		$fcolor = ($wcolor[0])?$wcolor[0]:"none";
		$fopa = $wcolor[1];

		// マーカー	
		$mwork = "";
		if($linemarker[$lname]!=""){
			$mwork = "m_".$lname;
			$mwork2= "";
			$workprm = $lib->trimexplode(',',$linemarker[htmlsc($lname)]);
			$worksize = 9;
			$worksize2=floor($worksize/2);
			foreach($workprm as $ppp){
				$pmode = mb_substr($ppp,0,1);
				$parg = mb_substr($ppp,1);
				$parg =is_numeric($parg)?$parg:9;
				$worksize=$parg;
				$worksize2=floor($worksize/2)+1;

				switch ($pmode)
				{
					case 'c':
						$mwork2=  '<circle cx="'.$worksize2.'" cy="'.$worksize2.'" r="'.($worksize2-1).'" stroke="white" stroke-width="1" fill="'.$lcolor.'"/>';
						break;
					case 'd':
						$mwork2=  '<polygon points="'.$worksize2.',1 '.($worksize).','.($worksize).' 0,'.($worksize).'" stroke="white" stroke-width="1"  fill="'.$lcolor.'"/>';
						break;

					case 'x':
						//$mwork2=  '<line x1="1" y1="1" x2="'.($worksize-1).'" y2="'.($worksize-1).'" stroke="'.$lcolor.'" stroke-width="1"/>'.'<line x1="'.($worksize-1).'" y1="1" x2="1" y2="'.($worksize-1).'" stroke="'.$lcolor.'" stroke-width="1"/>';
						$mwork2=  '<line x1="1" y1="1" x2="'.($worksize-1).'" y2="'.($worksize-1).'" stroke="white" stroke-width="3" stroke-linecap="round" />'.
'<line x1="'.($worksize-1).'" y1="1" x2="1" y2="'.($worksize-1).'" stroke="white" stroke-width="3" stroke-linecap="round" />'.'<line x1="1" y1="1" x2="'.($worksize-1).'" y2="'.($worksize-1).'" stroke="'.$lcolor.'" stroke-width="1"/>'.
'<line x1="'.($worksize-1).'" y1="1" x2="1" y2="'.($worksize-1).'" stroke="'.$lcolor.'" stroke-width="1"/>';


						break;

					default:
					case 's':
						$mwork2=  ' <rect x="1" y="1" width="'.($worksize-1).'" height="'.($worksize-1).'" stroke="white" stroke-width="1" fill="'.$lcolor.'"/>';

						break;

				}

			}

$worksize2=($worksize/2)+1;
$html .= <<<EOD
<marker id="$mwork" markerWidth="$worksize" markerHeight="$worksize" refX="$worksize2" refY="$worksize2">
$mwork2
</marker>
EOD;
			$lstyle .= ' marker-mid="url(#'.$mwork.')"';
			$lstyle2 = ' marker-start="url(#'.$mwork.')"';
			$linestyle[htmlsc($lname)] = $lstyle;


		}


$html .='<polygon points="'.$stroke.'" stroke="'.$lcolor.'" fill="'.$fcolor.'" fill-opacity="'.$fopa.'" '.$lstyle.$lstyle2.' />';


	}

	//-----------------
	// タイトル
	if(! $gtitle==""){
		$html .= $lib->CreateTitle( $gtitle, $tx, $ty , $titlestyle );
	}

	//-----------------
	//凡例。
	if($legend!=""){
		$html .=$lib->CreateLegend( $data, $color, $legendx, $legendy ,$linestyle);
	}	


$html .='</svg>';
	
/*
	// for debug
	$tbltxt="";
	foreach($data as $key => $value){
	
		$tbltxt .= ",".$key.",".$value."\n";
	}
	$html .= convert_html($tbltxt);
*/

	return $html;
}


?>
