<?php
header("Content-type:text/html;charset=gb2312");
set_time_limit(0);
//$url = "http://www.4399.com/flash_fl/2_1.htm";//page 1
//$url = "http://www.4399.com/flash_fl/more_2_2.htm";
//$url = "http://www.4399.com/flash_fl/more_2_3.htm";
//$url = "http://www.4399.com/flash_fl/more_2_4.htm";
//$url = "http://www.4399.com/flash_fl/more_2_6.htm";
//$url = "http://www.4399.com/flash_fl/3_1.htm";
//$url = "http://www.4399.com/flash_fl/more_3_2.htm";
//$url  ="http://www.4399.com/flash_fl/more_3_3.htm";
//$url = "http://www.4399.com/flash_fl/4_1.htm";
//$contents = file_get_contents($url);

//$pattern_field = "/<span\s+class=\"tit_a\">全部动作小游戏<\/span>\s+<div\s+class=\"r_pag pt\">[\s\S]+<\/div>\s+<\/div>\s+<ul\s+class=\"tm_ul\">[\S\s]+<\/ul>\s+<div\s+class=\"pag pt_1\">/";

//$pattern_field = "/<div\s+class=\"lf_game\s+mt\s+cf\">[\s\S]+<ul\s+class=\"tm_ul\">[\s\S]+<\/ul>[\s\S]+<\/div>/";//第一页
$pattern_field = "/<div\s+class=\"lf_game\s+cf\">[\s\S]+<ul\s+class=\"tm_ul\">[\s\S]+<\/ul>[\s\S]+<\/div>/";	//第二页

$pattern_li = "/<li>[\s\S]+<\/li>/U";

function grab_4399_guide($content_page)
{
	$pattern_guide = "/<div\s+class=\"n_box\">[\s\S]+<\/div>/U";

	if( preg_match($pattern_guide,$content_page,$match_guide)>0)
	{
		var_dump($match_guide);
	}
	else{
		echo "match guide failed...<br/>";
	}
}

