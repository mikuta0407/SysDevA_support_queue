<?php
require('function.php'); //デバッグモード関連とDB読み込み
$auth = false; //認証を利用する場合はここを書き換える!!!

switch ($auth) {
    case !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']):
    case $_SERVER['PHP_AUTH_USER'] !== 'admin':
    case $_SERVER['PHP_AUTH_PW']   !== 'sysdeva':
        header('WWW-Authenticate: Basic realm="Enter username and password."');
        header('Content-Type: text/plain; charset=utf-8');
        die('このページを見るにはログインが必要です');
}

header('Content-Type: text/html; charset=utf-8');

?>


<!DOCTYPE html>
<head>
    <title>システム開発演習A 質問受付システム 教員/アシスタント用</title>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="10"> <!-- ここで10秒ごと更新 -->
</head>

<body>
<h1>システム開発演習A 質問受付システム 教員/アシスタント用</h1>
<p>このページは10秒ごとに自動更新されます
    <br><b>更新はページ内の更新ボタンを利用してください</b></p>

<?php
/* POSTされてきたときの処理 */
debug($_POST['id']);

//数字かどうか、statusが範囲内かの確認
if (isset($_POST['id']) && isset($_POST['status'])){
    if (preg_match('/^[0-9]+$/', $_POST['id']) && preg_match('/^[0-9]+$/', $_POST['status']) && ($_POST['status'] == 0 || $_POST['status'] == 1)){
        
        //扱いやすく一旦代入
        $id = $_POST['id'];
        $status = $_POST['status'];
        if ($status == 0){ //現状が0なら1にUPDATE
            $db->query("UPDATE queue set status = 1 WHERE id = $id");
        } elseif ($status == 1){ //現状が1なら2にUPDATE
            $db->query("UPDATE queue set status = 2 WHERE id = $id");
        }
    }
}


/* 表表示 */
//statusが2(対応完了)以外のものをSELECT
$res = $db->query("SELECT id, groupnum, question, status FROM queue WHERE NOT status = 2");

//ループで回して表に流し込んでいく
$row = $res->fetchArray(); //ここで一旦fetchArrayで最初の行を$rowに入れてしまっているので、この後のループはdo-whileを使用

if (!$row){
    echo "<p>待っている人はいません</p>\n";
} else {
    //表部分HTMLの出力
    ?>
    <h4>現在の待ち状況</h4>

    <table border='1'>
	<tr>
		<th>待ち順位</th><th>班</th><th>質問内容</th><th>状況</th>
	</tr>
    <?php
    $count = 1; //待ち順位用
    do {
        //値に対応する文字をセット
        if ($row['question'] == 1){ $q_str = "授業に関する質問"; }
        if ($row['question'] == 2){ $q_str = "技術に関する質問"; }
        if ($row['question'] == 3){ $q_str = "店長に質問"; }

        if ($row['status'] == 0){ $s_str = "対応待ち"; }
        if ($row['status'] == 1){ $s_str = "対応中"; }
        if ($row['status'] == 2){ $s_str = "対応完了"; }

        echo "<tr>\n";
        echo "<td>{$count}</td>\n";
        echo "<td>{$row['groupnum']}班</td>\n";
        echo "<td>{$q_str}</td>\n";
        echo "<td>{$s_str}</td>\n";

        echo "<td><form action=\"./admin.php\" method=\"POST\">";
        echo "<input type=\"hidden\" name=\"id\" value=\"{$row['id']}\">"; //idをhiddenで仕込む
        echo "<input type=\"hidden\" name=\"status\" value=\"{$row['status']}\">"; //statusをhiddenで仕込む
        if ($row['status'] == 0){
            echo "<input type =\"submit\" value=\"対応開始\">";
        }  
        if ($row['status'] == 1){
            echo "<input type =\"submit\" value=\"対応完了\">";
        }  
        echo "</form></td>";

        echo "</tr>\n";
        $count++;
    } while( $row = $res->fetchArray() );
}

?>
</table>

<p><form action=./admin.php method="GET">
<input type="submit" value="更新"/>
</form></p>
</body>
</html>