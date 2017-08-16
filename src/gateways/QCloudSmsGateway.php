<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/2/8
 * Time: 下午3:43
 */

namespace yii\messenger\gateways;

require_once "QCloudSmsSDK.php";

use Qcloud\Sms\SmsSingleSender;
use Qcloud\Sms\SmsMultiSender;
use yii\messenger\base\GatewayErrorException;
use yii\messenger\base\MessageInterface;
use yii\messenger\base\SendResult;
use yii\messenger\messages\SmsMessage;

/**
 * sms通道的腾讯短信网关
 *
 * @package yii\messenger\gateways
 */
class QCloudSmsGateway extends BaseGateway
{
    public $appId;
    public $appKey;
    public $sign;

    /**
     * @param SmsMessage $message
     * @return array
     */
    public function formatMessage($message)
    {
        $result = [
            'nationCode' => $message->nationCode,
            'tpl_id' => $message->getTemplate($this),
            'mobile' => $message->getTo(),
            'params' => array_values($message->getExtra($this)),
            'sign' => isset($message->sign) ? $message->sign : $this->sign,
        ];
        if (!$result['tpl_id']) {
            //无模板时,内容需要自己处理签名
            $result['msg'] = $message->getContent($this) . "【{$result['sign']}】";
        }
        return $result;
    }

    public function sendInternal($to, MessageInterface $message, $config = [])
    {
        $ready = $this->formatMessage(SmsMessage::parseMessage($message));
        $template = $ready['tpl_id'];
        $to = $message->getTo();
        if ($template) {
            //使用模板
            if (is_array($to)) {
                $result = $this->sendMultiWithParams($ready['nationCode'], $to, $template, $ready['params'], $ready['sign']);
            } else {
                $result = $this->sendSingleWithParams($ready['nationCode'], $to, $template, $ready['params'], $ready['sign']);
            }
        } else {
            if (is_array($to)) {
                $result = $this->sendMulti(0, $ready['nationCode'], $to, $ready['msg']);
            } else {
                $result = $this->sendSingle(0, $ready['nationCode'], $to, $ready['msg']);
            }
        }
        return $this->handleResult($result);
    }

    /**
     * @param $result
     * @return SendResult
     * @throws GatewayErrorException
     */
    private function handleResult($result)
    {
        $ret = new SendResult();
        if (isset($result['ActionStatus']) && $result['ActionStatus'] == 'FAIL') {
            throw new GatewayErrorException($result['ErrorInfo'], $result['ErrorCode']);
        } elseif ($result['result'] != 0) {
            throw new GatewayErrorException($result['ErrorInfo'], $result['ErrorCode']);
        } else {
            $ret->status = true;
            $ret->gatewayId = $result['sid'];
        }
        return $ret;
    }


    /**
     * 普通单发，明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名
     * @param int $type 短信类型，0 为普通短信，1 营销短信
     * @param string $nationCode 国家码，如 86 为中国
     * @param string $phoneNumber 不带国家码的手机号
     * @param string $msg 信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param string $extend 扩展码，可填空串
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return string json string { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    public function sendSingle($type, $nationCode, $phoneNumber, $msg, $extend = '', $ext = '')
    {
        $singleSender = new SmsSingleSender($this->appId, $this->appKey);
        $result = $singleSender->send($type, $nationCode, $phoneNumber, $msg, $extend, $ext);
        return json_decode($result, true);
    }

    /**
     * 指定模板单发
     * @param string $nationCode 国家码，如 86 为中国
     * @param string $phoneNumber 不带国家码的手机号
     * @param int $templId 模板 id
     * @param array $params 模板参数列表，如模板 {1}...{2}...{3}，那么需要带三个参数
     * @param string $sign 签名，如果填空串，系统会使用默认签名
     * @param string $extend 扩展码，可填空串
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return string json string { "result": xxxxx, "errmsg": "xxxxxx"  ... }，被省略的内容参见协议文档
     */
    public function sendSingleWithParams($nationCode, $phoneNumber, $templId, $params, $sign, $extend = '', $ext = '')
    {
        $singleSender = new SmsSingleSender($this->appId, $this->appKey);
        $result = $singleSender->sendWithParam($nationCode, $phoneNumber, $templId, $params, $sign, $extend, $ext);
        return json_decode($result, true);
    }

    /**
     * 普通群发，明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名
     * 【注意】海外短信无群发功能
     * @param int $type 短信类型，0 为普通短信，1 营销短信
     * @param string $nationCode 国家码，如 86 为中国
     * @param string $phoneNumbers 不带国家码的手机号列表
     * @param string $msg 信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param string $extend 扩展码，可填空串
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return string json string { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    public function sendMulti($type, $nationCode, $phoneNumbers, $msg, $extend = "", $ext = "")
    {
        $multiSender = new SmsMultiSender($this->appId, $this->appKey);
        $result = $multiSender->send($type, $nationCode, $phoneNumbers, $msg, $extend, $ext);
        return json_decode($result, true);
    }

    /**
     * 指定模板群发
     * 【注意】海外短信无群发功能
     * @param string $nationCode 国家码，如 86 为中国
     * @param array $phoneNumbers 不带国家码的手机号列表
     * @param int $templId 模板 id
     * @param array $params 模板参数列表，如模板 {1}...{2}...{3}，那么需要带三个参数
     * @param string $sign 签名，如果填空串，系统会使用默认签名
     * @param string $extend 扩展码，可填空串
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return string json string { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    public function sendMultiWithParams($nationCode, $phoneNumbers, $templId, $params, $sign = '', $extend = '', $ext = '')
    {
        $multiSender = new SmsMultiSender($this->appId, $this->appKey);
        $result = $multiSender->sendWithParam($nationCode, $phoneNumbers, $templId, $params, $sign, $extend, $ext);
        return json_decode($result, true);
    }

}