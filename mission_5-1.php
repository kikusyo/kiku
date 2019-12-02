<?php
//(1)DB初期処理
//①DB接続
$dsn = '******';
$user = '******';
$password = '******';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//②テーブル作成
$sql = "CREATE TABLE IF NOT EXISTS bbsdate"
	." ("
	. "num INT AUTO_INCREMENT PRIMARY KEY,"
	. "name char(32),"
	. "comment TEXT,"
	. "time DATETIME,"
	. "pass char(32)"
	.");";
	$stmt = $pdo->query($sql);
//DB初期処理終了

//(2)内部処理
//①初期値設定(エラー回避)
$edit_mode = 0;//「編集」実行時"1"に、それ以外の場合"0"
$edit_num =0;

//②「1.登録 or 2.削除 or 3.編集先取得 or 4.編集内容上書き or 5.初回起動(無効送信時)」の分岐
if(isset($_POST['send']) && empty(!$_POST['name']) && empty(!$_POST['comment']) && empty(!$_POST['send_pass']) && empty($_POST['edit_check'])){
//1.登録
$time=date("Y/m/d H:i:s");
$sql = $pdo -> prepare("INSERT INTO bbsdate (name, comment,time,pass) VALUES (:name, :comment,:time,:pass)");
$sql -> bindParam(':name', $_POST['name'], PDO::PARAM_STR);
$sql -> bindParam(':comment', $_POST['comment'], PDO::PARAM_STR);
$sql -> bindParam(':time', $time, PDO::PARAM_STR);
$sql -> bindParam(':pass', $_POST['send_pass'], PDO::PARAM_STR);
$sql -> execute();

}elseif(isset($_POST['del']) && empty(!$_POST['del_num']) && empty(!$_POST['del_pass'])){
//2.削除
$sql ='SELECT pass from bbsdate where num = :num';
$stmt = $pdo -> prepare($sql);
$stmt->bindParam(':num', $_POST['del_num'], PDO::PARAM_STR);
$stmt->execute();
$pass_check = $stmt -> fetch(PDO::FETCH_COLUMN);
if($pass_check == $_POST['del_pass']){//パスワード検証
	$sql = "delete from bbsdate where num=:num";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':num', $_POST['del_num'], PDO::PARAM_INT);
	$stmt->execute();
}

}elseif(isset($_POST['edit']) && empty(!$_POST['edit_num']) && empty(!$_POST['edit_pass']) ){ 
//3.編集先取得
$sql ="SELECT pass from bbsdate where num=:num";
$stmt = $pdo -> prepare($sql);
$stmt->bindParam(":num",$_POST['edit_num'], PDO::PARAM_STR);
$stmt->execute();
$pass_check = $stmt -> fetch(PDO::FETCH_COLUMN);
if($pass_check == $_POST['edit_pass']){//パスワード検証
	$sql = "SELECT * FROM bbsdate WHERE num=:num";
	$stmt = $pdo -> prepare($sql);
	$stmt->bindParam(":num",$_POST['edit_num'], PDO::PARAM_STR);
	$stmt->execute();
	$edit_date = $stmt -> fetch(PDO::FETCH_ASSOC);
	$edit_num = $_POST['edit_num'];//各変数に代入
	$edit_name =$edit_date["name"];
	$edit_comme =$edit_date["comment"];
	$edit_mode = 1;//編集モードオン(「名前」「コメント」のinputboxのValue値に取得した変数を表示)
}

}else if(isset($_POST['send']) && empty(!$_POST['name']) && empty(!$_POST['comment'])  && empty(!$_POST['send_pass']) && empty(!$_POST['edit_check'])){
//4.編集内容上書き
$sql = "update bbsdate set name=:name,comment=:comment,pass=:pass where num=:num";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":num",$_POST['edit_check'], PDO::PARAM_STR);
$stmt->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
$stmt->bindParam(':comment',$_POST['comment'], PDO::PARAM_STR);
$stmt->bindParam(':pass',$_POST['send_pass'], PDO::PARAM_STR);
$stmt->execute();

}else{
//5.初回起動(無効送信時)
echo "書き込みをしてください"."</br>";
}
//内部処理終了
?>
<HTML>
<!--(3)ページレイアウト-->
掲示板へようこそ<br/>
【書き込み】
<form action = "mission_5-1.php" method="post" >
<?php
if($edit_mode == 1){//編集モードがオンの時、editの変数を表示
	echo "名前".'<input type="text" value = "' .$edit_name. '"  name = "name">';
	echo"<br/>";
	echo"コメント".'<input type="text" value= "' .$edit_comme. '"  name = "comment">';
}else{
	echo "名前".'<input type="text" value = "名前"  name = "name">';
	echo"<br/>";
	echo"コメント".'<input type="text" value= "コメント"  name = "comment">';
}
?>
<br/>
パスワード：<input type="text"  name = "send_pass"><input type="submit" value="送信" name = "send"><br/>
【削除】<br/>
投稿番号：<input type="text"   name = "del_num"><br/>
パスワード：<input type="text"   name = "del_pass"><input type="submit" value="削除" name = "del"><br/>
【編集】<br/>
投稿番号：<input type="text"   name = "edit_num"><br/>
パスワード：<input type="text"   name = "edit_pass"><input type="submit" value="編集" name = "edit"><br/>
<?php
echo '<input type="hidden" value = "' .$edit_num.'"  name = "edit_check">';//編集先num表示、管理用
?>
</from>
</HTML>

<?php
//(4)掲示板内容表示処理
//①DB接続
echo "<br>";
$dsn = 'mysql:dbname=tb210544db;host=localhost';
$user = 'tb-210544';
$password = 'R54hJgexrv';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
//②DB表示
$sql = 'SELECT * FROM bbsdate';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	foreach ($results as $row){
		echo $row['num'].',';
		echo $row['name'].',';
		echo $row['time']."<br>";
		echo "　".$row['comment']."<br>";
	echo "<hr>";
	}
?>

