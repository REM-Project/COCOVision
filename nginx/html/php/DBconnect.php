<!-- データベース接続用コード -->
<?php
	function dbconnect(){
		try{
		    $pdo = new PDO( //DB接続
		        'mysql:host=192.168.10.38;dbname=cocovision;charset=utf8', //接続先IPアドレス、データベース名、文字セット
		        'webuser', //接続ユーザー
		        'th1117' //ユーザーのパスワード
		    );
		    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		    return $pdo;
		}catch(PDOException $Exception){
		    die('接続エラー：' .$Exception->getMessage());
		    return $db = null;
		}
	}
?>