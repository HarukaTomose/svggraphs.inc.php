<?php
// $Id: graphline.inc.php,v 0.05 2023/10/19 Haruka Tomose

function plugin_graphline_convert()
{
	return "#graphline: You MUST use svggraphs plugin.";

}

function plugin_graphline_draw($argg, $lib)
{
	global $vars;

	// 描画領域 初期値。
	$cw=200;	$ch=50;		// キャンバスサイズデフォルト
	$offx = 20;	$offy =10;	// キャンバス上のグラフ開始座標。

	//グラフタイトル
	$gtitle = "";	// タイトル文字列
	$tx = round($cw/3); $ty=20;	//タイトル座標デフォルト

	// グラフ座標軸関連
	$miny=0;	$maxy=0;	// y軸の値域
	$minx=0;	$maxx=0;	// x軸の値域
	$miny_auto=TRUE;	$maxy_auto=TRUE;	// 自動判定フラグ
	$minx_auto=TRUE;	$maxx_auto=TRUE;	
	// 座標メモリ線
	$scalex = "";
	$sclistx = array();
	$scaley = "";
	$sclisty = array();

	$legend="";
	$legendx =0;	$legendy=0;
	$legendw =0;	$legendh=0;
	

	// 個々の折れ線用変数
	$data = array(); //実データ
	$color = array(); // 色



	// 引数処理
	foreach( $argg as $key => $arg){
		////tomoseDBG("arg[".$key."][".$arg."]");

		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			//$argss = array_map('trim', $argss); 
			switch ($argss[0])
			{
				case 'w': //幅指定
					$cw = is_numeric($argss[1])? $argss[1]: $cw;
					break;
				case 'h': //高さ指定
					$ch = is_numeric($argss[1])? $argss[1]: $ch;
					break;
				case 'offx': //オフセット位置指定
					$offx = is_numeric($argss[1])? $argss[1]: $offx;
					break;
				case 'offy': //オフセット位置指定
					$offy = is_numeric($argss[1])? $argss[1]: $offy;
					break;

				case 'minx': //グラフｘ軸最小値
					if( is_numeric($argss[1]) ){
						$minx = $argss[1];	$minx_auto=FALSE;
					}
					break;
				case 'maxx': //グラフｘ軸最大値
					if( is_numeric($argss[1]) ){
						$maxx = $argss[1];	$maxx_auto=FALSE;
					}
					break;
				case 'miny': //グラフｙ軸最小値
					if( is_numeric($argss[1]) ){
						$miny = $argss[1];	$miny_auto=FALSE;
					}
					break;
				case 'maxy': //グラフｙ軸最大値
					if( is_numeric($argss[1]) ){
						$maxy = $argss[1];	$maxy_auto=FALSE;
					}
					break;

				case 'tx': //タイトル座標ｘ
					$tx = is_numeric($argss[1])? $argss[1]: $tx;
					break;
				case 'ty': //タイトル座標ｙ
					$ty = is_numeric($argss[1])? $argss[1]: $ty;
					break;
				case 'title':
					$gtitle=htmlsc($argss[1]);
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

				case 'sxauto': // ｙ軸メモリの自動採番指定。
					// step[[,start],end] 形式の書式。
					// ここではその体裁のみ整理する。
					$tmp=$lib->trimexplode(',',($argss[1]).",,");
					$scalex= $tmp[0].",".$tmp[1].",".$tmp[2];
					break;
				case 'sx': // ｙ軸目盛、直接指定。
					if( is_numeric($argss[1]) ) array_push($sclistx,$argss[1]);
					break;

				case 'syauto': // ｙ軸メモリの自動採番指定。
					// step[[,start],end] 形式の書式。
					// ここではその体裁のみ整理する。
					$tmp=$lib->trimexplode(',',($argss[1]).",,");
					$scaley= $tmp[0].",".$tmp[1].",".$tmp[2];
					break;
				case 'sy': // ｙ軸目盛、直接指定。
					if( is_numeric($argss[1]) ) array_push($sclisty,$argss[1]);
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
					////tomoseDBG("push: key[".$datas[0]."][".$datas[1]."]");
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
	if(count($data)<=1) return "#graphline: No Data";


	// データ構造の解析。
	$dcount =0;
	$dtable = array();

	// 最初の行はｘ軸目盛になるので、取り出す。
	$tmp = array_shift( $data );
	$xtable = $lib->trimexplode(",",$tmp);
	//tomoseDBG("xtable:".implode(",",$xtable));
	// ｘ軸の最大・最小の確保
	if( $maxx_auto ) $maxx= end($xtable);
	if( $minx_auto ) $minx= reset($xtable);

	// それ以降はデータキー。
	foreach( $data as $key => $value){
		//tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // 各行にtrim()をかける
		$dcount = (count($tmp)>$dcount)?count($tmp):$dcount;
		$dtable[$key] = $tmp;
	}

	if( $maxy_auto ) $maxy = reset(reset($dtable));
	if( $miny_auto ) $miny = reset(reset($dtable));
	foreach( $dtable as $tline){
		foreach($tline as $value){
			if( $maxy_auto && $value>$maxy) $maxy=$value;
			if( $miny_auto && $value<$miny) $miny=$value;
		}
	}

	if($dcount<3) return "#graphline: to few item in one-line.";

	// キャンバスサイズを基準に、グラフ領域を決める
	$eow = $cw-10;	$eoh = $ch-15;
	$clipx = $eow-$offx; $clipy=$eoh - $offy;

	$html = '';
//<p>Camvas Size($cw,$ch), Drawsize($eow,$eoh)</p>

$html =<<<EOD

<svg xmlns="http://www.w3.org/2000/svg" width="$cw" height="$ch" viewBox="0 0 $cw $ch">

<line x1="$offx" y1="$offy" x2="$offx" y2="$eoh" style="stroke:rgb(0,0,0);stroke-width:2" />
<line x1="$offx" y1="$eoh" x2="$eow" y2="$eoh" style="stroke:rgb(0,0,0);stroke-width:2" />
<clipPath id="cliparea"><rect x="$offx" y="$offy" width="$clipx" height="$clipy" />
</clipPath>

EOD;
//<rect x="$offx" y="$offy" width="$eow" height="$eoh" />
//<rect x="20" y="10" width="490" height="235" />

	// y軸めもり
	// step[[,start],end] 形式の書式で$scaleyに入っている。これを配列に変換。
	if(! $scaley==""){
		$tmp=$lib->trimexplode(',',($scaley).",,");
		$tmp[0]=is_numeric($tmp[0])? $tmp[0]: round($maxy/2);
		$tmp[1]=is_numeric($tmp[1])? $tmp[1]: $miny;
		$tmp[2]=is_numeric($tmp[2])? $tmp[2]: $maxy;
	
		$sc =$tmp[1];
		do{
			array_push($sclisty,$sc);
			$sc+=$tmp[0];
		}while($sc<=$tmp[2]);
	}

	foreach($sclisty as $value){
		$ypos = intval($ch-15-(($value)*($ch-15-$offy)/($maxy-$miny)));

$html .=<<<EOD
<line x1="$offx" y1="$ypos" x2="$eow" y2="$ypos" stroke-dasharray="10,10" style="stroke:gray;stroke-width:1" />

EOD;
$html .='<text x="0" y="'.$ypos.'" fill="black">'.($value+$miny).'</text>';

	}
	// x軸めもり

	// y軸めもり
	// step[[,start],end] 形式の書式で$scaleyに入っている。これを配列に変換。
	if(! $scalex==""){
		$tmp=$lib->trimexplode(',',($scalex).",,");
		$tmp[0]=is_numeric($tmp[0])? $tmp[0]: round($maxx/2);
		$tmp[1]=is_numeric($tmp[1])? $tmp[1]: $minx;
		$tmp[2]=is_numeric($tmp[2])? $tmp[2]: $maxx;
	
		$sc =$tmp[1];
		do{
			array_push($sclistx,$sc);
			$sc+=$tmp[0];
		}while($sc<=$tmp[2]);
	}

	
	foreach($sclistx as $value){
		$xpos = intval($offx+(($value-$minx)*($cw-10-$offx)/($maxx-$minx)));

$html .=<<<EOD

<line x1="$xpos" y1="$offy" x2="$xpos" y2="$eoh" stroke-dasharray="10,10" style="stroke:gray;stroke-width:1" />

EOD;
$html .='<text x="'.$xpos.'" y="'.$ch.'" fill="black">'.($value).'</text>';

	}


	if(! $gtitle==""){
		$html .='<text x="'.$tx.'" y="'.$ty.'" fill="black">'.$gtitle.'</text>'."\n";
	}	

//<clipPath id="cliparea">
//<rect x="$minx" y="$miny" width="$maxx" height="$maxy" />
//</clipPath>


	$precol =0;

	// 各データごとの折れ線処理
	foreach( $dtable as $k =>$line){
		$legendw= (strlen($k)>$legendw)?strlen($k):$legendw;
		//tomoseDBG("start:".$k." len[".strlen($k)."]");

		$xwork = $offx;
		$html .='<polyline points="';

		foreach( $line as $key => $value){
			$xpos = $offx+(($xtable[$key]-$minx)*($cw-10-$offx)/($maxx-$minx));

			//tomoseDBG("item:".$key.", pos(".$xpos.",".$value.")");
			// まずデータ確認。数値でない可能性もあるため。
			// 数値でなければ、ここは捨てる。
			if (! is_numeric($value) ) {
				continue;
			}
			//ここでデータのコンバート。
			// yの座標範囲は 「最小値」$ch-10。「最大値」$offy。
			// ただし座標系的にマイナ方向なので置き換える必要がある。
			// yの値域は、最小 $miny,最大$maxy。これをコンバートしないとならない。
			// 倍率変換がこんな感じ。($ch-10-$offy)/($maxy-$miny)

			$ypos = intval($ch-15-(($value-$miny)*($ch-15-$offy)/($maxy-$miny)));
			$xpos = intval($offx+(($xtable[$key]-$minx)*($cw-10-$offx)/($maxx-$minx)));
			$html .=<<<EOD
$xpos,$ypos 
EOD;

		}
		// 線の色指定。
		$ccolor= (!$color[$k]=="")? $color[$k]:$lib->getnextcolor($precol);
		$precol=$ccolor;
//if(!$color[$k]=="") $ccolor=$color[$k];

$html .='" clip-path="url(#cliparea)" style="fill:none;stroke:'.$ccolor.';stroke-width:2" />';
$html .="\n";
	}


	//凡例
	$legendh= count($data)*14;
	$legendw= 50+$legendw*7;

	if(!$legend==""){

	$html .='<g transform="translate('.$legendx.','.$legendy.')" font-size="11">';
$html .= <<<EOD
	<rect width="$legendw" height="$legendh" style="fill:white;stroke-width:1;stroke:black" />
EOD;
	$tmp=12;
	$precol=0;
	foreach($data as $key=>$val){
	
		$ccolor= (!$color[$k]=="")? $color[$k]:$lib->getnextcolor($precol);
		$precol=$ccolor;
$html .=<<<EOD
<line x1="10" y1="$tmp" x2="30" y2="$tmp" style="stroke:$ccolor;stroke-width:1" />

EOD;
		$html .='<text x="40" y="'.($tmp+2).'" fill="black">'.htmlsc($key).'</text>'."\n";
		$tmp+=12;
	}
	$html .="</g>";
	}	


$html .='</svg>';

//$html .= <<<EOD
//<svg xmlns="http://www.w3.org/2000/svg" width="$cw" height="$ch" viewBox="0 0 $cw $ch">
//
//<polyline points="5,10 74,40" style="fill:none;stroke:rgb(0,0,255);stroke-width:2" />
//<polyline points="5,20 28,30 51,20 74,10" style="fill:none;stroke:rgb(0,255,0);stroke-width:2" />
//<polyline points="5,10 28,50 51,80 74,90" style="fill:none;stroke:rgb(255,0,0);stroke-width:2" />
//</svg>
//EOD;

	return $html;
}

?>
