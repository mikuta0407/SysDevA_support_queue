<?php

/* デバッグ用記述 */

// PHPのWarningもみ消しの有無
//OnでPHPのWarningが出てくるが、もみ消したいのでOff
ini_set('display_errors', "Off");

//debugモード (trueで有効)
$debug = false;

//Debug用のメッセージ表示関数
//Debug("hoge");という感じでコード内に書いておけば、$debugをfalseにするだけで表示されなくなる
function debug($str){
    global $debug;
    if ($debug){
        echo "<b>Debug: </b>$str<br>\n";
    }
}

//DB読み込み
class sqlite extends SQLite3 {

    function __construct()
    {
        $this->open("sysdeva_support.db");
    }
}

$db = new sqlite();

