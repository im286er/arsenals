<?php

namespace Arsenals\Core;

use Arsenals\Core\Abstracts\Arsenals;
use Arsenals\Core\Exceptions\FuncParamException;
use Arsenals\Core\Exceptions\TypeErrorException;

if (!defined('APP_NAME')) {
    exit('Access Denied!');
}
/**
 * 输入处理类.
 *
 * @author 管宜尧<mylxsw@126.com>
 */
class Input extends Arsenals
{
    /**
     * 正则表达式校验规则名称.
     *
     * @var string
     */
    const REGEXP = 'abs7sd765s542hjx726139';

    /**
     * 默认校验规则.
     *
     * @var array
     */
    private static $_validate_rules = [
        'required'         => 'self::_rule_required',
        'string'           => 'self::_rule_string',
        'number'           => 'self::_rule_number',
        'int'              => 'self::_rule_int',
        'float'            => 'self::_rule_float',
        'len'              => 'self::_rule_len',
        'range'            => 'self::_rule_range',
        'id'               => 'self::_rule_id',
        'in'               => 'self::_rule_in',
    ];

    /**
     * 初始化Input对象
     */
    public function __construct()
    {

        // 如果开启了MAGIC_QUOTES_GPC,则需要对所有的魔术变量stripslashes
        if (MAGIC_QUOTES_GPC) {
            $globals = ['_POST', '_GET', '_COOKIE', '_REQUEST'];

            foreach ($globals as $global) {
                $$global = self::_clean($global);
            }
        }


        $this->_get = &$_GET;
        $this->_post = &$_POST;
        $this->_request = &$_REQUEST;
        $this->_cookies = &$_COOKIE;

        parent::__construct();
    }

    /**
     * 获取GET值
     *
     * @param string $key     字段名称
     * @param mixed  $default 字段默认值
     * @param number|string 字段类型
     *
     * @return mixed
     */
    public function get($key, $default = null, $type = null)
    {
        // 如果不存在该字段，则返回默认值
        if (!array_key_exists($key, $this->_get)) {
            return $default;
        }

        // 如果类型为null，则直接返回字段值
        if (is_null($type)) {
            return $this->_get[$key];
        }
        // 进行类型校验
        try {
            $val = $this->_get[$key];
            if (!is_array($val)) {
                $vals = [$val];
            }
            foreach ($vals as $v) {
                self::validate($v, $type);
            }
        } catch (TypeErrorException $e) {
            throw new TypeErrorException("[{$key}] {$e->getMessage()}");
        }

        return $this->_get[$key];
    }

    /**
     * 获取POST值
     *
     * @param string $key     字段名称
     * @param mixed  $default 字段默认值
     * @param number|string 字段类型
     *
     * @return mixed
     */
    public function post($key, $default = null, $type = null)
    {
        // 如果不存在该字段，则返回默认值
        if (!array_key_exists($key, $this->_post)) {
            return $default;
        }
        // 如果类型为null，则直接返回字段值
        if (is_null($type)) {
            return $this->_post[$key];
        }
        // 进行类型校验
        try {
            $val = $this->_post[$key];
            if (!is_array($val)) {
                $vals = [$val];
            }
            foreach ($vals as $v) {
                self::validate($v, $type);
            }
        } catch (TypeErrorException $e) {
            throw new TypeErrorException("[{$key}] {$e->getMessage()}");
        }

        return $this->_post[$key];
    }

