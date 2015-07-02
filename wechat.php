<?php

/**
 * 微信公众平台的的接入文件
 * @author MewX <imewx@qq.com>
 */

// 安全模式需要的官方加密文件
require_once("utils/wechat_sdk.php");

// 拼音转换引擎
require_once("utils/pinyin_engine.php");

// SQL注入免疫程序
require_once("utils/sql_no_injection.php");

// 辅助类文件
require_once("serve/teacher.php");
require_once("serve/classroom.php");

// 功能函数文件
require_once("serve/account.php");
require_once("serve/grade.php");
require_once("serve/course.php");
require_once("serve/notice.php");
require_once("serve/exam.php");

// 微信信息代码
define("TOKEN", "ATAMS");
define("AppID", "#AppID Here#");
define("EncodingAESKey", "#EncodingAESKey Here#");

class ATAMSWechat extends Wechat {
    protected function onSubscribe() {
        $this->responseText("欢迎关注xtoolbox模拟教务管理系统！\n本系统的特色是高安全性（散列、防注入）、模糊语音查询！尽情体验~\n★ 输入“help/帮助”可以获取命令列表。");
    }

    protected function onUnsubscribe() {
        //$this->responseText('不要不要哒~');
        // 取消当前账号的绑定信息，留给其他测试人员进行绑定
        $this->dispatchMessage("14", "1");
    }

    protected function onScan() {
        //$this->responseText('二维码的EventKey：' . $this->getRequest('EventKey'));
        $this->responseText('又扫我干嘛，我不要不要哒！');
    }

    protected function onEventLocation() {
        //$this->responseText('收到了位置推送：' . $this->getRequest('Latitude') . ',' . $this->getRequest('Longitude'));
        $this->responseText('发位置推送干嘛，我不要不要哒！');
    }

    protected function onLocation() {
        //$this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
        $this->responseText('发位置干嘛，我不要不要哒！');
    }

    protected function onLink() {
        //$this->responseText('收到了链接：' . $this->getRequest('url'));
        $this->responseText('发链接干嘛，我不要不要哒！');
    }

    protected function onImage() {
        $this->responseText('发图片干嘛，我不要不要哒！');
    }

    protected function onText() {
        $temp = $this->getRequest('content');

        $firstPlus = strpos($temp, "+");
        $this->responseText(
            $this->dispatchMessage(
                (is_int($firstPlus) ? substr($temp, 0, $firstPlus) : $temp),
                (is_int($firstPlus) ? substr($temp, $firstPlus + 1, strlen($temp) - $firstPlus - 1) : "")
            )
        );
    }

    protected function onVoice() {
        $result = "语音原文：";
        $temp = $this->getRequest('recognition');
        $result = "语音原文：" . $temp . "\n";
        $temp = $this->blurRecognition($temp);
        $result = $result . "智能转码：" . $temp . "\n--------\n";

        // 文本处理模式
        $firstPlus = strpos($temp, "+");
        $this->responseText(
            $result . $this->dispatchMessage(
                (is_int($firstPlus) ? substr($temp, 0, $firstPlus) : $temp),
                (is_int($firstPlus) ? substr($temp, $firstPlus + 1, strlen($temp) - $firstPlus - 1) : "")
            )
        );

        //$this->responseText('收到了语音消息,识别结果为：' . $this->blurRecognition($temp));
    }

    protected function onUnknown() {
        $this->responseText('这一定是微信的BUG，不要不要哒！');
    }


    /* 模糊识别语音 */
    private function blurRecognition($msg) {
        // 判断工序：标准发音、代码、代码中文（含连接符、不含）、近似读法、关键词判断

        // 转换为拼音
        $py = Pinyin($msg);
        $py = str_replace("jia","+",$py);

        // 功能词help、new
        if($py == "niu" || $py == "liu" || $msg == "妞") {
            return "new";
        }
        elseif($msg == "跳" || $msg == "要不" || $msg == "老婆" || $msg == "好吧"
                || $msg == "那不" || $msg == "泡泡" || $msg == "帮助" || $msg == "好"
                || $msg == "脑" || $msg == "哪个") {
            return "help";
        }

        // 把百、千替换掉
        $py = str_replace("bai", "", $py);
        $py = str_replace("qian", "", $py);

        // 分解数字，年份、上十位数
        $py = str_replace("yi", "1", $py);
        $py = str_replace("er", "2", $py);
        $py = str_replace("san", "3", $py);
        $py = str_replace("si", "4", $py);
        $py = str_replace("wu", "5", $py);
        $py = str_replace("liu", "6", $py);
        $py = str_replace("qi", "7", $py);
        $py = str_replace("ba", "8", $py);
        $py = str_replace("jiu", "9", $py);

        // 判断10的问题（一次只替换一个）
        $i = 0;
        while(true) {
            $i ++;
            if($i > 10) break;
            $pos = strpos($py, "shi");
            if(is_bool($pos)) break;

            if($pos == 0 || $py[$pos - 1] == '+')
                $py = preg_replace("/shi/", "1", $py, 1);
            elseif(strlen($py) == $pos + 3 || $py[$pos + 3] == '+')
                $py = preg_replace("/shi/", "0", $py, 1);
            else
                $py = preg_replace("/shi/", "", $py, 1);
        }

        return $py;
    }

