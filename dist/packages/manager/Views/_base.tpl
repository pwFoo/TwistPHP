<!DOCTYPE html>
<html lang="en-GB">
<head>
	<!--================================ META ================================-->
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">

	<script>
		window.TwistManagerAjaxURL = '{route:base_uri}/ajax';
	</script>

	{meta:tags}
	{resource:ajax}

	<!--================================ LINKED DOCUMENTS ================================-->
	<link rel="shortcut icon" type="image/x-icon" href="{core:logo-favicon}">

	<!--================================ MOBILE STUFF ================================-->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
</head>
<body>
<div class="window">
	<nav>
		<div class="profile">
			<a href="#"><img src="{resource:core-uri}logos/logo.png"></a>
		</div>
		<ul class="navigation">
			{view:./components/global/menu.php}
		</ul>
	</nav>
	<div class="container">
		<section class="page">
			{route:response}
		</section>
		<footer>
			<p><a href="https://twistphp.com/" title="TwistPHP" target="_blank">TwistPHP</a> &copy; {date:Y}, Proud to be OpenSource | <a href="https://twistphp.com/docs/latest" title="TwistPHP Docs">docs</a></p>
		</footer>
	</div>
</div>
<div id="modalWindow">
	<div class="modalBoxOuter">
		<div class="modalBox">
			<a href="#" class="close">X</a>
			<a href="javascript:window.print();" class="printButton"><i class="fa fa-print"></i> Print</a>
			<div class="modalContent"></div>
		</div>
	</div>
</div>
</body>
</html>