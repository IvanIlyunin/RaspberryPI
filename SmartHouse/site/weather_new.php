<?php
	function average($arr) {
            $total  = 0;
            $count = count($arr); 
	    if ($count < 1)
               return 0;
	    foreach ($arr as $value) {
	        $total = $total + $value; 
	    }
	    $average = ($total/$count); 
	    return round($average,2);
	}

  function readBMP180() {
  $dbh = new PDO('sqlite:/home/pi/src/dht22/weather.sl3');
  $temperature = array();
  $humidity = array();

  $mode = $_GET["mode"];
  $delta = $_GET["delta"];
  if ($mode==="delta" &&  is_numeric($delta) === FALSE) {
    $mode = "last";
  } else {
    $delta = $delta / 1000;
 }


  $timezone = "'-3 hour'";

  $sql = "select * from weather_bmp180 where time > datetime('now', '-1 hour')";
  $window = 1;
  switch($mode)
  {
     case "hour":
      $sql = "select * from weather_bmp180 where time > datetime('now', '-1 hour')";
      break;
     case "3hour":
      $sql = "select * from weather_bmp180 where time > datetime('now', '-3 hour')";
      break;
    case "today":
      $sql = "select * from weather_bmp180 where time > datetime('now', 'start of day', " . $timezone . ")";
      break;
    case "last":
	$sql = "SELECT * FROM weather_bmp180 order by time desc limit 1";
      break;
    case "delta":
	$sql =  "select * from weather_bmp180 where time > datetime('".$delta."','unixepoch')";
	break;
    case "dbn":
      $sql = "select * from weather_bmp180 where time > datetime('now', '-1 day', "  . $timezone . ")";
      break; 
    case "week":
      $sql = "select * from weather_bmp180 where time > datetime('now', 'weekday 0', '-7 day', "  . $timezone . ")";
      $window = 5;
      break;
    case "wtn":
      $sql = "select * from weather_bmp180 where time > datetime('now', '-7 day', "  . $timezone . ")";
      $window = 5;
      break;
    case "mtn":
      $sql = "select * from weather_bmp180 where time > datetime('now', '-1 month', "  . $timezone . ")";
      $window = 100;
     break;
    case "month":
      $sql = "select * from weather_bmp180 where time > datetime('now', 'start of month', "  . $timezone . ")";
      $window = 100;
     break;
    case "ytn":
      $sql = "select * from weather_bmp180 where time > datetime('now', '-1 year', "  . $timezone . ")";
      $window = 300;
     break;
    case "year":
      $sql = "select * from weather_bmp180 where time > datetime('now', 'start of year', "  . $timezone . ")";
      $window = 300;
     break;
    default: 
      $mode = "last hour (default)";
  }

  $i = 0;
  $temperature_window = array();
  $humidity_window = array();
  $window_counter = 0;

  foreach($dbh->query($sql) as $row)
  {
    $window_counter++;
    $time = new DateTime($row[0], new DateTimezone('UTC'));

    $temperature_window[] =  $row[1];
    $humidity_window[] =  $row[2] *  7.50061561303 ;
    
    if ($window_counter === $window)
    {
	$temperature[] = array($time->getTimestamp()*1000,average($temperature_window));
	$humidity[] = array($time->getTimestamp()*1000,average($humidity_window));

        unset($temperature_window);
        unset($humidity_window);
        $temperature_window = array();
        $humidity_window = array();
        $window_counter = 0;
        $i++;
    }

  
    //$temperature[] = array($time->getTimestamp()*1000, round($row[1],2));
    //$humidity[] = array($time->getTimestamp()*1000, round($row[2],2));
    //$i++;
  }

  if (count($temperature_window)  > 0)
  {
    $temperature[] = array($time->getTimestamp()*1000,average($temperature_window));
    $humidity[] = array($time->getTimestamp()*1000,average($humidity_window));
  }
    return array("mode" => $mode, "count" => $i, "temperature" => $temperature, "pressure" => $humidity/*, "sql" => $sql*/);
  }

  function readDHT022() {
  $dbh = new PDO('sqlite:/home/pi/src/dht22/weather.sl3');
  $temperature = array();
  $humidity = array();

  $mode = $_GET["mode"];
  $delta = $_GET["delta"];
  if ($mode==="delta" &&  is_numeric($delta) === FALSE) {
    $mode = "last";
  } else {
    $delta = $delta / 1000;
 }


  $timezone = "'-3 hour'";

  $sql = "select * from weather where time > datetime('now', '-1 hour')";
  $window = 1;
  switch($mode)
  {
     case "hour":
      $sql = "select * from weather where time > datetime('now', '-1 hour')";
      break;
     case "3hour":
      $sql = "select * from weather where time > datetime('now', '-3 hour')";
      break;
    case "today":
      $sql = "select * from weather where time > datetime('now', 'start of day', " . $timezone . ")";
      break;
    case "last":
	$sql = "SELECT * FROM weather order by time desc limit 1";
      break;
    case "delta":
	$sql =  "select * from weather where time > datetime('".$delta."','unixepoch')";
	break;
    case "dbn":
      $sql = "select * from weather where time > datetime('now', '-1 day', "  . $timezone . ")";
      break; 
    case "week":
      $sql = "select * from weather where time > datetime('now', 'weekday 0', '-7 day', "  . $timezone . ")";
      $window = 5;
      break;
    case "wtn":
      $sql = "select * from weather where time > datetime('now', '-7 day', "  . $timezone . ")";
      $window = 5;
      break;
    case "mtn":
      $sql = "select * from weather where time > datetime('now', '-1 month', "  . $timezone . ")";
      $window = 100;
     break;
    case "month":
      $sql = "select * from weather where time > datetime('now', 'start of month', "  . $timezone . ")";
      $window = 100;
     break;
    case "ytn":
      $sql = "select * from weather where time > datetime('now', '-1 year', "  . $timezone . ")";
      $window = 300;
     break;
    case "year":
      $sql = "select * from weather where time > datetime('now', 'start of year', "  . $timezone . ")";
      $window = 300;
     break;
    default: 
      $mode = "last (default)";
  }

  $i = 0;
  $temperature_window = array();
  $humidity_window = array();
  $window_counter = 0;

  foreach($dbh->query($sql) as $row)
  {
    $window_counter++;
    $time = new DateTime($row[0], new DateTimezone('UTC'));

    $temperature_window[] =  $row[1];
    $humidity_window[] =  $row[2];
    
    if ($window_counter === $window)
    {
	$temperature[] = array($time->getTimestamp()*1000,average($temperature_window));
	$humidity[] = array($time->getTimestamp()*1000,average($humidity_window));

        unset($temperature_window);
        unset($humidity_window);
        $temperature_window = array();
        $humidity_window = array();
        $window_counter = 0;
        $i++;
    }

  
    //$temperature[] = array($time->getTimestamp()*1000, round($row[1],2));
    //$humidity[] = array($time->getTimestamp()*1000, round($row[2],2));
    //$i++;
  }

  if (count($temperature_window)  > 0)
  {
    $temperature[] = array($time->getTimestamp()*1000,average($temperature_window));
    $humidity[] = array($time->getTimestamp()*1000,average($humidity_window));
  }
   return array("mode" => $mode, "count" => $i, "temperature" => $temperature, "humidity" => $humidity/*, "sql" => $sql*/);
 }

  $dht022 = readDHT022();
  $bmp180 = readBMP180();
  $rows = array("mode" => $bmp180["mode"], "count_dht022" => $dht022["count"], "count_bmp180" => $bmp180["count"],  
          "temperature_dht022" => $dht022["temperature"], "temperature_bmp180" => $bmp180["temperature"],
          "humidity" => $dht022["humidity"], "pressure" => $bmp180["pressure"]);
  
  ob_start("ob_gzhandler");
  header('Content-type: application/json');
  echo json_encode($rows, JSON_NUMERIC_CHECK);
  header('Content-Length: ' . ob_get_length());
?>
