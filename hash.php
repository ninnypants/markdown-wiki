<html>
<head>
	<title></title>
</head>
<body>
<form method="post" action="">
	<input type="text" name="p">
	<input type="submit" value="Hash">
</form>
</body>
</html>
<?php
require 'include/user.class.php';
$usr = new User();
echo $usr->hash_password($_POST['p']);