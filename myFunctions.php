<?php
    function h($input)
    {
	    return htmlspecialchars($input, ENT_QUOTES, "utf-8");
    }
    
    function getDebugState() 
    {
		if (isset($_GET['debug']))
		{
			$_SESSION['debug'] = $_GET['debug'];
		}
		if (isset($_SESSION['debug']))
		{
			return $_SESSION['debug'] == 't';
		}
		else
		{
			return false;	
		}
	}

    function debug($varName, $varValue) 
    {
    	global $DEBUG;
    	if ($DEBUG)
    	{
	    	$value = h(print_r($varValue, true));
	    	echo "<pre>$varName=$value</pre>" . PHP_EOL;
    	}
    }
    
    function query($DBH, $sql, $data = array())
    {
	    try
	    {
		    // First we prepare the SQL statement by turning it into a query
		    $query = $DBH->prepare($sql);

		    // Then we execute the query using the data
		    $query->execute($data);
		
		    // And then get the results
		    $results = $query->fetchAll(PDO::FETCH_ASSOC);
		
		    // And finally we return the results
		    return $results;
	    }
	    catch(PDOException $e)
	    { // If something goes wrong we'll send the result to our debug function
		   	$eMessage=$e->getMessage();
				if (isset($_SESSION['Last PDO Exception']))
				{
					unset($_SESSION['Last PDO Exception']);
				}
				$pdoExcept['eMessage'] = wordwrap($eMessage, 120, "\n");
				$pdoExcept['sql'] = wordwrap($sql, 120, "\n");
				$pdoExcept['data'] = print_r($data,true);
				$_SESSION['Last PDO Exception'] = $pdoExcept;
	    }
    }
    
    function getTimeZones()
	{
		$TIME_ZONES['Cairo'] = "Africa/Cairo";
		$TIME_ZONES['Cancun'] = "America/Cancun";
		$TIME_ZONES['Chicago'] = "America/Chicago";
		$TIME_ZONES['Indianapolis'] = "America/Indianapolis"; 
		$TIME_ZONES['Los Angeles'] = "America/Los_Angeles";
		$TIME_ZONES['Mexico City'] = "America/Mexico_City"; 
		$TIME_ZONES['Montreal'] = "America/Montreal";
		$TIME_ZONES['New York'] = "America/New_York";
		$TIME_ZONES['Paris'] = "Europe/Paris";
		$TIME_ZONES['Sydney'] = "Australia/Sydney";	
		return $TIME_ZONES;
	}
	
	function connectToDB()
	{
		try
		{
			/* Git Group must coordinate exporting the database into the project
			 * so that each user can create their local copy.
			 * Then the group needs to research how to keep the dababase
			 * in sync with the Master
			*/
			$database = 'mysql:dbname=qsource;host=localhost';
			$dbUSer = 'qsourceadmin';
			$dbPassword = '0ff$pr!nG';

			$DBH = new PDO($database, $dbUSer, $dbPassword);
			
			/* To protect the SQL Database against Injection attacks, we also
		       turn off the default behavior of "emulating" SQL Prepared
		       statements, forcing REAL prepared statements! */
			$DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			
			/* This changes the level of error reporting to only report
		       exceptions */
			$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e)
		{
			$eMessage = $e->getMessage();
			echo wordwrap($eMessage, 150, "<br>");
			echo "<br>";
			exit;
		}
		return $DBH;	
	}
	
	function getUserRecord($userID, $DBH)
	{
		$sql = "SELECT * FROM user WHERE id =:userID";
		$data = array("userID" => $_SESSION['userID']);
		$rows = query($DBH, $sql, $data);
		$userRecord = $rows[0];
		
		$sql = "SELECT queue_id, privilege, name, join_code FROM user_queue LEFT JOIN queue ON (user_queue.queue_id = queue.id) WHERE user_id =:userID";
		$data = array("userID" => $_SESSION['userID']);
		$rows = query($DBH, $sql, $data);
		
		foreach ($rows as $row)
		{
			$nameShort = $row['queue_id'];
			$userRecord['queues'][$nameShort] = $row;
		}
		
		return $userRecord;	
	}
	
	function getQueuePrivilege($DBH)
	{
		$sql = "SELECT * FROM queue_privilege WHERE 1";
		$rows = query($DBH, $sql);
		
		foreach ($rows as $row)
		{
			$nameShort = $row['name_short'];
			$QUEUE_PRIVILEGE[$nameShort] = $row;
		}
		return $QUEUE_PRIVILEGE;	
	}
	/* Call this function with a required permission value and the user's actual
	 * permission value to determine if they have the required permission
	 */
	function hasPerm($requiredPermision, $userPermission)
	{
		return ((int)$requiredPermision & (int)$userPermission) > 0;
	}
?>