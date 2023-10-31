<?php

// $Id: graphradar.inc.php,v 0.02 2023/10/29

function plugin_graphradar_convert()
{
	return "#graphradar: You MUST use svggraphs plugin.";

}

function plugin_graphradar_draw($argg, $lib)
{
	global $vars;

	// �����ΰ� ����͡�
	$cw=100;	$ch=100;		// �����Х��������ǥե����
	$offx = 0;	$offy =0;	// �����Х���Υ���ճ��Ϻ�ɸ��

	//����ե����ȥ�
	$gtitle = "";	// �����ȥ�ʸ����
	$tx = round($cw/3); $ty=20;	//�����ȥ��ɸ�ǥե����

	// ����պ�ɸ����Ϣ	
	$cx= ($cw+$offx)/2;
	$cy= ($ch+$offy)/2;
	$r = ($cw>$ch)? $ch/2.5 : $cw/2.5;

	// ����դκǹ���
	$vmax = 100;

	// ���������
	$scales = array();

	// ����չ����ѿ�
	$data = array(); //�¥ǡ���
	$keyname = array(); // ����̾��
	$keyoffset = array(); //��ɽ��ʸ����ΰ�������

	$color = array(); // ��
	$fillcolor = array(); // �ɤ�Ĥ֤���
	

	$datacount = 0; // �ƥ����ѥѥ�᡼���������data count�Ȥ���Ȥ�

	$legend="";
	$legendx =0;	$legendy=0;
	$legendw =0;	$legendh=0;

	// ��������
	foreach( $argg as $key => $arg){
		////////tomoseDBG("arg[".$key."][".$arg."]");

		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			switch ($argss[0])
			{
				case 'w': //������
					$cw = ctype_digit($argss[1])? $argss[1]: $cw;
					break;
				case 'h': //�⤵����
					$ch = ctype_digit($argss[1])? $argss[1]: $ch;
					break;
				case 'offx': //���ե��åȰ��ֻ���
					$offx = is_numeric($argss[1])? $argss[1]: $offx;
					break;
				case 'offy': //���ե��åȰ��ֻ���
					$offy = is_numeric($argss[1])? $argss[1]: $offy;
					break;

				case 'tx': //�����ȥ��ɸ��
					$tx = ctype_digit($argss[1])? $argss[1]: $tx;
					break;
				case 'ty': //�����ȥ��ɸ��
					$ty = ctype_digit($argss[1])? $argss[1]: $ty;
					break;
				case 'title':
					$gtitle=htmlsc($argss[1]);
					break;

				case 'keyname':
					// key�� "key1,key2,key3,..." �Ȥ���ʸ����¤��
					$keyname= $lib->trimexplode(',',$argss[1]);
					break;

				case 'keyoffset':
					// keyoffset�� "name:offx,offy" �Ȥ���ʸ����¤��
					// �ޤ�̾�������å��� 
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	$tmpname=htmlsc($datas[0]);

					//���ե��åȤ�2�ͤΥ����å���
					$datas=$lib->trimexplode(',',$datas[1]);
					$keyoffset[$tmpname]=array();
					$keyoffset[$tmpname][0]=is_numeric($datas[0])? $datas[0]: 0;
					$keyoffset[$tmpname][1]=is_numeric($datas[1])? $datas[1]: 0;
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
					break;

				case 'fillcolor':
					$datas=$lib->trimexplode(':',$argss[1]);
					if(! $datas[1]=="")	{
						$fillcolor[htmlsc($datas[0])] = $datas[1];
					}
					break;

				case 'vmax':
					$vmax = ctype_digit($argss[1])? $argss[1]: $vmax;
					//array_push($noshow_target,htmlsc($argss[1]));

					break;

				case 'scale':
					// scales�� "sc1,sc2,sc3,..." �Ȥ������ͤ���
					$datas= $lib->trimexplode(',',$argss[1]);
					foreach( $datas as $v ){

						$scales[htmlsc($v)]=$v;
					}
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
	if(count($data)<=0) return "#graphradar: No Data";

	// ����պ�ɸ �Ʒ׻�
	$cx= ($cw+$offx)/2;
	$cy= ($ch+$offy)/2;

	$r = ($cw>$ch)? $ch/2.5 : $cw/2.5;

	// �ǡ�����¤�β��ϡ�
	$dcount =0;
	$dtable = array();

	$datacount = count($keyname);
	foreach($data as $key => $value){
		//////tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // �ƹԤ�trim()�򤫤���
		if(count($tmp)<$datacount){
			return "#graphradar: to few item in one-line.";
		}
		$dcount = (count($tmp)>$dcount)?count($tmp):$dcount;
		$dtable[$key] = $tmp;
	}
	
	// �����Х�����������ˡ�������ΰ�����
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
	$ctotal=0;
	$precol='lightgrey';

	// ��
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

	// ��������ǡ���
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

	// �졼�����ѤΥǡ�������
	foreach( $dtable as $lname =>$tline){
		$stroke ="";
		$i=0;
		foreach($tline as $key => $value){

			$sangle= $i*360/$datacount;

//			if( $maxy_auto && $value>$maxy) $maxy=$value;
//			if( $miny_auto && $value<$miny) $miny=$value;

			$endx= intval($cx + $r*($value/$vmax) * sin($sangle / 180 * pi()));
			$endy= intval($cy - $r*($value/$vmax) * cos($sangle / 180 * pi()));

			$stroke=$stroke." ".$endx.",".$endy;
			//$lcolor = $color[$key];
			$i=$i+1;

		}
		$lcolor = ($color[$lname])?$color[$lname]:"black";
		$fcolor = ($fillcolor[$lname])?$fillcolor[$lname]:"none";

//		$html .='<text x="10" y="10" >['.$stroke.']</text>';
//		$html .='<text x="10" y="20" >['.$key.']</text>';
//		$html .='<text x="10" y="30" >['.$lcolor.']</text>';
	

$html .=<<<EOD

<polygon points="$stroke" stroke="$lcolor" fill="$fcolor" fill-opacity="0.2" />

EOD;

	}


	// �����ȥ�
	if(! $gtitle==""){
		$html .='<text x="'.$tx.'" y="'.$ty.'" fill="black">'.$gtitle.'</text>';
	}


	$ctotal=0;

	//����
	foreach( $dtable as $k =>$line){
		$legendw= (strlen($k)>$legendw)?strlen($k):$legendw;
	}
	
	$legendh= count($data)*15;
	$legendw= 50+$legendw*8;


	if($legend!=""){

	$html .='<g transform="translate('.$legendx.','.$legendy.')" font-size="11">';
$html .= <<<EOD
	<rect width="$legendw" height="$legendh" style="fill:white;stroke-width:1;stroke:black" />
EOD;
	$tmp=12;
	$precol=0;
	foreach($data as $key=>$val){
	
		$ccolor= (!$color[$key]=="")? $color[$key]:$lib->getnextcolor($precol);
		$precol=$ccolor;
$html .=<<<EOD
<line x1="10" y1="$tmp" x2="30" y2="$tmp" style="stroke:$ccolor;stroke-width:1" />

EOD;
		$html .='<text x="40" y="'.($tmp+2).'" fill="black">'.htmlsc($key).'</text>'."\n";
		$tmp+=12;
	}
	$html .="</g>";
	}	


/*
	//���ͤΥǡ���ɽ��
	foreach($dtable as $key => $value){
		if( $noshow_target==$key ) $noshow=TRUE;
		if( $noshow ) continue;

		$tangle= 360*($ctotal+$value/2)/$tvalue;
		$txx= intval($cx + $r/2 * sin($tangle / 180 * pi()) -10);
		$txy= intval($cy - $r/2 * cos($tangle / 180 * pi()));
		if( $tangle<45 || $tangle>315 ){	$txy-=25*$r/100;	}
		if( $tangle>=45 && $tangle<135 ){	$txx+=10*($r+$ccircle)/100;	}
		if( $tangle>=135 && $tangle<225 ){	$txy+=25*$r/100;	}
		if( $tangle>=225 && $tangle<315 ){	$txx-=20*($r+$ccircle)/100;	}


		$tmptxt1 = $key;
		$tmptxt2 = round((($value/$tvalue)*100),2)."%";
		$html .= '<text x="'.$txx.'" y="'.($txy-6).'" fill="black" font-size="11">'.$tmptxt1.'</text>';
		$html .= '<text x="'.$txx.'" y="'.($txy+5).'" fill="black" font-size="11">'.$tmptxt2.'</text>';
		$ctotal+=$value;

	}
*/






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
