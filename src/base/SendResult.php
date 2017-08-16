<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/10
 * Time: 上午11:55
 */

namespace yii\messenger\base;


class SendResult
{
    /**
     * @var bool 发送结果,成功或失败
     */
    public $status = false;
    /**
     * @var string 网关返回的消息ID
     */
    public $gatewayId;
    /**
     * @var string 异常消息
     */
    public $error;
}