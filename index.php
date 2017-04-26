<?php
require_once("myFunctions.php");

session_name("helloWorldSession");
session_start();

if (isset($_POST['logout']))
{
	session_destroy();
	header('Location: ../HelloWorld/login.php');
	exit;
	//TEST
	//DID THIS WORK?
	//TEST
}
// *********** DEBUGGING STUFF ***********
	$DEBUG = getDebugState();
	
	if (count($_REQUEST) > 0) $_SESSION['LAST_REQUEST'] = $_REQUEST;
	debug('$_POST', $_POST);
	debug('$_SESSION', $_SESSION);
	//test again 
	
	
// *********** DATABASE PRE-LOADING ***********
$DBH = connectToDB();
$QUEUE_PRIVILEGE = getQueuePrivilege($DBH);
//debug('$QUEUE_PRIVILEGE', $QUEUE_PRIVILEGE);

//**************** Funtions ********************
function getJoinCode()
{
	global $DBH;
	$seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                //.'abcdefghijklmnopqrstuvwxyz'
                .'0123456789'); // and any other characters
	shuffle($seed); // probably optional since array_is randomized; this may be redundant
	$rand = '';
	foreach (array_rand($seed, 6) as $k) $rand .= $seed[$k];
	
	$sql = "SELECT * FROM queue WHERE join_code='$rand'";
	$rows = query($DBH, $sql);
	
	if(count($rows) == 0)
	{
		return $rand;
	}
	else
	{
		getJoinCode();
		exit;
	}
}

// *********** PROCESS USER INPUT ***********

//PUT POST INTO DATABASE
if (isset($_SESSION['userID']) && isset($_POST['post']))
{
	$userID = $_SESSION['userID'];
	$post = $_POST['post'];
	$current_queue_id = $_SESSION['current_queue_id'];

	// We will INSERT into the post table.
	$sql = "INSERT INTO post(user_id, post_text, queue_id) VALUES(:user_id, :post_text, :queue_id)";

	// Now we match the keys to the values
	$data = array("user_id" => $userID,"post_text" => $post, "queue_id" => $current_queue_id);
					
	// And finally call our query function again...
	query($DBH, $sql, $data);
	
	$lastID = $DBH->lastInsertId();
	$sql = "UPDATE post SET reply_id = $lastID WHERE id = $lastID";
	$data = array();
	query($DBH, $sql, $data);
	
	header('Location: ../HelloWorld/');
	exit;
}

//UPDATE SCREENNAME AND TIMEZONE
if(isset($_SESSION['userID']) && isset($_POST["screen_name"]) && isset($_POST["tZone"]))
{
	$screen_name = $_POST['screen_name'];
	$time_zone = $_POST['tZone'];

	$sql = "UPDATE user 
	SET screen_name=:screen_name, time_zone=:time_zone
	WHERE id=:id";
				
	// Now we match the keys to the values
	$data = array("id" => $_SESSION['userID'], "screen_name" => $screen_name, "time_zone" => $time_zone);
				
	// And finally call our query function again...
	query($DBH, $sql, $data);
	header('Location: ../HelloWorld/');
	exit;
}

