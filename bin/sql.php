<?php
class sql
{
  public $conn;
  public static string $server;
  public static string $user;
  public static string $password;

  public function __construct($dbname="")
  {
    $this->conn = new mysqli($this::$server,$this::$user,$this::$password, $dbname);
    if ($this->conn->connect_error)
    {
        die("Connection failed: " . $this->conn->connect_error);
    }
  }
  public function query($sql)
  {
    $ret=[];
    $result = $this->conn->query($sql);
    
    if ($result && $result->num_rows > 0)
        while($row = $result->fetch_assoc())
            array_push($ret,$row);
    return $ret;
  }
  public function run($sql)
  {
    $ret=[];
    $result = $this->conn->query($sql);
  }
  public function close()
  {
    $conn->close();
  }
}
?>
