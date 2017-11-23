<?php

namespace Kmf\Ding;

class Api
{

    const QUERY_USER_INFO = 'queryUserInfo';
    const GET_ALL_USERS = 'getAllUsers';
    const SEND_MSG_EMAIL = 'sendMsgByEmail';
    const SEND_MSG_DINGID = 'sendMsgByDingId';

    public static function query()
    {
        $args = func_get_args();
        $obj = new \Kmf\Ding\Services\DingService();
        $function = $args[0];
        array_shift($args);
        $result = call_user_func_array(array($obj, $function), $args);
        return $result;
    }
}

