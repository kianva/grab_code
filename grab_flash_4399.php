<?php
	set_time_limit(0);
	$pattern_server = "/\/js\/server[\w\d]*\.js/";

	$pattern_js_webserver = "/var\s+webServer\s*=\s*\".*\"/";

	$pattern_str1 = "/var\s+str1\s+=\s+[\'\"]{1}.*[\'\"]{1};/";
	
	$conn = mysql_connect("localhost",'root','root');
	if( $conn )
	{
		$db = "little_game";
		mysql_select_db($db);
		$table = "`4399`";
		$sql = "select * from ".$table." where id >72027";
		$result = mysql_query($sql);
		$mark = 0;
		while( $row = mysql_fetch_array($result) )
		{
			$name = $row['name'] ;
			$url = $row['url'] ;
			$id = $row['id'] ;
			
			$contents = file_get_contents($url);

			if( preg_match($pattern_server,$contents,$match_server) > 0 )
			{
				echo $mark;
				//var_dump($match_server);
				$webserver = $match_server[0];
				$webserver = "http://www.4399.com".$webserver ;
				
				$js_content = file_get_contents($webserver);

				if( preg_match($pattern_js_webserver,$js_content,$match_webserver) > 0 )  //匹配js文件中的webserver
				{
					//var_dump($match_webserver);
					$js_webserver = $match_webserver[0];
					$arr_js = explode('"',$js_webserver);
					//var_dump($arr_js);

					$webserver_address = $arr_js[1];  //最终匹配的webserver 变量地址

					//匹配到js中的webserver 进一步匹配str1
					
					if( preg_match($pattern_str1,$contents,$match_str1) > 0 )
					{
						//var_dump($match_str1);
						$arr_str1 = explode("'",$match_str1[0]);
						//var_dump($arr_str1);
						$str1 = $arr_str1[1] ;

						$final_swf = $webserver_address.$str1 ;
						var_dump($final_swf);

						$sql2 = "update `4399` set flash = '".$final_swf."' where id = '".$id."'";
						mysql_query($sql2);
						if( mysql_affected_rows()> 0)
						{
							echo "write ".$id." succeed!<br/>";
						}
					}
					else{
						echo "match str1 failed...<br/>";
					}
					
				}
				else{
					echo "match webserver in js file failed...<br/>";
				}

			}
			else{
				echo $mark.":match server js file failed...".$url."<br/>";
			}
			$mark++;

		}
	}
	else{
		echo "mysql connection failed...<br/>";
	}

?>