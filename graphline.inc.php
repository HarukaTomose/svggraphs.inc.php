<?php
// $Id: graphline.inc.php,v 0.05 2023/10/19 Haruka Tomose

function plugin_graphline_convert()
{
	return "#graphline: You MUST use svggraphs plugin.";

}

function plugin_graphline_draw($argg, $lib)
{
	global $vars;

	// �����ΰ� ����͡�
	$cw=200;	$ch=50;		// �����Х��������ǥե����
	$offx = 20;	$offy =10;	// �����Х���Υ���ճ��Ϻ�ɸ��

	//����ե����ȥ�
	$gtitle = "";	// �����ȥ�ʸ����
	$tx = round($cw/3); $ty=20;	//�����ȥ��ɸ�ǥե����

	// ����պ�ɸ����Ϣ
	$miny=0;	$maxy=0;	// y�����Ͱ�
	$minx=0;	$maxx=0;	// x�����Ͱ�
	$miny_auto=TRUE;	$maxy_auto=TRUE;	// ��ưȽ��ե饰
	$minx_auto=TRUE;	$maxx_auto=TRUE;	
	// ��ɸ������
	$scalex = "";
	$sclistx = array();
	$scaley = "";
	$sclisty = array();

	$legend="";
	$legendx =0;	$legendy=0;
	$legendw =0;	$legendh=0;
	

	// �ġ����ޤ������ѿ�
	$data = array(); //�¥ǡ���
	$color = array(); // ��



	// ��������
	foreach( $argg as $key => $arg){
		////tomoseDBG("arg[".$key."][".$arg."]");

		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			//$argss = array_map('trim', $argss); 
			switch ($argss[0])
			{
				case 'w': //������
					$cw = is_numeric($argss[1])? $argss[1]: $cw;
					break;
				case 'h': //�⤵����
					$ch = is_numeric($argss[1])? $argss[1]: $ch;
					break;
				case 'offx': //���ե��åȰ��ֻ���
					$offx = is_numeric($argss[1])? $argss[1]: $offx;
					break;
				case 'offy': //���ե��åȰ��ֻ���
					$offy = is_numeric($argss[1])? $argss[1]: $offy;
					break;

				case 'minx': //����գ����Ǿ���
					if( is_numeric($argss[1]) ){
						$minx = $argss[1];	$minx_auto=FALSE;
					}
					break;
				case 'maxx': //����գ���������
					if( is_numeric($argss[1]) ){
						$maxx = $argss[1];	$maxx_auto=FALSE;
					}
					break;
				case 'miny': //����գ����Ǿ���
					if( is_numeric($argss[1]) ){
						$miny = $argss[1];	$miny_auto=FALSE;
					}
					break;
				case 'maxy': //����գ���������
					if( is_numeric($argss[1]) ){
						$maxy = $argss[1];	$maxy_auto=FALSE;
					}
					break;

				case 'tx': //�����ȥ��ɸ��
					$tx = is_numeric($argss[1])? $argss[1]: $tx;
					break;
				case 'ty': //�����ȥ��ɸ��
					$ty = is_numeric($argss[1])? $argss[1]: $ty;
					break;
				case 'title':
					$gtitle=htmlsc($argss[1]);
					break;

				case 'legend':
					$legend=htmlsc($argss[1]);
					break;

				case 'legendx': //�����ȥ��ɸ��
					$legendx = is_numeric($argss[1])? $argss[1]: $legendx;
					break;
				case 'legendy': //�����ȥ��ɸ��
					$legendy = is_numeric($argss[1])? $argss[1]: $legendy;
					break;

				case 'sxauto': // ��������μ�ư���ֻ��ꡣ
					// step[[,start],end] �����ν񼰡�
					// �����ǤϤ����κۤΤ��������롣
					$tmp=$lib->trimexplode(',',($argss[1]).",,");
					$scalex= $tmp[0].",".$tmp[1].",".$tmp[2];
					break;
				case 'sx': // ����������ľ�ܻ��ꡣ
					if( is_numeric($argss[1]) ) array_push($sclistx,$argss[1]);
					break;

				case 'syauto': // ��������μ�ư���ֻ��ꡣ
					// step[[,start],end] �����ν񼰡�
					// �����ǤϤ����κۤΤ��������롣
					$tmp=$lib->trimexplode(',',($argss[1]).",,");
					$scaley= $tmp[0].",".$tmp[1].",".$tmp[2];
					break;
				case 'sy': // ����������ľ�ܻ��ꡣ
					if( is_numeric($argss[1]) ) array_push($sclisty,$argss[1]);
					break;

				case 'data':
					// data�� "name:1,2,3,..." �Ȥ�����¤��
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
					// �Τ�ʤ����ޥ�ɡ��ΤƤ롣
					break;
			
			}
		}else{
			// ���ϹԤ� = ���ʤ���������
			// pukiwikiɽ/cvs�����ꤷ�ơ��ǡ����Ȥߤʤ���
			// ���ξ�硢�ǽ��ͭ�����Ǥ򥭡��Ȥߤʤ���
			$datas=$lib->trimexplode(',',$arg);
			// �ǽ��1���ܤ�ɬ���ΤƤ롣
			$tmp = array_shift($datas);
			$tmp = array_shift($datas);
			if(! $tmp=="") $data[$tmp] = implode(",",$datas);

		}
	}

	// �������Ϥ�����ä����ǡ����Ԥο�������å����롣
	if(count($data)<=1) return "#graphline: No Data";


	// �ǡ�����¤�β��ϡ�
	$dcount =0;
	$dtable = array();

	// �ǽ�ιԤϣ��������ˤʤ�Τǡ����Ф���
	$tmp = array_shift( $data );
	$xtable = $lib->trimexplode(",",$tmp);
	//tomoseDBG("xtable:".implode(",",$xtable));
	// �����κ��硦�Ǿ��γ���
	if( $maxx_auto ) $maxx= end($xtable);
	if( $minx_auto ) $minx= reset($xtable);

	// ����ʹߤϥǡ���������
	foreach( $data as $key => $value){
		//tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // �ƹԤ�trim()�򤫤���
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

	// �����Х�����������ˡ�������ΰ�����
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

	// y������
	// step[[,start],end] �����ν񼰤�$scaley�����äƤ��롣�����������Ѵ���
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
	// x������

	// y������
	// step[[,start],end] �����ν񼰤�$scaley�����äƤ��롣�����������Ѵ���
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

	// �ƥǡ������Ȥ��ޤ�������
	foreach( $dtable as $k =>$line){
		$legendw= (strlen($k)>$legendw)?strlen($k):$legendw;
		//tomoseDBG("start:".$k." len[".strlen($k)."]");

		$xwork = $offx;
		$html .='<polyline points="';

		foreach( $line as $key => $value){
			$xpos = $offx+(($xtable[$key]-$minx)*($cw-10-$offx)/($maxx-$minx));

			//tomoseDBG("item:".$key.", pos(".$xpos.",".$value.")");
			// �ޤ��ǡ�����ǧ�����ͤǤʤ���ǽ���⤢�뤿�ᡣ
			// ���ͤǤʤ���С������ϼΤƤ롣
			if (! is_numeric($value) ) {
				continue;
			}
			//�����ǥǡ����Υ���С��ȡ�
			// y�κ�ɸ�ϰϤ� �ֺǾ��͡�$ch-10���ֺ����͡�$offy��
			// ��������ɸ��Ū�˥ޥ��������ʤΤ��֤�������ɬ�פ����롣
			// y���Ͱ�ϡ��Ǿ� $miny,����$maxy������򥳥�С��Ȥ��ʤ��Ȥʤ�ʤ���
			// ��Ψ�Ѵ�������ʴ�����($ch-10-$offy)/($maxy-$miny)

			$ypos = intval($ch-15-(($value-$miny)*($ch-15-$offy)/($maxy-$miny)));
			$xpos = intval($offx+(($xtable[$key]-$minx)*($cw-10-$offx)/($maxx-$minx)));
			$html .=<<<EOD
$xpos,$ypos 
EOD;

		}
		// ���ο����ꡣ
		$ccolor= (!$color[$k]=="")? $color[$k]:$lib->getnextcolor($precol);
		$precol=$ccolor;
//if(!$color[$k]=="") $ccolor=$color[$k];

$html .='" clip-path="url(#cliparea)" style="fill:none;stroke:'.$ccolor.';stroke-width:2" />';
$html .="\n";
	}


	//����
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
