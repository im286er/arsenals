<?php

namespace Arsenals\Core\Views;

use Arsenals\Core\Abstracts\Arsenals;
use Arsenals\Core\Config;

if (!defined('APP_NAME')) {
    exit('Access Denied!');
}
/**
 * 简单的视图实现.
 *
 * @author 管宜尧<mylxsw@126.com>
 */
class SimpleView extends Arsenals implements View
{
    /**
     * (non-PHPdoc).
     *
     * @see \Arsenals\Core\Views\View::parse()
     */
    public function parse($vm)
    {
        if (!$vm instanceof ViewAndModel) {
            return false;
        }
        // 给视图传递的数据
        @extract($vm->getDatas());
        // 读取视图相关配置信息
        $config = Config::load('config');
        $_view_path = VIEW_PATH.($config['multi_theme'] ? $config['theme'].DIRECTORY_SEPARATOR : '');
        // 捕获视图输出内容
        ob_start();
        // 加载视图函数库
        if (file_exists("{$_view_path}functions.php")) {
            include "{$_view_path}functions.php";
        }
        // 加载视图
        include $_view_path.$vm->getView().'.php';
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }
}
