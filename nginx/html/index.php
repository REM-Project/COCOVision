<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>メニュー画面</title>
<link href="css/index.css" rel="stylesheet" type="text/css" media="all">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
<script src="javascript/clock.js"></script>
</head>
<body>
<?php
try{
    $pdo = new PDO( //DB接続
        'mysql:host=mysql;dbname=cocovision;charset=utf8', //接続先IPアドレス、データベース名、文字セット
        'webuser', //接続ユーザー
        'th1117' //ユーザーのパスワード
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}catch(PDOException $Exception){
    die('接続エラー：' .$Exception->getMessage());
}
try{
    $sql = "SELECT room_name FROM room_info"; //部屋名取得
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
}catch(PDOException $Exception){
    die('接続エラー：' .$Exception->getMessage());
}
$i = 0;
$roomname = []; //部屋名を入れる配列
while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
    $roomname[$i] = $row['room_name'];
	$i++;
}
$jssend = json_encode($roomname); //部屋名の配列をjsに送信
$pdo = null;
?>
<script>
	function roomdisplay(){ //プルダウンメニューに部屋名を表示する処理
	var phpreceive = JSON.parse('<?php echo $jssend; ?>');
	var selectmenu_id = document.getElementById("selectmenu");
	for(var i = 0; i < phpreceive.length; i++) {
		var room = document.createElement('option');
		room.text = phpreceive[i];
		room.value = phpreceive[i];
		selectmenu_id.appendChild(room);
	}
}
window.addEventListener('load', roomdisplay); //ここをwindow.onloadにしてしまうと、clock.jsのwindow.onloadと被ってしまい、clock.jsの処理が行われなくなってしまうためaddEventListenerを使用
</script>
	<span id="realtime"></span>
	<p id="sysName">COCOVision</p>
	<form action="roomEn.php" method="POST">
		<p id="selecttxt">選択
			<select tabindex="1" id="selectmenu" name="room">
				<option selected value="-">-</option>
			</select>
		</p>
		<button type="submit" accesskey="s" tabindex="1" id="displaybtn" >表示</button>
	</form>
	<form name="fast-foem" action="select.php" method="POST">
		<button type="submit" accesskey="s" tabindex="1" id="historybtn">履歴</button>
	</form>

</body>
</html>