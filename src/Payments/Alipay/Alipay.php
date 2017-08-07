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
    public function pay_app($orderid,$total_amount,$body,$subject,$timeout_express = "30m")
    {
        $submit = new AlipaySubmit();
        $result = $submit->buildParam($orderid,$total_amount,$body,$subject,$timeout_express);
        return $result;
    }
}
