<?php
// $Id: graphline.inc.php,v 0.08 2023/11/2 Haruka Tomose

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
	$titlestyle=array(0 => 'black'); // タイトル用のスタイル指定データ

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
	$linestyle=array(); // 線のスタイル指定データ
	$linemarker = array(); //マーカーの指定データ


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

				case 'titlestyle':
					$titlestyle= $lib->trimexplode(',',$argss[1]);
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

				case 'linestyle':
					//$linestyle= $lib->trimexplode(',',$argss[1]);
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	{
						$linestyle[htmlsc($datas[0])] = $datas[1];
					}
					break;
				case 'marker':
					//$linestyle= $lib->trimexplode(',',$argss[1]);
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	{
						$linemarker[htmlsc($datas[0])] = $datas[1];
					}
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
			// カンマ区切りなのでデータ構造は次の通り
			// (null),data名,d1,d2,...
			$tmp = array_shift($datas); // 最初のnullは捨てる
			$tmp = array_shift($datas); //データ名を確保
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

<line x1="$offx" y1="$offy" x2="$offx" y2="$eoh" style="stroke:rgb(0,0,0);stroke-width:1" />
<line x1="$offx" y1="$eoh" x2="$eow" y2="$eoh" style="stroke:rgb(0,0,0);stroke-width:1" />
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


	//-----------------
	// タイトル
	if(! $gtitle==""){
		//$fonteffect = 'font-weight="bold"';
		$fonteffect =' fill="'.$titlestyle[0].'"';
		array_shift($titlestyle);
		foreach($titlestyle as $param){
			switch ($param)
			{
				case 'bold':
					$fonteffect .=' font-weight="bold"';
					break;

				case 'underline':
					$fonteffect .=' text-decoration="underline"';
					break;

				default:
					// 知らない指定は捨てる。
					break;
			}
		}
		$html .='<text x="'.$tx.'" y="'.$ty.'"'.$fonteffect.'>'.$gtitle.'</text>';
	}

//<clipPath id="cliparea">
//<rect x="$minx" y="$miny" width="$maxx" height="$maxy" />
//</clipPath>


	$precol =0;

	//--------------
	// 各データごとの折れ線処理
	foreach( $dtable as $k =>$line){
		//$legendw= (strlen($k)>$legendw)?strlen($k):$legendw;
		$legendw= (mb_strwidth($k)>$legendw)?mb_strwidth($k):$legendw;

		$xwork = $offx;
		// 線の色指定。
		$ccolor= (!$color[$k]=="")? $color[$k]:$lib->getnextcolor($precol);
		$precol=$ccolor;

		// マーカーを置く場合の定義
		$mwork = "";
		if($linemarker[$k]!=""){
			$mwork = "m_".$k;
			$mwork2= "";
			$workprm = $lib->trimexplode(',',$linemarker[htmlsc($k)]);
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
						$mwork2=  '<circle cx="'.$worksize2.'" cy="'.$worksize2.'" r="'.($worksize2-1).'" stroke="none" stroke-width="1" fill="'.$ccolor.'"/>';
						break;
					case 'd':
						$mwork2=  '<polygon points="'.$worksize2.',1 '.($worksize).','.($worksize).' 0,'.($worksize).'" stroke="none" stroke-width="1"  fill="'.$ccolor.'"/>';
						break;

					case 'x':
						$mwork2=  '<line x1="1" y1="1" x2="'.($worksize-1).'" y2="'.($worksize-1).'" stroke="'.$ccolor.'" stroke-width="1"/>'.
'<line x1="'.($worksize-1).'" y1="1" x2="1" y2="'.($worksize-1).'" stroke="'.$ccolor.'" stroke-width="1"/>';

						break;

					default:
					case 's':
						$mwork2=  ' <rect x="1" y="1" width="'.($worksize-1).'" height="'.($worksize-1).'" stroke="none" stroke-width="1" fill="'.$ccolor.'"/>';

						break;

				}

			}

$worksize2=floor($worksize/2)+1;

$html .= <<<EOD
<marker id="$mwork" markerWidth="$worksize" markerHeight="$worksize" refX="$worksize2" refY="$worksize2">
$mwork2
</marker>
EOD;

		}
/*
$html .=<<<EOD
  <marker id="marker1" markerWidth="5" markerHeight="5" refX="2" refY="2">
    <circle cx="1" cy="1" r="2" stroke="none" fill="#f00"/>
  </marker>
EOD;
*/
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
//if(!$color[$k]=="") $ccolor=$color[$k];

		// 線のスタイル指定
//		$lstyle = 'stroke-dasharray="5"';
		$lstyle = '';
		$swidth = '1';
		$stroke = '';
		if( $linestyle[htmlsc($k)]!=""){
			$workprm = $lib->trimexplode(',',$linestyle[htmlsc($k)]);
			foreach($workprm as $ppp){
				$pmode = mb_substr($ppp,0,1);
				$parg = mb_substr($ppp,1);
				switch ($pmode)
				{
					case 's':
						$parg = is_numeric($parg)?$parg:5;
						$stroke .= ($stroke=="")?$parg :','.$parg ;
						//$lstyle .= ' stroke-dasharray="'.$parg .'"';
						break;
/*
					case 'm':
						$parg = is_numeric($parg)?$parg:5;
						$lstyle .= ' marker-mid="url(#marker1)"';
						break;
*/
					case 'w':
						$swidth = is_numeric($parg)?$parg:1;
						break;

					default:
						// 知らない指定は捨てる。
						break;
				}
			
			}

		}
		$lstyle .= ($stroke!="")?' stroke-dasharray="'.$stroke .'"':'';

		if($mwork!=""){
			$lstyle .= ' marker-mid="url(#'.$mwork.')"';
		}

$html .='" clip-path="url(#cliparea)" style="fill:none;stroke:'.$ccolor.';stroke-width:'.$swidth.'" '.$lstyle.'/>';
$html .="\n";
	}


	//凡例
	$legendh= count($data)*13+6;
	$legendw= 40+$legendw*7;

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
		$html .='<polyline points="10,'.$tmp.' 20,'.$tmp.' 30,'.$tmp.'" stroke="'.$ccolor.'" stroke-width="1" marker-mid="url(#m_'.$key.')" />';
/*
$html .=<<<EOD
<line x1="10" y1="$tmp" x2="30" y2="$tmp" style="stroke:$ccolor;stroke-width:1" />

EOD;
*/
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
