<?php
namespace Payments\Alipay\App;
use Payments\Config\AlipayConfig;

/**
 * Created by PhpStorm.
 * User: jokechat
 * Date: 2017/8/4
 * Time: 09:47
 */

class AlipaySubmit
{

    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    private $fileCharset = "UTF-8";

    //私钥文件路径
    public $rsaPrivateKeyFilePath;

    //私钥值
    public $rsaPrivateKey;


    public function buildParam($orderid,$total_amount,$body,$subject,$timeout_express = "30m",$notify_url = "")
    {
        $ali_config             = new AlipayConfig();
        $config                 = $ali_config->getConfig();


        $timestamp              = date("Y-m-d H:i:s");

        // 测试对比数据
//        $timestamp              = "2017-08-04 11:06:12";

        $params['app_id']       = $config['app_id'];
        $params['method']       = $config['method'];
        $params['format']       = $config['format'];
        $params['sign_type']    = $config['sign_type'];
        $params['timestamp']    = $timestamp;
        $params['alipay_sdk']   = $config['alipay_sdk'];
        $params['charset']      = $config['charset'];
        $params['version']      = $config['version'];

        if (empty($notify_url)) {
            $params['notify_url']   = $config['notify_url'];
        }

        // 私钥路径/私钥key 二选一
        $this->setRsaPrivateKeyFilePath($config['ca_rsa_private_key_filepath']);

        // 业务信息
        $bizcontent['body']     = $body;
        $bizcontent['subject']  = $subject;
        $bizcontent['out_trade_no']     = $orderid;
        $bizcontent['timeout_express']  = $timeout_express;
        $bizcontent['total_amount']     = $total_amount;
        $bizcontent['product_code']     = $config['product_code'];

        $params['biz_content'] = json_encode($bizcontent,JSON_UNESCAPED_UNICODE);

        ksort($params);


        // 获取签名信息
        $params['sign']     =$this->generateSign($params,$config['sign_type']);

        return $params;
    }


    /**
     * 设置私钥路径
     * @param $path
     */
    public function setRsaPrivateKeyFilePath($path)
    {
        $this->rsaPrivateKeyFilePath = $path;
        return $this;
    }

    public function setRsaPrivateKey($key)
    {
        $this->rsaPrivateKey = $key;
        return $this;
    }



    private function generateSign($params, $signType = "RSA") {
        return $this->sign($this->getSignContent($params), $signType);
    }


    private function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    private function characet($data, $targetCharset) {

        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }

    protected function sign($data, $signType = "RSA") {
        if($this->checkEmpty($this->rsaPrivateKeyFilePath)){
            $priKey=$this->rsaPrivateKey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        }else {
            $priKey = file_get_contents(__DIR__."/".$this->rsaPrivateKeyFilePath);
            $res = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if(!$this->checkEmpty($this->rsaPrivateKeyFilePath)){
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }
}