<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// graphmeter.inc.php
// svggraph���꡼�������С������Ū�ʥ᡼����ɽ���ץ饰����
//
// ver0.06 2023/10/29 H.Tomose

function plugin_graphmeter_convert()
{
	return "";
}
function plugin_graphmeter_draw($argg, $lib)
{

	$body ="";
	$gr_w = 400; //���ǥե����
	$gr_h = 30;  //�⤵�ǥե����
	$gr_txt = "";

	$gr_offset = 70; // ����ճ��ϰ��֤Υ��ե��å�
	$gr_max = 100; // ������������
	$gr_min = 0; // �������Ǿ���
	$gr_textoffset=0; // �ƥ����ȤΥ��ե��åȰ���

	$data = 0; //�¥ǡ���
	$color = array(); // ��
	$precol='lightgrey';
	$color[0]="lightgrey";

	$mcolor= $color[0];	// �᡼������
	$sccolor = 'blue';	// �᡼�����������ο�
	$txcolor= 'black';	// �ƥ����ȿ�


	// ��������
	foreach( $argg as $key => $arg){
		if(strpos($arg,'=')){
			$argss= $lib->trimexplode('=',$arg);
			//$argss = array_map('trim', $argss); 
			switch ($argss[0])
			{
				case 'w': //������
					$gr_w = ctype_digit($argss[1])? $argss[1]: $gr_w;
					break;
				case 'h': //�⤵����
					$gr_h = ctype_digit($argss[1])? $argss[1]: $gr_h;
					break;

				case 'offset': //���ե��å�
					$gr_offset = is_numeric($argss[1])? $argss[1]: $gr_offset;
					break;

				case 'text_offset': //�ƥ����ȥ��ե��å�
					$gr_textoffset = is_numeric($argss[1])? $argss[1]: $gr_textoffset;
					break;

				case 'data': //�ǡ�����
					$data = ctype_digit($argss[1])? $argss[1]: $data;
					break;
				case 'max': //�ǡ���������
					$gr_max = ctype_digit($argss[1])? $argss[1]: $gr_max;
					break;

				case 'text':
					// �᡼������ʸ����XSS�к����Ѵ��»ܡ�
					$gr_txt=htmlsc($argss[1]);
					break;

				case 'color':
					$color[0] = $lib->correctColor($argss[1]);
					break;

				case 'text_color':
					$txcolor = $lib->correctColor($argss[1]);
					break;

				case 'gauge_color':
					$sccolor = $lib->correctColor($argss[1]);
					break;

				default:
					// �Τ�ʤ����ޥ�ɡ��ΤƤ롣
					break;
			
			}
		}
	}

	//�ѥ�᡼������������֤η���

	// ���󤬤���ä��Τ����賫�ϡ�
	$tmpbuf ="";
//	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.($gr_xc*20+50).'" height="'.($gr_yc*20+50).'" viewBox="0 0 '.($gr_xc*20+50).' '.($gr_yc*20+50).'">'."\n";
	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.$gr_w.'" height="'.$gr_h.'" viewBox="0 0 '.gr_w.' '.$gr_h.'">'."\n";
	
	$areawidth = $gr_w-$gr_offset -40;
	$datawidth= $areawidth * ($data/$gr_max);
	$tmpx = $areawidth+$gr_offset+10;
	$dataheight = $gr_h*1/10;


	$gr_offset +=10;
//	$body.= '<text x="50" y="25" fill="'.$ccolor.'">width'.$gr_w.'_data:'.$data.'_max'.$gr_max.'</text>'."\n";

	$lncolor="stroke:".$sccolor.";stroke-width:3";

$body.=<<<EOD
<rect x="$gr_offset" y="0" width="$gr_w" height="$gr_h" style="fill:white;" />
<rect x="$gr_offset" y="$dataheight" width="$datawidth" height="$gr_h" style="fill:$color[0];" />
<line x1="$gr_offset" y1="$gr_h" x2="$tmpx" y2="$gr_h" style="$lncolor" />
EOD;

	$lncolor="stroke:".$sccolor.";stroke-width:1";

	for ($i = 0; $i <= 10; $i++) {
		$tmpx = $gr_offset+$areawidth * $i / 10;
		$tmph= $gr_h*7/10;
		if($i==0 ||$i==10) $tmph =0;
		if($i==5) $tmph =$gr_h*5/10;

$body.=<<<EOD
<line x1="$tmpx" y1="$tmph" x2="$tmpx" y2="$gr_h" style="$lncolor" />
EOD;

	$body.= '<text x="'.($tmpx+1).'" y="'.($gr_h-2).'" fill="'.$sccolor.'" font-size=10>'.($gr_max*($i*10)/100).'</text>'."\n";


	}
	// ���֥������ȡ�
	$gr_ytext=($gr_h+10)/2;

	$body.= '<text x="'.$gr_textoffset.'" y="'.$gr_ytext.'" fill="'.$txcolor.'">'.$gr_txt.'</text>'."\n";



	$body .= "</svg>";

	return $body;
}
?>