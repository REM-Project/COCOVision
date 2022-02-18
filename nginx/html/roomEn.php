<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>メニュー画面</title>
	<link href="css/roomEn.css" rel="stylesheet" type="text/css" media="all">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
	<script src="javascript/clock.js"></script>
</head>
<body>
<?php
	try{
    	$pdo = new PDO( //DB接続
        	'mysql:host=mysql;dbname=cocovision;charset=utf8',//接続先IPアドレス、データベース名、文字コード
        	'webuser', //ユーザー名
        	'th1117' //ユーザーのパスワード
    	);
    	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}catch(PDOException $Exception){
    	die('接続エラー：' .$Exception->getMessage());
	}
	$room = $_POST['room']; //mainページで選択した部屋名取得
	if($room != "-"){ //部屋が選択された場合
		try{
    		$sql = "SELECT table_name FROM room_info WHERE room_name = '$room'"; //取得した部屋名に対応しているテーブル名を取得
    		$stmh = $pdo->prepare($sql);
    		$stmh->execute();
    		$tablename = $stmh->fetchColumn(); //受け取る値が1つしかない場合はこの記述でOK
   		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
    	}
		try{
    		$sql = "SELECT co2, temp , humi,cong FROM $tablename WHERE id = (SELECT MAX(id) FROM $tablename);"; //取得したテーブル名に対応したセンサー値などを取得
    		$stmh = $pdo->prepare($sql);
    		$stmh->execute();
    		$sensorvalues = $stmh->fetch();
    		if($sensorvalues['cong'] == -1){ //congが-1の時はカメラが計測を行っていないときであるため表示を[計測なし]にする。
    			$sensorvalues['cong'] = "計測なし";
    		}
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
		try{
    		$sql = "SELECT rec_time FROM $tablename WHERE id = (SELECT MAX(id) FROM $tablename);"; //最後に追加された時刻を取得
    		$stmh = $pdo->prepare($sql);
    		$stmh->execute();
    		$rec_time = $stmh->fetchColumn(); //受け取る値が1つしかない場合はこの記述でOK
   		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
    	}
	} else {
		$sensorvalues = 0;
	}
$pdo = null; //DB切断
?>
	<span id="realtime"></span>
	<div class="box" id="sensorValue">
	</div>
	<form action="index.php" method="POST">
		<button type="submit" accesskey="s" tabindex="1" id="backbtn">戻る</button>
	</form>
	<script>
		var co2 = '<?php echo $sensorvalues['co2']; ?>'; //phpからco2濃度を取得
		var temp = '<?php echo $sensorvalues['temp']; ?>'; //phpからtempを取得
		var humi = '<?php echo $sensorvalues['humi']; ?>'; //phpからhumiを取得
		var cong = '<?php echo $sensorvalues['cong']; ?>'; //phpからcongを取得
		var roomname = '<?php echo $room; ?>'; //phpから部屋名を取得
		var refreshtime = new Date('<?php echo $rec_time; ?>'); //最後にデータベースに追加された時間をphpより取得
		refreshtime.setMinutes(refreshtime.getMinutes() + 2); //refreshtimeに2分追加
		refreshtime.setSeconds(refreshtime.getSeconds() + 3); //refreshtimeに3秒追加(毎回処理が2分ぴったりで終わらない可能性を考慮して余裕をもって3秒追加している)
		/*var oldtime = new Date(); 
		oldtime.setMinutes(oldtime.getMinutes() + 2);
		oldtime.setSeconds(oldtime.getSeconds() + 7);*/
		var br = [];
		var p = [];
		for(var i = 0; i < 5; i++){
			br[i] = document.createElement('br');
			p[i] = document.createElement('p');
		}
		var co2text = document.createTextNode('CO2: ' + co2 + 'ppm'); //HTMLに追加したい文字はcreateTextNodeで作成する必要あり。
		var temptext = document.createTextNode('温度: ' + temp + "℃");
		var humitext = document.createTextNode('湿度: ' + humi + "%");
		if(cong != -1){ //congが-1のときは[計測なし]を表示するため%を表示しない
			var congtext = document.createTextNode('混雑度: ' + cong);
		} else {//congが-1でないときは以下の処理を行う
			var congtext = document.createTextNode('混雑度: ' + cong + "%");
		}
		var roomtext = document.createTextNode(roomname);
		var valueDisplay = document.getElementById("sensorValue")
		valueDisplay.appendChild(roomtext); //部屋名表示
		valueDisplay.appendChild(br[0]);
		valueDisplay.appendChild(br[1]);
		valueDisplay.appendChild(co2text); //co2濃度を表示
		valueDisplay.appendChild(br[2]);
		valueDisplay.appendChild(p[0]);
		valueDisplay.appendChild(temptext); //温度を表示
		valueDisplay.appendChild(br[3]);
		valueDisplay.appendChild(p[1]);
		valueDisplay.appendChild(humitext); //湿度を表示
		valueDisplay.appendChild(br[4]);
		valueDisplay.appendChild(p[2]);
		valueDisplay.appendChild(congtext); //混雑度を表示
		function pageReload() {
			var nowtime = new Date(); //毎秒現在時刻を更新するためここに置く。
			if(refreshtime < nowtime) { //データベースに格納されている時間＋2分の時間を現在時刻が超えたら&&前の更新から2分経ったら
				location.reload() //ページのリロード
				/*oldtime = nowtime;
				oldtime.setMinutes(oldtime.getMinutes() + 2);*/
			}
		}
		setInterval(pageReload, 1000); //pageReloadを1秒ごとに呼び出す
	</script>

</body>
</html>
	