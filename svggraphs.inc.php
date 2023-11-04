<?php
// $Id: svggraphs.inc.php,v 0.10 2023/11/4 Haruka Tomose
// svg����եץ饰����
// ���Υץ饰�����ʣ���ץ饰����ˤ�볬��Ū�ʹ�¤�ˤ��롣
// svggraph �Ϥ����縵�ץ饰����
// ������Ƴ���ȡ��Ƽ�饤�֥���������ġ�

function plugin_svggraphs_convert()
{
	global $vars;
	static $lib = 0;
	$html = "#svggraps : bad parametor.";

	// ���饹���󥹥��󥹤�1��Υ������1�Ĥ������ʤ��褦�ˤ��롣
	// ʣ���Υ���դ�Ʊ��ڡ�����ɽ�����褦�Ȥ����Ȥ���
	// �ޡ�����̾�Τ򤫤֤�ʤ���Τˤ�������Τ��ᡣ
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
	// ����ư���򤹤뤿��ο���������
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

	// �ޡ�������̾�Τ��ʣ���ʤ��褦�ˤ��뤿����ѿ�
	private $mcount;

	function initLib()
	{
		//$testprop="test";
		$mcount=0;
	}

	function plugin_graphline_parse_arg($ppp)
	{
		// ���������ؿ�
		// �ޥ���饤��ΰ������1��1�����פ�ʬ�򤹤롣
		$targets = array_pop($ppp);
		$target = str_replace(array("\r\n","\r","\n"), "\n", $targets);
		$target = explode("\n",$target);
	
		if( !count($data)>1 ){
			//�Ǹ�ΰ�����ñ�ȹ�==�����ù����פʤΤ��᤹��
			array_push($ppp,$target);
		}
		else{
			//�Ǹ�ιԤ�ʣ���ԡ�1��1�����Ȥ����ɤ߽Ф����������ɲä��롣
			$target = array_map('trim', $target); // �ƹԤ�trim()�򤫤���
			$target = array_filter($target, 'strlen'); // ʸ������0�ιԤ������
			foreach($target as $line){
				array_push($ppp,$line);
			}
	
		}
	
		// �����ǡ֥ե�����׻��ꤷ�Ƥ��륱�������������ɤ߽Ф�������
		foreach($ppp as $prm)
		{
			if(strpos($prm,'=')){
				$prma= explode('=',$prm);
				$prma = array_map('trim', $prma); 
				switch ($prma[0])
				{
					case "file":

						// �ե������ɤ߹��߻��ꡣ
						$fd= file(DATA_DIR.encode($prma[1]).".txt");
						$insertpos =0;
						foreach($fd as $line ){
							// pukiwiki1.5 �� #auther ��ΤƤ������
							if( substr($line,0,1)=="#") continue;
							// ���ԤȤäѤ�ä��������Ƭ¦�������ɲá�
							// �ǡ����ν�˰�̣������Τǡ����֤�����ʤ��褦���������֤���ꤷ���ɲä��롣
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
		// ����ưŪ�����֤���Υ᥽�åɡ����ߤο����Ȥˡּ��פ����֡�

		$rslt = array_search( $color, Plugin_svggraphs_lib::$clist);
		if(! $rslt) {
			$rslt = 1;
		}else{
			$rslt= ( count($list)<=$rslt+1)? $rslt+1: 1;
		}
		return Plugin_svggraphs_lib::$clist[$rslt];
	}

	function correctColor( $color ,$defaultcolor="black"){
		// ������˻Ȥ�줿ʸ���������Ǥ��뤫��Ƚ��������롣
		// �����륤�󥸥�������󹶷�ϤΤ���ˡ�
		// ��̾������פޤ��ϡ�#xxxxxx�׷����Ǥʤ���Хǥե���ȿ����᤹��

		if(preg_match('/(^#[0-9A-Fa-f]{6})/', $color, $m)){
			//������16�ʻ��ꡣ����������äƤ����OK��
			return $m[1];

			}
		
		if(preg_match('/(^[A-Za-z]+$)/', $color, $m)){
			//��̾�Ρ��ꥹ�Ȥ��٤Ƥ�Ƚ�ꤹ����ʤ��ʤ��Τǡ��Ŷ����롣
			return $m[1];
		}

		//16�ʻ���Ǥʤ����Ŀ�̾�ǻȤ��ʤ�ʸ������ꤷ�Ƥ���Τǥ��顼��
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

	// ��ʣ���ʤ��֥ޡ�����̾�Ρפ��᤹����δؿ�
	function GetMarkerID()
	{

		$rslt = "svggraph_marker".$this->mcount;

		$this->mcount +=1;
		return $rslt;
		
	}

	// ��������᥽�åɡ�
	// �����ʣ���Υ���դǻȤ��Τǡ��饤�֥�������롣
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

	// �����ȥ�����᥽�åɡ�
	// �����ʣ���Υ���դǻȤ��Τǡ��饤�֥�������롣
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
					// �Τ�ʤ�����ϼΤƤ롣
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
