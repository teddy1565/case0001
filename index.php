<?php
  header('Access-Control-Allow-Origin:*');
  header('Access-Control-Allow-Headers:*');
  header('Access-Control-Allow-Methods:*');
?>
<?php
  class mysqlFC{
    private $account;
    private $pwd;
    private $databaseName = "jobs";
    private $host = "127.0.0.1";
    private $port= 3306;
    private $DBlink;
    private $tableName = "topicTable";
    private $linkState = "sql connect fail";
    //connect($dbname,$dbhost,$account[option],$pwd[option],$port[option]);
    function __call($name,$args){
      $argsLength = count($args);
      if($name=="login"){
        if($argsLength==0){
          $this->account="root";
          $this->pwd="123456";
        }else if($argsLength==2){
          $this->account=$args[0];
          $this->pwd=$args[1];
        }
      }else if($name=="connect"){
        if($argsLength==2){
          $this->login();
        }else if($argsLength==4||$argsLength==5){
          $this->login($args[2],$args[3]);
        }else{
          return 0;
        }
        if($argsLength==5){
          $this->port=args[4];
        }
        $this->host = $args[0];
        $this->databaseName = $args[1];
        $this->DBlink = mysqli_connect($this->host,$this->account,$this->pwd,$this->databaseName,$this->port) or die("sql connect error");
        $result = mysqli_query($this->DBlink,"SET NAMES 'utf8'") or die("sql connect error");
        if($result==1) {$this->linkState = "connected!";}
        else{$this->linkState = "connect ERROR";}
      }
    }
    function get_sql_all_file(){//
        $mysql_query_result = mysqli_query($this->DBlink,"SELECT * FROM $this->tableName");
        return $this->dataResult($mysql_query_result);
    }
    function get_a_column($column){//only get a rows data
      $mysql_query_result = mysqli_query($this->DBlink,"SELTCT $column FROM $this->tableName");
      return $this->dataResult($mysql_query_result);
    }
    function sql_query_and_response($query){//get the sqlTable  all data
      $mysql_query_result = mysqli_query($this->DBlink,$query);
      return $this->dataResult($mysql_query_result);
    }
    function sql_delet_rows($id,$topic){//delete a data
      $mysql_query_result = mysqli_query($this->DBlink,"DELETE FROM $this->tableName WHERE id=$id AND topic=$topic");
      return $this->sql_get_all_data();
    }
    function sql_add_new_rows($topic,$author,$update_when,$content){//add new data
      //echo "$id\t$topic\t$author\t$update_when\t$content";//for debug
      $mysql_query_result = mysqli_query($this->DBlink,"INSERT INTO jobs.topicTable(topic,author,update_when,content) VALUE($topic,$author,$update_when,$content)");
      return $this->sql_get_all_data();
    }
    function dataResult($mysql_query_result){//get sql status
      $result = Array();
      $index = 0;
      while($mysql_fetch_assoc_result = mysqli_fetch_assoc($mysql_query_result)):
        $result[$index] = $mysql_fetch_assoc_result;
        $index++;
      endwhile;
      return $result;
    }
    function set_table_name($rename){//change the defined target TableName
      $this->tableName = $rename;
    }
    function sql_get_all_data(){ //the function = get_sql_all_file() because i lazy to change varable name
      return $this->get_sql_all_file();
    }
  }
?>
<?php
  //mysqlFC->connect($dbname,$dbhost,$account[option],$pwd[option],$port[option]); before any operating use this function connect to mysql
  $data_base = new mysqlFC();
  $data_base->connect("127.0.0.1","jobs");
  //$test->sql_add_new_rows("100","2","3","2020-08-07","5");
  if($_GET["editPage_onload"]==true){
    //response sql data
    echo responseSQLTable($data_base);
  }
  function responseSQLTable($data_base){
    $json = '[';
    $data = $data_base->sql_get_all_data();
    $colName = array("id","topic","author","update_when","content");
    foreach($data as $key => $value){
      $json = $json./*'"'.$key.'":'.*/'{';
      $i=0;
      foreach($value as $k=>$v){
        $urlV = $v;
        $json = $json.'"'.$colName[$i].'"'.":".'"'.$urlV.'",';
        $i++;
      }
      $json = substr($json,0,-1);
      $json = $json.'},';
    }
    $json = substr($json,0,-1);
    $json = $json.']';
    $json = json_encode($json);
    return $json;
  }
  //$data_base->sql_add_new_rows("2","2","3","4","5");
   if($_SERVER["REQUEST_METHOD"]=="POST"){
      $inputid = urlencode($_POST["id"]);
      $inputtopic = urlencode($_POST["topic"]);
      $inputauthor = urlencode($_POST["author"]);
      $inputupdate_when = urlencode($_POST["update_when"]);
      $inputcontent = urlencode($_POST["content"]);
      $con=mysqli_connect("127.0.0.1","root","123456","jobs");
      mysqli_query($con,"INSERT INTO jobs.topicTable(topic,author,update_when,content)VALUE('$inputtopic','$inputauthor','$inputupdate_when','$inputcontent')"); 
      echo "新增成功";
    }
    function showTopic($data_base,$id){
      $result = $data_base->sql_get_all_data();
      foreach($result as $key=>$value){
        foreach($value as $k =>$v){
          if($id==$v){
            return $value;
          }
        }
      }
    }
    if(isset($_GET["id"])){
      $res = showTopic($data_base,$_GET["id"]);
      print_r($res);
    }
?>