<?php

// $Id: graphradar.inc.php,v 0.10 2023/11/4

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
	$titlestyle=array(0 => 'black'); // �����ȥ��ѤΥ����������ǡ���

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
	$linemarker = array(); //�ޡ������λ���ǡ���

	$color = array(); // ��
	$fillcolor = array(); // �ɤ�Ĥ֤���
	

	$datacount = 0; // �ƥ����ѥѥ�᡼���������data count�Ȥ���Ȥ�

	$legend="";
	$legendx =0;	$legendy=0;
	$legendw =0;	$legendh=0;

	// ��������
	foreach( $argg as $key => $arg){
		// ����Ū�ʥ����Ƚ��������줬�ʤ��ȥ����Ȥ�"="�򤫤��ʤ��Τǡ� 
		if( mb_substr($arg,0,2)=="//") continue;
		if( mb_substr($arg,0,1)=="#") continue;

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
				case 'titlestyle':
					$titlestyle= $lib->trimexplode(',',$argss[1]);
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
					// �ǡ���̾:��̾[,�ɤ�Ĥ֤�Ψ�פȤ��롣
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
			// ��Ƭ�˥���ޤ������硢pukiwikiɽ/cvs�ˤ��ǡ����Ȥߤʤ���
			if( mb_substr($arg,0,1)!=",") continue;

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

		// �ޡ�����	
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
	// �����ȥ�
	if(! $gtitle==""){
		$html .= $lib->CreateTitle( $gtitle, $tx, $ty , $titlestyle );
	}

	//-----------------
	//���㡣
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
