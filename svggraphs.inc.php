<?php
// $Id: svggraphs.inc.php,v 0.02 2017/03/14 Haruka Tomose
// svgグラフプラグイン。
// このプラグインは複数プラグインによる階層的な構造にする。
// svggraph はその大元プラグイン。
// 引数の導入と、各種ライブラリだけを持つ。

function plugin_svggraphs_convert()
{
	global $vars;
	$html = "#svggraps : bad parametor.";
	$lib = new Plugin_svggraphs_lib();
	
	$args=func_get_args();

	$argg= $lib->plugin_graphline_parse_arg($args);

	foreach( $argg as $key => $arg){

		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			if( ! $argss[0]=="gtype") continue;
			switch ($argss[1])
			{
				case 'line':
					require_once PLUGIN_DIR.'graphline.inc.php';
					$html =plugin_graphline_draw($argg, $lib);
					break;

				case 'circle':
					require_once PLUGIN_DIR.'graphcircle.inc.php';
					$html =plugin_graphcircle_draw($argg, $lib);
					break;
	
				case 'histgram':
					require_once PLUGIN_DIR.'graphhistgram.inc.php';
					$html =plugin_graphhistgram_draw($argg, $lib);
					break;

				case 'gridmap':
					require_once PLUGIN_DIR.'gridmap.inc.php';
					$html =plugin_gridmap_draw($argg, $lib);
					break;

				case 'meter':
					require_once PLUGIN_DIR.'graphmeter.inc.php';
					$html =plugin_graphmeter_draw($argg, $lib);
					break;
	

				default:
					break;
			}
		}
	}

	return $html;
}


class Plugin_svggraphs_lib
{

	function plugin_graphline_parse_arg($ppp)
	{
		// 引数整理関数
		// マルチラインの引数を「1行1引数」に分解する。
		$targets = array_pop($ppp);
		$target = str_replace(array("\r\n","\r","\n"), "\n", $targets);
		$target = explode("\n",$target);
	
		if( !count($data)>1 ){
			//最後の引数が単独行==引数加工不要なので戻す。
			array_push($ppp,$target);
		}
		else{
			//最後の行が複数行。1行1引数として読み出し＆引数に追加する。
			$target = array_map('trim', $target); // 各行にtrim()をかける
			$target = array_filter($target, 'strlen'); // 文字数が0の行を取り除く
			foreach($target as $line){
				array_push($ppp,$line);
			}
	
		}
	
		// 引数で「ファイル」指定しているケースに備えて読み出し処理。
		foreach($ppp as $prm)
		{
			if(strpos($prm,'=')){
				$prma= explode('=',$prm);
				$prma = array_map('trim', $prma); 
				switch ($prma[0])
				{
					case "file":
						//tomoseDBG("File target:".DATA_DIR.encode($prma[1]).".txt");
						// ファイル読み込み指定。
						$fd= file(DATA_DIR.encode($prma[1]).".txt");
						foreach($fd as $line ){
							// pukiwiki1.5 の #auther を捨てる処理。
							if( substr($line,0,1)=="#") continue;
							// 改行とっぱらって配列の後ろに追加。
							$line = trim(str_replace(array("\r\n","\r","\n"), "", $line));
							if( !$line=="") array_push($ppp,$line);
						}
						
						break;
				}
	
			}
		}
	
		return $ppp;
	}

	function trimexplode( $sep, $arr)
	{
	
		$datas=explode($sep,$arr);
		$datas = array_map('trim', $datas); 

		return $datas;
	}

	function getnextcolor( $color )
	{
		$clist = array(
			'0' => 'black',
			'1' => 'blue',
			'2' => 'red',
			'3' => 'green',
			'4' => 'purple',
			'5' => 'skyblue',
			'6' => 'yellow',
			'10' => 'lightgrey',
			'11' => 'lightsteelblue',
			'12' => 'salmon',
			'13' => 'lightgreen',
			'14' => 'violet',
			'15' => 'lightcyan',
			'16' => 'lightyellow',
			
		);

		$rslt = array_search( $color, $clist);
		if(! $rslt) {
			$rslt = 1;
		}else{
			$rslt= ( count($list)<=$rslt+1)? $rslt+1: 1;
		}
		return $clist[$rslt];
	}

}

?>