//REGISTER SECTION
if(isset($_POST['register']) && isset($_POST['password']) && isset($_POST['passwordVerify']) && isset($_POST['username']))
{
	$password = $_POST['password'];
	$passwordVerify = $_POST['passwordVerify'];
	$username = $_POST['username'];

	if(filter_var($username, FILTER_VALIDATE_EMAIL))
	{ //email is valid
		// Now we write a parameterized version of our SQL query
		$sql = "SELECT * FROM user WHERE username =:username";
		
		// Then we create an array to store the data to go with the query
		$data = array("username" => $username);
		
		// And then we call our query function...
		$rows = query($DBH, $sql, $data);
		
		// And if there were no matches we will continue with the registration
		if (count($rows) == 0)
		{
			if($password == $passwordVerify)
			{ //Passwords match, check if the email is valid
				// But the password needs to be properly hashed...
				$hashPassword = password_hash($password, PASSWORD_DEFAULT);
				
				// Now we can insert the user into the database...
				$sql = "INSERT INTO user(username, screen_name, password)
							VALUES(:username, :screen_name, :password)";
				
				// Now we match the keys to the values
				// We will make the default screen name the same as the username
				$data = array("username" => $username, "screen_name" => $username, "password" => $hashPassword);
				
				// And finally call our query function again...
				query($DBH, $sql, $data);
			}
			else // password and password verify DON'T match
			{
				$loginProblem = "Your password and password verify fields don't match. ";
				$loginProblem.= "Please try again.<br>".PHP_EOL;
				$_SESSION['username'] = $username;
			}
		}
		else
		{ // Let the user know that a user already exists with this username
			$loginProblem = "$username is in use, login or try another username.<br>".PHP_EOL;
		}
	}
	else // username is not a valid email
	{
		$loginProblem = "$username is not a valid email. Try again.<br>".PHP_EOL;
	}
	

	
	
	if (isset($loginProblem))
	{
		debug('$loginProblem',$loginProblem);
		$_SESSION['loginProblem'] = $loginProblem;
		header('Location: ../HelloWorld/register.php');
		exit;
			
	}
}	

//LOGIN SECTION
if((isset($_POST['login']) || isset($_POST['register'])) && isset($_POST['password']) && isset($_POST['username']))
{
	$password = $_POST['password'];
	$username = $_POST['username'];

	$sql = "SELECT * FROM user WHERE username =:username";
		
	// Then we create an array to store the data to go with the query
	$data = array("username" => $username);
		
	// And then we call our query function...
	$rows = query($DBH, $sql, $data);
	debug('$rows[0]', $rows[0]);
	
	//Check if entered username is in the database
	if(count($rows) == 1)
	{//user is in the database, check if password is correct
		
		if(!password_verify($password, $rows[0]['password']))
		{//entered password is the not same as password in database
			$loginProblem = "Incorrect password for $username.<br>".PHP_EOL;
			$_SESSION['username'] = $username;
		}
		else
		{//passwords match
			$_SESSION['userID'] = $rows[0]['id'];
			header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']); 
			die; 
		}
	
	}
	else if(count($rows) == 0)
	{//Username not in the database, report incorrect username
		$loginProblem = "User not found.<br>".PHP_EOL;
		
	}
	else
	{//user already exists with that username
		$loginProblem = "Please contact the webaster regarding a problem logging in with $username.<br>".PHP_EOL;
	}
	
	if (isset($loginProblem))
	{
		debug('$loginProblem',$loginProblem);
		$_SESSION['loginProblem'] = $loginProblem;
		header('Location: ../HelloWorld/login.php'); 
		exit;
	}
}
else
{ // neither Logout, Login, nor Register has occurred
	if (isset($_SESSION['userID']))
	{
		$current_queue_id = $_SESSION['current_queue_id'];
		$userRecord = getUserRecord($_SESSION['userID'], $DBH);
		debug('$userRecord',$userRecord);
		$QUEUES = $userRecord['queues'];
		
		$currentQueue = $QUEUES[$current_queue_id];
		$joinCode = $currentQueue['join_code'];

		$sql = "SELECT post.*, user.screen_name FROM post LEFT JOIN user ON post.user_id = user.id WHERE queue_id = :queue_id ORDER BY reply_id, date_time";
		$data = array("queue_id" => $current_queue_id);
		$posts = query($DBH, $sql, $data);
		debug('$posts',$posts);
	}
	else
	{ // If no previous userRecord is set then redirect to login page
		session_destroy();
		header('Location: ../HelloWorld/login.php');
		exit;
	}
}

