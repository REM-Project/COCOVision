<?php
       	$room = $_POST['rooms']; //select.phpから選択された部屋を受け取っている
		$startdate= $_POST['startdate']; //select.phpで指定した期間(始め)
		$enddate = $_POST['enddate']; //select.phpで指定した期間(終わり)
		$values = $_POST['values']; //select.phpで選択された項目を受け取っている
		$data = [[[]]]; //データベースから受け取るセンサー値などが入る
		$tablename = []; //選択された部屋名に対応したテーブル名が入る
		$j = 0;
		$csv_data = ""; //出力するcsv
		$maxlength = 0; //選択された部屋の中で一番行数が多いものの行数が入る。
		$confirmation =  ["記録時間", "CO2濃度", "温度", "湿度" ,"混雑度"]; //項目名
		$check = true; //選択された項目かどうかを判定するために使用
    try{ //データベース接続
		$dsn =  new PDO('mysql:dbname=cocovision;host=mysql;charset=utf8',//データベース名、接続するPCのIPアドレス、文字コード
	 		'webuser',//データベースユーザー名
	 		'th1117'//データベースユーザーパスワード
	    	);
		$dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dsn->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    }catch(PDOException $e){
       header("webページ: ./select.php");
       exit;
    }
		for($i = 0; $i < count($room); $i++){ //選択された部屋名に対応するテーブル名を取得
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
			for($i = 0; $i < count($room); $i++){ //選択された期間内のデータを取得
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
					$maxlength = $j - 1; //j(行数)が最大の時をmaxlengthに記録
				}
			}
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
        // データベース接続の切断
        $dsn = null;

	// 出力の設定
	// header("Content-Type: text/csv");
	// header("Content-Disposition: attachment; filename=データ履歴.csv");
	// header("Content-Transfer-Encoding: binary");
	
	//csvデータを作成
	if( !empty($data) ){ //センサー値などのデータが空ではないとき
		for($i = 0; $i < count($room); $i++){//一番上に部屋名を入れる。
			$csv_data .= $room[$i];
			for($l = 0; $l < count($values) + 2; $l++){ //それ以降は次の項目名が来るまで空白を挿入
				if($i != count($room) - 1){ //最後の部屋の時は改行を行うため次の処理は行わない
					$csv_data .= ",";
				}
			}
			if($i == count($room)-1){ //最後の部屋の時は改行
				$csv_data .= "\n";
			}
		}
		
		for($i = 0; $i < count($room); $i++){ //項目名をいれる
			$csv_data .= "計測時間";
			for($l = 0; $l < count($values); $l++){ //チェックされた項目の数繰り返す
				$csv_data .= "," .$values[$l]; //チェックされた項目を入れている。
				if($l == count($values) - 1 && $i != count($room) - 1){ //5つめの項目の後に一つ空白を入れるための処理(最後のテーブルの時は改行を行うためこの処理は行わない)
					$csv_data .= ",".",";
				}
			}
			if($i == count($room) - 1){ //最後の部屋の時は改行
				$csv_data .= "\n";
			}
		}
		//センサー値などのデータをいれる。
		for($j = 0; $j < $maxlength; $j++){ //jは行数
			for($i = 0; $i < count($room); $i++){ //iはテーブル
				for($k = 0; $k < 5; $k++){ //kは項目
					for($l = 0; $l < count($values); $l++){ //項目のチェックが入っているものと、項目が入っている配列が一致した時に変数checkをtrueにしてbreakを入れている。
						if($values[$l] == $confirmation[$k] || $k == 0){
							$check = true;
							break;	
						} else {
							$check = false;
						}
					}
					if($check){ //項目のチェックが入っている場合以下の処理を行う
						if(count($data[$i]) - 1 >= $j){ //dataがない場合は処理を行わない(同じ期間でもテーブルごとに入っているデータの数が違う場合があるためこれが必要になる。
							$csv_data .= $data[$i][$j][$k]; //センサー値を入れる
							if($k == 1){ //CO2の時はppm、温度は℃、湿度と混雑度は%を入れる。
								$csv_data .= "ppm";
							} else if ($k == 2){
								$csv_data .= "℃";
							} else if ($k == 3){
								$csv_data .= "%";
							} else if ($k == 4){
								$csv_data .= "%";
							}
							if($i != count($room) - 1 || $k != 4){
								$csv_data .= ","; //次のセルに移動
							}
						}
					}
					if($k == 4 && $i == count($room)-1){ //最後の項目の時かつ最後の部屋のデータを入れ終わったら改行
						$csv_data .= "\n";
					}else if($k == 4){ //最後の部屋のデータではなく、そのテーブルの最後の項目を入れ終わったときは空白のセルを入れる。
						$csv_data .= ",";
					}
				}
			}
		}
	}
	echo $csv_data; //csv出力
	return; //select.phpにリダイレクト
?>