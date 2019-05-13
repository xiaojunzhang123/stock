<?php
namespace app\index\validate;

use app\index\logic\BankLogic;
use app\index\logic\RegionLogic;
use think\Validate;

class Card extends Validate
{
    protected $rule = [
        'bank_user' => "require|max:32",
        'bank_name' => 'require|checkBank',
        'bank_province' => 'require|checkProvince',
        'bank_city' => 'require|checkCity',
        'bank_address' => 'max:128',
        'bank_card' => ["require", "regex" => "/^(\d{16}|\d{19})$/i"],
        'id_card'   => 'require|checkIdCard',
        'bank_mobile' => ["require", "regex" => "/^(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/i"],
    ];

    protected $message = [
        'bank_user.require' => '持卡人姓名不能为空！',
        'bank_user.max'     => '持卡人姓名最大32个字符！',
        'bank_name.require' => '请选择开户银行！',
        'bank_name.checkBank'   => '开户银行不存在！',
        'bank_province.require' => '请选择所在省份！',
        'bank_province.checkProvince' => '所在省份选择错误！',
        'bank_city.require'     => '请选择所在城市！',
        'bank_city.checkCity'   => '所在城市选择错误！',
        'bank_address.max'      => '支行名称最大128个字符！',
        'bank_card.require'     => '请输入银行卡号！',
        'bank_card.regex'       => '银行卡号格式错误！',
        'id_card.require'       => '请输入身份证号！',
        'id_card.checkIdCard'   => '身份证号格式错误！',
        'bank_mobile.require'   => '请输入预留手机号！',
        'bank_mobile.regex'     => '预留手机号格式错误！',
    ];

    protected $scene = [
        'modify'  => ['bank_user', 'bank_name', 'bank_province', 'bank_city', 'bank_address', 'bank_card', 'id_card', 'bank_mobile'],
    ];

    public function checkBank($value, $rule, $data)
    {
        $bank = (new BankLogic())->bankByName($value);
        return $bank ? $bank['state'] == 1 ? true : false : false;
    }

    public function checkProvince($value, $rule, $data)
    {
        $province = (new RegionLogic())->regionById($value);
        return $province ? true : false;
    }

    public function checkCity($value, $rule, $data)
    {
        $city = (new RegionLogic())->regionById($value);
        return $city ? $city['parent_id'] == $data['bank_province'] ? true : false : false;
    }

    public function checkIdCard($value, $rule, $data)
    {
        $vCity = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $value)) return false;
        if (!in_array(substr($value, 0, 2), $vCity)) return false;

        $vStr = preg_replace('/[xX]$/i', 'a', $value);
        $vLength = strlen($vStr);
        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }

        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18) {
            $vSum = 0;
            for ($i = 17 ; $i >= 0 ; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
            }
            if($vSum % 11 != 1) return false;
        }
        return true;
    }
}