//DELETE POSTS
if(isset($_POST['delete_post']) && isset($_SESSION['userID']))
{
	$userID = $_SESSION['userID'];
	$postID = $_POST['delete_post'];

	$sql = "SELECT user_id FROM post WHERE id =:postID";
		
	// Then we create an array to store the data to go with the query
	$data = array("postID" => $postID);
		
	// And then we call our query function...
	$rows = query($DBH, $sql, $data);
	
	if($rows[0]['user_id'] == $userID)
	{
		// We will INSERT into the post table.
		$sql = "DELETE FROM post WHERE id=:postID and user_id=$userID";
	
		// Now we match the keys to the values
		$data = array("postID" => $_POST['delete_post']);
						
		// And finally call our query function again...
		query($DBH, $sql, $data);
		unset($_SESSION['edit_post']);
		header('Location: ../HelloWorld/');
		exit;
	}
	else
	{
		$errorMessage = "You cannot delete another user's post.".PHP_EOL;
	}
	
	if (isset($errorMessage))
	{
		debug('$errorMessage',$errorMessage);
		$_SESSION['errorMessage'] = $errorMessage;
		header('Location: ../HelloWorld/'); 
		exit;
	}
}

//EDIT POSTS
//SET EDIT MODE ONLY IF USER LOGGED IN IS THE USER THAT CREATED POST TO BE EDITED
if (isset($_SESSION['userID']) && isset($_POST['edit_post']))
{
	$userID = $_SESSION['userID'];
	$postID = $_POST['edit_post'];
	
	$sql = "SELECT user_id FROM post WHERE id =:postID";
		
	// Then we create an array to store the data to go with the query
	$data = array("postID" => $postID);
		
	// And then we call our query function...
	$rows = query($DBH, $sql, $data);
	
	if($rows[0]['user_id'] == $userID)
	{
		$_SESSION['edit_post'] = $_POST['edit_post'];
		unset($_SESSION['reply_post']);
		header('Location: ../HelloWorld/');
		exit;
	}
	else
	{
		$errorMessage = "You cannot edit another user's post.".PHP_EOL;
	}
	
	if (isset($errorMessage))
	{
		debug('$errorMessage',$errorMessage);
		$_SESSION['errorMessage'] = $errorMessage;
		header('Location: ../HelloWorld/'); 
		exit;
	}
}
//EDIT THE POST
if(isset($_POST['edited_post']) && isset($_SESSION['userID']) && isset($_SESSION['edit_post']))
{
	$userID = $_SESSION['userID'];
	
	$sql = "UPDATE post SET post_text=:post_text WHERE id=:postID AND user_id=$userID";
					
	// Now we match the keys to the values
	$data = array("post_text" => $_POST['edited_post'], "postID" => $_SESSION['edit_post']);
					
	// And finally call our query function again...
	query($DBH, $sql, $data);
	unset($_SESSION['edit_post']);
	header('Location: ../HelloWorld/');
	exit;
}

//CANCEL BUTTON
if(isset($_SESSION['userID']) && isset($_POST['Cancel']) && (isset($_SESSION['edit_post']) || isset($_SESSION['reply_post'])))
{
	unset($_SESSION['edit_post']);
	unset($_SESSION['reply_post']);
	header('Location: ../HelloWorld/');
	exit;
}

//REPLY TO A POST
//SET REPLY MODE
if (isset($_SESSION['userID']) && isset($_POST['reply_post']))
{
	$userID = $_SESSION['userID'];
	$_SESSION['reply_post'] = $_POST['reply_post'];
	unset($_SESSION['edit_post']);
	header('Location: ../HelloWorld/');
	exit;
}

