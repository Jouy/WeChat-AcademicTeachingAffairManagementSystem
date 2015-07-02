<?php

/**
 * 通知相关操作的函数
 * @author MewX <imewx@qq.com>
 */

// 包含基础通信函数
require_once('utils/shell.php');


// 定义通知类
class Notice{
    public $notid;
    public $title;
    public $content;
    public $time;

    function Notice($tid, $ttitle, $tcontent, $ttime) {
        $this->notid = $tid;
        $this->title = $ttitle;
        $this->content = $tcontent;
        $this->time = $ttime;
    }
}


// 获取通知列表，最多获取9个
function noticeList() {
    // query
    //$sql = connectDatabase();
    //
    // if(is_string($sql)) return $sql;
    // $result = $sql->query(
    //     //_remove_sql_inject() 有时候需要用，对于附加数据
    //     $sql->real_escape_string("select notid, title, time from notice;")
    // );
    // return json_encode($sql);
    // $a = array();
    // while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    // 	// add to array
    // 	$temp = new Notice($row['notid'], $row['title'], "", $row['time']);
    // 	$a[] = $temp;
	// }
    // mysqli_free_result($result);
    // closeDatabase($sql);

    // query
    $pdo = connectDatabase();
    $rs = $pdo->query("select notid, title, time from notice order by time desc limit 0, 9;");
    $a = array();
    while($row = $rs->fetch()) {
		// add to array
		$temp = new Notice($row['notid'], $row['title'], "", $row['time']);
		$a[] = $temp;
	}

    // merge
    if(sizeof($a) == 0)
        return "通知列表为空";

    $result = "获取通知列表\n--------";
    foreach($a as $temp) {
        $result = $result . "\n" . "[" . $temp->notid . "] " . $temp->title . " (" . $temp->time . ")";
    }
    return $result;
}


// 查看某一个通知的具体内容
function noticeContent($id) {
    if(!is_numeric($id))
        return "通知编号必须为数字！请重新输入！";

    // query
    $pdo = connectDatabase();
    $rs = $pdo->query("select notid, title, content, time from notice where notid = " . $id . ";");
    $row = $rs->fetch();
    if(!$row)
        return "不存在编号为 [" . $id . "] 的通知，请检查！";

    return "查看通知\n--------\n".
        "通知编号：" . $row['notid'] . "\n".
        "通知标题：" . $row['title'] . "\n".
        "通知内容：" . $row['content'] . "\n".
        "发布时间：" . $row['time'];
}

?>
