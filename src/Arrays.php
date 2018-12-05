<?php

namespace Starrysea\Arrays;

class Arrays
{
    /**
     * 判断数组中是否包含指定字符类型以外的字符类型
     * @param array $array 数组数据
     * @param string $type 判断类型
     * @return bool true => 包含, false => 不包含
     */
    public static function is_types(array $array, string $type)
    {
        foreach ($array as $value){
            if (!$type($value))
                return true; // 包含存在指定类型以外的类型的数据
        }
        return false; // 不包含存在指定类型以外的类型的数据
    }

    /**
     * 转换字符编码
     * @param array $arr 数组
     * @param string $in_charset 原字符串编码
     * @param string $out_charset 输出的字符串编码
     * @return array 新数组数据
     */
    public static function iconv(array $arr, string $in_charset = "GBK", string $out_charset = "UTF-8")
    {
        return eval('return '.iconv($in_charset, $out_charset,var_export($arr,true).';'));
    }

    /**
     * 递归转义数据
     * @param array $arr 转义的数据
     * @param string|array $exclude 排除转义key
     * @return array 转义后的数组
     */
    public static function htmlspecialchars(array $arr, $exclude = [])
    {
        $arr = json_decode(json_encode($arr),true);
        $exclude = self::toArray($exclude);
        foreach ($arr as $key => $value){
            if (is_array($value)){
                $arr[$key] = self::htmlspecialchars($value, $exclude);
            }elseif (!in_array((string)$key, $exclude)){
                $arr[$key] = e($value);
            }
        }
        return $arr;
    }

    /**
     * 多个二维数组键名且内容一致时进行合并为三维数组
     * @param array $array 二维数组数据
     * @param String $key 指定合并条件键名,键名与内容一致时进行合并为新的数组
     * @return array 三维数组,以条件键名的内容定为一维数组的key
     */
    public static function merging(array $array, string $key)
    {
        $datas = [];
        foreach ($array as $title => $values) {
            $datas[$values[$key]][] = $values;
        }
        return $datas;
    }

    /**
     * 提取二维数组中指定键名的数据
     * @param array $array 二维数组
     * @param string $key 指定条件键名
     * @return array 一维数组
     */
    public static function extract_field(array $array, string $key)
    {
        $date = [];
        foreach ($array as $value){
            if ($value[$key]) $date[] = $value[$key];
        }
        return $date;
    }

    /**
     * 数组数字累加,支持多维数组,非数字自动排除
     * @param array $arr 数组
     * @param array|string $field 统计key
     * @param bool|int $thorough true => 深统计,多维数组同key累加, false => 浅统计,当前维度key, int => 统计维度数,<=0当前维度,1当前+二级数组,以此类推
     * @return array|int|string 统计结果
     */
    public static function count(array $arr, $field, $thorough = false)
    {
        $data = is_array($field) ? array_fill_keys($field, 0) : 0;
        foreach ($arr as $key => $value){
            if (is_numeric($value) && $field === $key){
                $data += $value;
            }elseif (is_array($value) && ($thorough === true || $thorough-- > 0)){
                $son = self::count($value, $field, $thorough);
                if (is_array($son)){
                    foreach ($son as $sonkey => $sonval){
                        data_set($data, $sonkey, data_get($data, $sonkey, 0) + $sonval);
                    }
                }else{
                    $data += $son;
                }
            }elseif (is_numeric($value) && !is_array($value) && (in_array($key, self::toArray($field)))){
                data_set($data, $key, data_get($data, $key, 0) + $value);
            }
        }
        return $data; // [['arr'=>1],['arr'=>2]] return ['arr'=>3]
    }

    /**
     * 删除数组中指定的字段
     * @param array $array 数组
     * @param array|string $field 删除的字段
     * @param int $dimension 删除的层级,默认当前层
     * @return array 删除后剩余的数据
     */
    public static function unsets(array $array, $field, int $dimension = 0)
    {
        foreach ($array as $key => $value){
            if (in_array((string)$key, self::toArray($field))){
                unset($array[$key]);
            }else if (is_array($value) && $dimension-- > 0){
                $array[$key] = self::unsets($value, $field, $dimension);
            }
        }
        return $array;
    }

    /**
     * 互相删除两个数组中相同的内容
     * @param array $arrayone 数组一
     * @param array $arraytwo 数组二
     * @return array
     *      survivedata 生存内容[key顺推]
     *      crashedsum 撞毁总数量
     */
    public static function collision(array $arrayone, array $arraytwo)
    {
        $arrayone = array_unique($arrayone);
        $arraytwo = array_unique($arraytwo);
        $data = [];
        $zsum = 0;
        foreach ($arrayone as $key=>$value){
            if (!in_array($value,$arraytwo)) {
                $data[] = $value;
            }else{
                unset($arrayone[$key]);
                $arraytwo = array_flip ($arraytwo);
                unset($arraytwo[$value]);
                $arraytwo = array_flip ($arraytwo);
                $zsum ++;
            }
        }
        foreach ($arraytwo as $key=>$value){
            if (!in_array($value,$arrayone)) {$data[] = $value;}else{$zsum ++;}
        }
        return [
            'survivedata' => $data,
            'crashedsum'  => $zsum,
        ];
    }

    /**
     * 内容反向筛选
     * @param array $filtered 数据 [a,b,c,d]
     * @param array $tofilter 筛选数据 [c]
     * @return array 返回筛选后结果[a,b,d]
     */
    public static function filter(array $filtered, array $tofilter)
    {
        foreach ($filtered as $key => $value){
            if (in_array($value, $tofilter)) {
                unset($filtered[$key]);
            }
        }
        return $filtered;
    }

    /**
     * 获取二维数组中指定的键名的值
     * @param array $array 二维数组
     * @param array|string $keys 指定key
     * @return array
     */
    public static function only(array $array, $keys)
    {
        $data = [];
        foreach ($array as $key => $value){
            $data[$key] = array_intersect_key($array[$key], array_flip((array) $keys));
        }
        return $data;
    }

    /**
     * 字符串转数组
     * @param array|string $value 数据
     * @return array 一维数组
     */
    public static function toArray($value)
    {
        return is_array($value) ? $value : [$value];
    }

    /**
     * 一维转二维，二维不变
     * @param array $array 一维或二维数组
     * @return array 二维数组
     */
    public static function OneToTwo($array)
    {
        foreach ($array as $value){
            if (!is_array($value)){
                return [$array];
            }
        }
        return $array;
    }

    /**
     * 数组综合重组,key与value合为一个值
     * @param array $array 数组
     * @param string $separate 分隔符
     * @return array 新数组
     */
    public static function composite(array $array, string $separate = ':')
    {
        $data = [];
        foreach ($array as $key => $value){
            array_push($data, $key . $separate . $value);
        }
        return $data; // ['arr'=>'xh'] => ['arr:xh']
    }
}
