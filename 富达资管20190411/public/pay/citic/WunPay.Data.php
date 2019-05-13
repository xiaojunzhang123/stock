<?php
/**
 *
 * 
 */
require_once "WunPay.Config.php";
require_once "WunPay.Exception.php";

class WunPayDataBase
{
	protected $values = array();
	private $key = '';
	public function __construct($key='')
	{
		$this->key = $key;
	}
	
	protected function SetKey($key)
	{
		$this->key = $key;
	}

	public function GetKey()
	{
		return $this->key;
	}
	
	/**
	* 设置签名，详见签名生成算法
	* @param string $value 
	**/
	public function SetSign()
	{
		$sign = $this->MakeSign();
		$this->values['sign'] = $sign;
		return $sign;
	}
	
	/**
	* 获取签名，详见签名生成算法的值
	* @return 值
	**/
	public function GetSign()
	{
		return $this->values['sign'];
	}
	
	/**
	* 判断签名，详见签名生成算法是否存在
	* @return true 或 false
	**/
	public function IsSignSet()
	{
		return array_key_exists('sign', $this->values);
	}

	/**
	 * 输出xml字符
	 * @throws UniPayException
	**/
	public function ToXml()
	{
		if(!is_array($this->values) 
			|| count($this->values) <= 0)
		{
    		throw new WunPayException("数组数据异常！");
    	}
    	
    	$xml = "<xml>";
    	foreach ($this->values as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
	}
	
    /**
     * 将xml转为array
     * @param string $xml
     * @throws UniPayException
     */
	public function FromXml($xml)
	{	
		if(!$xml){
			throw new WunPayException("xml数据异常！");
		}
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);	
        
		return $this->values;
	}
	
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams()
	{
		$buff = "";
		foreach ($this->values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign()
	{	
		//签名步骤一：按字典序排序参数
		ksort($this->values);
		$string = urldecode($this->ToUrlParams());
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$this->key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	
	/**
	 * 获取设置的值
	 */
	public function GetValues()
	{
		return $this->values;
	}
}

/**
 * 
 * 接口调用结果类
 * 
 *
 */
class WunPayResults extends WunPayDataBase
{
	/**
	 * 
	 * 检测签名
	 */
	public function CheckSign()
	{
	   
		//fix异常
		if(!$this->IsSignSet()){
			throw new WunPayException("签名错误！");
		}
		
		$sign = $this->MakeSign();
		
		if($this->GetSign() == $sign){
			return true;
		}
		
	//	throw new WunPayException("签名错误！");

	}
	
	/**
	 * 
	 * 使用数组初始化
	 * @param array $array
	 */
	public function FromArray($array)
	{
		$this->values = $array;
	}
	
	/**
	 * 
	 * 使用数组初始化对象
	 * @param array $array
	 * @param 是否检测签名 $noCheckSign
	 */
	public static function InitFromArray($array, $noCheckSign = false)
	{
		$obj = new self();
		$obj->FromArray($array);
		if($noCheckSign == false){
			$obj->CheckSign();
		}
        return $obj;
	}
	
	/**
	 * 
	 * 设置参数
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}
	
    /**
     * 将xml转为array
     * @param string $xml
     * @throws UniPayException
     */
	public static function Init($xml,$key='')
	{	
		$obj = new self($key);
		$obj->FromXml($xml);
		
		//fix bug 2015-06-29
		if($obj->values['return_code'] != 'SUCCESS'){
			 return $obj->GetValues();
		}

		$obj->CheckSign();

        return $obj->GetValues();
	}
}

/**
 * 
 * 回调基础类
 * 
 *
 */
class WunPayNotifyReply extends  WunPayDataBase
{
	/**
	 * 设置支付版本
	 * @param string $value
	 **/
	public function SetVersion($value)
	{
		$this->values['version'] = $value;
	}
	/**
	 * 获取支付版本
	 * @return 值
	 **/
	public function GetVersion()
	{
		return $this->values['version'];
	}
	/**
	 * 判断支付版本是否存在
	 * @return true 或 false
	 **/
	public function IsVersionSet()
	{
		return array_key_exists('version', $this->values);
	}
	
	/**
	 * 
	 * 设置错误码 FAIL 或者 SUCCESS
	 * @param string
	 */
	public function SetReturn_code($return_code)
	{
		$this->values['return_code'] = $return_code;
	}
	
	/**
	 * 
	 * 获取错误码 FAIL 或者 SUCCESS
	 * @return string $return_code
	 */
	public function GetReturn_code()
	{
		return $this->values['return_code'];
	}

	/**
	 * 
	 * 设置错误信息
	 * @param string $return_code
	 */
	public function SetReturn_msg($return_msg)
	{
		$this->values['return_msg'] = $return_msg;
	}
	
	/**
	 * 
	 * 获取错误信息
	 * @return string
	 */
	public function GetReturn_msg()
	{
		return $this->values['return_msg'];
	}
	
	/**
	 * 
	 * 设置返回参数
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}
}

/**
 * 
 * 统一下单输入对象
 * 
 *
 */
class WunPayUnifiedOrder extends WunPayDataBase
{

    /**
     * 设置支付版本
     * @param string $value
     **/
    public function SetVersion($value)
    {
        $this->values['version'] = $value;
    }
    /**
     * 获取支付版本
     * @return 值
     **/
    public function GetVersion()
    {
        return $this->values['version'];
    }
    /**
     * 判断支付宝网关支付版本是否存在
     * @return true 或 false
     **/
    public function IsVersionSet()
    {
        return array_key_exists('version', $this->values);
    }

    /**
     * 设置支付宝网关支付平台APPID
     * @param string $value
     **/
    public function SetAppid($value)
    {
        $this->values['appid'] = $value;
    }
    /**
     * 获取支付宝网关支付平台APPID
     * @return 值
     **/
    public function GetAppid()
    {
        return $this->values['appid'];
    }
    /**
     * 判断支付宝网关支付平台APPID是否存在
     * @return true 或 false
     **/
    public function IsAppidSet()
    {
        return array_key_exists('appid', $this->values);
    }
    /**
     * 设置商品或支付单简要描述
     * @param string $value
     **/
    public function SetBody($value)
    {
    	$this->values['body'] = $value;
    }
    /**
     * 获取商品或支付单简要描述的值
     * @return 值
     **/
    public function GetBody()
    {
    	return $this->values['body'];
    }
    /**
     * 判断商品或支付单简要描述是否存在
     * @return true 或 false
     **/
    public function IsBodySet()
    {
    	return array_key_exists('body', $this->values);
    }

    /**
     * 设置支付宝网关支付平台分配的商户号
     * @param string $value
     **/
    public function SetMch_id($value)
    {
        $this->values['mch_id'] = $value;
    }
    /**
     * 获取支付宝网关支付平台分配的商户号的值
     * @return 值
     **/
    public function GetMch_id()
    {
        return $this->values['mch_id'];
    }
    /**
     * 判断支付平台分配的商户号是否存在
     * @return true 或 false
     **/
    public function IsMch_idSet()
    {
        return array_key_exists('mch_id', $this->values);
    }



    /**
     * 设置随机字符串，不长于32位。推荐随机数生成算法
     * @param string $value
     **/
    public function SetNonce_str($value)
    {
        $this->values['nonce_str'] = $value;
    }
    /**
     * 获取随机字符串，不长于32位。推荐随机数生成算法的值
     * @return 值
     **/
    public function GetNonce_str()
    {
        return $this->values['nonce_str'];
    }
    /**
     * 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
     * @return true 或 false
     **/
    public function IsNonce_strSet()
    {
        return array_key_exists('nonce_str', $this->values);
    }


    /**
     * 设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
     * @param string $value
     **/
    public function SetOut_trade_no($value)
    {
        $this->values['out_trade_no'] = $value;
    }
    /**
     * 获取商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号的值
     * @return 值
     **/
    public function GetOut_trade_no()
    {
        return $this->values['out_trade_no'];
    }
    /**
     * 判断商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号是否存在
     * @return true 或 false
     **/
    public function IsOut_trade_noSet()
    {
        return array_key_exists('out_trade_no', $this->values);
    }


    /**
     * 设置符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型
     * @param string $value
     **/
    public function SetFee_type($value)
    {
        $this->values['fee_type'] = $value;
    }
    /**
     * 获取符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型的值
     * @return 值
     **/
    public function GetFee_type()
    {
        return $this->values['fee_type'];
    }
    /**
     * 判断符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型是否存在
     * @return true 或 false
     **/
    public function IsFee_typeSet()
    {
        return array_key_exists('fee_type', $this->values);
    }


    /**
     * 设置订单总金额，只能为整数，详见支付金额
     * @param string $value
     **/
    public function SetTotal_fee($value)
    {
        $this->values['total_fee'] = $value;
    }
    
    /**
     * 获取订单总金额，只能为整数，详见支付金额的值
     * @return 值
     **/
    public function GetTotal_fee()
    {
        return $this->values['total_fee'];
    }
    /**
     * 判断订单总金额，只能为整数，详见支付金额是否存在
     * @return true 或 false
     **/
    public function IsTotal_feeSet()
    {
        return array_key_exists('total_fee', $this->values);
    }

    /**
     * 设置接口名称
     * @param string $value
     **/
    public function SetMethod($value)
    {
        $this->values['method'] = $value;
    }
    /**
     * 获取接口名称
     * @return 值
     **/
    public function GetMethod()
    {
        return $this->values['method'];
    }
    /**
     * 判断接口名称是否存在
     * @return true 或 false
     **/
    public function IsMethodSet()
    {
        return array_key_exists('method', $this->values);
    }

    /**
     * 设置支付完成跳转地址
     * @param string $value
     **/
    public function SetReturn_url($value)
    {
    	$this->values['return_url'] = $value;
    }
    /**
     * 获取支付完成跳转地址
     * @return 值
     **/
    public function GetReturn_url()
    {
    	return $this->values['return_url'];
    }
    /**
     *  判断支付完成跳转地址
     * @return true 或 false
     **/
    public function IsReturn_urlSet()
    {
    	return array_key_exists('return_url', $this->values);
    }


    /**
     * 设置支付宝网关支付平台异步通知回调地址
     * @param string $value
     **/
    public function SetNotify_url($value)
    {
        $this->values['notify_url'] = $value;
    }
    /**
     * 获取支付宝网关支付平台异步通知回调地址的值
     * @return 值
     **/
    public function GetNotify_url()
    {
        return $this->values['notify_url'];
    }
    /**
     * 判断支付宝网关支付平台异步通知回调地址是否存在
     * @return true 或 false
     **/
    public function IsNotify_urlSet()
    {
        return array_key_exists('notify_url', $this->values);
    }

   

	}

/**
 *
 * 订单查询输入对象
 * @author widyhu
 *
 */
class WunPayOrderQuery extends WunPayDataBase
{
	/**
	 * 设置支付版本
	 * @param string $value
	 **/
	public function SetVersion($value)
	{
		$this->values['version'] = $value;
	}
	/**
	 * 获取支付版本
	 * @return 值
	 **/
	public function GetVersion()
	{
		return $this->values['version'];
	}
	/**
	 * 判断支付版本是否存在
	 * @return true 或 false
	 **/
	public function IsVersionSet()
	{
		return array_key_exists('version', $this->values);
	}
	
	/**
	 * 设置支付宝网关分配的商户应用appid
	 * @param string $value
	 **/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	 * 获取支付宝网关分配的商户应用appid的值
	 * @return 值
	 **/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	 * 判断支付宝网关分配的商户应用appid是否存在
	 * @return true 或 false
	 **/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	 * 设置支付宝网关支付分配的商户号
	 * @param string $value
	 **/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	 * 获取支付宝网关支付分配的商户号的值
	 * @return 值
	 **/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	 * 判断支付宝网关支付分配的商户号是否存在
	 * @return true 或 false
	 **/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}
    /**
     * 设置接口名称
     * @param string $value
     **/
    public function SetMethod($value)
    {
        $this->values['method'] = $value;
    }
    /**
     * 获取接口名称
     * @return 值
     **/
    public function GetMethod()
    {
        return $this->values['method'];
    }
    /**
     * 判断接口名称是否存在
     * @return true 或 false
     **/
    public function IsMethodSet()
    {
        return array_key_exists('method', $this->values);
    }
	

	/**
	 * 设置商户系统内部的订单号
	 * @param string $value
	 **/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	 * 获取商户系统内部的订单号
	 * @return 值
	 **/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	 * 判断商户系统内部的订单号
	 * @return true 或 false
	 **/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	 * 设置随机字符串，不长于32位。推荐随机数生成算法
	 * @param string $value
	 **/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	 * 获取随机字符串，不长于32位。推荐随机数生成算法的值
	 * @return 值
	 **/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	 * 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
	 * @return true 或 false
	 **/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}
}

