<!DOCTYPE html>
<html lang="ru-RU">
	<head>
		{{ if seo.title }}<title>{{ seo.title }}</title>{{ endif }}
		{{ if seo.description }}<meta name="description" content="{{ seo.description }}" />{{ endif }}
		{{ if seo.keywords }}<meta name="keywords" content="{{ seo.keywords }}" />{{ endif }}
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui" />
		<link rel="shortcut icon" href="/favicon.png" type="image/x-icon" />
		<link href="/public/css/style.css" rel="stylesheet" type="text/css" />
	</head>
	<body class="Body">
		{{ use "inc/header" }}
		<div class="Content">
			<div class="TaskWindow _hidden" block="taskwindow"><div class="TaskWindow-pane"></div></div>
			<div class="ContentWrap" block="contentwrap"></div>
		</div>
		{{ sidebar }}
		{{ use "inc/popup" }}
		<script src="/public/js/gui_min.js"></script>
		<script src="/public/js/interface_min.js"></script>
		<script src="/public/js/layouts_min.js"></script>
		<script src="/public/js/router.js"></script>
	</body>
</html>