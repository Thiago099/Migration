<label>Banco</label>
<select class="" name="database">
  <?php
    $db=new sql();
    $r=$db->query('SHOW DATABASES');
    foreach ($r as $i):
    $ii=$i['Database'];
    $display=true;
    switch ($ii)
    {
      case 'information_schema':
      case 'performance_schema':
      case 'phpmyadmin':
      case 'mysql':
      $display=false;
        break;
    }
    if($display):
    ?>
    <option value="<?php echo  $ii?>" <?php if(isset($_GET['database']) && $_GET['database'] == $ii) echo "selected"?>><?php echo $ii ?></option>
  <?php endif;endforeach; ?>
</select>