function grab_4399($contents,$pattern_field,$pattern_li,$leixing)
{
	$queue_name = array();
	$queue_url = array();
	$queue_pic = array();
	$queue_flash = array();

	if( preg_match($pattern_field,$contents,$match_field) > 0 )
	{
		var_dump($match_field);
		$field_contents = $match_field[0] ;
		if( preg_match_all( $pattern_li,$field_contents,$match_li ) > 0 )
		{
			//var_dump($match_li);
			$length = count( $match_li[0] );
			echo $length;
			$pattern_pic = "/http.*(jpg|png|gif|jpeg)/";
			//$pattern_name = "/alt=\'[\s\S]+\'/";
			$pattern_url = "/href=(\".*\">|\'.*\'>)/";
			for($i = 0;$i<$length;$i++)
			{
				
				if( preg_match($pattern_pic,$match_li[0][$i],$match_pic)>0)
				{
					//var_dump($match_pic);
					array_push($queue_pic,$match_pic[0]);
				}
				else{
					echo $i."match pic failed...<br/>";
				}
				
				
				$name = strip_tags($match_li[0][$i]);
				array_push($queue_name,$name);
				
				
				
				if( preg_match($pattern_url,$match_li[0][$i],$match_url)>0)
				{
					//var_dump($match_url);
					$url = $match_url[0];
					$mark1 = strstr($url,"'");
					$string = substr($mark1,0,1);
					//echo $string;echo "<br/>";
					
					if( $string == "'")
					{
						$arr_url1 = explode("'",$url);
						//var_dump($arr_url1);
						if(  substr($arr_url1[1],0,3) == 'htt' || substr($arr_url1[1],0,3) == 'www' )
						{
						}
						else{
							$arr_url1[1] = "http://www.4399.com".$arr_url1[1];
						}
						array_push($queue_url,$arr_url1[1]);
						
						//接下来进入内容页

						/*
						$content_page = file_get_contents($arr_url1[1]);

						//$pattern_page_field = "/<a\s+href=\".*\"\s+target=\"_self\"><img\s+src=\".*\"\s+alt=\"开始游戏\"><\/a>/";
						$pattern_page_field = "/开始游戏/";


						if( preg_match($pattern_page_field,$content_page,$match_page)>0)
						{
							echo "match_page:";
							var_dump($match_page);
						}
						else{
							echo $i."match page failed...<br/>";
						}
						*/

					}
					else{
						$arr_url1 = explode('"',$url);
						//var_dump($arr_url1);
						$arr_url1[1] = "http://www.4399.com".$arr_url1[1];
						array_push($queue_url,$arr_url1[1]);

						//接下来进入内容页

					}
					
				}
				else{
					echo $i."match url failed...<br/>";
				}
				

			}
		}
		else{
			echo "match li failed...<br/>";
		}
	}
	else{
		echo "match field failed...<br/>";
	}
	
	/*
	echo "<h1>url</h1>";
	var_dump($queue_url);
	echo "<h1>pic</h1>";
	var_dump($queue_pic);
	echo "<h1>name</h1>";
	var_dump($queue_name);
	*/

	$length = count($queue_url);
	//$length = 1 ;
	$pattern_page_field = "/<a\s+href=\'.*\'\s+target=\"_self\"\s+class=\'img_border\'>/";
	$pattern_page_field2 = "/<a\s+class=\"go_game\"\s+target=\"_self\"\s+href=\".*\">/";
	$pattern_page_url = "/\/flash\/.*\.htm/U";
	$pre = "http://www.4399.com";
	for($i=0;$i<$length;$i++)
	{
		$content_page = file_get_contents($queue_url[$i]);
		
		//$pattern_page_field = "/<div\s+class=\"b1_m_l\">[\s\S]+<\/div>/";
		if( preg_match($pattern_page_field,$content_page,$match_page)>0)		//匹配成功则说明在游戏入口页
		{
			//var_dump($match_page);
			
			if( preg_match( $pattern_page_url,$match_page[0],$match_page_url)>0)		
			{
				//var_dump($match_page_url);

				$flash_url = $pre.$match_page_url[0] ;
				array_push($queue_flash,$flash_url);

			}
			else{
				echo "match page flash url failed...<br/>";
			}
			
			//

		}
		else{
			echo $i.":".$queue_url[$i];echo "<br/>";
			echo $i."match page failed...try to match page pattern2..<br/>";
			if( preg_match($pattern_page_field2,$content_page,$match_page2)>0)
			{
				var_dump($match_page2);
				//继续匹配flash url
				if( preg_match($pattern_page_url,$match_page2[0],$match2_url)>0)
				{
					if(  substr($match2_url[0],0,3) == 'htt' || substr($match2_url[0],0,3) == 'www' )
					{
					}
					else{
						$flash_url2 = $pre.$match2_url[0];
					}
					echo "it's flash_url2:".$flash_url2."<br/>";
					array_push($queue_flash,$flash_url2);
				}
				else{
					echo $i."match pattern2 succeed!!But match url in pattern2 failed...This shouldn't happen..<br/>";
				}
			}
			else{
				echo $i."match page2 failed...maybe it's already game url...try to verdict..<br/>";
				echo "It's:".$queue_url[$i]."<br/>";
				array_push($queue_flash,$queue_url[$i]);
			}

		}
	}
	

	echo "<h1>name</h1>";
	var_dump($queue_name);
	echo "<h1>url</h1>";
	var_dump($queue_url);
	echo "<h1>pic</h1>";
	var_dump($queue_pic);
	
	echo "<h1>queue_flash</h1>";
	var_dump($queue_flash);
	
	
	//$leixing = "益智";
	$leixing = iconv('utf-8','gb2312',$leixing);
	$conn = mysql_connect("localhost",'root','root');
	if( $conn)
	{
		mysql_select_db("little_game");
		for( $i = 0;$i<$length;$i++)
		{
			$name = $queue_name[$i];
			$url  = $queue_flash[$i] ;
			$pic = $queue_pic[$i];
			
			mysql_query("set names gbk");
			$sql = "insert into `4399`(name,leixing,pic,url) values('".$name."','".$leixing."','".$pic."','".$url."')";
			mysql_query($sql);
			if( mysql_affected_rows()>0)
			{
				echo $name.",".$url.",插入成功<br/>";
			}
			else{
				echo "插入失败<br/>";
			}
		}
	}
	else{
		echo "mysql connection failed...<br/>";
	}
	
	
}

