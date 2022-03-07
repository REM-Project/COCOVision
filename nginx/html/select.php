<!--  部屋環境確認ページ
読み込んでいるファイル
 javascript : clock.js , jquery-3.6.0.min.js , jquery-ui.min.js , datepicker-ja.js , jquery-ui-timepicker-addon.min.js , jquery-ui-timepicker-ja.js
 css : select-sp.css , select-pc.css , jquery-ui.css , jquery-ui-timepicker-addon.min.css
 php : DBconnect.php
-->

<!DOCTYPE html>
<html lang="ja">
 
<head>
	<meta charset="UTF-8">
	<title>年月選択画面</title>
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0"> <!-- スマホとPCからの接続に合わせたレスポンシブ設定 -->
	<link rel="stylesheet" media="screen and (max-width:768px)" href="css/select-sp.css"> <!-- スマホからの読み込みの場合にselect-sp.cssを読み込む -->
	<link rel="stylesheet" media="screen and (min-width:769px)" href="css/select-pc.css"> <!-- PCからの読み込みの場合にselect-pc.cssを読み込む -->
	<!-- datepickerを使うために読み込み↓ -->
	<link href="css/jquery-ui.css" rel="stylesheet">
	<link href="css/jquery-ui-timepicker-addon.min.css" rel="stylesheet">
	<script src= "javascript/jquery-3.6.0.min.js"></script>
	<script src="javascript/jquery-ui.min.js"></script>
	<script src="javascript/datepicker-ja.js"></script>
	<script src="javascript/jquery-ui-timepicker-addon.min.js"></script>
	<script src="javascript/jquery-ui-timepicker-ja.js"></script>
</head>
<body>
	<?php
		require("php/DBconnect.php"); //データベース接続関数の読み込み
		$pdo = dbconnect(); //データベース接続
		try{ //部屋の名前を取得
    		$sql = "SELECT room_name FROM room_info";
    		$stmh = $pdo->prepare($sql);
    		$stmh->execute();
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
		$i = 0;
		$roomname = []; //部屋の名前を入れる。
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
    		$roomname[$i] = $row['room_name'];
			$i++;
		}
		$roomsend = json_encode($roomname); //javascriptから部屋名の配列を読み込めるようにする
    	$pdo = null; //データベース切断
	?>
	<span id="realtime"></span> <!-- 時計の設置場所 -->
	<h1 id="headName">データ保存履歴</h1>
	
	<form action="past.php" method="POST">
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
		<button type="button" onclick="multipleaction('past.php')" id="displaybtn" align="left">表示</button> <!-- 表示ボタンを押したときpast.phpを遷移 -->
		<button type="button" onclick="multipleaction('csvdownload.php')" id="csvbtn">csv出力</button> <!-- csv出力ボタンを押したときcsvdownload.phpを読み込む -->
	</form>	
	<form action="index.php" method="POST"> <!-- 戻るボタンを押したらindex.phpに遷移 -->
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
			input = document.createElement('input'); //inputエレメント生成
			input.setAttribute("type", "checkbox"); //inputエレメントのtypeにcheckboxを入れる
			input.setAttribute("class", "check2"); //inputエレメントのclassにcheck2を入れる
			input.setAttribute("name", "rooms[]"); //inputエレメントのnameにroom配列を入れる
			input.setAttribute("value", roomname[i]); //inputエレメントのvalueに部屋名を入れる
			input.setAttribute("checked", "checked"); //inputエレメントのcheckedにcheckedを入れる
			inputText = document.createTextNode(roomname[i]); //HTMLに追加したい文字はcreateTextNodeで作成する必要あり。
			document.getElementById('selectPart').appendChild(input); //selectPartにチェックボックス追加
			document.getElementById('selectPart').appendChild(inputText); //selectPartにチェックボックスに対応した部屋名を追加
			//スマホからの接続時
			if ((navigator.userAgent.indexOf('iPhone') > 0 && navigator.userAgent.indexOf('iPad') == -1) || navigator.userAgent.indexOf('iPod') > 0 || navigator.userAgent.indexOf('Android') > 0) { 
				br = document.createElement('br');
				document.getElementById('selectPart').appendChild(br);//改行を入れる
			}
		}
		
		//チェックが入っていない場合の処理
		function check(){ //項目名か部屋名どちらかのチェックがすべて外れている場合、[表示]ボタンと[csv出力]ボタンを押せないようにする
			var check1 = false;
			var check2 = false;
			for(var i = 0; i < document.getElementsByClassName("check1").length; i++){ //項目名のチェックボックスに対する処理
				if(document.getElementsByClassName("check1")[i].checked){
					check1 = true;
					document.getElementById("displaybtn").disabled = false;
					document.getElementById("csvbtn").disabled = false;
					break;
				}
			}
			for(var j = 0; j < document.getElementsByClassName("check2").length; j++){ //部屋名のチェックボックスに対する処理
				if(document.getElementsByClassName("check2")[j].checked){
					check2 = true;
					document.getElementById("displaybtn").disabled = false;
					document.getElementById("csvbtn").disabled = false;
					break;
				}
			}
			if(!check1 || !check2){ //項目名 or 部屋名に一つもチェックが入っていない場合は表示ボタンとcsv出力ボタンを押せないようにする。
				document.getElementById("displaybtn").disabled = true;
				document.getElementById("csvbtn").disabled = true;
			}
		}
		document.querySelector("#selectPart").addEventListener('change', check); //チェックが外れたりついたりするたびに[check]関数を呼び出す
		function multipleaction(url){ //表示ボタンとcsv出力ボタンは同じフォームを参照しているため、action先をボタンによって分けるためにこの関数を利用する　
			var f = document.querySelector("form");
			var action = f.setAttribute("action", url);
			document.querySelector("form").submit();
		}
	</script>
	<script src="javascript/clock.js"></script> <!-- 時計の処理の呼び出し -->
</body>

</html>
