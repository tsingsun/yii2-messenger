<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/10
 * Time: 上午11:48
 */

namespace yii\messenger\base;


use yii\base\Event;

class SendEvent extends Event
{
    /**
     * @var MessageInterface
     */
    public $message;

    /**
     * @var SendResult
     */
    public $sendResult;
}