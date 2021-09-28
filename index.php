<?php
require('function.php'); //デバッグモード関連とDB読み込み
$auth = false; //認証を利用する場合はここを書き換える!!!

switch ($auth) {
    case !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']):
    case $_SERVER['PHP_AUTH_USER'] !== 'student':
    case $_SERVER['PHP_AUTH_PW']   !== 'sysdeva':
        header('WWW-Authenticate: Basic realm="Enter username and password."');
        header('Content-Type: text/plain; charset=utf-8');
        die('このページを見るにはログインが必要です');
}

header('Content-Type: text/html; charset=utf-8');

/* 定数系 */
$group_max = 8;
$question_max = 3;

?>


<!DOCTYPE html>
<head>
    <title>システム開発演習A 質問受付システム</title>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="10"> <!-- ここで10秒ごと更新 -->
</head>

<body>
<h1>システム開発演習A 質問受付システム</h1>
    <p>システム開発演習Aで質問をする際の受付システムです。<br>
    2020年のプログラミング基礎演習Aで使ったシステムとほぼ同様のシステムです。</p>

    <p>このページは10秒ごとに自動更新されます
    <br><b>更新はページ内の更新ボタンを利用してください</b></p>

<?php

/* POSTされてきたときの処理 */
if (isset($_POST['groupnum']) && isset($_POST['question'])){

    //入ってきたPOSTの値を扱いやすいように変数に入れる
    debug($_POST['groupnum']);
    $groupnum = $_POST['groupnum'];
    $question = $_POST['question'];

    //var_dump($groupnum);

     //プルダウンメニューなので基本は最後のelseにのみ入るが、変なPOSTへの対策に数字かどうか及び範囲内かの判定はする
    if ((!preg_match('/^[0-9]+$/', $groupnum) || !preg_match('/^[0-9]+$/', $question)) || ($groupnum <= 0 || $groupnum > $group_max) || ($question <= 0 || $question > $question_max)){
        echo "<font color = red><b>POST内容がおかしいです</b></font>\n";
    } else {

        //二重登録防止処理
        //まだ未完了の同一質問が残っていたら登録を弾くため、一旦SELECT
        $res = $db->query("SELECT groupnum, question, status FROM queue WHERE groupnum = $groupnum AND question = $question AND NOT status = 2");
        $row = $res->fetchArray();
        
        if ($row){ //ヒットしたらrowに値があるので蹴る
            echo "<font color = red><b>既に並んでいます。対応をお待ち下さい</b></font>\n";
        } else { //結果がなければfalseなのでここに
            //INSERT処理(本当はエスケープとかしたいけどしてない(curlで変なことされたら死ぬが、一応↑で回避できてはいる))
            //statusが0(対応待ち)の状態でINSERT (詳細はREADME.md)
            $db->query("INSERT INTO queue(groupnum, question, status) VALUES ($groupnum, $question, 0)");
        }
        
        
    }

}

/* 表表示 */
//statusが2(対応完了)以外のものをSELECT
$res = $db->query("SELECT groupnum, question, status FROM queue WHERE NOT status = 2");

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
        echo "</tr>\n";
        $count++;
    } while( $row = $res->fetchArray() );
    
}

?>
    </table>

    <p><form action=./index.php method="GET">
    <input type="submit" value="更新"/>
    </form></p>

    <h4>質問申込み</h4>
        <p>班名と質問内容を入力して「待ち行列に並ぶ」ボタンをクリックしてください</p>
        <form action="./index.php" method="POST">
            <h6>班名: 
                <select name="groupnum">
                    <?php
                    //班のプルダウンメニューを生成
                    //POSTされてきた班番号があった場合はそれを選択済みにする
                    for ($i = 1; $i <= $group_max; $i++){
                        echo '<option value="';
                        echo $i;
                        if ($i == $groupnum){
                            echo '" selected';
                        } else {
                            echo '"';
                        }
                        echo ">{$i}班</option>\n";
                    }
                    ?>
                </select>
            </h6>

            <h6>質問内容: 
                <select name="question">
                    <option value="1">授業に関する質問</option>
                    <option value="2">技術に関する質問</option>
                    <option value="3">店長に質問</option>
                    
                </select>
            </h6>
            <h6><input type="submit" value="　待ち行列に並ぶ　"></h6>
            
        </form>
</body>
</html>