    /**
     * 获取REQUEST值
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function request($key, $default = null, $type = null)
    {
        // 如果不存在该字段，则返回默认值
        if (!array_key_exists($key, $this->_request)) {
            return $default;
        }
        // 如果类型为null，则直接返回字段值
        if (is_null($type)) {
            return $this->_request[$key];
        }
        // 进行类型校验
        try {
            $val = $this->_request[$key];
            if (!is_array($val)) {
                $vals = [$val];
            }
            foreach ($vals as $v) {
                self::validate($v, $type);
            }
        } catch (TypeErrorException $e) {
            throw new TypeErrorException("[{$key}] {$e->getMessage()}");
        }

        return $this->_request[$key];
    }

    /**
     * 获取COOKIE值
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function cookie($key, $default = null, $type = null)
    {
        // 如果不存在该字段，则返回默认值
        if (!array_key_exists($key, $this->_cookies)) {
            return $default;
        }
        // 如果类型为null，则直接返回字段值
        if (is_null($type)) {
            return $this->_cookies[$key];
        }
        // 进行类型校验
        try {
            self::validate($this->_cookies[$key], $type);
        } catch (TypeErrorException $e) {
            throw new TypeErrorException("[{$key}] {$e->getMessage()}");
        }

        return $this->_cookies[$key];
    }

    /**
     * Server变量.
     *
     * @param unknown $key
     * @param string  $default
     *
     * @return unknown|string
     */
    public function server($key, $default = null)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        return $default;
    }

    /**
     * 反引用一个引用字符串.
     *
     * @param string|array $array
     *
     * @return string
     */
    protected static function _clean($array)
    {
        if (is_array($array)) {
            return array_map(__CLASS__.'::_clean', $array);
        }

        return stripslashes($array);
    }

    /**
     * 校验变量是否合法.
     *
     * 1. 如果type为数字，则使用PHP的filter函数进行校验
     * 2. 如果type为回调函数，则使用该回到函数进行校验，异常抛出异常或者返回FALSE即可
     * 3. 如果type为字符串，则对type以|进行分割，分别对各个规则进行校验，首先会使用
     * 		PHP的filter函数进行校验，如果不存在校验规则，使用自定义校验规则进行校验
     *
     * 自定义校验规则函数规范：
     * 	1. 如果是普通函数，则规则名为函数名即可
     * 	2. 如果为对象的方法，则使用class_name@ method_name（不含空格）
     *  3. 如果为类的静态方法，则使用class_name::$method_name
     *  4. 函数（方法）参数为$var, $rule
     *
     * 如果type字段为REGEXP常量，则使用正则表达式进行规则匹配，第三个参数为正则表达式
     *
     * @param mixed         $var
     * @param string|number $type
     * @param mixed         $optionals
     *
     * @throws TypeErrorException
     * @throws FuncParamException
     *
     * @return Ambigous <\Arsenals\Core\mixed, mixed>|string|mixed
     */
    public static function validate($var, $type, $optionals = null)
    {
        // 如果type为数字，则认为该类型为FILTER_VALIDATE_*常量类型
        if (is_numeric($type)) {
            return self::_filter_var($var, $type, $optionals);
        }

        // 如果type为字符串，则进行规则校验
        if (is_string($type)) {
            // 如果规则类型为正则表达式，则使用正则表达式进行校验
            if ($type == self::REGEXP) {
                if (!preg_match($optionals, $var)) {
                    throw new TypeErrorException(self::L('FIELD_TYPE_INVALID', null, 'Field type invalid!'));
                }

                return $var;
            }
            $var = trim(str_replace(' ', '', $var), '|');
            $filters = preg_split('/\|/', $type);
            foreach ($filters as $filter) {
                // 如果校验规则在PHP filter中，则使用PHPfilter进行校验
                // 否则，如果自定义校验规则中，则使用自定义校验规则进行校验
                if (in_array($filter, filter_list())) {
                    self::_filter_var($var, $filter, $optionals);
                } elseif (array_key_exists(($filter_name = substr($filter, 0, ($mpos = intval(strpos($filter, ':'))) == 0 ? strlen($filter) : $mpos)), self::$_validate_rules)) {
                    $validate_entity = conv_str_to_call_user_func_param(self::$_validate_rules[$filter_name]);

                    $validate_res = call_user_func_array($validate_entity,
                            [$var, substr($filter, intval(strpos($filter, ':')) + 1)]);
                    if ($validate_res === false) {
                        throw new TypeErrorException(self::L('FIELD_TYPE_INVALID', null, 'Field type invalid!'));
                    }
                } else {
                    throw new FuncParamException("Validation rule {$filter} not exist！");
                }
            }

            return $var;
        }
        // 如果类型为回调函数，则使用回调函数校验
        if (is_callable($type)) {
            return call_user_func($type, $var);
        }
        throw new FuncParamException('Validation rule has something wrong!');
    }

    /**
     * 注册验证规则.
     *
     * @param string $rule_name
     * @param string $entity_name 可以是类方法字符串表示或者回调函数
     */
    public static function validateRuleRegister($rule_name, $entity_name)
    {
        self::$_validate_rules[$rule_name] = $entity_name;
    }

    /**
     * 原生的PHP filter函数校验.
     *
     * @param mixed         $var
     * @param string|number $type
     * @param array         $optionals
     *
     * @throws TypeErrorException
     *
     * @return mixed
     */
    private static function _filter_var($var, $type, $optionals)
    {
        $filtered = filter_var($var, is_numeric($type) ? $type : filter_id($type), $optionals);
        if ($filtered === false) {
            throw new TypeErrorException(self::L('FIELD_TYPE_INVALID', null, 'Field type invalid!'));
        }

        return $filtered;
    }

    /**
     * 校验规则，字段必须.
     *
     * @param unknown $var
     * @param unknown $rule
     *
     * @throws TypeErrorException
     *
     * @return bool
     */
    private static function _rule_required($var, $rule)
    {
        if (is_null($var) || $var == '') {
            throw new TypeErrorException(self::L('FIELD_REQUIRED', null, 'Field required!'));
        }

        return true;
    }

    /**
     * 校验规则，字段为字符串.
     *
     * @param unknown $var
     * @param unknown $rule
     */
    private static function _rule_string($var, $rule)
    {
        return self::_rule_len($var, $rule);
    }

    /**
     * 校验规则，字段为数字.
     *
     * @param unknown $var
     * @param unknown $rule
     */
    private static function _rule_number($var, $rule)
    {
        if (!is_numeric($var)) {
            throw new TypeErrorException(self::L('FIELD_NOT_VALID_NUM', null, 'Field is not a valid number!'));
        }

        return self::_rule_range($var, $rule);
    }

    /**
     * 校验规则，范围.
     *
     * @param unknown $var
     * @param unknown $rule
     *
     * @return bool
     */
    private static function _rule_range($var, $rule)
    {
        if (!is_null($rule) && $rule != '') {
            $dot_pos = strpos($rule, ',');
            $min = $dot_pos === 0 ? 0 : intval(substr($rule, 0, $dot_pos === false ? strlen($rule) : $dot_pos));
            $max = $dot_pos === false ? null : intval(substr($rule, $dot_pos + 1));

            if ($var < $min) {
                throw new TypeErrorException(self::L('FIELD_NOT_LESS_MIN', ['min' => $min], "Field can not be less than {$min}"));
            }
            if (!is_null($max) && $var > $max) {
                throw new TypeErrorException(self::L('FIELD_NOT_MORE_MAX', ['max' => $max], "Field can not be greater than {$max}"));
            }
        }

        return true;
    }

    /**
     * 校验规则， in.
     */
    private static function _rule_in($var, $rule)
    {
        if (!is_null($rule) && $rule != '') {
            $rul_arr = preg_split('/,/', $rule);
            if (!in_array($var, $rul_arr)) {
                throw new TypeErrorException(self::L('FIELD_MUST_ON_OF', ['rule' => $rule], "Field must one of {$rule}！"));
            }
        }

        return true;
    }

    /**
     * 校验规则，字段为整数.
     *
     * @param unknown $var
     * @param unknown $rule
     */
    private static function _rule_int($var, $rule)
    {
        if ($var != (int) $var) {
            throw new TypeErrorException(self::L('FIELD_NOT_VALID_INTEGER', null, 'Field is not a valid integer!'));
        }

        return self::_rule_range($var, $rule);
    }

    /**
     * 校验规则，字段为浮点型数.
     *
     * @param unknown $var
     * @param unknown $rule
     */
    private static function _rule_float($var, $rule)
    {
        if (is_float($var)) {
            throw new TypeErrorException(self::L('FIELD_NOT_VALID_FLOAT', null, 'Field is not a valid float!'));
        }

        return self::_rule_range($var, $rule);
    }

    /**
     * 校验规则， 字段长度.
     *
     * @param unknown $var
     * @param unknown $rule
     */
    private static function _rule_len($var, $rule)
    {
        if (!is_null($rule) && $rule != '') {
            $dot_pos = strpos($rule, ',');
            $len_min = $dot_pos === 0 ? 0 : intval(substr($rule, 0, $dot_pos === false ? strlen($rule) : $dot_pos));
            $len_max = $dot_pos === false ? null : intval(substr($rule, $dot_pos + 1));

            $var_len = strlen($var);

            if ($var_len < $len_min) {
                throw new TypeErrorException(self::L('FIELD_LEN_LESS', ['len_min' => $len_min], "Field length can not be less than {$len_min} characters!"));
            }
            if (!is_null($len_max) && $var_len > $len_max) {
                throw new TypeErrorException(self::L('FIELD_LEN_MORE', ['len_max' => $len_max], "Field length can not be greater than {$len_max} characters!"));
            }
        }

        return true;
    }

    /**
     * 校验字段 ID.
     *
     * @param unknown $var
     * @param unknown $rule
     *
     * @throws TypeErrorException
     *
     * @return bool
     */
    private static function _rule_id($var, $rule)
    {
        self::_rule_int($var, $rule);
        if ($var <= 0) {
            throw new TypeErrorException(self::L('FIELD_NOT_ID', null, 'Field is not a valid ID!'));
        }

        return true;
    }
}
