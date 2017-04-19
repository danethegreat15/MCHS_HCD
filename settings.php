<?php
	require_once("myFunctions.php");
	session_name("helloWorldSession");
	session_start();
	// ***************** Debugging Stuff *************************
	$DEBUG = getDebugState();
	
	if (count($_REQUEST) > 0) $_SESSION['LAST_REQUEST'] = $_REQUEST;
	debug('$_POST', $_POST);
	debug('$_SESSION', $_SESSION);
	
	// ***************** Load Constant Arrays *********************
	$TIME_ZONES = getTimeZones();
	
	// ***************** Database Pre-Loading ********************
	$DBH = connectToDB();
	
	if (isset($_SESSION['userID']))
	{
		$userRecord = getUserRecord($_SESSION['userID'], $DBH);
		debug('$userRecord',$userRecord);
		$screen_name = $userRecord['screen_name'];
		$timeZone = $userRecord['time_zone'];
	}
	$QUEUE_PRIVILEGE = getQueuePrivilege($DBH);
	//debug('$QUEUE_PRIVILEGE', $QUEUE_PRIVILEGE);
	


	// ***************** User Input Response *********************
	
	if(isset($_SESSION['queueCodeAttributeValue']) && isset($_SESSION['queueNameAttributeValue']))
	{
		$queueNameAttributeValue = $_SESSION['queueNameAttributeValue'];
		$queueCodeAttributeValue = $_SESSION['queueCodeAttributeValue'];
		unset($_SESSION['queueCodeAttributeValue']);
		unset($_SESSION['queueNameAttributeValue']);
	}
	
	if (isset($_SESSION['userID']) && isset($_SESSION['queue_name'])) 
	{
		$queueNameAttributeValue ='value="'.$_SESSION['queue_name'].'"';
		$queueCodeAttributeValue =' autofocus';
		unset($_SESSION['queue_name']);
	}
	
	if (isset($_SESSION['userID']) && isset($_SESSION['join_queue_name'])) 
	{
		$queueCodeAttributeValue ='value="'.$_SESSION['join_queue_name'].'"';
		$queueCodeAttributeValue .=' autofocus';
		unset($_SESSION['join_queue_name']);
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Help Request Settings</title>
		<link rel="stylesheet" type="text/css" href="myStyles.css">
	</head>
<body>
	<font face='Trebuchet MS', 'sans-serif'>
    <center>
    <h1>Account Setings</h1>
    <?php
	if (isset($_SESSION['queueErrorMessage']))
	{
		echo "<div class='warningDiv'><span class='textWarning'>{$_SESSION['queueErrorMessage']}</span></div>".PHP_EOL;
		unset($_SESSION['queueErrorMessage']);
	}
	?>
    <fieldset><legend>Settings</legend>
    <form action="../HelloWorld/" method="POST">
    	Username: 
		<br>
		<input type="text" name="screen_name" value= <?php echo "'$screen_name'" ?>> 
		<br>
  		Time zone: <div class="styled-select slate tZone">
  		<select name="tZone">
			<?php
				foreach ($TIME_ZONES as $key => $value)
				{
					if($timeZone == $value)
					{
						$selected = "selected";
					}
					else
					{
						$selected = "";	
					}
					echo "<option value='$value' $selected>$key</option>" . PHP_EOL;
				}
			?>
		</select>
		</div>
		
		<button name="submit" class="button button4" style="vertical-align:middle">Submit</button>
    </form>
    </fieldset>
    <br>
    <fieldset><legend>Create A Queue</legend>
    <form action="../HelloWorld/" method="POST">
    	Queue Name: 
		<br>
		<input type="text" name="queue_name" <?php echo $queueNameAttributeValue;?>> 
		<br>
  		Join Code:
  		<br>
  		<input name="queue_code" <?php echo $queueCodeAttributeValue;?>>
  		<br>
  		<button name="create_queue" class="button buttonCreateQueue" style="vertical-align:middle">Create</button>
  		<button name="generate_code" class="button buttonCreateCode" style="vertical-align:middle">Generate Code</button>
    </form>
    </fieldset>
	 <br>
    <fieldset><legend>Join A Queue</legend>
    <form action="../HelloWorld/" method="POST">
  		Join Code:
  		<br>
  		<input name="join_queue_name" <?php echo $queueCodeAttributeValue;?>>
  		<br>
  		<button name="join_queue" class="button button4" style="vertical-align:middle">Join</button>
    </form>
    </fieldset>
</center>
	<form action="../HelloWorld/" method="POST"> <button name="back" value="back" class="button buttonBack" style="vertical-align:middle">Back</button></form>
</font>
</body>
</html>