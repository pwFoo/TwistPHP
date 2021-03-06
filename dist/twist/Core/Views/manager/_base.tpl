<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-gb" dir="ltr">
	<head>
		<title>TwistPHP Manager</title>
		<!--================================ META ================================-->
		<meta charset="utf-8">
		<!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

		<!--================================ THIRD-PARTY RESOURCES ================================-->
		{resource:unsemantic}
		{resource:font-awesome}
		{resource:arable}
		{resource:jquery}

		<!--================================ INTERFACE RESOURCES ================================-->
		{resource:twist/manager}

		<!--================================ LINKED DOCUMENTS ================================-->
		<link rel="shortcut icon" type="image/x-icon" href="{core:logo-favicon}">

		<!--================================ MOBILE STUFF ================================-->
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
	</head>
	<body>
	<div class="grid-container">
		<h1 class="grid-100 tablet-grid-100 mobile-grid-100 no-vertical-margin"><img src="{resource:core-uri}twist/logos/logo.png">TwistPHP</h1>
        <ul id="menu" class="tabs grid-20 prefix-5 tablet-grid-25 mobile-grid-100">{view:./components/global/menu.php}</ul>
		<div class="grid-70 prefix-5 tablet-grid-65 tablet-prefix-5 mobile-grid-90 mobile-prefix-5 grid-parent">
			{route:response}
		</div>
	</div>
	</body>
</html>