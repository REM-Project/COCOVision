<!DOCTYPE html>
<html lang="ja">
 
<head>
	<meta charset="UTF-8">
	<title>年月選択画面</title>
	<link rel="stylesheet" media="screen and (max-width:768px)" href="css/select-sp.css">
	<link rel="stylesheet" media="screen and (min-width:769px)" href="css/select-pc.css">
	<link href="css/jquery-ui.css" rel="stylesheet">
	<link href="css/jquery-ui-timepicker-addon.min.css" rel="stylesheet">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
	<script src= "javascript/jquery-3.6.0.min.js"></script>
	<script src="javascript/jquery-ui.min.js"></script>
	<script src="javascript/datepicker.js"></script>
	<script src="javascript/jquery-ui-timepicker-addon.min.js"></script>
	<script src="javascript/jquery-ui-timepicker-ja.js"></script>
</head>
<body>
	<?php
		try{
    		$pdo = new PDO(
        		'mysql:host=mysql;dbname=cocovision;charset=utf8', //接続先IPアドレス、データベース名、文字コード
        		'webuser', //接続ユーザー
        		'th1117' //ユーザーパスワード
    		);
    		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
		try{ //部屋の名前を取得
    		$sql = "SELECT room_name FROM room_info";
    		$stmh = $pdo->prepare($sql);
    		$stmh->execute();
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
		$i = 0;
		$roomname = [];
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
    		$roomname[$i] = $row['room_name'];
			$i++;
		}
		$roomsend = json_encode($roomname); //jsに部屋名の配列を送信
    	$pdo = null; //データベース切断
	?>
	<span id="realtime"></span>
	<h1 id="headName">データ保存履歴</h1>
	
	<form name = "period" action="past.php" method="POST">
		<h2 id="selecttxt">期間選択</h2>
		<br>		
		<input id="datepicker1" name="startdate" type="text" readonly="readonly"/>
		<br>
		<p class="transition">・</p>
		<p class="transition">・</p>
		<p class="transition">・</p>
		<input id="datepicker2" name="enddate" type="text" readonly="readonly"/>
		<br>
		<div id="selectPart">
			<h2>表示項目</h2>
			<input type="checkbox" class="check1" name="values[]" value="CO2濃度" checked="checked">CO2濃度
			<input type="checkbox" class="check1" name="values[]" value="温度" checked="checked">温度
			<input type="checkbox" class="check1" name="values[]" value="湿度" checked="checked">湿度
			<input type="checkbox" class="check1" name="values[]" value="混雑度" checked="checked">混雑度
			<br>
			<h2>表示する部屋</h2>
		</div>
		<button type="button" onclick="multipleaction('past.php')" id="displaybtn" align="left">表示</button>
		<button type="button" onclick="multipleaction('csvdownload.php')" id="csvbtn">csv出力</button>
	</form>	
	<form action="index.php" method="POST">
		<button type="submit" accesskey="s" tabindex="1" id="backbtn">戻る</button>
	</form>
	<script>
		$(function() { //期間選択の始め日時をカレンダーで選択できるよう導入
			$('#datepicker1').datetimepicker();
		});
    	$(function() { //期間選択の終わり日時をカレンダーで選択できるよう導入
  			$('#datepicker2').datetimepicker();
		});
		
		var roomname = JSON.parse('<?php echo $roomsend; ?>'); //部屋名の配列をphpから受け取る
		for(var i = 0; i < roomname.length; i++){ //項目名を選択するチェックボックスを作成する
			input = document.createElement('input'); 
			input.setAttribute("type", "checkbox");
			input.setAttribute("class", "check2");
			input.setAttribute("name", "rooms[]");
			input.setAttribute("value", roomname[i]);
			input.setAttribute("checked", "checked");
			inputText = document.createTextNode(roomname[i]);
			document.getElementById('selectPart').appendChild(input);
			document.getElementById('selectPart').appendChild(inputText);
			if ((navigator.userAgent.indexOf('iPhone') > 0 && navigator.userAgent.indexOf('iPad') == -1) || navigator.userAgent.indexOf('iPod') > 0 || navigator.userAgent.indexOf('Android') > 0) { //スマホからの接続時
				br = document.createElement('br');
				document.getElementById('selectPart').appendChild(br);//改行を入れる
			}
		}
		
		//チェックが入っていない場合の処理
		function check(){ //項目名か部屋名どちらかのチェックがすべて外れている場合、[表示]ボタンと[csv出力]ボタンを押せないようにする
			var check1 = false;
			var check2 = false;
			for(var i = 0; i < document.getElementsByClassName("check1").length; i++){
				if(document.getElementsByClassName("check1")[i].checked){
					check1 = true;
					document.getElementById("displaybtn").disabled = false;
					document.getElementById("csvbtn").disabled = false;
					break;
				}
			}
			for(var j = 0; j < document.getElementsByClassName("check2").length; j++){
				if(document.getElementsByClassName("check2")[j].checked){
					check2 = true;
					document.getElementById("displaybtn").disabled = false;
					document.getElementById("csvbtn").disabled = false;
					break;
				}
			}
			if(!check1 || !check2){
				document.getElementById("displaybtn").disabled = true;
				document.getElementById("csvbtn").disabled = true;
			}
		}
		document.querySelector("#selectPart").addEventListener('change', check); //チェックが外れたりついたりするたびに[check]関数を呼び出す
		function multipleaction(url){ //表示ボタンとcsv出力ボタンは同じフォームを参照しているため、action先をボタンによって分けるためにこの関数をりようする　
			var f = document.querySelector("form");
			var action = f.setAttribute("action", url);
			document.querySelector("form").submit();
		}
	</script>
	<script src="javascript/clock.js"></script>
</body>

</html>
