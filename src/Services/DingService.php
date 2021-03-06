<?php

namespace Kmf\Ding\Services;

use Kmf\Ding\Enum;
use Unirest;

class DingService
{
    private $app_id;
    private $app_key;
    private $app_dir;

    public function __construct()
    {
        $this->app_id = env('DING_APP_ID', '');
        $this->app_key = env('DING_APP_KEY', '');
        $this->app_dir = env('DING_APP_DIR', '/tmp');
    }

    public function __call($name, $arguments)
    {
        return false;
    }

    public function getTicKet()
    {
        $file = $this->app_dir . "/ticket_{$this->app_id}.log";
        $need_req = true;
        $ticket = null;

        if (file_exists($file)) {
            $content = file_get_contents($file);
            $objs = explode(':', $content);
            $time = $objs[0];
            $ticket = $objs[1];

            if (!empty($ticket) && time() - $time < 1 * 60 * 60) {
                $need_req = false;
            }
        }

        if ($need_req) {
            $ticket = $this->getTicketFromAPI();
            file_put_contents($file, time() . ':' . $ticket);
        }

        return $ticket;

    }

    private function getTicketFromAPI()
    {
        $obj = Unirest\Request::get(Enum\Constant::DING_API . '/basic/get_ticket', null, [
            'appid' => $this->app_id,
            'appkey' => $this->app_key
        ]);
        return !empty($obj->body->ticket) ? $obj->body->ticket : false;
    }

    /**
     * 查询用户信息 (建议工号查询)
     * @param $key 查询关键字，可以是工号、电话号码、姓名或邮箱，支持模糊匹配(工号除外)
     */
    public function queryUserInfo($key)
    {
        $obj = Unirest\Request::get(Enum\Constant::DING_API . '/contacts/users/query', null, [
            'key' => $key,
            'ticket' => $this->getTicKet()
        ]);
        $list = !empty($obj->body->list) ? $obj->body->list : [];
        $list = Enum\Helper::object2array($list);
        $list = Enum\Helper::keepFields($list, ['dingid', 'workcode', 'name', 'email', 'avatar']);
        return $list;
    }

    /**
     * Modified By zzY at 2018.01.16
     * 获取全部用户或通过指定关键词进行模糊搜索，模糊搜索不能用户id
     *  参数	            参数类型    必须      说明
     *  ids	            string	    否	    | 分隔的钉钉ID列表
     *  emptypes        string	    否	    | 分隔的员工类型列表，可为空，包括全职员工、全职教师、专职教师、兼职教师、兼职员工、在编实习
     *  department_ids	string	    否	    | 分隔的钉钉部门ID列表，可为空
     *  emails	        string	    否	    | 分隔的邮箱列表，可为空
     *  workcodes	    string	    否	    | 分隔的工号ID列表，可为空
     *  mobiles	        string	    否	    | 分隔的手机号码列表，可为空
     *  names	        string	    否	    | 分隔的姓名列表，列表中只有一个值时返回模糊查询结果，可为空
     *  logic	        string	    否	    and 或 or，条件的运算规则，默认为or，返回条件结果的合集
     *  sub	            bool	    否	    true 返回包含子部门的数据，false只返回当前部门的数据
     *  page	        int	        否	    数据量大的时候建议用分页 例如 1
     * @param array $params  参数可空，用于兼容早期版本
     * @return array|mixed
     */
    public function getAllUsers(array  $params = [])
    {
        $params['ticket'] = $this->getTicKet();
        $obj = Unirest\Request::get(Enum\Constant::DING_API . '/contacts/users/get/ding', null, $params);
        $list = !empty($obj->body->list) ? $obj->body->list : [];
        $list = Enum\Helper::object2array($list);
        $list = Enum\Helper::keepFields($list, ['dingid', 'workcode', 'name', 'email', 'avatar', 'department_ids']);
        return $list;
    }

    /**
     * 通过钉钉ID发送消息通知
     * @param $dingids 用户钉钉ID  array
     * @param $content 发送内容
     * @param string $type 消息类型 link text oa
     */
    public function sendMsgByDingId($dingids, $content, $type = 'text')
    {
        return $this->sendMsg('id', $dingids, $content, $type);
    }

    /**
     * 通过邮箱发送消息
     * @param $emails
     * @param $content
     * @param string $type
     * @return mixed
     */
    public function sendMsgByEmail($emails, $content, $type = 'text')
    {
        return $this->sendMsg('email', $emails, $content, $type);
    }

    /**
     * 消息发送
     * 单发与群发 群发人数不能超过100人
     * @param string $send_type id(钉钉ID), email, workcode(工号)
     * @param $targetIds 目标用户，根据send_type写入  array
     * @param $content 发送内容
     * @param $type
     * @return bool
     */
    private function sendMsg($send_type = 'id', $targetIds, $content, $type = 'text')
    {
        if (empty($targetIds) || count($targetIds) > 100) {
            return false;
        }
        $key = 'dingids';
        switch ($send_type) {
            case 'id':
                $key = 'dingids';
                break;
            case 'email':
                $key = 'emails';
                break;
            case 'workcode':
                $key = 'workcodes';
                break;
        }
        !is_array($targetIds) && ($targetIds = array($targetIds));
        $str_targetIds = implode('|', $targetIds);
        $content = base64_encode($content);
        $obj = Unirest\Request::get(Enum\Constant::DING_API . '/message/ding_notice', null, [
            'ticket' => $this->getTicKet(),
            "{$key}" => $str_targetIds,
            'content' => $content,
            'type' => $type,
        ]);
        $data['errcode'] = $obj->body->errcode;
        $data['errmsg'] = $obj->body->errmsg;
        $data['messageId'] = $obj->body->messageId;
        return $data;
    }

    /**
     * 获取钉钉二维码登录地址
     *
     * @author caoyang <caoyang@kmf.com>
     * @return string
     */
    public function loginUrl()
    {
        $url = Enum\Constant::DING_BASE_HOST . '/sso/login/' . $this->app_id . '?ticket=' . $this->getTicket();
        return $url;
    }

    /**
     * 获取钉钉退出登录地址
     *
     * @author caoyang <caoyang@kmf.com>
     * @param $path 退出登录后跳转地址
     * @return string
     */
    public function logoutUrl($path)
    {
        $url = Enum\Constant::DING_API . '/sso/logout?path=' . $path;
        return $url;
    }

    public function verify($token)
    {
        $obj = Unirest\Request::get(Enum\Constant::DING_API . '/sso/verify', null, [
            'token' => $token,
            'ticket' => $this->getTicKet()
        ]);

        return $obj->body;
    }

    /**
     * 获取部门以及子部门
     * @author caoyang <caoyang@kmf.com>
     *
     * @param $ids
     * @return array
     */
    public function getDepartments($ids)
    {
        $obj = Unirest\Request::get(Enum\Constant::DING_API . '/contacts/departments/get/ding?sub=true&ids=' . $ids, null, [
            'ticket' => $this->getTicKet()
        ]);

        $list = !empty($obj->body->list) ? $obj->body->list : [];

        return $list;
    }
}