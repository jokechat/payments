<?php
namespace Payments\Wxpay;
use Payments\Config\WxPayConf_pub;
use Payments\Msg\StatusMsg;
use Payments\Wxpay\UnifiedOrder_pub;
/**
 * 微信支付相关操作
 * @author jokechat
 * @date 2017年4月12日
 */
class Wxpay
{
    /**
     * 生成微信扫码支付数据
     * @param string $orderid 订单号
     * @param number $price 金额  单位元
     * @param string $body 支付明细描述
     * @param string $trade_type 支付类型  暂定扫码支付
     * @return  StatusMsg 返回json话的状态码
     */
    public function native_pay($orderid,$price,$body ="充值",$trade_type ="NATIVE")
    {

        $unifiedOrder       = new UnifiedOrder_pub();
        $code_url             = "";
        //自定义订单号，此处仅作举例
        $timeStamp         = time();
        $out_trade_no      = $orderid;
        $unifiedOrder->setParameter("body",$body);//商品描述
        $unifiedOrder->setParameter("out_trade_no",$out_trade_no);//商户订单号
        $unifiedOrder->setParameter("total_fee", $price*100);//总金额
        $notify_url            = WxPayConf_pub::NOTIFY_URL; // 支付回调网址
        $unifiedOrder->setParameter("notify_url", $notify_url);//通知地址
        $unifiedOrder->setParameter("trade_type",$trade_type);//交易类型
        //获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();
        
       $statusMsg           = new StatusMsg();

       //商户根据实际情况设置相应的处理流程
       if ($unifiedOrderResult["return_code"] == "FAIL")
       {
           $statusMsg->message      = $unifiedOrderResult['return_msg'];  
           return json_encode($statusMsg);
       }
       elseif($unifiedOrderResult["result_code"] == "FAIL")
       {
           $statusMsg->message  = $unifiedOrderResult['err_code_des'];
           return json_encode($statusMsg);
       }elseif($unifiedOrderResult["code_url"] != NULL)
       {
            //从统一支付接口获取到code_url
            $code_url = $unifiedOrderResult["code_url"];
       }
       
        $data['out_trade_no'] = $out_trade_no;
        $data['code_url'] = $code_url;
        $data['unifiedOrderResult'] = $unifiedOrderResult;
        $data['package']  = "Sign=WXPay";
        $data['timeStamp']= "$timeStamp";

       return $data;
        
    }
}
?>