//PUT REPLY INTO DATABASE
if (isset($_SESSION['userID']) && isset($_POST['reply_to_post']))
{
	$userID = $_SESSION['userID'];
	$post = $_POST['reply_to_post'];
	$replyID = $_SESSION['reply_post'];
	$current_queue_id = $_SESSION['current_queue_id'];

	// We will INSERT into the post table.
	$sql = "INSERT INTO post(user_id, post_text, reply_id, queue_id) VALUES(:user_id, :post_text, :reply_id, :queue_id)";

	// Now we match the keys to the values
	$data = array("user_id" => $userID,"post_text" => $post, "reply_id" => $replyID, "queue_id" => $current_queue_id);
					
	// And finally call our query function again...
	query($DBH, $sql, $data);
	unset($_SESSION['reply_post']);
	header('Location: ../HelloWorld/');
	exit;
}

//SETTINGS BUTTON
if (isset($_POST['settings']) && isset($_SESSION['userID']))
{
	header('Location: ../HelloWorld/settings.php');
	exit;
}

//CHANGE USER'S SELECTED QUEUE
if(isset($_POST['selectedQueue']) && isset($_SESSION['userID']))
{
	$_SESSION['current_queue_id'] = $_POST['selectedQueue'];
	header('Location: ../HelloWorld/');
	exit;
}

//CREATE A QUEUE
if (isset($_SESSION['userID']) && isset($_POST['create_queue']) && isset($_POST['queue_name']) && isset($_POST['queue_code']))
{
		$userID = $_SESSION['userID'];
		$queueCode = $_POST['queue_code'];
		$queueName = $_POST['queue_name'];
		
		$sql = "SELECT * FROM queue WHERE join_code =:join_code";
			
		$data = array("join_code" => $queueCode);
			
		$rows = query($DBH, $sql, $data);
		
		if(count($rows) == 0)
		{
			$sql = "INSERT INTO queue(name, join_code) VALUES(:queue_name, :queue_code)";
			$data = array("queue_name" => $queueName, "queue_code" => $queueCode);
			query($DBH, $sql, $data);
			
			$totalPrivilege = 0;
			foreach($QUEUE_PRIVILEGE as $aPrivilege)
			{
				$totalPrivilege += $aPrivilege['value'];
			}
			
			$lastID = $DBH->lastInsertId();
			$sql = "INSERT INTO user_queue(queue_id, user_id, privilege) VALUES(:queue_id, :user_id, :privilege)";
			$data = array("queue_id" => $lastID, "user_id" => $userID, "privilege" => $totalPrivilege);
			query($DBH, $sql, $data);
			
			
			
			$_SESSION['current_queue_id'] = $lastID;
			header('Location: ../HelloWorld/');
			exit;
		}
		else
		{
			$queueErrorMessage = "That join code is already in use. Please try another one.".PHP_EOL;
			$_SESSION['queue_name'] = $_POST['queue_name'];
		}
		
		if (isset($queueErrorMessage))
		{
			debug('$queueErrorMessage',$queueErrorMessage);
			$_SESSION['queueErrorMessage'] = $queueErrorMessage;
			header('Location: ../HelloWorld/settings.php'); 
			exit;
		}
	}

//BACK BUTTON
if (isset($_POST['back']) && isset($_SESSION['userID']))
{
		header('Location: ../HelloWorld/');
		exit;
	}
	
//GENERATE A RANDOM CODE
if (isset($_SESSION['userID']) && isset($_POST['generate_code']))
{
	//	$uniqueCode = false;
	//	while(!$uniqueCode)
		{
			$randomCode = getJoinCode();
	//		$sql = "SELECT * FROM queue WHERE join_code='$randomCode'";
	//		$rows = query($DBH, $sql);
			
	//		if(count($rows) == 0) $uniqueCode = true;
		}
		
		$_SESSION['queueNameAttributeValue'] = 'value="'.$_POST['queue_name'].'"';
		$_SESSION['queueCodeAttributeValue'] ='value="'.$randomCode.'"';
		header('Location: ../HelloWorld/settings.php');
		exit;
	}

