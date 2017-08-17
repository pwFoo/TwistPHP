<!DOCTYPE html>
<html class="no-js" lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>TwistPHP - The PHP MVC Framework with a TWIST</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    {resource:arable}
    {resource:modernizr}
</head>
<body>
<h1>Uploading</h1>
{resource:twist/fileupload}

<!--<div id="twistupload-test-1"></div>
<script>
	var fileuploadtest1 = new twistfileupload( 'twistupload-test-1', '/upload/file', 'test-1', false );
</script>

<div id="twistupload-test-2"></div>
<script>
	var fileuploadtest2 = new twistfileupload( 'twistupload-test-2', '/upload/file', 'test-2', true );
</script>

<div id="twistupload-test-3"></div>
<script>
	var fileuploadtest3 = new twistfileupload( 'twistupload-test-3', '/upload/asset', 'test-3', false );
</script>

<div id="twistupload-test-4"></div>
<script>
	var fileuploadtest4 = new twistfileupload( 'twistupload-test-4', '/upload/asset', 'test-4', true );
</script>-->

{file:upload}
{file:upload,multiple=1}
{asset:upload}
{asset:upload,multiple=1}
</body>
</html>