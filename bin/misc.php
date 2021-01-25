<?php
	function ident($str,$lenght)
	{
      $count=$lenght-strlen($str);
      for ($j=0; $j < $count; $j++)
        $str.=' ';
      return $str;
  	}
	function file_dump($path)
	{
		$file = fopen('config.json', "r");
		$ret=fread($file,filesize('config.json'));
		fclose($file);
		return $ret;
	}
	function exists($database,$table)
	{
		$db = new sql();
		return count($db->query("SELECT TABLE_NAME AS tabela FROM INFORMATION_SCHEMA.TABLES
		WHERE TABLE_SCHEMA = '$database'
		AND  TABLE_NAME = '$table'"))==1;
	}
	function fwrite_long($file,$content)
	{
		$pieces = str_split($content, 1024 * 4);
		foreach ($pieces as $piece)
		{
			fwrite($file, $piece, strlen($piece));
		}
	}
	function match_after(&$c ,$target,$source)
	{
			$source_lenght=count($source);
			$target_lenght=count($target);
			for ($i=0; $i < $source_lenght; $i++) {
				if($i+$c>=$target_lenght) return false;
				if($source[$i]!=$target[$i+$c]) return false;
			}
			$c+=$source_lenght;
			return true;
	}
	function match_before($c,$target,$source)
	{
			$source_lenght=count($source);
			$target_lenght=count($target);
			for ($i=0; $i < $source_lenght; $i++) {
				if($i+$c>=$target_lenght) return false;
				if($source[$i]!=$target[$i+$c]) return false;
			}
			return true;
	}
	function camel($data)
	{
		$ret='';
		foreach ($data as $i)
		{
			$ret.=ucfirst($i).'_';
		}
		$ret=substr($ret, 0, -1);
		return $ret;
	}
?>