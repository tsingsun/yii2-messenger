<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/8
 * Time: 上午10:01
 */

namespace yii\messenger\channels;


use yii\messenger\base\ChannelInterface;

class EmailChannel extends BaseChannel
{
    public function getChannelType()
    {
        return ChannelInterface::CHANNEL_EMAIL;
    }

}