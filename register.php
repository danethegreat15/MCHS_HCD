<?php
	require_once("myFunctions.php");
	session_name("qSourceSession");

	session_start();
	// ***************** Debugging Stuff *************************
	$DEBUG = getDebugState();
	
	if (count($_REQUEST) > 0) $_SESSION['LAST_REQUEST'] = $_REQUEST;
	debug('$_POST', $_POST);
	debug('$_SESSION', $_SESSION);
	// ***************** User Input Response *********************
	if (isset($_SESSION['username'])) {
		$usernameAttributeValue ='value="'.$_SESSION['username'].'"';
		$passwordAttributeValue =' autofocus';
		unset($_SESSION['username']);
	}
	else
	{
		$usernameAttributeValue ='placeholder="Username@domain.com"';
		$usernameAttributeValue.=' autofocus';
		$passwordAttributeValue ='';
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Register</title>
		<link rel="stylesheet" type="text/css" href="myStyles.css">
	</head>
	<body>
		<center>
		<h1 class = "center">Register</h1>
<?php
	if (isset($_SESSION['loginProblem']))
	{
		echo "<div class='warningDiv'><span class='textWarning'>{$_SESSION['loginProblem']}</span></div>".PHP_EOL;
		unset($_SESSION['loginProblem']);
	}
?>
	<br>
	<fieldset class='center'>

		<form action="../" method="POST">

			
				<input type="hidden" name="register" value="register">
				Email:
				<input type="email" name="username" required="required" <?php echo $usernameAttributeValue;?>>
				Password:
				<input type="password" name="password" required="required" <?php echo $passwordAttributeValue;?>>
				Verify Password:
				<input type="password" name="passwordVerify" required="required">
				<!-- <input type="submit" value="Register"> -->
				<button class="button button1" style="horizontal-align:middle vertical-align:middle">Register</button>
				
			
		</form>
		</fieldset>
		<br>
		<br>
		<a href="login.php" class="button button3">Already Have An Account?</a>
	</center>
	</body>
</html>