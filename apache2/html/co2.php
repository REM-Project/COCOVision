<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>タイトル</title>
</head>
<body>
<?php
try{
    $pdo = new PDO(
        'mysql:host=mysql;dbname=co2;charset=utf8',
        'webuser',
        'th1117'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}catch(PDOException $Exception){
    die('接続エラー1：' .$Exception->getMessage());
}

try{
    $sql = "SELECT co2,temp,humi FROM system_values";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
}catch(PDOException $Exception){
    die('接続エラー2：' .$Exception->getMessage());
}
?>
<table><tbody>
    <tr><th>co2</th><th>temp</th><th>humi</th></tr>
<?php
    while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
?>
    <tr>
        <th><?=htmlspecialchars($row['co2'])?></th>
        <th><?=htmlspecialchars($row['temp'])?></th>
	<th><?=htmlspecialchars($row['humi'])?></th>
    </tr>
<?php
    }
    $pdo = null;
?>
</tbody></table>
</body>
</html>
