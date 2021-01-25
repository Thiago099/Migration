<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <?php
    ini_set('max_execution_time', 9999);
    ini_set("memory_limit","1024M"); 
     include 'bin/sql.php';
     include 'bin/misc.php';
     include 'bin/database_compare.php';
     $config = json_decode(file_dump('config.json'));
     $program         = $config->program;
     sql::$server     = $config->server;
     sql::$user       = $config->user;
     sql::$password   = $config->password;
     ?>
    <link rel="stylesheet" href="style/main.css">
  </head>
  <body>
<div class="container">

    <form class="" action="" method="get">
      <?php
      include 'components/select_databases.php';
      include 'components/menu.php';
      ?>
    </form>
    <?php
    $path=[
      'Gerar migrações'              => 'generate_migration.php',
      'Gerar banco de referência'    => 'generate_database.php',
    ];
    if(isset($_GET['action']))
    {
      include 'functionalities/'.$path[$_GET['action']];
    }
    // include 'functionalities/codigo_json.php';
    ?>
  </body>
  </div>
</html>
