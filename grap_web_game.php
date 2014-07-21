<?php

$url = "http://game.51wan.com/51game/baidu/51wan4.htm?a=40001&b=989685&c=100";

$content = file_get_contents($url);


$queue_pic = array();
$queue_name = array();
$queue_type = array();
$queue_description = array();
$queue_url = array();
$pattern = "/<ol\s+class=\"clean\"\s+id=\"list_recommend\">[\s\S]+<\/ol>/";
if(preg_match($pattern,$content,$match)>0)
{
	var_dump($match);
	$pattern2 = "/<li\s+class=\"h\">[\s\S]+<\/li>/U";
	if(preg_match_all($pattern2,$match[0],$match2)>0)
	{
		var_dump($match2);

		$pattern3 = "/<img\s+src=\"[\S\s]+\.jpg\"/";
		$length = count($match2[0]);

		//$pattern3_name = "<img\s+src=\".*\"\s+alt=\".*\">";
		$pattern3_name = "/[\x{4e00}-\x{9fa5}]+/u";
		$pattern3_type = "/<span\s+class=\"commendGame_type\">.*<\/span>/";
		$pattern3_description = "/<span\s+class=\"commendGame_Intro\">.*<\/span>/";
		$pattern3_url = "/<a\s+class=\"link\"\s+href=\".*\"/";

		for($i = 0;$i<$length;$i++)
		{
			if(preg_match($pattern3,$match2[0][$i],$match3)>0)
			{
				//var_dump($match3);
				$arr = explode('"',$match3[0]);
				array_push($queue_pic,$arr[1]);
			}
			else{
				echo "第三次匹配图片失败<br/>";
			}
			
			if(preg_match($pattern3_name,$match2[0][$i],$match3_name)>0)
			{
				//var_dump($match3_name);
				array_push($queue_name,$match3_name[0]);
			}
			else{
				echo "第三次匹配名字失败<br/>";
			}

			if(preg_match($pattern3_type,$match2[0][$i],$match3_type)>0)
			{
				//var_dump($match3_type);
				$type = strip_tags($match3_type[0]);
				array_push($queue_type,$type);
			}
			else{
				echo "第三次匹配类型失败<br/>";
			}

			if(preg_match($pattern3_description,$match2[0][$i],$match3_description)>0)
			{
				//var_dump($match3_description);
				$description = strip_tags($match3_description[0]);
				array_push($queue_description,$description);
				
			}
			else{
				echo "第三次匹配描述失败<br/>";
			}

			if(preg_match($pattern3_url,$match2[0][$i],$match3_url)>0)
			{
				//var_dump($match3_url);
				$pattern_url2 = "/http:.*\s+/";
				
				if(preg_match($pattern_url2,$match3_url[0],$match_url)>0)
				{
					//var_dump($match_url);
					$arr_url = explode('"',$match_url[0]);
					$link = $arr_url[0];
					array_push($queue_url,$link);
				}
				else{
					echo "深度匹配url失败<br/>";
				}
			}
			else{
				echo "第三次匹配URL失败<br/>";
			}




		}
	}
	else{
		echo "第二次匹配失败<br/>";
	}
}
else{
	echo "匹配失败<br/>";
}

echo "<h1>Result_name:</h1>";
var_dump($queue_name);
echo "<h1>Result_pic:</h1>";
var_dump($queue_pic);
echo "<h1>Result_type:</h1>";
var_dump($queue_type);
echo "<h1>Result_description:</h1>";
var_dump($queue_description);
echo "<h1>Result_url:</h1>";
var_dump($queue_url);


$conn = mysql_connect("localhost",'root','root');
if($conn)
{
	$db = "web_game";
	mysql_select_db($db);

	for($i = 0;$i<8;$i++)
	{
		//$sql = "update 51wan set name = '".$queue_name[$i]."' && ";
		$sql = "insert into 51wan(name,leixing,pic,url,description) values('".$queue_name[$i]."','".$queue_type[$i]."','".$queue_pic[$i]."','".$queue_url[$i]."','".$queue_description[$i]."')";
		mysql_query($sql);
		if(mysql_affected_rows()>0)
		{
			echo $i."成功插入一条记录<br/>";
		}
		else{
			echo $i."插入失败<br/>";
		}
	}

}
else{
	echo "mysql conn failed...<br/>";
}


?>