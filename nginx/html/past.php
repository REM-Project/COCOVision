<!--  部屋環境確認ページ
読み込んでいるファイル
 javascript : clock.js
 css : past.css
 php : DBconnect.php
-->
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<title>年月選択画面</title>
	<link href="css/past.css" rel="stylesheet" type="text/css" media="all">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0"> <!-- スマホとPCからの接続に合わせたレスポンシブ設定 -->
	<script src="javascript/clock.js"></script> <!-- 時計の読み込み -->
	<script src="javascript/chart.min.js"></script>
</head>
<body>
	<?php
	$startdate = $_POST['startdate']; //selectで選択した参照期間のはじめ
	$enddate = $_POST['enddate']; //参照期間の終わり
	$checkitems = $_POST['values']; //チェックしたco2濃度などの文字列を値を受け取っている
	$roomname = $_POST['rooms']; //チェックした部屋を受け取っている
	$data = [[[]]]; //センサー値などのデータを取得する際に利用する3次元配列
	$maxlength = 0; //選択された部屋の中で最も多い行数を入れる
	$j = 0;
		require("php/DBconnect.php"); //データベース接続関数の読み込み
		$pdo = dbconnect(); //データベース接続
		
		//チェックされた部屋名に対応するテーブル名を取得
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
		//選択された期間内のデータを取得
		for($i = 0; $i < count($roomname); $i++){
			$j = 0;
			$table = $tablename[$i];
			$data[$i][0][0] = 0; //これがないと後の処理で存在しない配列を参照することになりエラーがでる
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
		$datasend = json_encode($data); //jsでこの配列が使えるように必要となる処理
		$tablesend = json_encode($tablename); //jsでこの配列が使えるように必要となる処理
		$checkItemSend = json_encode($checkitems); //jsでこの配列が使えるように必要となる処理
		$roomsend = json_encode($roomname); //jsでこの配列が使えるように必要となる処理
		$pdo = null;//データベース接続を切る
	?>
	<span id="realtime"></span> <!-- 時計の設置場所 -->
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
		var newrow = table.insertRow(); //改行する際に使用
		var newcell = newrow.insertCell(); //セルを追加する際に使用
		var newtext = document.createTextNode('');
		var item = ["記録時間", "CO2濃度", "温度", "湿度" ,"混雑度"]; //項目名
		var maxlength = '<?php echo $maxlength; ?>';
		var check = true; //選択された項目かどうかを判定するために使用
			
		//ここからtableに履歴データを表示する処理。動作は1行目にチェックが入った部屋名すべてを入れ、2行目にチェックが入った項目名を部屋の数だけ入れる。3行目以降は「1部屋目のデータ」、「2部屋目のデータ」・・・「改行」を繰り返して表を作成する。
		
		//部屋の名前をテーブルの一番上に入れる処理。
		for(var i = 0; i < tablename.length; i++){ //登録されている部屋の数だけ繰り返す
			for(var k = 0; k < checkitems.length + 2; k++){ //チェックした項目 + 記録時間 + 見栄えのための空白で tablename.length + 2
				if(i != 0 || k != 0){ //最初はすでにある1つめのセルに部屋名を入れるため、セル追加を行わない。
					newcell = newrow.insertCell();
				}
				
				if(k == 0){ //一番端に部屋名を入れる。
					newtext = document.createTextNode(roomname[i]);
				} else { //その後は----を入れる。空白にすると見栄えが悪かったためこちらを採用。
					newtext = document.createTextNode("-------------");
				}
				
				if(i < tablename.length - 1 || k < checkitems.length + 1 ){ //最後は空白を入れずに改行するため改行のタイミングのみ----を表示テキストに追加しない
					newcell.appendChild(newtext);
				}
				
				if(i == tablename.length - 1 && k == checkitems.length){ //kはcheckitems.length + 1まで繰り返すが、改行時は空白セルが入らないためiがcheckitems.lengthの時に改行を行う。
					newrow = table.insertRow(); //改行
				}
			}
		}
		
		//2行目に項目名を入力する処理を行っている。
		for(var i = 0; i < tablename.length; i++) {
			for(var k = 0; k < 6; k++){ //項目名の数と見栄えのための空白を合わせた数繰り返す。
				for(l = 0; l < checkitems.length; l++){ //チェックが入っている項目が来た時にcheckをtrueにしている。
					if(checkitems[l] == item[k] || k == 0 || k == 5){
						check = true;
						break;	
					} else {
						check = false; //チェックが入ったものとitem[]を参照し、一致しなかった場合にcheckをfalseにすることでチェックしなかった項目に対する処理は飛ばす。そうすることでkが一つ増えるためitem[k]が飛ばされることとなり、表示されない。
					}
				}
				if(check){ //checkがtrueのときに以下の処理を行う（チェックが入ってる項目のみ処理を行う)
					if(k != 5 || i != tablename.length - 1){ //最後のテーブルの項目の時はこの処理を行わない
						if(i != 0 || k != 0){ //最初は行を追加したばかりで、すでに一つセルが生成されていためセル追加の処理を行わない。
							newcell = newrow.insertCell();
						}
						if(k == 5){
							newtext = document.createTextNode(""); //最後は空白を入れる(混雑度と記録時間の間に空白セルを一ついれるため）
						}else{
							newtext = document.createTextNode(item[k]); //項目テキスト作成
						}
						newcell.appendChild(newtext); //項目挿入
					}	
				}
			}
		}
		
		//3行目以降にセンサー値等を入力する処理を行っている。
		for(var j = 0; j < maxlength; j++){ //一番行数が多い部屋の数だけ繰り返す。
			newrow = table.insertRow(); //改行
			for(var i = 0; i < tablename.length; i++) {
				for(var k = 0; k < 6; k++){ //混雑度がきたらその分増やす
					for(l = 0; l < checkitems.length; l++){ //チェックが入っている項目が来た時にcheckをtrueにしている。
						if(checkitems[l] == item[k] || k == 0 || k == 5){ //チェックが入った項目かどうかを検証。k == 0は記録時間、k == 5は空白なのでcheckをtrueにしてtableに出力する処理を行う。checkがfalseになった項目のデータは出力されない。
							check = true;
							break;	
						} else {
							check = false;
						}
					}
					if(check){ //チェックされた項目の場合の処理
						if(sqldata[i][0][0] == 0 || sqldata[i].length <= j || k == 5){ //参照した配列にデータが入っていない場合は空白を入れる。
							if(i != tablename.length - 1 || k != 5){ //最後のテーブルかつ最後の項目を参照している場合は以下の処理を行わない
								newcell = newrow.insertCell(); //セルの追加
								newtext = document.createTextNode(""); //テキストに空白を入れる。
								newcell.appendChild(newtext); //セルに空白を挿入
							}
						} else { //参照した配列にデータが入っている場合
							newcell = newrow.insertCell(); //セル追加	
							newtext = document.createTextNode(sqldata[i][j][k]);
							newcell.appendChild(newtext); //センサー値などのデータを挿入
							if(k == 1){ //ここでCO2濃度などに単位を付けている。
								newtext = document.createTextNode("ppm");
							}else if(k == 2) {
								newtext = document.createTextNode("℃");
							}else if(k == 3) {
								newtext = document.createTextNode("%");
							}else if(k == 4 && newtext == null) { //混雑度がnullの場合は%をつけないために条件を追記
								newtext= document.createTextNode("%");
							} else {
								newtext = document.createTextNode("");
							}
							newcell.appendChild(newtext); //単位を挿入
						}
					}
				}
			}
		}
		newrow = table.insertRow(); //改行
		
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
			let h2 = document.createElement('h2'); //h2エレメント作成
			h2.innerHTML = roomname[i]; //h2に部屋名を挿入
			document.getElementById('chart-area').appendChild(h2); //部屋名表示
			for(var j = 0; j < sqldata[i].length; j++){ //部屋ごとの行数分だけ繰り返す
				label[j] = sqldata[i][j][0];
				co2data[j] = sqldata[i][j][1];
				tempdata[j] = sqldata[i][j][2];
				humidata[j] = sqldata[i][j][3];
				congdata[j] = sqldata[i][j][4];
			}
			const co2Charts = function () { //CO2グラフ作成関数
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

			const tempCharts = function () { //温度グラフ作成関数
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
			const humiCharts = function () { //湿度グラフ作成関数
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
			const congCharts = function () { //混雑度グラフ作成関数
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
			for(l = 0; l < checkitems.length; l++){ //チェックが入っている項目が来た時にグラフ作成
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
        	delete data_csv; //data_csvオブジェクトはもういらないので消去してメモリを開放
    	}
	</script>
</body>
</html>