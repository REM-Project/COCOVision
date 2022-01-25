<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<title>年月選択画面</title>
	<link href="css/past.css" rel="stylesheet" type="text/css" media="all">
	<script src="javascript/clock.js"></script>
	<script src="javascript/chart.min.js"></script>

</head>
<body>
	<?php
	$startdate = $_POST['startdate']; //selectで選択した参照期間のはじめ
	$enddate = $_POST['enddate']; //参照期間の終わり
	$checkitems = $_POST['values']; //チェックしたco2濃度などの文字列を値を受け取っている
	$roomname = $_POST['rooms']; //チェックした部屋を受け取っている
	$data = [[[]]];
	$maxlength = 0;
	$j = 0;
		try{ //データベース接続
    		$pdo = new PDO(
        		'mysql:host=mysql;dbname=cocovision;charset=utf8', //ipアドレス、接続データベース
        		'webuser', //ログインするユーザー名
        		'th1117' //パスワード
    		);
    		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}catch(PDOException $Exception){
    		die('接続エラー：' .$Exception->getMessage());
		}
		for($i = 0; $i < count($roomname); $i++){
			try{
    			$sql = "SELECT table_name FROM room_info WHERE room_name = '$roomname[$i]'";
    			$stmh = $pdo->prepare($sql);
    			$stmh->execute();
			}catch(PDOException $Exception){
    			die('接続エラー：' .$Exception->getMessage());
			}
			$tablename[$i] = $stmh->fetchColumn();
		}
		for($i = 0; $i < count($roomname); $i++){
			$j = 0;
			$table = $tablename[$i];
			$data[$i][0][0] = 0;
			$data[$i][0][1] = 0;
			$data[$i][0][2] = 0;
			$data[$i][0][3] = 0;
			$data[$i][0][4] = 0;
			try{
				$sql =  "SELECT rec_time, co2, temp , humi, cong FROM $table WHERE rec_time between '$startdate' and '$enddate';"; //配列が使えなかったから$tableに収める。startdateとenddateは''をつけないと検索できない。
				$stmh2 = $pdo->prepare($sql);
				$stmh2->execute();
				foreach ( $stmh2 as $row){
					for($k = 0; $k < 5; $k++) {
						$data[$i][$j][$k] = $row[$k]; //$iは部屋、$jは行数、$kは記録時間、温度、湿度、CO2濃度,混雑度。
					}
					$j++;
				}
				if($maxlength < $j){
					$maxlength = $j; //この変数はjavascriptで使う。
				}
			}catch(PDOException $Exception){
				die('接続エラー：' .$Exception->getMessage());
			}
		}
		$datasend = json_encode($data);
		$tablesend = json_encode($tablename);
		$checkItemSend = json_encode($checkitems);
		$roomsend = json_encode($roomname);
		$pdo = null;//データベース接続を切る
	?>
	<span id="realtime"></span>
	<div class="line">
		<form action="index.php" method="POST">
			<button type="submit" accesskey="s" tabindex="1" id="homebtn">ホーム</button>
		</form>
		<form action="select.php" method="POST">
			<button type="submit" accesskey="s" tabindex="1" id="backbtn">戻る</button>
		</form>
	</div>
	<h1 id="titletext">データ履歴</h1>
	<button id="csvbtn"><a id="download" download="データ履歴.csv" onclick="handleDownload()">csvファイルダウンロード</a></button>
	<div class="tab-wrap">
    	<input id="TAB-01" type="radio" name="TAB" class="tab-switch" checked="checked" /><label class="tab-label" for="TAB-01">表で表示</label>
    	<div class="tab-content">
        	<table id = "data"></table>
    	</div>
    	<input id="TAB-02" type="radio" name="TAB" class="tab-switch" /><label class="tab-label" for="TAB-02">グラフで表示</label>
    	<div class="tab-content">
        	<div id="chart-area"></div>
    	</div>
	<script>
		var sqldata = JSON.parse('<?php echo $datasend; ?>');
		var tablename = JSON.parse('<?php echo $tablesend; ?>');
		var checkitems = JSON.parse('<?php echo $checkItemSend; ?>');
		var roomname = JSON.parse('<?php echo $roomsend; ?>');
		var table = document.getElementById('data');
		var newrow = table.insertRow();
		var newcell = newrow.insertCell();
		var newtext = document.createTextNode('');
		var item = ["記録時間", "CO2濃度", "温度", "湿度" ,"混雑度"]; //混雑度が入る
		var maxlength = '<?php echo $maxlength; ?>';
		var check = true;
			
		//ここからtableに履歴データを表示
		//部屋の名前をテーブルの一番上に入れる。
		for(var i = 0; i < tablename.length; i++){
			for(var k = 0; k < checkitems.length + 2; k++){
				if(i != 0 || k != 0){
					newcell = newrow.insertCell();
				}
				if(k == 0){
					newtext = document.createTextNode(roomname[i]);
				} else {
					newtext = document.createTextNode("-------------");
				}
				if(i < tablename.length - 1 || k < checkitems.length + 1 ){
					newcell.appendChild(newtext);
				}
				if(i == tablename.length - 1 && k == checkitems.length ){ //最後に行追加を行うとなぜかうまくならないため、kが3のときに行追加をするとkが4の時に行追加が行われる。
					newrow = table.insertRow();
				}
			}
		}
		
		//tableに項目名を入力する処理を行っている。
		for(var i = 0; i < tablename.length; i++) {
			for(var k = 0; k < 6; k++){
				for(l = 0; l < checkitems.length; l++){ //チェックが入っている項目が来た時にcheckをtrueにしている。
					if(checkitems[l] == item[k] || k == 0 || k == 5){
						check = true;
						break;	
					} else {
						check = false; //チェックが入ったものとitem[]を参照し、一致しなかった場合にcheckをfalseにすることで処理を飛ばす。そうすることでkが一つ増えるためitem[k]が飛ばされることとなり、表示されない。
					}
				}
				if(check){ //checkがtrueのときに以下の処理を行う（チェックが入ってる項目のみ処理を行う
					if(k != 5 || i != tablename.length - 1){ //最後のテーブルの項目の最後の時はこの処理を行わない
						if(i != 0 || k != 0){ //最初は行を追加したばかりですでに一つセルが生成されていためセル追加の処理を行わない。
							newcell = newrow.insertCell();
						}
						if(k == 5){
							newtext = document.createTextNode(""); //最後は空白を入れる(混雑度と記録時間の間に空白セルを一ついれるため）
						}else{
							newtext = document.createTextNode(item[k]);
						}
						newcell.appendChild(newtext);
					}	
				}
			}
		}
		
		//tableにセンサー値等を入力する処理を行っている。
		for(var j = 0; j < maxlength; j++){
			newrow = table.insertRow();
			for(var i = 0; i < tablename.length; i++) {
				for(var k = 0; k < 6; k++){ //混雑度がきたらその分増やす
					for(l = 0; l < checkitems.length; l++){ //チェックが入っている項目が来た時にcheckをtrueにしている。
						if(checkitems[l] == item[k] || k == 0 || k == 5){
							check = true;
							break;	
						} else {
							check = false;
						}
					}
					if(check){
						if(sqldata[i][0][0] == 0 || sqldata[i].length <= j || k == 5){
							if(i != tablename.length - 1 || k != 5){
								newcell = newrow.insertCell();
								newtext = document.createTextNode("");
								newcell.appendChild(newtext);
							}
						} else {
							newcell = newrow.insertCell();		
							newtext = document.createTextNode(sqldata[i][j][k]);
							newcell.appendChild(newtext);
							if(k == 1){ //ここでCO2濃度などに単位を付けている。
								newtext = document.createTextNode("ppm");
							}else if(k == 2) {
								newtext = document.createTextNode("℃");
							}else if(k == 3) {
								newtext = document.createTextNode("%");
							}else if(k == 4) {
								newtext= document.createTextNode("%");
							} else {
								newtext = document.createTextNode("");
							}
							newcell.appendChild(newtext);
						}
					}
				}
			}
		}
		newrow = table.insertRow();
		
		//ここからグラフ表示コード
		let label = [];
		let co2data = [];
		let tempdata = [];
		let humidata = [];
		let congdata = [];
		for(var i = 0; i < sqldata.length; i++){
			label = [];
			co2data = [];
			tempdata = [];
			humidata = [];
			congdata = [];
			let h2 = document.createElement('h2');
			h2.innerHTML = roomname[i];
			document.getElementById('chart-area').appendChild(h2);
			for(var j = 0; j < sqldata[i].length; j++){
				label[j] = sqldata[i][j][0];
				co2data[j] = sqldata[i][j][1];
				tempdata[j] = sqldata[i][j][2];
				humidata[j] = sqldata[i][j][3];
				congdata[j] = sqldata[i][j][4];
			}
			const co2Charts = function () {
  				const co2DataSet = {
    				type: 'line',
    				data: {
    					labels: label,
      					datasets: [{
        					label: 'CO2濃度',
        					data: co2data,
        					backgroundColor: 'rgba(60, 160, 220, 0.3)',
        					borderColor: 'rgba(60, 160, 220, 0.8)'
      					}]
    				},
    				options: {}
  				};
  				const ctx = document.createElement('canvas');
  				document.getElementById('chart-area').appendChild(ctx);
  				new Chart(ctx, co2DataSet);
			};

			const tempCharts = function () {
  				const tempDataSet = {
    				type: 'line',
    				data: {
    					labels: label,
      					datasets: [{
        					label: '温度',
        					data: tempdata,
        					backgroundColor: 'rgba(60, 0, 0, 0.3)',
        					borderColor: 'rgba(60, 0, 0, 0.8)'
      					}]
    				},
    				options: {}
  				};
  				const ctx = document.createElement('canvas');
  				document.getElementById('chart-area').appendChild(ctx);
  				new Chart(ctx, tempDataSet);
			}
			const humiCharts = function () {
  				const humiDataSet = {
    				type: 'line',
    				data: {
    					labels: label,
      					datasets: [{
        					label: '湿度',
        					data: humidata,
        					backgroundColor: 'rgba(60, 160, 100, 0.3)',
        					borderColor: 'rgba(60, 160, 100, 0.8)'
      					}]
    				},
    				options: {}
  				};
  				const ctx = document.createElement('canvas');
  				document.getElementById('chart-area').appendChild(ctx);
  				new Chart(ctx, humiDataSet);
			}
			const congCharts = function () {
  				const congDataSet = {
    				type: 'line',
    				data: {
    					labels: label,
      					datasets: [{
        					label: '混雑度',
        					data: congdata,
        					backgroundColor: 'rgba(0, 0, 220, 0.3)',
        					borderColor: 'rgba(0, 0, 220, 0.8)'
      					}]
    				},
    				options: {}
  				};
  				const ctx = document.createElement('canvas');
  				ctx.setAttribute("width", "32");
  				document.getElementById('chart-area').appendChild(ctx);
  				new Chart(ctx, congDataSet);
			}
			for(l = 0; l < checkitems.length; l++){ //チェックが入っている項目が来た時にcheckをtrueにしている。
				if(checkitems[l] == item[1]){
					co2Charts();
				} else if(checkitems[l] == item[2]) {
					tempCharts();
				} else if(checkitems[l] == item[3]){
					humiCharts();
				} else if(checkitems[l] == item[4]){
					congCharts();
				}
			}
		};
		
		//ここからCSV出力コード
		function handleDownload() { 
        	var bom = new Uint8Array([0xEF, 0xBB, 0xBF]);//文字コードをBOM付きUTF-8に指定
        	var table = document.getElementById('data');//id=dataという要素を取得
        	var data_csv="";//ここに文字データとして値を格納していく
        	for(var i = 0;  i < table.rows.length; i++){ //CSVダウンロード処理
        		for(var j = 0; j < table.rows[i].cells.length; j++){
          			if(table.rows[i].cells[j].innerText == "-------------"){ //------が入らないようにする処理を追記
          				data_csv += "";
          			} else {
            			data_csv += table.rows[i].cells[j].innerText;//HTML中の表のセル値をdata_csvに格納
            		}
            		if(j == table.rows[i].cells.length-1) data_csv += "\n";//行終わりに改行コードを追加
            		else data_csv += ",";//セル値の区切り文字として,を追加
          		}
        	}
        	var blob = new Blob([ bom, data_csv], { "type" : "text/csv" });//data_csvのデータをcsvとしてダウンロードする関数
        	if (window.navigator.msSaveBlob) { //IEの場合の処理
        		window.navigator.msSaveBlob(blob, "データ履歴.csv"); 
        	    //window.navigator.msSaveOrOpenBlob(blob, "データ履歴.csv");// msSaveOrOpenBlobの場合はファイルを保存せずに開ける
        	} else {
        	    document.getElementById("download").href = window.URL.createObjectURL(blob);
        	}
        	delete data_csv;//data_csvオブジェクトはもういらないので消去してメモリを開放
    	}
	</script>
</body>
</html>