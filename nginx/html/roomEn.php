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
        	'mysql:host=mysql;dbname=cocovision;charset=utf8',
        	'webuser',
        	'th1117'
    	);
    	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}catch(PDOException $Exception){
    	die('接続エラー：' .$Exception->getMessage());
	}
	$room = $_POST['room']; //mainページで選択した部屋名取得
	if($room != "-"){
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
    		if($sensorvalues['cong'] == -1){
    			$sensorvalues['cong'] = "計測なし";
    		}
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
		try{
    		$sql = "SELECT rec_time FROM $tablename WHERE id = (SELECT MAX(id) FROM $tablename);";
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
		var co2 = '<?php echo $sensorvalues['co2']; ?>';
		var temp = '<?php echo $sensorvalues['temp']; ?>';
		var humi = '<?php echo $sensorvalues['humi']; ?>';
		var cong = '<?php echo $sensorvalues['cong']; ?>';
		var roomname = '<?php echo $room; ?>';
		var refreshtime = new Date('<?php echo $rec_time; ?>');
		refreshtime.setMinutes(refreshtime.getMinutes() + 2);
		refreshtime.setSeconds(refreshtime.getSeconds() + 3);
		var oldtime = new Date();
		oldtime.setMinutes(oldtime.getMinutes() + 2);
		oldtime.setSeconds(oldtime.getSeconds() + 7);
		var br = [];
		var p = [];
		for(var i = 0; i < 10; i++){
			br[i] = document.createElement('br');
			p[i] = document.createElement('p');
		}
		var co2text = document.createTextNode('CO2: ' + co2 + 'ppm'); //HTMLに追加したい文字はcreateTextNodeで作成する必要あり。
		var temptext = document.createTextNode('温度: ' + temp + "℃");
		var humitext = document.createTextNode('湿度: ' + humi + "%");
		if(cong != -1){
			var congtext = document.createTextNode('混雑度: ' + cong);
		} else {
			var congtext = document.createTextNode('混雑度: ' + cong + "%");
		}
		var roomtext = document.createTextNode(roomname);
		var valueDisplay = document.getElementById("sensorValue")
		valueDisplay.appendChild(roomtext);
		valueDisplay.appendChild(br[1]);
		valueDisplay.appendChild(br[2]);
		valueDisplay.appendChild(co2text);
		valueDisplay.appendChild(br[3]);
		valueDisplay.appendChild(p[0]);
		valueDisplay.appendChild(temptext);
		valueDisplay.appendChild(br[4]);
		valueDisplay.appendChild(p[1]);
		valueDisplay.appendChild(humitext);
		valueDisplay.appendChild(br[5]);
		valueDisplay.appendChild(p[2]);
		valueDisplay.appendChild(congtext);
		function pageReload() {
			var nowtime = new Date(); //毎秒現在時刻を更新するためここに置く。
			if(refreshtime < nowtime) { //データベースに格納されている時間＋2分の時間を現在時刻が超えたら&&前の更新から2分経ったら
				location.reload() //ページのリロード
				oldtime = nowtime;
				oldtime.setMinutes(oldtime.getMinutes() + 2);
			}
		}
		setInterval(pageReload, 1000);
	</script>

</body>
</html>
	