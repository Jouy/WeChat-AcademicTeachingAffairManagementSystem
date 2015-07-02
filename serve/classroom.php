<?php

/**
 * 教室表的相关内容
 * @author MewX <imewx@qq.com>
 */

// 定义教室类
class Classroom {
    public $clsid;
    public $name;
    public $capacity;

    function Classroom($id, $cname, $cc) {
        $this->clsid = $id;
        $this->name = $cname;
        $this->capacity = $cc;
    }
}

?>
