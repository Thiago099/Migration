<label>Select</label>
<textarea name="name" rows="40" cols="200" spellcheck="false">
<?php

$database=$_GET['database'];
$target="{$database}_referece";
$db=new sql($target);

$up = diff($database,$target);
$down = diff($target,$database);


$time=date('YmdHis', time());
$p_migration="$program/application/migrations/{$time}_$time.php";



$code="<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_$time extends CI_Migration 
{
    public function up()
    {
";
foreach ($up as $ii)
{
    $code.="        \$this->db->query(\"$ii\");\n";
    $db->run($ii);
}
$code.="
    }
    public function down()
    {
";
foreach ($down as $ii)
{
    $code.="        \$this->db->query(\"$ii\");\n";
}
$code.="
    }
}
?>";
if(count($up)>0)
{
    $f_migration = fopen($p_migration,'w');
    fwrite_long($f_migration, $code);
    echo "arquivo gerado : $p_migration";
}
else
echo $code;

?></textarea>
