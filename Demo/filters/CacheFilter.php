<?php
namespace Demo\filters;

use Arsenals\Core\Abstracts\Filter;
/**
 * 缓存过滤器
 * 
 * @author 管宜尧<mylxsw@126.com>
 *
 */
class CacheFilter implements Filter {
	
	/* (non-PHPdoc)
	 * @see \Arsenals\Core\Abstracts\Filter::doFilter()
	 */
	public function doFilter(\Arsenals\Core\Filters $filterChain,\Arsenals\Core\Router $router) {
		$path_info = $router->getPathInfo();
		// 所有文章页面内容缓存
		if(\Arsenals\Core\str_start_with($path_info, 'articles/show/') ){
			//$cache_file = CACHE_PATH . md5($path_info);
			$cache_file = BASE_PATH . $path_info;

			// 如果存在文件，则读取缓存
			// 这里实际上没有用了
			// if (file_exists($cache_file)) {
			// 	// 文件创建时间s
			// 	//$alive_time = time() - filectime($cache_file);
				
			// 	require $cache_file;
			// 	return true;
			// }
			// 如果不存在，则重新写入文件
			ob_start();
			$filterChain->doFilter();
			$content = ob_get_contents();
			ob_end_clean();
			file_put_contents($cache_file, $content);
			echo $content;
			return true;
		}

		$filterChain->doFilter();
	}
}