<?php

/**
 * 账号相关操作的函数
 * @author MewX <imewx@qq.com>
 */

// 包含基础通信函数
require_once('utils/shell.php');


// 获取密码的散列密文
function getPasswordEncrypted($password) {
    return base64_encode(hash('sha256', $password));
}

// 根据 username 和 password 绑定当前的wxid
function accountBinding($wxid, $username, $password) {
    // 防范SQL注入
    $username = _remove_sql_inject($username);
    $password = getPasswordEncrypted($password);

    // 校验账号和密码
    $pdo = connectDatabase();
    $rs = $pdo->query("select stuid from student where stuid = '" . $username.
        "' and pwd = '" . $password . "';");
    $row = $rs->fetch();
    if(!$row)
        return "账号不存在或密码错误！";

    // 检查是否绑定，已绑定不允许操作
    $rs = $pdo->query("select stuid from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if($row)
        return "您已绑定该学号[" . $row['stuid'] . "]，请先解绑！";

    // 检查目标账号是否被绑定
    $rs = $pdo->query("select wxid from student where stuid = '" . $username . "';");
    $row = $rs->fetch();
    if(!$row || strcmp($row['wxid'], ""))
        return "该学号已被其他用户绑定，无法再次绑定！";

    // 绑定操作
    $pdo->exec("update student set wxid = '" . $wxid . "' where stuid = '" . $username . "';");

    return "祝贺您绑定成功! ==> " . $username;
}


// 根据 wxid 获取账号信息
function accountInfo($wxid) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 获取账号信息
    $result = "获取账号信息\n--------\n".
        "学号：" . $row['stuid'] . "\n".
        "姓名：" . $row['name'] . "\n".
        "性别：" . (!strcmp($row['sex'], 'M') ? '男' : '女') . "\n".
        "少数名族：" . $row['ethnicgroups'] . "\n".
        "寝室房号：" . $row['address'] . "\n".
        "出生地：" . $row['birthplace'] . "\n".
        "政治面貌：" . $row['politicalstatus'] . "\n".
        "身份证号：" . $row['idcard'] . "\n".
        "备注：" . $row['comments'];

    return $result;
}


// 密码修改
function accountChangePassword($wxid, $oldpwd, $newpwd) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);
    $oldpwd = getPasswordEncrypted($oldpwd);
    $newpwd = getPasswordEncrypted($newpwd);

    // 检查旧密码和新密码是否相同
    if(!strcmp($oldpwd, $newpwd))
        return "新旧密码相同，无需修改！";

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select * from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 获取账号，保护部分账号
    $stuid = $row['stuid'];
    if(!strcmp($stuid,"201230") || !strcmp($stuid,"201231") || !strcmp($stuid,"201232"))
        return "受保护的账号，密码无法修改。（new指令创建的账号可以修改密码。）";

    // 校验账号和密码
    $rs = $pdo->query("select stuid from student where wxid = '" . $wxid.
        "' and pwd = '" . $oldpwd . "';");
    $row = $rs->fetch();
    if(!$row)
        return "密码错误！修改密码失败。";

    // 修改密码
    $pdo->exec("update student set pwd = '" . $newpwd . "' where wxid = '" . $wxid . "';");

    return "密码修改成功";
}


// 取消绑定
function accountCancelBinding($wxid, $password) {
    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);
    $password = getPasswordEncrypted($password);

    // 检查是否绑定，未绑定不允许操作
    $pdo = connectDatabase();
    $rs = $pdo->query("select stuid from student where wxid = '" . $wxid . "';");
    $row = $rs->fetch();
    if(!$row)
        return "请先绑定！";

    // 校验账号和密码
    $rs = $pdo->query("select stuid from student where wxid = '" . $wxid.
        "' and pwd = '" . $password . "';");
    $row = $rs->fetch();
    if(!$row)
        return "密码错误！解绑失败。";

    // 解绑
    $pdo->exec("update student set wxid = '' where wxid = '" . $wxid . "';");

    return "成功取消绑定！";
}


// 新建一个测试用户，并绑定
function accountNew($wxid, $username, $password) {
    // 先取消绑定
    accountCancelBinding($wxid, $password);

    // 防范SQL注入
    $wxid = _remove_sql_inject($wxid);
    $username = _remove_sql_inject($username);
    $password = getPasswordEncrypted($password);

    // 创建随机的个人信息
    $pdo = connectDatabase();
    $rs = $pdo->query("select max(stuid) from student;");
    $row = $rs->fetch();
    if(!$row)
        return "内部错误，学生表为空！";
    $new_stuid = $row['max(stuid)'] + 1;
    $pdo->exec("insert into student(stuid,wxid,pwd,name) values ('" . $new_stuid . "', '" . $wxid . "', '" . $password . "', '" . $username . "')");

    // 复制其他的成绩信息
    $rs = $pdo->query("select max(grdid) from grade;");
    $row = $rs->fetch();
    if(!$row)
        return "内部错误，成绩表为空！";
    $new_grdid = $row['max(grdid)'] + 1;
    $pdo->exec("insert into grade(grdid,stuid,corid,gradenew,gradeold) values ('" . $new_grdid . "','" . $new_stuid . "','1','51.5','0.0');");
    $new_grdid ++;
    $pdo->exec("insert into grade(grdid,stuid,corid,gradenew,gradeold) values ('" . $new_grdid . "','" . $new_stuid . "','3','70.0','0.0');");
    $new_grdid ++;
    $pdo->exec("insert into grade(grdid,stuid,corid,gradenew,gradeold) values ('" . $new_grdid . "','" . $new_stuid . "','6','80.5','51.5');");

    return "创建新的用户" . $new_stuid . "成功，并已完成绑定！";
}

?>
