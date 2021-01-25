<?php
function diff($source,$target)
{
  $db=new sql();

$source_tables=$db->query("SELECT
TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = '$source'");

$target_tables=$db->query("SELECT
TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = '$target'");
$output=[];
$alter=[];
foreach ($source_tables as $ii)
{
  $source_table = $ii['TABLE_NAME'];
  $found_table=false;
  foreach ($target_tables as $jj)
  {
    $target_table = $jj['TABLE_NAME'];
    if($source_table === $target_table)
    {
      $found_table=true;
      $table=$source_table;
      $source_columns = $db->query("SELECT COLUMN_NAME,IS_NULLABLE,COLUMN_TYPE,EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$source' AND TABLE_NAME='$table'");
      $target_columns = $db->query("SELECT COLUMN_NAME,IS_NULLABLE,COLUMN_TYPE,EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$target' AND TABLE_NAME='$table'");

      $source_fks = $db->query("SELECT i.CONSTRAINT_NAME `constraint`
      FROM information_schema.TABLE_CONSTRAINTS i
      WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND i.TABLE_SCHEMA = '$source'
      AND i.TABLE_NAME = '$table'
      GROUP BY `constraint`;");

      $target_fks = $db->query("SELECT i.CONSTRAINT_NAME `constraint`
      FROM information_schema.TABLE_CONSTRAINTS i
      WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND i.TABLE_SCHEMA = '$source'
      AND i.TABLE_NAME = '$table'
      GROUP BY `constraint`;");

      $source_pks = $db->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$source' AND TABLE_NAME='$table' AND COLUMN_KEY='PRI'");
      $target_pks = $db->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$target' AND TABLE_NAME='$table' AND COLUMN_KEY='PRI'");


      $ret='';
      foreach ($source_columns as $kk)
      {
        $source_column = $kk['COLUMN_NAME'];
        $found_column=false;
        foreach ($target_columns as $ll)
        {
          $target_column = $ll['COLUMN_NAME'];
          if($source_column === $target_column)
          {
            $column=$source_column;
            $found_column=true;
            if
            (
                 $kk['IS_NULLABLE']!==$ll['IS_NULLABLE']
              || $kk['COLUMN_TYPE']!==$ll['COLUMN_TYPE']
              || $kk['EXTRA']!==$ll['EXTRA']
            )
            {
              $null=$kk['IS_NULLABLE']==='YES'?'NULL':'NOT NULL';
              $extra=$kk['EXTRA']!=''?" $kk[EXTRA]":'';
              $ret.=" CHANGE COLUMN `$kk[COLUMN_NAME]` `$kk[COLUMN_NAME]` $kk[COLUMN_TYPE] $null$extra,\n";
            }
            break;
          }
        }
        if(!$found_column)
        {
          $null=$kk['IS_NULLABLE']==='YES'?'NULL':'NOT NULL';
          $extra=$kk['EXTRA']!=''?" $kk[EXTRA]":'';
          $ret.="                  ADD COLUMN `$kk[COLUMN_NAME]` $kk[COLUMN_TYPE] $null$extra,\n";
        }

      }
      foreach ($target_columns as $kk)
      {
        $target_column = $kk['COLUMN_NAME'];
        $found_column=false;
        foreach ($source_columns as $ll)
        {
            $source_column = $ll['COLUMN_NAME'];
            if($source_column === $target_column)
            {
              $found_column=true;
              break;
            }
          }
          if(!$found_column)
          {
            $ret.="                  DROP COLUMN `$target_column`,\n";
          }
        }
        foreach ($target_fks as $kk)
        {
          $target_fk=$kk['constraint'];
          $found_fk=false;
          foreach ($source_fks as $ll)
          {
            $source_fk=$ll['constraint'];
            if($source_fk === $target_fk)
            {
              $found_fk=true;
            }
          }
          if(!$found_fk)
          {
            $ret.="                  DROP FOREIGN KEY `$kk[constraint]`,\n";
          }
        }
        foreach ($source_fks as $kk)
        {
          $source_fk=$kk['constraint'];
          $found_fk=false;
          foreach ($target_fks as $ll)
          {

            $target_fk=$ll['constraint'];
            if($source_fk === $target_fk)
            {
              $found_fk=true;
              break;
            }
          }
          if(!$found_fk)
          {
            $ret.="
            ADD INDEX `$kk[constraint]` (`$kk[column]`),
            ADD CONSTRAINT `$kk[constraint]` FOREIGN KEY (`$kk[column]`) REFERENCES `$kk[schema]`.`$kk[table]` (`$kk[key]`) ON UPDATE $kk[UPDATE_RULE] ON DELETE $kk[DELETE_RULE],\n";
          }
        }
        foreach ($target_pks as $ii) 
        {
          $found_pk=false;
          foreach ($source_pks as $jj) 
          {
            if($ii['COLUMN_NAME']===$jj['COLUMN_NAME'])
            {
              $found_pk=true;
              break;
            }
          }
          if(!$found_pk)
          {
            $ret.="                  DROP PRIMARY KEY,\n";;
          }
        }
        foreach ($source_pks as $ii) 
        {
          $found_pk=false;
          foreach ($target_pks as $jj) 
          {
            if($ii['COLUMN_NAME']===$jj['COLUMN_NAME'])
            {
              $found_pk=true;
              break;
            }
          }
          if(!$found_pk)
          {
            $ret.="                  ADD PRIMARY KEY (`$ii[COLUMN_NAME]`),\n";
          }
      }
      if($ret!=='')
      {
        $ret=substr($ret, 0, -2);
        $alter[]="
                  ALTER TABLE `$table`\n$ret;\n        ";
      }

    }
  }
  if(!$found_table)
  {
    $database=$source;
    $table=$source_table;
    $db=new sql('information_schema');
    $fields = $db->query("SELECT COLUMN_NAME,IS_NULLABLE,COLUMN_TYPE,EXTRA FROM COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$table'");
    $primary = $db->query("SELECT COLUMN_NAME FROM COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$table' AND COLUMN_KEY='PRI'");
    $fks = $db->query("SELECT k.CONSTRAINT_NAME `constraint`,k.CONSTRAINT_SCHEMA `schema`, COLUMN_NAME `column`,k.REFERENCED_TABLE_NAME `table`, k.REFERENCED_COLUMN_NAME `key`, r.UPDATE_RULE, r.DELETE_RULE
        FROM information_schema.TABLE_CONSTRAINTS i
        LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
        LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON i.CONSTRAINT_NAME = r.CONSTRAINT_NAME
        WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND i.TABLE_SCHEMA = '$database'
        AND i.TABLE_NAME = '$table'
        GROUP BY K.COLUMN_NAME;");
    $more=$db->query("SELECT ENGINE,TABLE_COLLATION,TABLE_COMMENT FROM TABLES  WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$table'")[0];
    $comment='';
    if($more['TABLE_COMMENT']!='')$comment="\n      COMMENT='$more[TABLE_COMMENT]'";
    $ret="
          CREATE TABLE `$table`
          (\n";
    foreach ($fields as $i)
    {
      $null=$i['IS_NULLABLE']==='YES'?'NULL':'NOT NULL';
      $extra=$i['EXTRA']!=''?" $i[EXTRA]":'';
      $ret.="              `$i[COLUMN_NAME]` $i[COLUMN_TYPE] $null$extra,\n";
    }

    if(count($primary)>0)
    {
      $ret.='              PRIMARY KEY (';
      foreach ($primary as $i)
      {
        $ret.="`$i[COLUMN_NAME]`, ";
      }
      $ret=substr($ret, 0, -2);
      $ret.=") USING BTREE,\n";
    }
    if(!empty($fks))
    {
      $ret2="
              ALTER TABLE `$table`\n";
      foreach ($fks as $i)
      {
        $ret2.="              ADD INDEX `$i[constraint]` (`$i[column]`) USING BTREE,\n";
      }
      foreach ($fks as $i)
      {
        $ret2.="              ADD CONSTRAINT `$i[constraint]` FOREIGN KEY (`$i[column]`) REFERENCES `$i[table]` (`$i[key]`) ON UPDATE $i[UPDATE_RULE] ON DELETE $i[DELETE_RULE],\n";
      }
      $ret2=substr($ret2, 0, -2).";\n        ";
      $alter[]=$ret2;
    }
    $ret=substr($ret, 0, -2);
    $ret.="
          )
          COLLATE='$more[TABLE_COLLATION]'
          ENGINE=$more[ENGINE]$comment;\n       ";
    $output[]= $ret;
  }
}
foreach ($target_tables as $ii)
{
  $target_table = $ii['TABLE_NAME'];
  $found_table=false;
  foreach ($source_tables as $jj)
  {
    $source_table = $jj['TABLE_NAME'];
    if($source_table === $target_table)
    {
      $found_table=true;
    }
  }
  if(!$found_table)
  {
    $output[]= "DROP TABLE `$target_table`;";
  }
}

return array_merge($output,$alter);
}
?>