/*
for( $i = 2; $i<=59;$i++)
{
	$url = "http://www.4399.com/flash_fl/more_5_".$i.".htm";
	$contents = file_get_contents($url);
	grab_4399($contents,$pattern_field,$pattern_li);
}
*/

$pattern_field1 = "/<div\s+class=\"lf_game\s+mt\s+cf\">[\s\S]+<ul\s+class=\"tm_ul\">[\s\S]+<\/ul>[\s\S]+<\/div>/"; //第一页
$pattern_field2 = "/<div\s+class=\"lf_game\s+cf\">[\s\S]+<ul\s+class=\"tm_ul\">[\s\S]+<\/ul>[\s\S]+<\/div>/";	//第2页

for( $i = 1;$i<=8;$i++ )
{
	if( $i== 1)
	{
		$leixing = "搞笑";
		//$leixing = iconv('utf-8','gb2312',$leixing);
		$url = "http://www.4399.com/flash_fl/1_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);
		
		$url = "http://www.4399.com/flash_fl/more_1_2.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field2,$pattern_li,$leixing);

	}

	if( $i == 2 )
	{
		$leixing = "冒险";
		$url = "http://www.4399.com/flash_fl/6_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);

		for( $j = 2;$j<=14;$j++)
		{
			$url = "http://www.4399.com/flash_fl/more_6_".$j.".htm";
			$contents = file_get_contents($url);
			grab_4399($contents,$pattern_field2,$pattern_li,$leixing);
		}
	}

	if( $i == 3)
	{
		$leixing = "棋牌";
		$url = "http://www.4399.com/flash_fl/7_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);

		$url = "http://www.4399.com/flash_fl/more_7_2.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field2,$pattern_li,$leixing);

	}

	if( $i == 4)
	{
		$leixing = "策略";
		$url = "http://www.4399.com/flash_fl/8_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);

		for( $j = 2;$j<=8;$j++)
		{
			$url = "http://www.4399.com/flash_fl/more_8_".$j.".htm";
			$contents = file_get_contents($url);
			grab_4399($contents,$pattern_field2,$pattern_li,$leixing);
		}
	}
	
	if( $i == 5)
	{
		$leixing = "休闲";
		$url = "http://www.4399.com/flash_fl/12_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);

		for( $j = 2;$j<=25;$j++)
		{
			$url = "http://www.4399.com/flash_fl/more_12_".$j.".htm";
			$contents = file_get_contents($url);
			grab_4399($contents,$pattern_field2,$pattern_li,$leixing);
		}

	}

	if( $i== 6) 
	{
		$leixing = "装扮";
		$url = "http://www.4399.com/flash_fl/16_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);

		for($j = 2 ;$j<=45;$j++)
		{
			$url = "http://www.4399.com/flash_fl/more_16_".$j.".htm";
			$contents = file_get_contents($url);
			grab_4399($contents,$pattern_field2,$pattern_li,$leixing);
		}
	}

	if( $i == 7 )
	{
		$leixing = "儿童";
		$url = "http://www.4399.com/flash_fl/13_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);

		for($j = 2;$j<=8;$j++)
		{
			$url = "http://www.4399.com/flash_fl/more_13_".$j.".htm";
			$contents = file_get_contents($url);
			grab_4399($contents,$pattern_field2,$pattern_li,$leixing);
		}
	}

	if( $i == 8)
	{
		$leixing = "益智";
		$url = "http://www.4399.com/flash_fl/5_1.htm";
		$contents = file_get_contents($url);
		grab_4399($contents,$pattern_field1,$pattern_li,$leixing);

		for($j=2;$j<=59;$j++)
		{
			$url = "http://www.4399.com/flash_fl/more_5_".$j.".htm";
			$contents = file_get_contents($url);
			grab_4399($contents,$pattern_field2,$pattern_li,$leixing);
		}
	}
	
}


?>