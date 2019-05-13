<?php
namespace app\index\logic;

use app\index\model\Stock;
use app\common\libraries\api51;
use app\common\quotation\sina;
use think\console\Output;

class StockLogic
{
    protected $_library;
    protected $_sinaQuotation;
    public function __construct()
    {
        $this->_library = new api51();
        $this->_sinaQuotation = new sina();
    }

    public function stockByCode($code)
    {
        $stock = Stock::where(["code" => $code])->find();
        return $stock ? $stock->toArray() : [];
    }

    // 新浪财经行情
    public function quotationBySina($codes){
        $codes = $this->_fullCodeByCodes($codes);
        return $this->_sinaQuotation->real($codes);
    }

    public function simpleData($codes)
    {
        $codes = $this->_fullCodeByCodes($codes);
        /*$result = $this->_sinaQuotation->real($codes);
        $response = [];
        foreach ($result as $key => $value){
            $response[$key] = [
				"code"  => $key,
                "prod_name" => $value['prod_name'],
                "last_px"   => $value['last_px'],
                "px_change" => $value['px_change'],
                "px_change_rate" => $value['px_change_rate']
            ];
        }
        return $response;*/
        //$codes = $this->_handleCodes($codes);
        $code = implode(',', $codes);
        $fields = ['name','nowPrice','diff_money','diff_rate'];
        $response = $this->_library->realtime($code, $fields);
        //var_dump($response);die();
        if($response){
            $_resp = [];
            $data = $response['showapi_res_body']['list'];
            //$fields = $data['fields'];
            foreach ($data as $key=>$val){
                $_resp[$val['code']] = [
                    "code"  => $val['code'],
                    "prod_name" => $val['name'],
                    "last_px"   => $val['nowPrice'],
                    "px_change" => $val['diff_money'],
                    "px_change_rate" => $val['diff_rate']
                ];
            }
            return $_resp;
        }
        return [];
    }

    public function klineData($code, $period = 6, $count = 50)
    {
        $code = $this->_fullCodeByCodes($code);
        $code = reset($code);
        //$code = $this->_handleCodes($code);
        if($code){
            $period = in_array($period, [6,7,8]) ? $period : 6;
            $response = $this->_library->kline($code, $period, $count);
            if($response && isset($response['showapi_res_body']['dataList'])){

                $_resp = [];
                $kline = $response['showapi_res_body']['dataList'];
                // $fields = $data['fields'];
                // $kline = $data[$code];
                foreach ($kline as $item){
                    $_temp = [
                        'open_px' => $item['open'],
                        'close_px' => $item['close'],
                        'low_px' => $item['min'],
                        'high_px' => $item['max'],
                        'min_time' => $item['time'],
                        'business_balance' => 0,
                        'business_amount' => $item['volumn']
                    ];
                    $_resp[] = $_temp;
                }
                return array_reverse($_resp);
            }
        }
        return [];
    }

