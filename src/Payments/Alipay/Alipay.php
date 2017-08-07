<?php
namespace Payments\Alipay;
use Payments\Alipay\App\AlipaySubmit;

/**
 * Created by PhpStorm.
 * User: jokechat
 * Date: 2017/8/4
 * Time: 00:23
 */

class Alipay
{
    /**
     * 生成app支付数据
     * @param $orderid 订单id
     * @param $total_amount 订单金额
     * @param $body 主体说明
     * @param $subject
     * @param string $timeout_express 支付超时时间
     * @param string $notify_url 回调网址
     * @return mixed
     */
    public function pay_app($orderid,$total_amount,$body,$subject,$timeout_express = "30m",$notify_url = "")
    {
        $submit = new AlipaySubmit();
        $result = $submit->buildParam($orderid,$total_amount,$body,$subject,$timeout_express,$notify_url);
        return $result;
    }
}
