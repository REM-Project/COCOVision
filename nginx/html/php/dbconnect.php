try{
    $pdo = new PDO(
        'mysql:host=mysql;dbname=co2;charset=utf8',
        'webuser',
        'th1117'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}catch(PDOException $Exception){
    die('接続エラー：' .$Exception->getMessage());
}