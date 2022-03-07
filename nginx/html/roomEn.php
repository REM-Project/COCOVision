<!--  部屋環境確認ページ
読み込んでいるファイル
 javascript : clock.js
 css : roomEn.css
 php : DBconnect.php
-->

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>メニュー画面</title>
	<link href="css/roomEn.css" rel="stylesheet" type="text/css" media="all">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0"> <!-- スマホとPCからの接続に合わせたレスポンシブ設定 -->
	<script src="javascript/clock.js"></script> <!-- 時計の読み込み -->
</head>
<body>
<?php
	require("php/DBconnect.php"); //データベース接続関数の読み込み
	$pdo = dbconnect(); //データベース接続
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
	<span id="realtime"></span> <!-- 時計の表示場所 -->
	<div class="box" id="sensorValue"></div> <!-- 測定値の周りの枠線の表示場所 -->
	<form action="index.php" method="POST"> <!-- 戻るボタンを押すとメインページに遷移 -->
		<button type="submit" accesskey="s" tabindex="1" id="backbtn">戻る</button> <!-- 戻るボタン生成 -->
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

		var br = [];
		var p = [];
		for(var i = 0; i < 5; i++){
			br[i] = document.createElement('br');
			p[i] = document.createElement('p');
		}
		var co2text = document.createTextNode('CO2: ' + Math.round(co2) + 'ppm'); //HTMLに追加したい文字はcreateTextNodeで作成する必要あり。
		var temptext = document.createTextNode('温度: ' + Math.round(temp * 10) / 10 + "℃");
		var humitext = document.createTextNode('湿度: ' + Math.round(humi * 10) / 10 + "%");
		if(cong == ""){
			var congtext = document.createTextNode('混雑度: 計測無し');
		} else {
			var congtext = document.createTextNode('混雑度: ' + Math.round(cong * 10) / 10 + '%');
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
	