//JOIN A QUEUE
if(isset($_POST['join_queue']) && isset($_POST['join_queue_name']) && isset($_SESSION['userID']))
{
	$sql = "SELECT * FROM queue WHERE join_code =:join_code";
	$data = array("join_code" => $_POST['join_queue_name']);
	$rows = query($DBH, $sql, $data);
	
	if(count($rows) == 1)
	{
		
	}
	else
	{
		$queueErrorMessage = "That join code is not associated with a queue.".PHP_EOL;
		$_SESSION['join_queue_name'] = $_POST['join_queue_name'];
	}
	
	if (isset($queueErrorMessage))
	{
		debug('$queueErrorMessage',$queueErrorMessage);
		$_SESSION['queueErrorMessage'] = $queueErrorMessage;
		header('Location: ../HelloWorld/settings.php'); 
		exit;
	}
}

	
	$timezone = $userRecord['time_zone'];
	$screen_name = $userRecord['screen_name'];
	date_default_timezone_set($timezone);
?>
<!-- *********** MODIFY HTML *********** -->
<!DOCTYPE html>
<html>
	<body>
	
	<head>
		<link rel="stylesheet" type="text/css" href="myStyles.css">
		<title>Help Request</title>
	</head>
	<center>
		<h1>
			<?php 
				echo "Welcome " . h($screen_name) . ","; 
			?>
		</h1>
	<p>
	<font face='Trebuchet MS', 'sans-serif'>Today's date is
	<?php
		echo date("l, F jS, Y") . "<br>" . PHP_EOL;
		echo date("h:i A T") . "<br>" . PHP_EOL;
		//echo date("h:i.s A T") . "<br>" . PHP_EOL;
	?>

	<br>
	
    <?php
	    if(count($QUEUES) > 0)
	    {
	    	echo '<p class="queueChange">';
			echo '<form action="" method="POST">';
			echo 'Your Queues: <div class="styled-select slate rightSelect">';
			echo '<select name="selectedQueue">';
				
			$current_queue_id = $_SESSION['current_queue_id'];
			    
			$sql = "SELECT * FROM queue WHERE id =:current_queue_id";
			
		    $data = array("current_queue_id" => $current_queue_id);
			
		    $current_queue = query($DBH, $sql, $data);
	
			foreach ($QUEUES as $key => $value)
			{
				$temp = $value['queue_id'];
				if($current_queue[0]['id'] == $temp)
				{
					$selected = "selected";
				}
				else
				{
					$selected = "";	
				}
				$name = $value['name'];
				echo "<option value='$temp' $selected>$name</option>" . PHP_EOL;
			}
			echo '</select>';
			echo '</div>';
			echo '<button name="submit" class="button button4" style="vertical-align:middle">Submit</button>';
			echo '<br>';
			echo '<br>';
			if (isset($_SESSION['current_queue_id'])) echo "Join Code: $joinCode";
	    	echo '</form>';
	    	echo '</p>';
	    }
	    else
	    {
	    	echo "You are not enrolled in any queue at the moment.";
	    }
    ?>
	
	<br>
	<form action="" method = "POST">
		<!--<input type="submit" value="Submit"> -->
	</form>
		<br>
		<?php
			if (isset($_SESSION['current_queue_id']))
			{
					
				if (isset($_SESSION['errorMessage']))
				{
					echo "<div class='warningDiv'><span class='textWarning'>{$_SESSION['errorMessage']}</span></div>".PHP_EOL;
					unset($_SESSION['errorMessage']);
				}
				echo "<div id='postDiv'>".PHP_EOL;
				echo	"<table class='table tablePosts'>".PHP_EOL;
				echo		"<tr>".PHP_EOL;
				echo			"<th>Date and Time</th>".PHP_EOL;
				echo				"<th>Author</th>".PHP_EOL;
				echo			"<th>Post</th>".PHP_EOL;
				echo		"</tr>".PHP_EOL;
	
				foreach ($posts as $post)
				{
					if($post['id'] == $post['reply_id'])
					{
						$cssClass = 'original_post';
					}
					else
					{
						$cssClass = 'reply_post';
					}
					echo "<tr class='$cssClass'>".PHP_EOL;

						$pDate = $post['date_time'];
						$changetime = new DateTime($pDate, new DateTimeZone('UTC'));
						$changetime->setTimezone(new DateTimeZone($timezone));
						$pDate = $changetime->format('D, d M Y h:i A');
						echo "<td>$pDate</td>".PHP_EOL;
						$pPoster = h($post['screen_name']);
						echo "<td>$pPoster</td>".PHP_EOL;
						$postText = h($post['post_text']);
						$posterID = $post['user_id'];
						$postID = $post['id'];
						$replyID = $post['reply_id'];
						$postTextButtons = "";
						$postTextButtons.= "<br>";
						$postTextButtons.= "<form action='' method='POST'>".PHP_EOL;
						if($postID == $replyID) 
						{
							$postTextButtons.= "<button name='reply_post' class='button buttonReply'";
							$postTextButtons.= " value='$postID'>Reply</button>";
						}
						if ($posterID == $_SESSION['userID'])
						{
							$postTextButtons.= "<button name='delete_post' class='button button6'";
							$postTextButtons.= " value='$postID'>Delete</button>";
							$postTextButtons.= "<button name='edit_post' class='button button6'";
							$postTextButtons.= " value='$postID'>Edit</button>".PHP_EOL;
						}
						$postTextButtons.= "</form>".PHP_EOL;
				 		echo "<td>".wordwrap($postText, 50, "<br>").$postTextButtons."</td>".PHP_EOL;

					echo "</tr>".PHP_EOL;
				}
				
			
			echo "</table>".PHP_EOL;
		
			echo "<br>".PHP_EOL;

			$html = "";
			$formTitle = "Post:";
			$name = "post";
			$value = '';
			$cancelButton = "";
			$displayValue = "";
			if (isset($_SESSION['edit_post']))
			{
				$postID = $_SESSION['edit_post'];
				$formTitle = "Edit ".$formTitle;
				$name = 'edited_post';
				foreach ($posts as $post)
				{
					if (($postID == $post['id']) && ($_SESSION['userID'] == $post['user_id']))
					{
						$value = h($post['post_text']);
					}
				}
				$cancelButton = "<button name='Cancel' class='button button5' style='vertical-align:middle'>Cancel</button>";
			}
			
			else if (isset($_SESSION['reply_post']))
			{
				$postID = $_SESSION['reply_post'];
				$formTitle = "Reply To ".$formTitle;
				$name = 'reply_to_post';
				foreach ($posts as $post)
				{
					if ($postID == $post['id'])
					{
						$displayValue = h($post['post_text']);
					}
				}
				$cancelButton = "<button name='Cancel' class='button button5' style='vertical-align:middle'>Cancel</button>";
			}
			$html = "$displayValue".'<br>'.PHP_EOL;
			$html.= "<form action='' method='POST'>".PHP_EOL;
			$html.= $formTitle."<br>".PHP_EOL;
			$html.= "<input type='text' name='$name' value='$value'>"."<br>".PHP_EOL;
			$html.= "<input type='submit' value='Submit' class='button button4' style='vertical-align:middle'>".PHP_EOL;
			$html.= "</form>".PHP_EOL;
			$html.= "<form action='' method='POST'>".$cancelButton."</form>".PHP_EOL;
			echo $html;
			}
		?>
		</div>
	</p>
	<p>
		<br>
		<form action="" method="POST">
			<!-- <button type="submit" name="logout">Logout</button> -->
			<button name="logout" class="button buttonLogout" style="vertical-align:middle">Logout</button>
			<button name="settings" class="button buttonSettings" style="vertical-align:middle">Settings</button>
		</form>
	</p>
	</font>
	</center>
	</body>
</html>