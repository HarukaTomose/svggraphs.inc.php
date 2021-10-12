<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// gridmap.inc.php
// オセロ的な方眼紙＋その上に任意アイテムを置くようなプラグイン。
//
// 2017/08/12 H.Tomose

function plugin_gridmap_convert()
{
	return "";
}
function plugin_gridmap_draw($argg, $lib)
{

	$body ="";
	$gr_w = 150;
	$gr_h = 150;
	$gr_sc = 20;

	$gr_xc = 5; // 方眼、列数
	$gr_yc = 5; // 方眼、行数

	$data = array(); //実データ
	$color = array(); // 色
	$precol='lightgrey';

	// 引数処理
	foreach( $argg as $key => $arg){
		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			//$argss = array_map('trim', $argss); 
			switch ($argss[0])
			{
				case 'w': //幅指定
					$gr_w = ctype_digit($argss[1])? $argss[1]: $gr_w;
					break;
				case 'h': //高さ指定
					$gr_h = ctype_digit($argss[1])? $argss[1]: $gr_h;
					break;

				case 'r': //行数
					$gr_yc = ctype_digit($argss[1])? $argss[1]: $gr_yc;
					break;
				case 'c': //桁数
					$gr_xc = ctype_digit($argss[1])? $argss[1]: $gr_xc;
					break;

				case 'data':
					// dataは "name:x,y" という構造。
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

				default:
					// 知らないコマンド。捨てる。
					break;
			
			}
		}
	}

	// 情報がそろったので描画開始。
	$tmpbuf ="";
//	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.($gr_xc*20+50).'" height="'.($gr_yc*20+50).'" viewBox="0 0 '.($gr_xc*20+50).' '.($gr_yc*20+50).'">'."\n";
	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.$gr_w.'" height="'.$gr_h.'" viewBox="0 0 '.($gr_xc*$gr_sc+50).' '.($gr_yc*$gr_sc+50).'">'."\n";


	// Grid 描画。行数・列数から自動作成。
	for($i=0; $i <= $gr_xc; $i++){
		$body .= '<line x1="'.($i*$gr_sc+$gr_sc).'" y1="20" x2="'.($i*$gr_sc+$gr_sc).'" y2="'.($gr_yc*$gr_sc+$gr_sc).'" style="stroke:gray;stroke-width:1" />'."\n";
		if($i==$gr_xc)break;
		$body.= '<text x="'.(($i*$gr_sc)+$gr_sc+5).'" y="10" fill="'.$ccolor.'">'.($i+1).'</text>'."\n";

	}
	for($i=0; $i <= $gr_yc; $i++){
		$body .= '<line x1="'.$gr_sc.'" y1="'.($i*$gr_sc+$gr_sc).'" x2="'.($gr_xc*$gr_sc+$gr_sc).'" y2="'.($i*$gr_sc+$gr_sc).'	" style="stroke:gray;stroke-width:1" />'."\n";
		if($i==$gr_yc)break;
		$body.= '<text x="0" y="'.($i*$gr_sc+$gr_sc+15).'" fill="'.$ccolor.'">'.($i+1).'</text>'."\n";

	}

	// オブジェクト。
	foreach($data as $key => $value){
		//tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // 各行にtrim()をかける
		$tmpx =$tmp[0]*$gr_sc+5;
		$tmpy =$tmp[1]*$gr_sc+15;
		$ccolor= (!$color[$k]=="")? $color[$k]:$lib->getnextcolor($precol);
		$precol=$ccolor;

		$body.= '<text x="'.$tmpx.'" y="'.$tmpy.'" fill="'.$ccolor.'">'.$key.'</text>'."\n";


	}


	$body .= "</svg>";

	return $body;
}
?>