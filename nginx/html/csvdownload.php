<?php
       	$item = '"計測時間" ,"CO2","温度","湿度","混雑度",,';
       	$room = $_POST['rooms'];
		$startdate= $_POST['startdate']; 
		$enddate = $_POST['enddate']; 
		$values = $_POST['values'];
		$data = [[[]]];
		$tablename = [];
		$j = 0;
		$csv_data = "";
		$maxlength = 0;
		$confirmation =  ["記録時間", "CO2濃度", "温度", "湿度" ,"混雑度"];
		$check = true;
    try{ //データベース接続
		$dsn =  new PDO('mysql:dbname=cocovision;host=mysql;charset=utf8',//データベース名など
	 		'webuser',//データベースユーザー名
	 		'th1117'//データベースユーザーパスワード
	    	);
		$dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dsn->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    }catch(PDOException $e){
       header("webページ: ./select.php");
       exit;
    }
		for($i = 0; $i < count($room); $i++){
			try{
    			$sql = "SELECT table_name FROM room_info WHERE room_name = '$room[$i]'";
    			$stmh = $dsn->prepare($sql);
    			$stmh->execute();
			}catch(PDOException $Exception){
    			die('接続エラー：' .$Exception->getMessage());
			}
			$tablename[$i] = $stmh->fetchColumn();
		}
		try{
			for($i = 0; $i < count($room); $i++){
				$j = 0;
				$table = $tablename[$i];
    			$sql = "SELECT rec_time, co2, temp , humi, cong FROM $table WHERE rec_time between '$startdate' and '$enddate';";
    			$stmh2 = $dsn->prepare($sql);
    			$stmh2->execute();
    			foreach ( $stmh2 as $row){
					for($k = 0; $k < 5; $k++) {
						$data[$i][$j][$k] = $row[$k]; //$iは部屋、$jは行数、$kは記録時間、温度、湿度、CO2濃度,混雑度。
					}
					$j++;
				}
				if($maxlength < $j){
					$maxlength = $j - 1; 
				}
			}
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
        // データベース接続の切断
        $dsn = null;

	// 出力の設定
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=データ履歴.csv");
	header("Content-Transfer-Encoding: binary");
	
	//csvデータを作成
	if( !empty($data) ){
		for($i = 0; $i < count($room); $i++){
			$csv_data .= $room[$i];
			for($l = 0; $l < count($values) + 2; $l++){
				$csv_data .= ",";
			}
			if($i == count($room)-1){
				$csv_data .= "\n";
			}
		}
		
		for($i = 0; $i < count($room); $i++){//項目名をいれる
			$csv_data .= "計測時間";
			for($l = 0; $l < count($values); $l++){
				$csv_data .= "," .$values[$l];
				if($l == count($values) - 1){
					$csv_data .= ",".",";
				}
			}
			if($i == count($room) - 1){
				$csv_data .= "\n";
			}
		}
		
		for($j = 0; $j < $maxlength; $j++){//データをいれる
			for($i = 0; $i < count($room); $i++){
				for($k = 0; $k < 5; $k++){
					for($l = 0; $l < count($values); $l++){ //チェックが入っている項目が来た時にcheckをtrueにしている。
						if($values[$l] == $confirmation[$k] || $k == 0){
							$check = true;
							break;	
						} else {
							$check = false;
						}
					}
					if($check){
						if(count($data[$i]) - 1 < $j){
							$csv_data .= ",";
						}else{
							$csv_data .= $data[$i][$j][$k];
							if($k == 1){
								$csv_data .= "ppm";
							} else if ($k == 2){
								$csv_data .= "℃";
							} else if ($k == 3){
								$csv_data .= "%";
							} else if ($k == 4){
								$csv_data .= "%";
							}
							$csv_data .= ",";
								
						}
					}
					if($k == 4 && $i == count($room)-1){
						$csv_data .= "\n";
					}else if($k == 4){
						$csv_data .= ",";
					}
				}
			}
		}
	}
	
echo $csv_data;

return;
    ?>