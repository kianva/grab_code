<?php
set_time_limit(0);
$pattern_field = "/<div\s+class=\"n_box\">[\s\S]+<\/div>/U";

$pattern_start = "/<b\s+class=\"strongb\">.*<\/b>[\s\S]+<b\s+class=\"strongb\">.*<\/b>/";
$pattern_view = "/<b\s+class=\"strongb\">游戏介绍<\/b>[\s\S]+<b\s+class=\"strongb\">游戏目标<\/b>/";
$pattern_target = "/<b\s+class=\"strongb\">游戏目标<\/b>[\s\S]+<\/div>/";

$pattern_p = "/<p>[\s\S]+<\/p>/U";

$conn = mysql_connect("localhost",'root','root');
if( $conn )
{
	mysql_select_db("gamebox");
	$table = "`4399`";
	$sql = "select id,url from ".$table."";
	$result = mysql_query($sql);
	while( $row = mysql_fetch_array($result) )
	{
		$id = $row['id'];
		$url = $row['url'] ;
		$contents = file_get_contents($url);

		if( preg_match_all($pattern_field,$contents,$match_field) > 0 )
		{
			//var_dump($match_field);
		
			//$match_field[0][1] 为目标代码
			$target = $match_field[0][1] ;
			//var_dump($target);
			

			if( preg_match_all($pattern_p ,$target,$match_p) > 0 )
			{
				var_dump($match_p);

				//$length = count( $match_p[0] );
				
				$kaishi =  isset( $match_p[0][0] )? $match_p[0][0]:'' ;
				$jieshao = isset( $match_p[0][1] )? $match_p[0][1]:'' ;
				$mubiao =  isset( $match_p[0][2] )? $match_p[0][2]:'' ;

				$kaishi = strip_tags($kaishi);
				$jieshao = strip_tags($jieshao);
				$mubiao = strip_tags($mubiao);

				$kaishi = iconv('gb2312','utf-8',$kaishi);
				$jieshao = iconv('gb2312','utf-8',$jieshao);
				$mubiao = iconv('gb2312','utf-8',$mubiao);

				//echo $kaishi."<br/>";
				//echo $jieshao."<br/>";
				//echo $mubiao."<br/>";
				
				$sql = "update `4399` set kaishi = '".$kaishi."',jieshao = '".$jieshao."',mubiao = '".$mubiao."' where id = '".$id."'";
				mysql_query($sql);
				if( mysql_affected_rows() > 0 )
				{
					echo $id."插入成功";
				}
				else{
					echo $id."插入失败:( !!!<br/>";
				}
			}
			else{
				echo "match p failed...<br/> ";
			}

		}
		else{
			echo "match field failed...<br/>";
		}
	}
}
else{
	echo "mysql connection failed...<br/>";
}

?>