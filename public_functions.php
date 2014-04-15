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

	function array_iconv($data, $input = 'gbk', $output = 'utf-8')  //对数组和字符串进行编码转换
	{
		if(!is_array($data))
		{
			return iconv($input, $output, $data);
		}else
		{
			foreach ($data as $key=>$val)
			{
				if(is_array($val))
				{
					$data[$key] = array_iconv($val, $input, $output);
				}else
				{
					$data[$key] = iconv($input, $output, $val);
				}
			}
			return $data;
		}
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

	function cgmdate( $timestamp = "", $format = "n-d H:i", $convert = 1 )  //格式化时间 来自：手游江湖 如：3小时前
	{
		global $timeoffset;
		$todaytime = strtotime( "today" );
		$timeoffset = $timeoffset ? $timeoffset : 8;
		$timeformat = 'H:i';
		$s = gmdate( $format, $timestamp + $timeoffset * 3600);
		if ( !$convert )
		{
			return $s;
		}
		$lang = array
			(
				0 => '前',
				1 => '天',
				2 => '前天',
				3 => '昨天',
				4 => '今天',
				5 => '小时',
				6 => '半',
				7 => '分',
				8 => '秒',
				9 => '刚才'
			);
		$timenow = time();
		$time = $timenow - $timestamp;
		if ( $todaytime <= $timestamp )
		{
			if ( 10800 < $time )
			{		
				$d = date('n-d H:i',$timestamp);
				return $lang[4]."&nbsp;".gmdate( $timeformat, $timestamp + $timeoffset * 3600);
			}
			if ( 3600 < $time )
			{
				return intval( $time / 3600 )."&nbsp;".$lang[5].$lang[0];
			}
			if ( 1800 < $time )
			{
				return $lang[6].$lang[5].$lang[0];
			}
			if ( 60 < $time )
			{
				return intval( $time / 60 )."&nbsp;".$lang[7].$lang[0];
			}
			if ( 0 < $time )
			{
				return $time."&nbsp;".$lang[8].$lang[0];
			}
			if ( $time == 0 )
			{
				return $lang[9];
			}
			return $s;
		}
		if ( 0 <= ( $days = intval( ( $todaytime - $timestamp ) / 86400 ) ) && $days < 2 )
		{
			if ( $days == 0 )
			{
				return $lang[3]."&nbsp;".gmdate( $timeformat, $timestamp + $timeoffset * 3600);
			}
			if ( $days == 1 )
			{
				return $lang[2]."&nbsp;".gmdate( $timeformat, $timestamp + $timeoffset * 3600);
			}
		}
		else
		{
			return $s;
		}
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

	function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE)   //远程打开一个文件  来自：Discuz 7
	{
		$return = '';
		$matches = parse_url($url);
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = !empty($matches['port']) ? $matches['port'] : 80;

		if($post)
		{
			$out = "POST $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= 'Content-Length: '.strlen($post)."\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
			$out .= $post;
		}
		else
		{
			$out = "GET $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
		}
		$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
		if(!$fp)
		{
			return '';
		}else
		{
			stream_set_blocking($fp, $block);
			stream_set_timeout($fp, $timeout);
			@fwrite($fp, $out);
			$status = stream_get_meta_data($fp);
			if(!$status['timed_out'])
			{
				while(!feof($fp))
				{
					if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n"))
					{
						break;
					}
				}

				$stop = false;
				while(!feof($fp) && !$stop)
				{
					$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
					$return .= $data;
					if($limit)
					{
						$limit -= strlen($data);
						$stop = $limit <= 0;
					}
				}
			}
			@fclose($fp);
			return $return;
		}
	}

	function dheader($string, $replace = true, $http_response_code = 0)  //重写header，来自Discuz
	{
		$string = str_replace(array("\r", "\n"), array('', ''), $string);
		if(empty($http_response_code) || PHP_VERSION < '4.3' )
		{
			@header($string, $replace);
		}else
		{
			@header($string, $replace, $http_response_code);
		}
		if(preg_match('/^\s*location:/is', $string))
		{
			exit();
		}
	}

	function fileext($filename) //获取扩展名 来自：AKCMS
	{
		$ext = strtolower(trim(substr(strrchr($filename, '.'), 1)));
		$offset = strpos($ext, '?');
		if($offset !== false)
		{
			return substr($ext, 0, $offset);
		}else
		{
			return $ext;
		}
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

	function recursive_mkdir($dirname)  //  循环创建目录  来自AKCMS
	{
		$dirname = str_replace('\\', '/', $dirname);
		$a_path = explode('/', $dirname);
		if(count($a_path) == 0)
		{
			mkdir($dirname);
		}else
		{
			array_pop($a_path);
			$path = @implode('/', $a_path);
			if(is_dir($path . '/'))
			{
				@mkdir($dirname);
			}else
			{
				ak_mkdir($path);
				@mkdir($dirname);
			}
		}
	}

	function fl_touch($file)  //创建一个空白文件
	{
		$dir = dirname($file);
		recursive_mkdir($dir);
		@touch($file);
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

	function gbk_addslashes($text)  //gbk字符串 转义
	{
		if(!function_exists('mb_strpos')) return addslashes($text);
		if(strpos($text, '\\') === false) return addslashes($text);
		$ok = '';
		while(1)
		{
			$i = mb_strpos($text, chr(92), 0, 'GBK');
			if($i === false) break;
			$t = mb_substr($text, 0, $i, 'GBK').chr(92).chr(92);
			$text = substr($text, strlen($t) - 1);
			$ok .= $t;
		}
		$text = $ok.$text;
		$text = str_replace(chr(39), chr(92).chr(39), $text);
		$text = str_replace(chr(34), chr(92).chr(34), $text);
		return $text;
	}

	function gbk_stripslashes($text)  //gbk字符串 反转义
	{
		$text = str_replace(chr(92).chr(34), chr(34), $text);
		$text = str_replace(chr(92).chr(39), chr(39), $text);
		$ok = '';
		while(1)
		{
			$i = mb_strpos($text, chr(92).chr(92), 0, 'GBK');
			if($i === false) break;
			$t = mb_substr($text, 0, $i, 'GBK').chr(92);
			$text = substr($text, strlen($t) + 1);
			$ok .= $t;
		}
		$text = $ok.$text;
		return $text;
	}

	function isemail($email) //检测是否为电子邮件地址
	{
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}

	function is_utf8($string)  //检测是否为UTF-8字符串 来自：PHPCMS
	{
		return preg_match('%^(?:
					[\x09\x0A\x0D\x20-\x7E] # ASCII
					| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
					| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
					| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
					| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
					| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
					| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
					| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
					)*$%xs', $string);
	}

	function mysql_addslashes($value, $charset = 'utf-8')  //转义SQL语句的中的value
	{
		if(is_array($value))
		{
			foreach($value as $k => $v)
			{
				$value[$k] = mysql_addslashes($v);
			}
		}else
		{
			if($charset == 'gbk')
			{
				$value = gbk_addslashes($value);
			}else
			{
				if(function_exists('mysql_real_escape_string'))
				{
					$value = mysql_real_escape_string($value);
				}else
				{
					$value = addslashes($value);
				}
			}
		}
		return $value;
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

	function stric($k,$charset ='UTF-8')  //转码
	{
		$nk = ($contentscharset = mb_detect_encoding($k, "ASCII, UTF-8, GB2312, GBK")) == "$charset" ? $k : iconv($contentscharset, "$charset", $k);
		return $nk;
	}

	function str_exists($string, $find)  //查询字符是否存在于某字符串  来自：PHPCMS
	{
		return !(strpos($string, $find) === FALSE);
	}

	function ssetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false)  //重写setcookie
	{
		global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
		$var = ($prefix ? $cookiepre : '').$var;
		if($value == '' || $life < 0)
		{
			$value = '';
			$life = -1;
		}
		$life = $life > 0 ? $timestamp + $life : ($life < 0 ? $timestamp - 31536000 : 0);
		$path = $httponly && PHP_VERSION < '5.2.0' ? "$cookiepath; HttpOnly" : $cookiepath;
		$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
		if(PHP_VERSION < '5.2.0')
		{
			setcookie($var, $value, $life, $path, $cookiedomain, $secure);
		}else
		{
			setcookie($var, $value, $life, $path, $cookiedomain, $secure, $httponly);
		}
	}


	function timediff($start,$end)  //计算时差  时间戳
	{
		return ceil(($end - $start) / 86400);
	}

	function utf8_trim($str) //UTF8字符串整齐化
	{
		$hex = '';
		$len = strlen($str) - 1;
		for($i = $len; $i >= 0; $i -= 1)
		{
			$ch = ord($str[$i]);
			$hex .= " $ch";
			if(($ch & 128) == 0 || ($ch & 192) == 192)
			{
				return substr($str, 0, $i);
			}
		}
		return $str . $hex;
	}

	function writeover($fileName, $data, $method = 'rb+', $ifLock = true, $ifChmod = true) //追加写入文件 来自：PHPWIND
	{
		@fl_touch($fileName);
		$handle = fopen($fileName, $method);
		$ifLock && flock($handle, LOCK_EX);
		$writeCheck = fwrite($handle, $data);
		$method == 'rb+' && ftruncate($handle, strlen($data));
		fclose($handle);
		$ifChmod && @chmod($fileName, 0777);
		return $writeCheck;
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
