<?php

/**
 * 考试相关操作的函数
 * @author MewX <imewx@qq.com>
 */

// 包含基础通信函数
require_once('utils/shell.php');


// 定义考试类
class Exam {
    public $exmid;
    public $name;
    public $clsid;
    public $begtime;
    public $duration;
    public $corid;

    function Exam($a, $b, $c, $d, $e, $f) {
        $this->exmid = $a;
        $this->name = $b;
        $this->clsid = $c;
        $this->begtime = $d;
        $this->duration = $e;
        $this->corid = $f;
    }
}


// 查看未来考试
function examFunture($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 查询未来考试
    $rs = $pdo->query("select * from exam where begtime > now() order by begtime asc limit 0, 9;");
    $a = array();
    while($row = $rs->fetch()) {
		// add to array
        $a[] = new Exam($row['exmid'], $row['name'], $row['clsid'], $row['begtime'], $row['duration'], $row['corid']);
	}

    // merge
    $result = "未来考试\n--------";
    foreach($a as $temp) {
        // 查询课程信息
        $rs = $pdo->query("select * from course where corid = " . $temp->corid . ";");
        $row = $rs->fetch();
        if(!$row)
            return "课程没有对应上？内部错误！";
        $c = new Course($temp, $row['name'], $row['tchid'], $row['clsid']);

        // 获取教室
        $rs = $pdo->query("select * from classroom where clsid = " . $temp->clsid . ";");
        $row = $rs->fetch();
        if(!$row)
            return "教室没有对应上？内部错误！";
        $class_name = $row['name'];

        $result = $result . "\n" . $c->name . "\n".
            "( 考试名称: " . $temp->name . "; 教室: " . $class_name . "; 开始时间: " . date("Y-m-d h:m:s", strtotime($temp->begtime)) . "; 时长: " . $temp->duration . "分钟 )";
    }

    return $result;
}


// 查看已考完考试
function examPast($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 查询未来考试
    $rs = $pdo->query("select * from exam where begtime <= now() order by begtime desc limit 0, 9;");
    $a = array();
    while($row = $rs->fetch()) {
		// add to array
        $a[] = new Exam($row['exmid'], $row['name'], $row['clsid'], $row['begtime'], $row['duration'], $row['corid']);
	}

    // merge
    $result = "已完成考试\n--------";
    foreach($a as $temp) {
        // 查询课程信息
        $rs = $pdo->query("select * from course where corid = " . $temp->corid . ";");
        $row = $rs->fetch();
        if(!$row)
            return "课程没有对应上？内部错误！";
        $c = new Course($temp, $row['name'], $row['tchid'], $row['clsid']);

        // 获取教室
        $rs = $pdo->query("select * from classroom where clsid = " . $temp->clsid . ";");
        $row = $rs->fetch();
        if(!$row)
            return "教室没有对应上？内部错误！";
        $class_name = $row['name'];

        $result = $result . "\n" . $c->name . "\n".
            "( 考试名称: " . $temp->name . "; 教室: " . $class_name . "; 开始时间: " . date("Y-m-d h:m:s", strtotime($temp->begtime)) . "; 时长: " . $temp->duration . "分钟 )";
    }

    return $result;

}

?>
