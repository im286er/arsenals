<?php

namespace Arsenals\Core;
use Arsenals\Core\Exceptions\RedefineException;
use Arsenals\Core\Exceptions\ClassNotFoundException;
use Arsenals\Core\Abstracts\Arsenals;
/**
 * 载入类对象注册器
 * 
 * @author 管宜尧<mylxsw@126.com>
 *
 */
class Registry extends Arsenals {
	/**
	 * @static 缓存的对象
	 */
	private static $_cache_objects = array();
	/**
	 * 该类禁止实例化
	*/
	private function __construct(){}
	/**
	 * 防止该类被克隆
	 */
	private function __clone(){}
	/**
	 * 判断指定类是否已经加载过了
	 * 
	 * @param string $class_name
	 * @return boolean
	 */
	public static function exist($class_name){
		return array_key_exists(ucfirst($class_name), self::$_cache_objects);
	}
	/**
	 * 载入指定类对象
	 *
	 * 如果该对象不存在，则创建该对象
	 *
	 * @param string $calss_name 类名
	 * @return object
	 */
	public static function load($class_name){
		$class_name = ucfirst($class_name);
		if(!array_key_exists($class_name, self::$_cache_objects)){
			self::$_cache_objects[$class_name] = new $class_name;
		}
		return self::$_cache_objects[$class_name];
	}
	/**
	 * 获取指定的对象
	 *
	 * 如果该对象不存在，则返回指定的默认值
	 *
	 * @param string $class_name 要载入的类名
	 * @param mixed $default 默认值
	 *
	 * @return object
	 */
	public static function get($class_name, $default = null){
		$class_name = ucfirst($class_name);
		if(isset(self::$_cache_objects[$class_name])){
			return self::$_cache_objects[$class_name];
		}
		return $default;
	}
	/**
	 * 注册一个类对象
	 *
	 * @param string $class_name 要注册的类名
	 * @param object $object 要注册的对象，如果为null，则注册类名的对象
	 *
	 * @return void
	 */
	public static function register($class_name, $object = null){
		$class_name = ucfirst($class_name);
		if(!array_key_exists($class_name, self::$_cache_objects)){
			self::$_cache_objects[$class_name] = is_null($object) ? (new $class_name) : $object;
			return ;
		}else if(!is_null($object)){
			throw new RedefineException('该类名已经注册，无法再次注册不同的对象!');
		}
	}
	
	/**
	 * 替换已经注册的类对象
	 *
	 * @param string $class_name 要注册的类名
	 * @param object $object 要注册的对象，如果为null，则注册类名的对象
	 *
	 * @return void
	 */
	public static function replace($calss_name, $object = null){
		$class_name = ucfirst($class_name);
		if(is_null($object)){
			self::$_cache_objects[$class_name] = new $class_name;
		}else{
			self::$_cache_objects = $object;
		}
	}
	/**
	 * 移除已经注册的类对象
	 *
	 * @param string $class_name 要移除的类对象名
	 * @return void
	 */
	public static function remove($class_name){
		$class_name = ucfirst($class_name);
		if(!array_key_exists($class_name, self::$_cache_objects)){
			throw new ClassNotFoundException('没有找到该类的对象!');
		}
		unset(self::$_cache_objects[$calss_name]);
	}
	/**
	 * 重置
	 */
	public static function clear(){
		self::$_cache_objects = array();
	}
}