    /* 根据收到的指令分派代码 */
    private function dispatchMessage($msg, $plus) {
        /**
         * 指令列表
         **
         * - 语音可以识别你需要的服务，然后弹出语法
         *
         * 1    账号操作
         * - 11 账号绑定（语法：11+用户名+密码）：e.g. 11+MewX+pwd | 亲，密码就别读出来了吧！
         * - 12 信息查询（语法：12）
         * - 13 密码修改（语法：13+旧密码+新密码）
         * - 14 取消绑定（语法：14+密码）
         *
         * 2    成绩查询
         * - 21 所有成绩（语法：21）
         * - 22 课程列表（语法：22）
         * - 23 某门课成绩（语法：23+课程序号）
         *
         * 3    课表查询
         * - 31 今天课表（语法：31）
         * - 32 明天课表（语法：32）
         * - 33 昨天课表（语法：33）
         * - 34 本周课表（语法：34）
         * - 35 下周课表（语法：35）
         * - 36 某天课表（语法：36+8位数日期。例如：36+20150513）
         *
         * 4    通知查询
         * - 41 通知列表（语法：41）
         * - 42 查看通知（语法：42+通知序号）
         *
         * 5    考试查询
         * - 51 未来考试（语法：51）
         * - 52 已结束考试（语法：52）
         *
         */

        $msg = trim($msg);
        $plus = trim($plus);
        $arr = explode("+", $plus);
        switch($msg) {
            case "帮助":
            case "h":
            case "help":// 上面识别“帮助”
                return "指令大类列表（输入序号）：\n".
                    "new - 创建一个随机信息的账户\n".
                    "测试账户用户名201230、201231、201232密码均为1\n\n".
                    "1 - 账号操作\n".
                    "2 - 成绩查询\n".
                    "3 - 课表查询\n".
                    "4 - 通知查询\n".
                    "5 - 考试查询";

            case "1":
                return "指令列表（账号操作）：\n".
                    "11 - 账号绑定（语法：11+用户名+密码）\n".
                    "12 - 信息查询（语法：12）\n".
                    "13 - 密码修改（语法：13+旧密码+新密码）\n".
                    "14 - 取消绑定（语法：14+密码）";

            case "2":
                return "指令列表（成绩查询）：\n".
                    "21 - 所有成绩（语法：21）\n".
                    "22 - 课程列表（语法：22）\n".
                    "23 - 某门课成绩（语法：23+课程序号）";

            case "3":
                return "指令列表（课表查询）：\n".
                    "31 - 今天课表（语法：31）\n".
                    "32 - 明天课表（语法：32）\n".
                    "33 - 昨天课表（语法：33）\n".
                    "34 - 本周课表（语法：34）\n".
                    "35 - 下周课表（语法：35）\n".
                    "36 - 某天课表（语法：36+8位数日期。例如：36+20150513）";

            case "4":
                return "指令列表（通知查询）：\n".
                    "41 - 通知列表（语法：41）\n".
                    "42 - 查看通知（语法：42+通知序号）";

            case "5":
                return "指令列表（考试查询）：\n".
                    "51 - 未来考试（语法：51）\n".
                    "52 - 已结束考试（语法：52）";


            case "11":
                if(sizeof($arr) != 2) return "参数长度不对！\n账号绑定（语法：11+用户名+密码）";
                else return accountBinding($this->getRequest('fromusername'), $arr[0], $arr[1]);

            case "12":
                return accountInfo($this->getRequest('fromusername'));

            case "13":
                if(sizeof($arr) != 2) return "参数长度不对！\n密码修改（语法：13+旧密码+新密码）";
                else return accountChangePassword($this->getRequest('fromusername'), $arr[0], $arr[1]);

            case "14":
                // 这里一个参数不行
                if(sizeof($arr) != 1 || $arr[0] == "") return "参数长度不对！\n取消绑定（语法：14+密码）";
                else return accountCancelBinding($this->getRequest('fromusername'), $arr[0]);


            case "21":
                return gradeAll($this->getRequest('fromusername'));

            case "22":
                return courseList($this->getRequest('fromusername'));

            case "23":
                // 这里一个参数不行
                if(sizeof($arr) != 1 || $arr[0] == "") return "参数长度不对！\n某门课成绩（语法：23+课程序号）";
                else return gradeOne($this->getRequest('fromusername'), $arr[0]);


            case "31":
                return courseToday($this->getRequest('fromusername'));

            case "32":
                return courseTomorrow($this->getRequest('fromusername'));

            case "33":
                return courseYesterday($this->getRequest('fromusername'));

            case "34":
                return courseThisWeek($this->getRequest('fromusername'));

            case "35":
                return courseNextWeek($this->getRequest('fromusername'));

            case "36":
                // 这里一个参数不行
                if(sizeof($arr) != 1 || $arr[0] == "") return "参数长度不对！\n某天课表（语法：36+8位数日期。例如：36+20150513）";
                else return courseTarget($this->getRequest('fromusername'), $arr[0]);


            case "41":
                return noticeList();

            case "42":
                // 这里一个参数不行
                if(sizeof($arr) != 1 || $arr[0] == "") return "参数长度不对！\n查看通知（语法：42+通知序号）";
                    return noticeContent($arr[0]);


            case "51":
                return examFunture($this->getRequest('fromusername'));

            case "52":
                return examPast($this->getRequest('fromusername'));

            case "new":
                if(sizeof($arr) != 2) return "创建一个随机信息的账户\n（语法：new+用户名+密码）";
                else return accountNew($this->getRequest('fromusername'), $arr[0], $arr[1]);


            default:
                return "不知道您所云何物，请输入“help/帮助”指令查看帮助~";
        }

    }
}

// 定义封装的微信类
$wechat = new ATAMSWechat(TOKEN, AppID, EncodingAESKey, TRUE);
$wechat->run();

?>
