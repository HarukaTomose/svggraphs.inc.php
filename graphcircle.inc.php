<?php

// $Id: graphcircle.inc.php,v 0.09 2023/11/03

function plugin_graphcircle_convert()
{
	return "#graphcircle: You MUST use svggraphs plugin.";

}

function plugin_graphcircle_draw($argg, $lib)
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

	// ����չ����ѿ�
	$data = array(); //�¥ǡ���
	$color = array(); // ��
	$keyoffset = array(); //ɽ������ǡ���ʸ����ΰ�������

	$noshow_target = array();; //��ɽ���ˤ������̾��
	$noshow = FALSE;

	// ����˱ߤ���������Ⱦ��
	$ccircle =0;

	// ��������
	foreach( $argg as $key => $arg){
		////////tomoseDBG("arg[".$key."][".$arg."]");

		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			//$argss = array_map('trim', $argss); 
			switch ($argss[0])
			{
				case 'w': //������
					$cw = ctype_digit($argss[1])? $argss[1]: $cw;
					break;
				case 'h': //�⤵����
					$ch = ctype_digit($argss[1])? $argss[1]: $ch;
					break;
				case 'offx': //���ե��åȰ��ֻ���
					//ctype_digit �ϡ�-�פ�����դ��Ƥ���ʤ���
					//$offx = ctype_digit($argss[1])? $argss[1]: $offx;
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

				case 'center': //����˱ߤ��������α�Ⱦ��
					$ccircle = ctype_digit($argss[1])? $argss[1]: $ccircle;
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
					////////tomoseDBG("push: key[".$datas[0]."][".$datas[1]."]");
					break;

				case 'noshow':
					$noshow_target = htmlsc($argss[1]);
					//array_push($noshow_target,htmlsc($argss[1]));

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
	if(count($data)<=0) return "#graphcircle: No Data";

	// ����պ�ɸ �Ʒ׻�
	$cx= ($cw+$offx)/2;
	$cy= ($ch+$offy)/2;

	$r = ($cw>$ch)? $ch/2.5 : $cw/2.5;

	// �ǡ�����¤�β��ϡ�
	$dcount =0;
	$dtable = array();

	foreach($data as $key => $value){
		//////tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // �ƹԤ�trim()�򤫤���
		$dtable[$key] = $tmp[0];
	}

	// ��Ψ����뤿��ˡ����ڽ�����
	$tval = 0;
	$dcount=0;
	foreach($dtable as $key => $value){
		$tvalue =$value+$tvalue;
//		////tomoseDBG("curent item[".$value."],total[".$tvalue."]");
		$dcount++;
	}



	if($dcount<1) return "#graphcircle: to few item in one-line.";

	// �����Х�����������ˡ�������ΰ�����
	//$eow = $cw-10;	$eoh = $ch-15;
	//$clipx = $eow-$offx; $clipy=$eoh - $offy;

	$html = '';
//<p>Camvas Size($cw,$ch), Drawsize($eow,$eoh)</p>
//<circle cx="$cx" cy="$cy" r="$r" stroke="black" stroke-width="1" />

$html =<<<EOD
<svg xmlns="http://www.w3.org/2000/svg" width="$cw" height="$ch" viewBox="0 0 $cw $ch">
<!-- <path d="M 10 10 Q 20 0 40 10 60 20 80 10 100 0 120 10" stroke="black" fill="transparent"/> -->
<!-- <circle cx="$cx" cy="$cy" r="$r" stroke="black" stroke-width="2" /> -->

EOD;
	$ctotal=0;
	$precol='lightgrey';
	foreach($dtable as $key => $value){
		$sangle= 360*$ctotal/$tvalue;
		$startx= intval($cx + $r * sin($sangle / 180 * pi()));
		$starty= intval($cy - $r * cos($sangle / 180 * pi()));

		$eangle= 360*($ctotal+$value)/$tvalue;
		$endx= intval($cx + $r * sin($eangle / 180 * pi()));
		$endy= intval($cy - $r * cos($eangle / 180 * pi()));
		$mode = (($eangle-$sangle)>180)?1:0;

		$ccolor= (!$color[$k]=="")? $color[$k]:$lib->getnextcolor($precol);
		$precol=$ccolor;

		$tmp='<path d="M'.$cx.' '.$cy.' L'.$startx.' '.$startY.' L'.$endx.' '.$endy.' Z" />';
			////tomoseDBG("|point item[".$tmp."]");
		

$html .=<<<EOD
<path d="M$cx $cy L$startx $starty A$r $r 0 $mode 1 $endx $endy Z" stroke="gray" stroke-width="1" fill="$ccolor" />

EOD;
		$ctotal+=$value;

	}

	//�����
	if( $ccircle>0){
		$html .='<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$ccircle.'" stroke="gray" stroke-width="1" fill="white"/>';

	}

	// �����ȥ�
	if(! $gtitle==""){
		$html .= $lib->CreateTitle( $gtitle, $tx, $ty , $titlestyle );
	}

	$ctotal=0;

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

		if(array_key_exists(0,$keyoffset[$key] )) $txx+=$keyoffset[$key][0];
		if(array_key_exists(1,$keyoffset[$key] )) $txy+=$keyoffset[$key][1];


		$tmptxt1 = $key;
		$tmptxt2 = round((($value/$tvalue)*100),2)."%";
		$html .= '<text x="'.$txx.'" y="'.($txy-6).'" fill="black" font-size="11">'.$tmptxt1.'</text>';
		$html .= '<text x="'.$txx.'" y="'.($txy+5).'" fill="black" font-size="11">'.$tmptxt2.'</text>';
		$ctotal+=$value;

	}








$html .='</svg>';
	
	$tbltxt="";
	foreach($data as $key => $value){
	
		$tbltxt .= ",".$key.",".$value."\n";
	}
//	$html .= convert_html($tbltxt);

	return $html;
}


?>
