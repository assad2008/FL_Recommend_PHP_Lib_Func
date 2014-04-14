<?php
/**
* @file public_functions.php
* @synopsis  飞流PHP公共函数
* @author Yee, <rlk002@gmail.com>
* @version 1.0
* @date 2014-04-14 16:10:18
*/

	function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) //RC4 加密 来自：Discuz
	{
		$ckey_length = 4;
		$key = md5($key != '' ? $key : RC4KEY);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++)
		{
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++)
		{
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++)
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if($operation == 'DECODE')
		{
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
			{
				return substr($result, 26);
			}else
			{
				return '';
			}
		}else 
		{
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

	function arrayrtokeystring($arr)  //数组转成字符串
	{
		if(!$arr || count($arr) == 0)
		{
			return false;
		}

		$str = '';
		foreach($arr AS $k=>$v)
		{
			$str .= $k.',';
		}
		$str = substr($str,0,-1);
		return $str;
	}


	function auto_addslashes(&$array)  //转义
	{
		if($array)
		{
			foreach($array as $key => $value)
			{
				if(! is_array ( $value ))
				{
					$array [$key] = addslashes($value);
				}
				else
				{
					auto_addslashes($array [$key]);
				}
			}
		}
	}

	function auto_stripslashes(&$array) //反转义
	{
		if($array)
		{
			foreach($array as $key => $value)
			{
				if(!is_array($value))
				{
					$array[$key] = stripslashes($value);
				}
				else
				{
					auto_stripslashes($array[$key]);
				}
			}
		}
	}

	function bytes_to_string( $bytes )  //byte转为字符串  100000 bytes=>100KB
	{
		if (!preg_match("/^[0-9]+$/", $bytes)) return 0;
		$sizes = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
		$extension = $sizes[0];
		for( $i = 1; ( ( $i < count( $sizes ) ) && ( $bytes >= 1024 ) ); $i++ )
		{
			$bytes /= 1024;
			$extension = $sizes[$i];
		}
		return round( $bytes, 2 ) . ' ' . $extension;
	}

	function create_guidq()  //创建UUID
	{
		$charid = md5(uniqid(mt_rand(), true));
		$hyphen = chr(45);
		$uuid = substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12);
		return $uuid;
	}

	function countfilelines($filepath)   //检测文件有多少行
	{
		$fp = fopen($filepath, "r");
		$line = 0;
		while(fgets($fp)) $line++;
		fclose($fp);
		return $line;
	}

	function cutstr($string, $length, $dot = ' ...')  //截取字符串 来自:Discuz
	{
		global $charset;
		$charset = 'utf-8';
		if(strlen($string) <= $length) 
		{
			return $string;
		}

		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

		$strcut = '';
		if(strtolower($charset) == 'utf-8') 
		{
			$n = $tn = $noc = 0;
			while($n < strlen($string))
		 	{
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) 
				{
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) 
				{
					$tn = 2; $n += 2; $noc += 2;
				} elseif(224 <= $t && $t <= 239) 
				{
					$tn = 3; $n += 3; $noc += 2;
				} elseif(240 <= $t && $t <= 247) 
				{
					$tn = 4; $n += 4; $noc += 2;
				} elseif(248 <= $t && $t <= 251) 
				{
					$tn = 5; $n += 5; $noc += 2;
				} elseif($t == 252 || $t == 253) 
				{
					$tn = 6; $n += 6; $noc += 2;
				} else 
				{
					$n++;
				}

				if($noc >= $length)
			 	{
					break;
				}

			}
			if($noc > $length) 
			{
				$n -= $tn;
			}

			$strcut = substr($string, 0, $n);

		}else
		{
			for($i = 0; $i < $length; $i++) 
			{
				$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			}
		}

		$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

		return $strcut.$dot;
	}

	function dirsize($dir)  //计算目录大小
	{
		$dh = opendir($dir);
		$size = 0;
		while($file = readdir($dh)) 
		{
			if($file != '.' and $file != '..')
		 	{
				$path = $dir."/".$file;
				if(@is_dir($path))
			 	{
					$size += dirsize($path);
				}else
			 	{
					$size += filesize($path);
				}
			}
		}
		@closedir($dh);
		return $size;
	}

	function debug($var = null,$type = 2) //人性化DEBUG
	{
		if($var === NULL)
		{
			$var = $GLOBALS;
		}
		header("Content-type:text/html;charset=utf-8");
		echo '<pre style="word-wrap: break-word;background-color:black;color:white;font-size:13px; border: 2px solid green;padding: 5px;">变量跟踪信息：'."\n";
		if($type == 1)
		{
			var_dump($var);
		}elseif($type == 2)
		{
			print_r($var);
		}
		echo '</pre>';
		exit();
	}

	function fileext($filename)  //获取扩展名
	{
		return trim(substr(strrchr($filename, '.'), 1, 10));
	}

	function ffile_get_contents($url) //重写file_get_contents 增加超时时间
	{
		$ctx = stream_context_create(
		array(   
        'http' => array(   
				'timeout' => 3 //设置一个超时时间，单位为秒   
				)   
			)
		);   
		$r = file_get_contents($url, 0, $ctx);
		unset($ctx);
		return $r;
	}

	function get_pwd_salt() //获得密码加密使用的salt
	{
		return substr(uniqid(rand()), -6);
	}

	function getfilesize($url)  //远程获取文件长度 By YEE
	{
		$url = parse_url($url);
		if($fp = @fsockopen($url['host'],empty($url['port'])?80:$url['port'],$error))
		{
			fputs($fp,"GET ".(empty($url['path'])?'/':$url['path'])." HTTP/1.1\r\n");
			fputs($fp,"Host:$url[host]\r\n\r\n");
			while(!feof($fp))
			{
				$tmp = fgets($fp);
				if(trim($tmp) == '')
				{
					break;
				}else if(preg_match('/Content-Length:(.*)/si',$tmp,$arr))
				{
					return trim($arr[1]);
				}
			}
			return FALSE;
		}else
		{
			return FALSE;
		}
	}

	function get_client_ip()  //获得客户端IP
	{
		static $realip = NULL;
		if ($realip !== NULL)
		{
			return $realip;
		}
		if(isset($_SERVER))
		{
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($arr as $ip)
				{
					$ip = trim($ip);
					if ($ip != 'unknown')
					{
						$realip = $ip;
						break;
					}
				}
			}
			elseif (isset($_SERVER['HTTP_CLIENT_IP']))
			{
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			}
			else
			{
				if (isset($_SERVER['REMOTE_ADDR']))
				{
					$realip = $_SERVER['REMOTE_ADDR'];
				}
				else
				{
					$realip = '0.0.0.0';
				}
			}
		}
		else
		{
			if (getenv('HTTP_X_FORWARDED_FOR'))
			{
				$realip = getenv('HTTP_X_FORWARDED_FOR');
			}
			elseif (getenv('HTTP_CLIENT_IP'))
			{
				$realip = getenv('HTTP_CLIENT_IP');
			}
			else
			{
				$realip = getenv('REMOTE_ADDR');
			}
		}
		preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
		$realip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
		return $realip;
	}

	function isemail($email) //检测是否为电子邮件地址
	{
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}

	function monthsunixtime($mon = false) //得到一个月的时间戳返回
	{
		$arr =array();
		$date = getdate();
		if(!$mon)
		{
			$arr['s'] = mktime(0,0,0,$date['mon'],1,$date['year']);
			$arr['e'] = mktime(23,59,59,$date['mon'],30,$date['year']);
		}else
		{
			$arr['s'] = mktime(0,0,0,$mon,1,$date['year']);
			$arr['e'] = mktime(23,59,59,$mon,31,$date['year']);
		}
		return $arr;
	}

	function random($length, $numeric = 0) //随机数函数 来自:Discuz 7
	{
		PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
		$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++)
		{
			$hash .= $seed[mt_rand(0, $max)];
		}
		return $hash;
	}

	function rumcmdnowait($cmd) //无等待执行一个Linux命令
	{
		pclose(popen($cmd, 'r'));
	}

	function rumcmd($cmd)  //执行一个Linux命令
	{
		passthru($cmd);
	}

	function stric($k,$char ='UTF-8')  //转码
	{
		$nk = ($contentscharset = mb_detect_encoding($k, "ASCII, UTF-8, GB2312, GBK")) == "$char" ? $k : iconv($contentscharset, "$char", $k);
		return $nk;
	}

	function timediff($start,$end)  //计算时差  时间戳
	{
		return ceil(($end - $start) / 86400);
	}

	function xml2array($url, $get_attributes = 1, $priority = 'tag') //解析XML
	{
		$contents = "";
		if (!function_exists('xml_parser_create'))
		{
			return array ();
		}
		$parser = xml_parser_create('');
		function_exists(ffile_get_contents)
		{
			$contents =  ffile_get_contents($url);
		}else
		{
			$contents = file_get_contents($url);
		}
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		if (!$xml_values)
		{
		return False;
		}
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		$current = & $xml_array;
		$repeated_tag_index = array (); 
		foreach ($xml_values as $data)
		{
			unset ($attributes, $value);
			extract($data);
			$result = array ();
			$attributes_data = array ();
			if (isset ($value))
			{
				if ($priority == 'tag')
				{
					$result = $value;
				}
				else
				{
					$result['value'] = $value;
				}
			}
			if (isset ($attributes) and $get_attributes)
			{
				foreach ($attributes as $attr => $val)
				{
					if ($priority == 'tag')
					{
						$attributes_data[$attr] = $val;
					}
					else
					{
						$result['attr'][$attr] = $val;
					}
				}
			}
			if ($type == "open")
			{ 
				$parent[$level -1] = & $current;
				if (!is_array($current) or (!in_array($tag, array_keys($current))))
				{
					$current[$tag] = $result;
					if ($attributes_data)
					{
						$current[$tag . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level] = 1;
					$current = & $current[$tag];
				}
				else
				{
					if (isset ($current[$tag][0]))
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					}
					else
					{ 
						$current[$tag] = array (
							$current[$tag],
							$result
						); 
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if (isset ($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
					}
					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = & $current[$tag][$last_item_index];
				}
			}
			elseif ($type == "complete")
			{
				if (!isset ($current[$tag]))
				{
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
					{
						$current[$tag . '_attr'] = $attributes_data;
					}
				}
				else
				{
					if (isset ($current[$tag][0]) and is_array($current[$tag]))
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					}
					else
					{
						$current[$tag] = array (
							$current[$tag],
							$result
						); 
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes)
						{
							if (isset ($current[$tag . '_attr']))
							{ 
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset ($current[$tag . '_attr']);
							}
							if ($attributes_data)
							{
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			}
			elseif ($type == 'close')
			{
				$current = & $parent[$level -1];
			}
		}
		return ($xml_array);
	}
