<!--  メインページ
読み込んでいるファイル
 javascript : clock.js
 css : index.css
 php : DBconnect.php
-->

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>メニュー画面</title>
<link href="css/index.css" rel="stylesheet" type="text/css" media="all">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0"> <!-- スマホやPCからの接続に合わせたレスポンシブ設定 -->
<script src="javascript/clock.js"></script> <!-- 時計の読み込み -->
</head>
<body>
<?php
	require("php/DBconnect.php"); //データベース接続関数の呼び出し
	$pdo = dbconnect(); //データベース接続
	
//部屋名取得
try{
    $sql = "SELECT room_name FROM room_info";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
}catch(PDOException $Exception){
    die('接続エラー：' .$Exception->getMessage());
}

$i = 0; //ループを回す際に利用
$roomname = []; //部屋名を入れる配列

while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
    $roomname[$i] = $row['room_name']; //部屋名を変数に格納
	$i++; //配列にデータを入れるために1ずつ増やす。
}
$jssend = json_encode($roomname); //部屋名の配列をjsから読み込めるようにする
$pdo = null; //データベース接続切断
?>
<script>
	//プルダウンメニューに部屋名を表示する関数
	function roomdisplay(){
	var phpreceive = JSON.parse('<?php echo $jssend; ?>'); //phpのroomnameの受け取り
	var selectmenu_id = document.getElementById("selectmenu");
	for(var i = 0; i < phpreceive.length; i++) { //roomnameの数だけ繰り返す
		var room = document.createElement('option'); //optionエレメント作成
		room.text = phpreceive[i]; //optionエレメントのtextに部屋名を設定
		room.value = phpreceive[i]; //optionエレメントのvalueに部屋名を設定
		selectmenu_id.appendChild(room); //selectmenuの一番下にoptionを追加
	}
}
window.addEventListener('load', roomdisplay); //ここをwindow.onloadにしてしまうと、clock.jsのwindow.onloadと被ってしまい、clock.jsの処理が行われなくなってしまうためaddEventListenerを使用
</script>

	<span id="realtime"></span>
	<p id="sysName">COCOVision</p>
	<form action="roomEn.php" method="POST"> <!-- 表示ボタンが押されたらroomEn.phpに遷移-->
		<p id="selecttxt">選択
			<select tabindex="1" id="selectmenu" name="room"> <!-- 部屋一覧のプルダウンメニュー -->
				<option selected value="-">-</option>
			</select>
		</p>
		<button type="submit" accesskey="s" tabindex="1" id="displaybtn" >表示</button> 
	</form>
	<form action="select.php" method="POST"> <!-- 履歴ボタンが押されたらselect.phpに遷移 -->
		<button type="submit" accesskey="s" tabindex="1" id="historybtn">履歴</button>
	</form>
</body>
</html>