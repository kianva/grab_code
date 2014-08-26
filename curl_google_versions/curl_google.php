<?php
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set("PRC");
set_time_limit(0);

function detect($var){
	$encode = mb_detect_encoding($var);
	
	if( $encode == 'EUC-CN' ) $real_encode = 'GB2312';
	else if( $encode == 'GB2312' || $encode == 'gb2312') $real_encode = 'GB2312';
	else if( $encode == 'GBK' || $encode == 'gbk' ) $real_encode = 'GBK';
	else if( $encode == 'UTF-8' || $encode == 'utf-8'  ) $real_encode = 'UTF-8';
	else { $real_encode = 'GB2312'; }
	return $real_encode;
}

function match_charset($contents){
	
	//$contents = file_get_contents($url);
	$pattern = "/charset=(\")*(UTF-8|GB2312|utf-8|gb2312|gbk|GBK){1}/";
	if( preg_match($pattern,$contents,$match) > 0  )
	{
		//var_dump($match);
		$encode = $match[2]; 
	}
	else{
		$encode = 'UTF-8';
	}
	
	return $encode ;
}

function grab_google($query,$page,$conn)
{
	ob_start();
	$query_backup = $query ;
	$queue_url = array();
	$queue_url2 = array();
	$dir = dirname(__FILE__)."/html/";
	if( !file_exists($dir) )
	{
		mkdir($dir,0777,true);
	}
	$mark = 0;
	
	$useragent = "Mozilla/4.0";
	//$query = urlencode($query);
	$proxy = "http://211.44.42.21:13128";
	
	$pattern = "/<table\s+width=\"100%\"\s+border=\"0\"\s+cellpadding=\"0\"\s+cellspacing=\"0\"\s+bgcolor=\"#D5DDF3\">[\s\S]+<\/table>[\s\S]+<br\s+clear=\"all\">/";
	$pattern_body = "/<body>[\s\S]+<\/body>/";
	$pattern_a = "/<a[\s\S]+>[\s\S]+<\/a>/U";
	$pattern_p = "/<p>[\S\s]+<\/p>/U";

	$pagesize = 10;
	$offset = $pagesize*($page-1);
	$url = "http://www.google.com/search?hl=en&tbo=d&site=&source=hp&q=".$query."&start=".$offset;
	$ch = curl_init ("");

	curl_setopt ($ch, CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_PROXY,$proxy);
	curl_setopt($ch,CURLOPT_PROXYUSERPWD,'wdl:wdl421024');
	curl_setopt ($ch, CURLOPT_USERAGENT, $useragent); // set user agent
	curl_setopt( $ch, CURLOPT_HTTPHEADER,array('X-FORWARDED-FOR:8.8.8.8','CLIENT-IP:8.8.8.8')) ;
	curl_setopt( $ch, CURLOPT_REFERER,"http://www.baidu.com/"); //构造来源
	curl_setopt( $ch, CURLOPT_HEADER,0);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	$output = curl_exec($ch);
	//echo $output;
	curl_close($ch);

	
	if( preg_match($pattern,$output,$match) > 0 )
	{
		$pattern_li = "/<p>[\s\S]+<\/p>/U";
		if(preg_match_all($pattern_li,$match[0],$match_li)>0)
		{
			//var_dump($match_li);
			
			$pattern_url = "/http.*&amp/";

			$length = count($match_li[0]);
			//$length = 1;
			for($i = 0;$i<$length;$i++)
			{
				if(preg_match($pattern_url,$match_li[0][$i],$match_url)>0)	//匹配url
				{
					//var_dump($match_url);
					$arr_url = explode('&amp',$match_url[0]);
					array_push($queue_url,$arr_url[0]);
				}
				else{
					//echo $i."match url failed...<br/>";
				}
			}
		}
		else{
			//echo "匹配li失败<br/>";
		}
	}
	else{
		//echo "match google field failed...<br/>";
	}
	//var_dump($queue_url);
	
	$length = count($queue_url);
	
	for($i=0;$i<$length;$i++)
	{
		$url = $queue_url[$i] ;

		$pattern_url_html = "/http.*\.html/";
		
		if(preg_match($pattern_url_html,$url,$match_url_html)>0)
		{
			//var_dump($match_url_html);
			array_push($queue_url2,$match_url_html[0]);
			
			$url = $match_url_html[0];

			@$contents = file_get_contents($url);
			//接下来获取此URL的encode
			$encode = match_charset($contents);
			
			if(preg_match($pattern_body,$contents,$match_body)>0)//匹配采集的url的body主体
			{
				$body = $match_body[0] ;

				if(preg_match_all($pattern_a,$body,$match_a)>0)
				{
					//var_dump($match_a);
					//echo "<h1>".$url."</h1>";		//输出采集页面的网址
					$length_a = count($match_a[0]);
					
					for( $j =0;$j<$length_a;$j++)
					{
						$string = str_replace($match_a[0][$j],'',$body);
						$body = $string;
					}	

					if(preg_match_all($pattern_p,$string,$match_p)>0)
					{
						//var_dump($match_p);
						$string_p = '';
						$length_p = count($match_p[0]);
						for($p =0;$p<$length_p;$p++)
						{
							//$string_p .= strip_tags($match_p[0][$p],'<p>') ;
							$string_p .= strip_tags($match_p[0][$p],'<div> <p>');
						}

						$mark++;
						if( $mark == 1 )			//两篇整合成一篇 
						{
							$string_p_1 = $string_p ;

							//$encode_article1 = mb_detect_encoding($string_p_1);
							$encode_article1 = $encode;
						}

						if( $mark%2 == 0 )
						{
							$mark == 0;
							//$encode_article2 = mb_detect_encoding($string_p,array('GB2312','GBK','UTF-8')); //检测文章2的编码
							$encode_article2 = $encode ;
							if( $encode_article2 == 'GB2312' || $encode_article2 == 'gb2312' )
							{
								//$string_p = iconv('GB2312','UTF-8',$string_p);
								$real_encode_query = detect( $query );
								//$query = @iconv($real_encode_query,'GB2312',$query);
								$query = mb_convert_encoding($query,'GB2312',$real_encode_query);
								if( $encode_article1 != $encode_article2 )
								{
									//$string_p_1 = @iconv($encode_article1,'GB2312',$string_p_1);
									$string_p_1 = mb_convert_encoding($string_p_1,'GB2312',$encode_article1);
								}
							}
							else if( $encode_article2 == 'GBK' || $encode_article2 == 'gbk' )
							{
								//$string_p = iconv('GBK','UTF-8',$string_p);
								$real_encode_query = detect($query);
								//$query = @iconv('UTF-8','GBK',$query);
								$query = mb_convert_encoding($query,'GBK',$real_encode_query);
								if( $encode_article1  != $encode_article2 )
								{
									//$sting_p_1 = @iconv($encode_article1,'GBK',$string_p_1);
									$string_p_1 = mb_convert_encoding($string_p_1,'GBK',$encode_article1);
								}
							}
							else if( $encode_article2 == 'UTF-8' || $encode_article2 == 'utf-8' )
							{
								//$query = iconv('UTF-8','GB2312',$query);
								$query = $query_backup ;
								if( $encode_article1 != $encode_article2)
								{
									//$string_p_1 = @iconv($encode_article1,"UTF-8",$string_p_1);
									$string_p_1 = mb_convert_encoding($string_p_1,'UTF-8',$encode_article1);
								}
							}
							else{
								//$string_p = iconv('GB2312','UTF-8',$string_p);
								$real_encode_query = detect($query);
								//$query = @iconv($real_encode_query,'GB2312',$query);
								$query = mb_convert_encoding($query,$encode_article2,$real_encode_query);
								$real_encode_query = detect($query);
								if( $encode_article1 != $encode_article2 )
								{
									//$string_p_1 = @iconv($encode_article1,'GB2312',$string_p_1);
									$string_p_1 = mb_convert_encoding($string_p_1,$encode_article2,$encode_article1);
								}
							}

							//$string_p_1 = $string_p ; 08.21注释
							$string_p = $string_p_1.$string_p ;
							
							//$meta = detect( $string_p ); //查询整合一起的两篇文章的编码
							$meta = $encode_article2;
							
							$encode_query1= '';
							$encode_query1 = detect($query);
	?>
							<html>
							<head>
								<?php if( isset($meta) ) { ?><meta charset = '<?php echo $meta; ?>'> <?php } ?>
								<title>
									<?php
										if( $encode_query1 != $meta )
										{
											//echo @iconv("UTF-8",$meta,$query);

											echo mb_convert_encoding($query,$meta,$encode_query1);
											//echo $query;
										}
										else{
											echo $query;
										}
									?>
								</title>
							</head>
							<body>
	<?php
							if( $encode_query1 != $meta )
							{
								//echo "<center><h3>".iconv($encode_query1,$meta,$query)."</h3></center>";
								//echo "<center><h2>".$query_backup."</h2></center>";
								echo "<center><h2>".mb_convert_encoding($query,$meta,$encode_query1)."</h2></center>";
							}
							else{
								//echo "<center><h3>".$query_backup."</h3></center>";
								echo "<center><h3>".$query."</h3></center>";
							}
							//08.20判断编码,再输出
							echo $string_p ;
	?>
							</body>
							</html>
	<?php			
							$ob_output = ob_get_contents();
							ob_clean();
							
							$query = @iconv('UTF-8','GB2312',$query_backup);

							$fp = fopen($dir.$query."_".$page."_".$i.".html",'w+');
							fwrite($fp,$ob_output);
							fclose($fp);
							
							if( $conn )
							{
								$time = date("Y-m-d H:i");
								$sql = "insert into article(title,content,time,source) values('".$query_back."','".$string_p."','".$time."','".$url."')";
								mysql_query($sql);
								if( mysql_affected_rows() > 0 )
								{
									$success = '插入成功-'.$query_back."\r\n";
									$fp2 = fopen('d:/logs.txt','a+');
									fwrite($fp2,$success);
									fclose($fp2);
								}
								else{
									$error = "插入失败".$query_back."\r\n";
									$fp3 = fopen("d:/error_log.txt",'a+');
									fwrite($fp3,$error);
									fclose($fp3);
								}
							}
							else{
								echo "连接数据库失败<br/>";
							}
						}
					}
					else{
						//echo "match p failed...<br/>";
					}
				}
				else{
					//echo "match a failed...<br/>";
				}

			}
			else{
				//echo "match body failed...<br/>";
			}
			
		}
		else{
			//echo "match url_html failed...<br/>";
		}
	}
}

$path = "d:/gamelist/key/";

$conn = mysql_connect("localhost",'root','root');
mysql_select_db("google_search");

if( is_dir($path) )
{
	if( $dh = opendir($path) )
	{
		while( ( $file = readdir($dh) ) != false )
		{
			$file = iconv('GB2312','UTF-8',$file);
			if( substr($file,-3) == 'txt' )
			{
				$file_path = $path.$file ;
				$file_path = iconv("UTF-8",'GB2312',$file_path);
				$lines = file($file_path);
			
				foreach( $lines as $value)
				{
					$query = trim($value);
					$query = @iconv('GB2312','UTF-8',$query);

					for( $page = 1;$page< 10;$page++)
					{
						grab_google($query,$page,$conn);
						sleep(30);
					}
				}
			}
		}
	}
}


?>