<?php

/**
 * 课表相关操作的函数
 * @author MewX <imewx@qq.com>
 */

// 包含基础通信函数
require_once('utils/shell.php');


// 定义课程类
class Course {
    public $corid;
    public $name;
    public $tchid;
    public $clsid;

    function Course($id, $cname, $ctchid, $cclsid) {
        $this->corid = $id;
        $this->name = $cname;
        $this->tchid = $ctchid;
        $this->clsid = $cclsid;
    }
}

// 定义日程类
class Schedule {
    public $schid;
    public $corid = array(); // int

    function Schedule($id, $arr) {
        $this->schid = $id;
        $this->corid = $arr;
    }
}

// 定义日期类
class Daytime {
    public $dayid;
    public $schid;
    public $stuid;
    public $daydate;

    function Daytime($id, $dschid, $dstuid, $date) {
        $this->dayid = $id;
        $this->schid = $dschid;
        $this->stuid = $dstuid;
        $this->daydate = $date;
    }
}


// 获取某天的课表
function courseOneDay($wxid, $date) {
    // date("Ymd") 是当天的日期：20150513
    // 返回的格式：
    // 2015-05-13 周三/今天
    // 1,2 计算机导论
    // 7,8 通信原理

    // 转换日期格式为mysql识别的格式
    $date = date("Y-m-d", strtotime($date));
    $day = date("w", strtotime($date));
    $result = $date . " 周";
    switch($day) {
        case '1': $result = $result . "一"; break;
        case '2': $result = $result . "二"; break;
        case '3': $result = $result . "三"; break;
        case '4': $result = $result . "四"; break;
        case '5': $result = $result . "五"; break;
        case '0': return $result . "日\n（没课）";
        case '6': return $result . "六\n（没课）";
    }

    // 获取用户id
    $pdo = connectDatabase();
    $rs = $pdo->query("select stuid from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "未知的内部错误，总之是没找到你的微信号！";
    $id = $row['stuid'];

    // 查询Daytime是否已经存在，不存在的话就随机创建
    $rs = $pdo->query("select * from daytime where daydate = '" . $date . "' and stuid = " . $id .";");
    $row = $rs->fetch();
    $schid = 1;
    if(!$row) {

        // 获取最大的dayid值
        $rs = $pdo->query("select max(dayid) from daytime;");
        $row = $rs->fetch();

        // 创建一个随机的课表
        $rangevar = range(1,22);
        shuffle($rangevar);
        $schid = $rangevar[0];
        $pdo->exec("insert into daytime(dayid, schid, stuid, daydate) ".
            "values (" . ($row ? (int)($row['max(dayid)']) + 1: '1') . ", " . $schid . ", " . $id . ", '" . $date . "');");
    }
    else {
        // 已经安排课表了，输出
        $schid = $row['schid'];
    }

    // 获取当天课表
    $rs = $pdo->query("select corid01, corid02, corid03, corid04, corid05, corid06, corid07, corid08, corid09, corid10, corid11, corid12 from schedule where schid = " . $schid . ";");
    $row = $rs->fetch();

    // 获取课程列表
    $arr = array();
    $arr[] = $row['corid01'];
    $arr[] = $row['corid02'];
    $arr[] = $row['corid03'];
    $arr[] = $row['corid04'];
    $arr[] = $row['corid05'];
    $arr[] = $row['corid06'];
    $arr[] = $row['corid07'];
    $arr[] = $row['corid08'];
    $arr[] = $row['corid09'];
    $arr[] = $row['corid10'];
    $arr[] = $row['corid11'];
    $arr[] = $row['corid12'];

    // 循环判断
    for($i = 0; $i < count($arr); $i ++) {
        if(strcmp($arr[$i], "")) {
            // 不为空
            $temp = $arr[$i];
            $result = $result . "\n" . ($i+1);
            for($j = $i + 1; $j < count($arr); $j ++) {
                if($temp == $arr[$j]) {
                    $result = $result . ", " . ($j+1);
                    $i = $j;
                }
                else
                    break;
            }

            // 查询课程信息
            $rs = $pdo->query("select * from course where corid = " . $temp . ";");
            $row = $rs->fetch();
            if(!$row)
                return "课程没有对应上？内部错误！";
            $c = new Course($temp, $row['name'], $row['tchid'], $row['clsid']);

            // 获取教室
            $rs = $pdo->query("select * from classroom where clsid = " . $c->clsid . ";");
            $row = $rs->fetch();
            if(!$row)
                return "教室没有对应上？内部错误！";
            $class_name = $row['name'];

            // 获取教师
            $rs = $pdo->query("select * from teacher where tchid = " . $c->tchid . ";");
            $row = $rs->fetch();
            if(!$row)
                return "教师没有对应上？内部错误！";
            $teacher_name = $row['name'];

            $result = $result . "节: " . $c->name . "(" . $class_name . ", " . $teacher_name . ");";
        }
    }

    return $result;
}


// 获取今天课程表
function courseToday($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    return courseOneDay($wxid, date("Ymd"));
}


// 获取明天的课程表
function courseTomorrow($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    return courseOneDay($wxid, date('Ymd',strtotime('+1 day')));
}


// 获取昨天的课程表
function courseYesterday($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    return courseOneDay($wxid, date('Ymd',strtotime('-1 day')));
}


// 获取本周课表
function courseThisWeek($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 先判断当前是周几，然后汇总周一~周五的课表
    $date = date("Y-m-d");
    $day = date("w", strtotime($date));
    $result = "";
    for($i = 0; $i <= 6; $i ++) {
        if($i != 0)
            $result = $result . "\n--------\n";
        $result = $result . courseOneDay($wxid, date('Ymd',strtotime(($i - $day) . "day")));
    }
    return $result;
}


// 获取下周课表
function courseNextWeek($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 先判断当前是周几，然后汇总周一~周五的课表
    $date = date("Y-m-d");
    $day = date("w", strtotime($date));
    $result = "";
    for($i = 0; $i <= 6; $i ++) {
        if($i != 0)
            $result = $result . "\n--------\n";
        $result = $result . courseOneDay($wxid, date('Ymd',strtotime(($i + 7 - $day) . "day")));
    }
    return $result;
}


// 安全地获取指定日期的课表
function courseTarget($wxid, $date) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 先判断日期是否合法
    if(strlen($date) == 8 && !strcmp($date, date('Ymd',strtotime($date))))
        return courseOneDay($wxid, $date);
    return "输入的日期(" . $date . ")日期不合法！";
}

?>
