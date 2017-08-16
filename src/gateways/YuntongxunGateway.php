<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace yii\messenger\gateways;

use yii\messenger\base\GatewayErrorException;
use yii\messenger\base\HasHttpRequest;
use yii\messenger\base\MessageInterface;
use yii\messenger\base\SendResult;
use yii\web\BadRequestHttpException;

/**
 * sms通道的容联云通讯
 *
 * @see http://www.yuntongxun.com/doc/rest/sms/3_2_2_2.html
 */
class YuntongxunGateway extends BaseGateway
{
    use HasHttpRequest;

    const ENDPOINT_TEMPLATE = 'https://%s:%s/%s/%s/%s/%s/%s?sig=%s';
    const SERVER_IP = 'app.cloopen.com';
    const DEBUG_SERVER_IP = 'sandboxapp.cloopen.com';
    const SERVER_PORT = '8883';
    const SDK_VERSION = '2013-12-26';
    const SUCCESS_CODE = '000000';

    public $appId;
    public $accountSid;
    public $isSubAccount;
    public $accountToken;

    /**
     *
     * @param MessageInterface $message
     * @return array;
     */
    public function formatMessage($message)
    {
        return [
            'to' => $message->getTo(),
            'templateId' => $message->getTemplate($this),
            'appId' => $this->appId,
            'datas' => $message->getExtra($this),
        ];
    }


    /**
     * @param array|int|string $to
     * @param MessageInterface $message
     * @param array $config
     *
     * @return SendResult
     *
     * @throws BadRequestHttpException;
     */
    public function sendInternal($to, MessageInterface $message, $config = [])
    {
        $datetime = date('YmdHis');

        $endpoint = $this->buildEndpoint('SMS', 'TemplateSMS', $datetime, $config);

        $result = $this->request('post', $endpoint, [
            'json' => $this->formatMessage($message),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json;charset=utf-8',
                'Authorization' => base64_encode($this->accountSid . ':' . $datetime),
            ],
        ]);

        $ret = new SendResult();
        if ($result['statusCode'] != self::SUCCESS_CODE) {
//            $ret->error = $result['statusCode'];
            throw new GatewayErrorException($result['statusCode'], $result['statusCode'], $result);
        } else {
            $ret->status = true;
            $ret->gatewayId = $result['smsMessageSid'];
        }

        return $ret;
    }

    /**
     * Build endpoint url.
     *
     * @param string $type
     * @param string $resource
     * @param string $datetime
     * @param array $config
     *
     * @return string
     */
    protected function buildEndpoint($type, $resource, $datetime, $config = [])
    {
        $serverIp = YII_DEBUG ? self::DEBUG_SERVER_IP : self::SERVER_IP;

        $accountType = $this->isSubAccount ? 'SubAccounts' : 'Accounts';

        $sig = strtoupper(md5($this->accountSid . $this->accountToken . $datetime));

        return sprintf(self::ENDPOINT_TEMPLATE, $serverIp, self::SERVER_PORT, self::SDK_VERSION, $accountType, $this->accountSid, $type, $resource, $sig);
    }
}
