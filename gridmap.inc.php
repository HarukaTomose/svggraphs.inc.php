<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// gridmap.inc.php
// ������Ū�������ܤ��ξ��Ǥ�ե����ƥ���֤��褦�ʥץ饰����
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

	$gr_xc = 5; // ���㡢���
	$gr_yc = 5; // ���㡢�Կ�

	$data = array(); //�¥ǡ���
	$color = array(); // ��
	$precol='lightgrey';

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

				case 'r': //�Կ�
					$gr_yc = ctype_digit($argss[1])? $argss[1]: $gr_yc;
					break;
				case 'c': //���
					$gr_xc = ctype_digit($argss[1])? $argss[1]: $gr_xc;
					break;

				case 'data':
					// data�� "name:x,y" �Ȥ�����¤��
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
					// �Τ�ʤ����ޥ�ɡ��ΤƤ롣
					break;
			
			}
		}
	}

	// ���󤬤���ä��Τ����賫�ϡ�
	$tmpbuf ="";
//	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.($gr_xc*20+50).'" height="'.($gr_yc*20+50).'" viewBox="0 0 '.($gr_xc*20+50).' '.($gr_yc*20+50).'">'."\n";
	$body ='<svg xmlns="http://www.w3.org/2000/svg" width="'.$gr_w.'" height="'.$gr_h.'" viewBox="0 0 '.($gr_xc*$gr_sc+50).' '.($gr_yc*$gr_sc+50).'">'."\n";


	// Grid ���衣�Կ���������鼫ư������
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

	// ���֥������ȡ�
	foreach($data as $key => $value){
		//tomoseDBG("data:".$key."/".$value);
		$tmp =explode(",",$value);
		$tmp = array_map('trim', $tmp); // �ƹԤ�trim()�򤫤���
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