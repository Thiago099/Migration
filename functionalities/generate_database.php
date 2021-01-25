<!-- <label>Select</label> -->
<!-- <textarea name="name" rows="40" cols="200" spellcheck="false"> -->
<?php
$database=$_GET['database'];

$target="{$database}_referece";
$db=new sql();
$db->run("DROP DATABASE IF EXISTS `$target`");
$db->run("CREATE DATABASE `$target`");
$db=new sql($target);
foreach (diff($database,$target) as $ii) 
{
    $db->run($ii);
} 


?></textarea>
