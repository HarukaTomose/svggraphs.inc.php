<?php
// $Id: svggraphs.inc.php,v 0.10 2023/11/4 Haruka Tomose
// svgグラフプラグイン。
// このプラグインは複数プラグインによる階層的な構造にする。
// svggraph はその大元プラグイン。
// 引数の導入と、各種ライブラリだけを持つ。

function plugin_svggraphs_convert()
{
	global $vars;
	static $lib = 0;
	$html = "#svggraps : bad parametor.";

	// クラスインスタンスは1回のコールで1つしか作らないようにする。
	// 複数のグラフを同一ページで表示しようとしたとき、
	// マーカー名称をかぶらないものにする処理のため。
	if( $lib==0 ) $lib = new Plugin_svggraphs_lib();
	$lib->initLib();

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

				case 'rader':
					require_once PLUGIN_DIR.'graphradar.inc.php';
					$html =plugin_graphradar_draw($argg, $lib);
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

	static $testprop="dummy";
	// 色を自動選択するための色指定配列。
	static $clist = array(
			'0' => 'black',
			'1' => 'blue',
			'2' => 'red',
			'3' => 'green',
			'4' => 'purple',
			'5' => 'skyblue',
			'6' => 'yellow',
			'7' => 'brown',
			'8' => 'darkblue',
			'10' => 'lightgrey',
			'11' => 'lightsteelblue',
			'12' => 'salmon',
			'13' => 'lightgreen',
			'14' => 'violet',
			'15' => 'lightcyan',
			'16' => 'lightyellow',
			'17' => 'crimson', 
			
		);

	// マーカーの名称を重複しないようにするための変数
	private $mcount;

	function initLib()
	{
		//$testprop="test";
		$mcount=0;
	}

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

						// ファイル読み込み指定。
						$fd= file(DATA_DIR.encode($prma[1]).".txt");
						$insertpos =0;
						foreach($fd as $line ){
							// pukiwiki1.5 の #auther を捨てる処理。
							if( substr($line,0,1)=="#") continue;
							// 改行とっぱらって配列の先頭側に挿入追加。
							// データの順に意味があるので、順番が狂わないように挿入位置を指定して追加する。
							$line = trim(str_replace(array("\r\n","\r","\n"), "", $line));
							//if( !$line=="") array_splice($ppp,$line);
							if( !$line=="") array_push($ppp,$insertpos,0,$line);
							$insertpos +=1;
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

	function getnextcolor( $color ,$defaultcolor="blue")
	{
		// 色を自動的に選ぶためのメソッド。現在の色をもとに「次」を選ぶ。

		$rslt = array_search( $color, Plugin_svggraphs_lib::$clist);
		if(! $rslt) {
			$rslt = 1;
		}else{
			$rslt= ( count($list)<=$rslt+1)? $rslt+1: 1;
		}
		return Plugin_svggraphs_lib::$clist[$rslt];
	}

	function correctColor( $color ,$defaultcolor="black"){
		// 色指定に使われた文字列が妥当であるかを判定処理する。
		// いわゆるインジェクション攻撃系のために、
		// 「名前指定」または「#xxxxxx」形式でなければデフォルト色を戻す。

		if(preg_match('/(^#[0-9A-Fa-f]{6})/', $color, $m)){
			//いわゆる16進指定。形式さえ合っていればOK。
			return $m[1];

			}
		
		if(preg_match('/(^[A-Za-z]+$)/', $color, $m)){
			//色名称？リストすべてを判定する手段がないので、妥協する。
			return $m[1];
		}

		//16進指定でなくかつ色名で使えない文字を指定しているのでエラー。
		return $defaultcolor;
/*
		$rslt = array_search( $color, Plugin_svggraphs_lib::$clist);
		if(! $rslt) {
			return $defaultcolor;
		}else{
			return Plugin_svggraphs_lib::$clist[$rslt];
		}
		return true;
*/

	}

	// 重複しない「マーカー名称」を戻すための関数
	function GetMarkerID()
	{

		$rslt = "svggraph_marker".$this->mcount;

		$this->mcount +=1;
		return $rslt;
		
	}

	// 凡例作成メソッド。
	// 凡例は複数のグラフで使うので、ライブラリに入れる。
	function CreateLegend( $data, $color, $x, $y ){
		$rslt = "";

		$legendw=0;
		foreach( $data as $k =>$line){
			$legendw= (mb_strwidth($k)>$legendw)?mb_strwidth($k):$legendw;
		}

		$legendh= count($data)*13+6;
		$legendw= 40+$legendw*7;

		$rslt .='<g transform="translate('.$x.','.$y.')" font-size="11">';
		$rslt .= <<<EOD
<rect width="$legendw" height="$legendh" style="fill:white;stroke-width:1;stroke:black" />
EOD;
		$tmp=12;
		$precol=0;


		foreach($data as $key=>$val){
	
			$ccolor= (!$color[$key]=="")? $color[$key]:$this->getnextcolor($precol);
			$precol=$ccolor;
			$rslt .='<polyline points="10,'.$tmp.' 20,'.$tmp.' 30,'.$tmp.'" stroke="'.$ccolor.'" stroke-width="1" marker-mid="url(#m_'.$key.')" />';
			$rslt .='<text x="40" y="'.($tmp+2).'" fill="black">'.htmlsc($key).'</text>'."\n";
			$tmp+=12;

		}

//		tomoseDBG("legend[".$rslt."]");
		$rslt .="</g>";

		return $rslt;

	}

	// タイトル作成メソッド。
	// 凡例は複数のグラフで使うので、ライブラリに入れる。
	function CreateTitle( $text, $tx, $ty , $style ){

		$rslt = "";
		$pipe = false;
		$pipewidth = 3;
		//$fonteffect = 'font-weight="bold"';
		$fill = 'fill="'.$style[0].'" ';
		$fonteffect ='';
		array_shift($style);
		foreach($style as $param){
			switch ($param)
			{
				case 'bold':
					$fonteffect .=' font-weight="bold"';
					$pipewidth = 3;
					break;

				case 'underline':
					$fonteffect .=' text-decoration="underline"';
					break;

				case 'pipe':
					$pipe = true;
					//$fonteffect ='stroke="white" '.$fonteffect;
					break;

				default:
					// 知らない指定は捨てる。
					break;
			}
		}

		if($pipe){
			
			$rslt .='<text x="'.$tx.'" y="'.$ty.'" stroke-width="'.$pipewidth.'" stroke="white" stroke-linecap="round" '.$fonteffect.'>'.$text.'</text>';
		}
		$rslt .='<text x="'.$tx.'" y="'.$ty.'" stroke-width="2" '.$fill.$fonteffect.'>'.$text.'</text>';

			
		return $rslt;

	}


	
}

?>
