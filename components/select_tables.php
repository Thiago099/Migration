<?php
if(isset($_GET['database'])):
 ?>
  <label>Tabela</label>
  <select class="" name="table">
  <?php
  $db=new sql($_GET['database']);
  $r=$db->query("SHOW TABLES");
  foreach ($r as $i):
    $ii=$i["Tables_in_$_GET[database]"];
    ?>
    <option value="<?php echo  $ii?>" <?php if(isset($_GET['table']) && $_GET['table'] == $ii) echo "selected"?>><?php echo $ii ?></option>
  <?php endforeach;?>
  </select>

<?php
endif;
 ?>
