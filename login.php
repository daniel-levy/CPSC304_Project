<!--Page containing database of login.-->
 
<p><font size="4">Log In</p> 
<p><font size="2">Username&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Password</font></p>
<form method="GET" action="login.php">
<p>
<input type="text" name="username" size="20">
<input type="text" name="password" size="20">
<input type="submit" value="Log In" name="loginSubmit">
</p>
</form>

<p><font size="4">Sign Up</p> 
<p><font size="2">Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Username&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Password&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Type</font></p>
<form method="POST" action="login.php">
<p>
<input type="text" name="email" size="20">
<input type="text" name="username" size="20">
<input type="text" name="password" size="20">
<select name="type">
    <option value="1">Standard User</option>
    <option value="2">Administrator</option>
</select>
<input type="submit" value="Sign Up" name="signInSubmit">
</p>
</form>



<?php
/* This tells the system that it's no longer just parsing 
   HTML; it's now parsing PHP. */

// keep track of errors so it redirects the page only if
// there are no errors
$success = True;
$db_conn = OCILogon("ora_n7y9a", "a13398145", 
                    "dbhost.ugrad.cs.ubc.ca:1522/ug");

function executePlainSQL($cmdstr) { 
     // Take a plain (no bound variables) SQL command and execute it.
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr); 
     // There is a set of comments at the end of the file that 
     // describes some of the OCI specific functions and how they work.

	if (!$statement) {
		echo "<br>Cannot parse this command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); 
           // For OCIParse errors, pass the connection handle.
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute this command: " . $cmdstr . "<br>";
		$e = oci_error($statement); 
           // For OCIExecute errors, pass the statement handle.
		echo htmlentities($e['message']);
		$success = False;
	} else {

	}
	return $statement;

}

function executeBoundSQL($cmdstr, $list) {
	/* Sometimes the same statement will be executed several times.
        Only the value of variables need to be changed.
	   In this case, you don't need to create the statement several
        times.  Using bind variables can make the statement be shared
        and just parsed once.
        This is also very useful in protecting against SQL injection
        attacks.  See the sample code below for how this function is
        used. */

	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse this command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}

	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			//echo $val;
			//echo "<br>".$bind."<br>";
			OCIBindByName($statement, $bind, $val);
			unset ($val); // Make sure you do not remove this.
                              // Otherwise, $val will remain in an 
                              // array object wrapper which will not 
                              // be recognized by Oracle as a proper
                              // datatype.
		}
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute this command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement);
                // For OCIExecute errors pass the statement handle
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}
}

// Connect Oracle...
if ($db_conn) {
	// Log in to the users account
	if (array_key_exists('loginSubmit', $_GET)) {
		$user = $_GET['username'];
		$pass = $_GET['password'];
		
		$result = executePlainSQL('select * from users where username=\''.$user.'\' and password=\''.$pass.'\''); 
		
		if ($_GET && $success) {
			$type = null;
			while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
				$type = $row[3]; 
			}
			if($type == 1){
				header("location: song.php");
			} else if($type == 2){
				header("location: song_admin.php");
			} else {
				echo "<br>Error: Username or Password is incorrect<br>";
			}
			
		} else {
			echo "<br>Error: Username or Password is incorrect<br>";
		}
	} 
	// Find the songs with similar ratings to the song entered into the search bar
	else if (array_key_exists('signInSubmit', $_POST)) {
		$email = $_POST['email'];
		$user = $_POST['username'];
		$pass = $_POST['password'];
		$type = $_POST['type'];
		
		$result = executePlainSQL('select count(*) from users where email=\''.$email.'\'');
		$count = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$count = $row[0]; 
		}
		if ($_POST && $success && $count == 0) {
			$final = executePlainSQL('insert into users values(\''.$email.'\',\''.$user.'\',\''.$pass.'\','.$type.')'); 
			OCICommit($db_conn);
			if ($_POST && $success) {
				if($type == 1){
					header("location: song.php");
				} else if($type == 2){
					header("location: song_admin.php");
				} else {
					echo "<br>Error: Something went wrong. Please try again<br>";
				}
			} else {
				echo "<br>Error: Something went wrong: Please try again<br>";
			}
		} else {
			echo "<br>Error: Account already exists<br>";
		}
	} 
	

	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}
?>
