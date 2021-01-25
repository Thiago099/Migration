<?php
if(isset($_GET['database'])):
?>

<label>Opções</label>
</div>
  <div class="button_container">
    <div class="button">
    <!-- <input type="submit" value="Selecionar"> -->
    <input type="submit" name="action" value="Gerar banco de referência">
    <input type="submit" name="action" value="Gerar migrações">

    </div>
  </div>
<div class="container">
<?php else: ?>
<label>Opções</label>
<input class="solo" type="submit" value="Selecionar">
<?php
endif;
?>
