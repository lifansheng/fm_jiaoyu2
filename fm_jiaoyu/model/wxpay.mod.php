<?php
class WxpayService
{
    protected $mchid;
    protected $appid;
    protected $apiKey;
    public $data = null;
    public function __construct($payweid,$paytype) {
		$setting = uni_setting_load('payment',  $payweid);
		if($paytype == 'wxapp'){
			$appid = pdo_getcolumn('account_wxapp', array('acid' => $payweid), 'key');
			$wxpayset = $setting['payment']['wechat'];
			$this->mchid = $wxpayset['mchid']; //https://pay.weixin.qq.com 产品中心-开发配置-商户号
			$this->appid = $appid; //微信支付申请对应的公众号的APPID
			$this->apiKey = $wxpayset['signkey'];   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
		}else{
			$appid = pdo_getcolumn('account_wechats', array('acid' => $payweid), 'key');
			$wxpayset = $setting['payment']['wechat'];
			$this->mchid = $wxpayset['mchid']; //https://pay.weixin.qq.com 产品中心-开发配置-商户号
			$this->appid = $appid; //微信支付申请对应的公众号的APPID
			$this->apiKey = $wxpayset['apikey'];   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
		}
	}
	 /**
	 * 查询订单
	 * @param string out_trade_no 本地订单号wxpayid
	 * @return string
	 */
	public function orderquery($wxpayid){
		$config = array(
			'mch_id' => $this->mchid,
			'appid' => $this->appid,
			'key' => $this->apiKey,
		);
		$unified = array(
			'appid' => $config['appid'],
			'mch_id' => $config['mch_id'],
			'nonce_str' => self::createNonceStr(),
			'out_trade_no' => $wxpayid,
		);
		$unified['sign'] = self::getSign($unified, $config['key']);
		$responseXml = $this->curlPost($payweid,'https://api.mch.weixin.qq.com/pay/orderquery', self::arrayToXml($unified));
		$unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $unifiedOrder;
	}
	 /**
	 * 企业付款
	 * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
	 * @param float $totalFee 收款总费用 单位元
	 * @param string $outTradeNo 唯一的订单号
	 * @param string $orderName 订单名称
	 * @param string $notifyUrl 支付结果通知url 不要有问号
	 * @param string $timestamp 支付时间
	 * @param string $desc 备注必填
	 * @return string
	 */
	public function createJsBizPackage($openid, $totalFee, $outTradeNo,$trueName,$payweid,$desc){
		$config = array(
			'mch_id' => $this->mchid,
			'appid' => $this->appid,
			'key' => $this->apiKey,
		);
		$unified = array(
			'mch_appid' => $config['appid'],
			'mchid' => $config['mch_id'],
			'nonce_str' => self::createNonceStr(),
			'openid' => $openid,
			'check_name'=>'FORCE_CHECK', //校验用户姓名选项。NO_CHECK：不校验真实姓名，FORCE_CHECK：强校验真实姓名
			're_user_name'=>$trueName,   //收款用户真实姓名（不支持给非实名用户打款）
			'partner_trade_no' => $outTradeNo,
			'spbill_create_ip' => '127.0.0.1',
			'amount' => intval($totalFee * 100), //单位 转为分
			'desc'=>$desc,  //企业付款操作说明信息
		);
		$unified['sign'] = self::getSign($unified, $config['key']);
		$responseXml = $this->curlPost($payweid,'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', self::arrayToXml($unified));
		$unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		if ($unifiedOrder === false) {
			die('parse xml error');
		}
		if ($unifiedOrder->return_code != 'SUCCESS') {
			die($unifiedOrder->return_msg);
		}
		if ($unifiedOrder->result_code != 'SUCCESS') {
			die($unifiedOrder->err_code);
		}
		return true;
	}
    /**
     * 退款
     * @param float $totalFee 订单金额 单位元
     * @param float $refundFee 退款金额 单位元
     * @param string $refundNo 退款单号
     * @param string $wxOrderNo 微信订单号
     * @param string $orderNo 商户订单号
     * @return string
     */
    public function doRefund($totalFee, $refundFee, $wxOrderNo='',$orderNo='',$payweid)
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
		
        $unified = array(
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'total_fee' => intval($totalFee * 100),       //订单金额     单位 转为分
            'refund_fee' => intval($refundFee * 100),       //退款金额 单位 转为分
            'sign_type' => 'MD5',           //签名类型 支持HMAC-SHA256和MD5，默认为MD5
            'transaction_id'=>$wxOrderNo,               //微信订单号
            'out_trade_no'=>$orderNo,        //商户订单号
            'out_refund_no'=>'refund_'.uniqid(),        //商户退款单号
            'refund_desc'=>'',     //退款原因（选填）
        );
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = $this->curlPost($payweid,'https://api.mch.weixin.qq.com/secapi/pay/refund', self::arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }
        return true;
    }
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public function curlPost($payweid,$url = '', $postData = '', $options = array())
    {
		//print_r(IA_ROOT . '/addons/fm_jiaoyu/public/cert/'.$payweid.'/apiclient_key.pem');
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,IA_ROOT . '/addons/fm_jiaoyu/public/cert/'.$payweid.'/apiclient_cert.pem');
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,IA_ROOT . '/addons/fm_jiaoyu/public/cert/'.$payweid.'/apiclient_key.pem');
        //第二种方式，两个文件合成一个.pem文件
//        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}
?>