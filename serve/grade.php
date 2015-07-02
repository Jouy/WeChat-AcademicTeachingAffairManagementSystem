<?php

/**
 * 成绩相关操作的函数
 * @author MewX <imewx@qq.com>
 */

// 包含基础通信函数
require_once('utils/shell.php');


// 定义成绩类
class Grade {
    public $grdid;
    public $stuid;
    public $corid;
    public $gradenew;
    public $gradeold;

    function Grade($a, $b, $c, $d, $e) {
        $this->grdid = $a;
        $this->stuid = $b;
        $this->corid = $c;
        $this->gradenew = $d;
        $this->gradeold = $e;
    }
}


// 所有成绩
function gradeAll($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select stuid from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 获取stuid
    $stuid = $row['stuid'];

    // 列举grade
    $rs = $pdo->query("select * from grade where stuid = '" . $stuid . "' limit 0, 9;");
    $a = array();
    while($row = $rs->fetch()) {
		// add to array
        $a[] = new Grade($row['grdid'], $row['stuid'], $row['corid'], $row['gradenew'], $row['gradeold']);
    }

    // merge
    $result = "全部成绩\n--------";
    foreach($a as $temp) {
        // 查询课程信息
        $rs = $pdo->query("select name from course where corid = " . $temp->corid . ";");
        $row = $rs->fetch();
        if(!$row)
            return "课程没有对应上？内部错误！";
        $course_name = $row['name'];

        $result = $result . "\n" . $course_name . "( 有效成绩: " . $temp->gradenew . "; 上次成绩: " . $temp->gradeold . " );";
    }

    return $result;
}


// 课程列表
function courseList($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 列举课程
    $rs = $pdo->query("select * from course limit 0, 99;");
    $a = array();
    while($row = $rs->fetch()) {
		// add to array
        $a[] = new Course($row['corid'], $row['name'], $row['tchid'], $row['clsid']);
    }

    // merge
    $result = "课程列表\n--------";
    foreach($a as $temp) {
        $result = $result . "\n" . $temp->corid . ": " . $temp->name . ";";
    }

    return $result;
}


// 某门课成绩
function gradeOne($wxid, $cid) {
    if(!is_numeric($cid))
        return "课程编号必须为数字！请重新输入！";

    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 获取stuid
    $stuid = $row['stuid'];

    // 查询课程信息
    $rs = $pdo->query("select name from course where corid = " . $cid . ";");
    $row = $rs->fetch();
    if(!$row)
        return "课程没有对应上？内部错误！";
    $course_name = $row['name'];

    $rs = $pdo->query("select * from grade where stuid = '" . $stuid . "' and corid = " . $cid . ";");
    $row = $rs->fetch();
    if(!$row)
        return "没有该考试的成绩信息！";

    return "成绩查询\n--------\n" . $course_name . "( 有效成绩: " . $row['gradenew'] . "; 上次成绩: " . $row['gradeold'] . " );";
}

?>
