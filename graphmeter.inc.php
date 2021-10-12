<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// graphmeter.inc.php
// svggraphシリーズ：横バーグラフ的なメーター表示プラグイン。
//
// 2021/10/12 H.Tomose

function plugin_graphmeter_convert()
{
	return "";
}
function plugin_graphmeter_draw($argg, $lib)
{

	$body ="";
	$gr_w = 400; //幅デフォルト
	$gr_h = 30;  //高さデフォルト
	$gr_txt = "";

	$gr_offset = 70; // オフセット位置
	$gr_max = 100; // ゲージ最大値

	$data = 0; //実データ
	$color = array(); // 色
	$precol='lightgrey';
	$color[0]="lightgrey";

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

				case 'offset': //オフセット
					$gr_offset = ctype_digit($argss[1])? $argss[1]: $gr_offset;
					break;
				case 'data': //データ値
					$data = ctype_digit($argss[1])? $argss[1]: $data;
					break;
				case 'max': //データ値
					$gr_max = ctype_digit($argss[1])? $argss[1]: $gr_max;
					break;

				case 'text':
					// メータ前の文字列
					$gr_txt=$argss[1];
					break;

				case 'color':
					if(! $argss[1]=="")	{
						//array_push($data,$argss[1]);
						$color[0] = $argss[1];
					}
					////////tomoseDBG("push: key[".$datas[0]."][".$datas[1]."]");
					break;

				default:
					// 知らないコマンド。捨てる。
					break;
			
			}
		}
	}

	//パラメータから描画位置の決定

	// 情報がそろったので描画開始。
	$tmpbuf ="";
//	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.($gr_xc*20+50).'" height="'.($gr_yc*20+50).'" viewBox="0 0 '.($gr_xc*20+50).' '.($gr_yc*20+50).'">'."\n";
	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.$gr_w.'" height="'.$gr_h.'" viewBox="0 0 '.gr_w.' '.$gr_h.'">'."\n";
	
	$areawidth = $gr_w-$gr_offset -40;
	$datawidth= $areawidth * ($data/$gr_max);
	$tmpx = $areawidth+$gr_offset+10;
	$dataheight = $gr_h*1/10;


	$gr_offset +=10;
//	$body.= '<text x="50" y="25" fill="'.$ccolor.'">width'.$gr_w.'_data:'.$data.'_max'.$gr_max.'</text>'."\n";

$body.=<<<EOD
<rect x="$gr_offset" y="0" width="$gr_w" height="$gr_h" style="fill:white;" />
<rect x="$gr_offset" y="$dataheight" width="$datawidth" height="$gr_h" style="fill:$color[0];" />
<line x1="$gr_offset" y1="$gr_h" x2="$tmpx" y2="$gr_h" style="stroke:black;stroke-width:3" />
EOD;

	for ($i = 0; $i <= 10; $i++) {
		$tmpx = $gr_offset+$areawidth * $i / 10;
		$tmph= $gr_h*7/10;
		if($i==0 ||$i==10) $tmph =0;
		if($i==5) $tmph =$gr_h*5/10;

$body.=<<<EOD
<line x1="$tmpx" y1="$tmph" x2="$tmpx" y2="$gr_h" style="stroke:black;stroke-width:1" />
EOD;

	$body.= '<text x="'.($tmpx+1).'" y="'.($gr_h-2).'" fill="'.$ccolor.'" font-size=10>'.($i*10).'</text>'."\n";


	}
	// オブジェクト。

	$body.= '<text x="0" y="15" fill="'.$ccolor.'">'.$gr_txt.'</text>'."\n";



	$body .= "</svg>";

	return $body;
}
?>