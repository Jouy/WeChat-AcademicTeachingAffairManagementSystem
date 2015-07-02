<?php

/**
 * 教师表的相关内容
 * @author MewX <imewx@qq.com>
 */

// 定义教师类
class Teacher {
    public $tchid;
    public $name;

    function Teacher($id, $tname) {
        $this->tchid = $id;
        $this->name = $tname;
    }
}

?>