    public function realData($code, $crc='', $min='')
    {
        $cd = $code;
        $code = $this->_fullCodeByCodes($code);
        $code = reset($code);
        //$code = $this->_handleCodes($code);
        $min_date = $min ? date("Hi", strtotime($min)) : [];
        if($code){
            try{
                $_response = [];
                $real = $this->_library->realtime($code);
                $realFields = [
                    'time',
                    'buy1_n',
                    'openPrice',
                    'todayMax',
                    'todayMin',
                    'nowPrice',
                    'tradeNum',
                    'tradeAmount',
                ];
                $realData = $real['showapi_res_body']['list'][0];
                /*foreach ($realFields as $key=>$val){
                    $_response[$val] = $realData[$val];
                }*/
                $_response['data_timestamp']        = $realData['time'];
                $_response['shares_per_hand']       = $realData['buy1_n'];
                $_response['open_px']               = $realData['openPrice'];
                $_response['high_px']               = $realData['todayMax'];
                $_response['low_px']                = $realData['todayMin'];
                $_response['last_px']               = $realData['nowPrice'];
                $_response['last_px']               = $realData['nowPrice'];
                $_response['business_amount']       = $realData['tradeNum'];
                $_response['business_balance']      = $realData['tradeAmount'];
                $_response['preclose_px']           = $realData['closePrice'];
                $_response['px_change']             = $realData['diff_money'];
                $_response['px_change_rate']        = $realData['diff_rate'];
                $_response['amplitude']             = $realData['swing'];
                $_response['business_amount_in']       = 1;
                $_response['business_amount_out']      = 1;
                $_response['total_shares']             = $realData['totalcapital'];
                $_response['pe_rate']                  = $realData['pe'];
                $_response['circulation_value']        = $realData['circulation_value'];
                $_response['bid_grp'] = [
                    $realData['buy1_n'],
                    $realData['buy1_m'],
                    $realData['buy2_n'],
                    $realData['buy2_m'],
                    $realData['buy3_n'],
                    $realData['buy3_m'],
                    $realData['buy4_n'],
                    $realData['buy4_m'],
                    $realData['buy5_n'],
                    $realData['buy5_m'],
                ];
                $_response['offer_grp'] = [
                    $realData['sell1_n'],
                    $realData['sell1_m'],
                    $realData['sell2_n'],
                    $realData['sell2_m'],
                    $realData['sell3_n'],
                    $realData['sell3_m'],
                    $realData['sell4_n'],
                    $realData['sell4_m'],
                    $realData['sell5_n'],
                    $realData['sell5_m'],
                ];
                $trend = $this->_library->trend($cd, $crc, $min_date);
                $trendFields = ['min_time',' last_px','avg_px','business_amount'];
                //$trendCrc  = $trend['data']['trend']['crc'][$code];
                $trendCrc    = '';
                $trendData   = $trend['showapi_res_body']['dataList'][0]['minuteList'];
                $_response['trend_crc'] = $trendCrc;
                $_response['trend'] = [];
                foreach ($trendData as $item){
                    $_response['trend'][] = [
                        'min_time' => $item['time'],
                        'last_px'  => $item['nowPrice'],
                        'avg_px'   => $item['avgPrice'],
                        'business_amount'   => $item['volume']
                    ];
                }
                return $_response;
            }catch (\Exception $e){
                return [];
            }
        }
        return [];
    }

    public function realTimeData($codes)
    {
        $codes = $this->_fullCodeByCodes($codes);
        //$codes = $this->_handleCodes($codes);
        $code = implode(',', $codes);
        $response = $this->_library->realtime($code);
        if($response){
            $_resp = [];
            $data = $response['showapi_res_body']['list'];
            //$fields = $data['fields'];
            foreach ($data as $key=>$val){
                //if($key != 'fields'){
                    $_temp = [];
                    $_temp['code'] = $val['code'];
                    foreach($val as $k=>$v){
                        if($v == 'offer_grp' || $v == 'bid_grp'){
                            $_array = explode(',', $val[$k]);
                            array_pop($_array);
                            $_temp[$k] = $_array;
                        }else{
                            $_temp[$k] = $val[$k];
                        }
                    }
                    $_resp[] = $_temp;
                //}
            }
            return $_resp;
        }
        return [];
    }

    private function _fullCodeByCodes($codes)
    {
        return Stock::where(["code" => ["IN", $codes]])->column("full_code");
    }

    private function _handleCodes($codes = [])
    {
        if(is_array($codes)){
            array_filter($codes, function(&$item){
                preg_match('/^([sh|sz]{2})(\d{6})/i', $item, $_match);
                if($_match){
                    if($_match[1] == 'sh'){
                        $item = "{$_match[2]}.SS";
                    }elseif($_match[1] == 'sz'){
                        $item = "{$_match[2]}.SZ";
                    }
                }
            });
        }elseif (!empty($codes)){
            preg_match('/^([sh|sz]{2})(\d{6})/i', $codes, $match);
            if($match){
                if($match[1] == 'sh'){
                    $codes = "{$match[2]}.SS";
                }elseif($match[1] == 'sz'){
                    $codes = "{$match[2]}.SZ";
                }
            }
        }
        return $codes;
    }
}