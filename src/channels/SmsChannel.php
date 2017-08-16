<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 上午10:34
 */

namespace yii\messenger\channels;


use yii\messenger\base\ChannelInterface;

class SmsChannel extends BaseChannel
{
    public function getChannelType()
    {
        return ChannelInterface::CHANNEL_SMS;
    }

}