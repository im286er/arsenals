<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>C.D.Cafe</title>
	<meta name="description" content="C.D.Cafe主页， 基于Arsenals框架开发">
	<meta name="author" content="mylxsw, code.404, ink, cookbook, recipes">
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="<?php \Demo\views\ink\public_resource_path();?>ink/css/ink.css" />
  	<!--[if lte IE 7 ]>
  		<link rel="stylesheet" type="text/css" href="<?php \Demo\views\ink\public_resource_path();?>ink/css/ink-ie7.css" />
  	<![endif]-->
  	<?php foreach ($load_css as $key => $val):?>
  		<link rel="stylesheet" type="text/css" href="<?php \Demo\views\ink\public_resource_path();?><?php echo $val;?>" />
  	<?php endforeach;?>
  	<link rel="stylesheet" type="text/css" href="<?php \Demo\views\ink\resource_path();?>css/custom.css" />
  	<link rel="stylesheet" type="text/css" href="<?php \Demo\views\ink\resource_path();?>css/style.css" />
	<!--
	<link rel="stylesheet/less" type="text/css" href="<?php \Demo\views\ink\resource_path();?>css/custom.css" />
  	<link rel="stylesheet/less" type="text/css" href="<?php \Demo\views\ink\resource_path();?>css/style.css" />
	<script type="text/javascript" src="<?php \Demo\views\ink\public_resource_path();?>less-1.4.2.min.js" ></script>
	-->
	<?php \Demo\views\ink\custom_css();?>
	
	<script type="text/javascript" src="<?php \Demo\views\ink\public_resource_path();?>ink/js/holder.js"></script>
	<script type="text/javascript" src="<?php \Demo\views\ink\public_resource_path();?>ink/js/ink.min.js"></script>
	<script type="text/javascript" src="<?php \Demo\views\ink\public_resource_path();?>ink/js/ink-ui.min.js"></script>
	<script type="text/javascript" src="<?php \Demo\views\ink\public_resource_path();?>ink/js/autoload.js"></script>
	<script type="text/javascript" src="<?php \Demo\views\ink\public_resource_path();?>require.js" ></script>
	<script type="text/javascript">
		window.onerror = function(err){
			alert("您的页面加载时出现了异常，可能影响部分功能的正常使用，建议使用Chrome浏览器。");
			return true;
		};

		var global = {
			view_resources_path: "<?php \Demo\views\ink\resource_path();?>",
			public_resources_path: "<?php \Demo\views\ink\public_resource_path();?>" 
		};
		require.config({
			baseUrl: global.view_resources_path + "js/",
			paths: {
				"jquery": global.public_resources_path + "jquery-1.8.3.min",
				"underscore": global.public_resources_path + "underscore-1.4.4",
				"backbone": global.public_resources_path + "backbone"
			}
		});
	</script>
</head>
<body>
<!--[if lt IE 8]>
	<div class="browser-invalid-tip ink-alert basic">
		<p>您正在使用一款<strong>过期</strong>的浏览器，请<a href="http://browsehappy.com/">升级您的浏览器</a>或者<a href="http://www.google.com/chromeframe/?redirect=true">下载谷歌浏览器</a>以获取最佳体验效果。</p>
		<p>You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	</div>
	<script>
		window.setTimeout(function(){
			document.getElementById("main").style.display = "none";
			document.getElementById("footer").style.display = "none";
		}, 0);
	</script>
<![endif]-->
<div class="ink-grid" id="main">
	<header id="header">
		<h1><img class="left-img" src="<?php \Demo\views\ink\resources();?>uploads/logo.jpg" /><img class="right-img" src="<?php \Demo\views\ink\resources();?>uploads/logo-right.jpg" /></h1>
		<nav class="ink-navigation vspace">
		<ul class="menu horizontal grey rounded ">
			<?php echo Demo\views\ink\top_nav(isset($current_nav) ? $current_nav : '');?>
		</ul>
		</nav>
	</header>
	<div class="column-group" id="main-content">