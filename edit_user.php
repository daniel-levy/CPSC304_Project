<!--Page containing database of songs with admin functionality.-->
<p>User Interface For administrator</p>
<p>*search user and show username/email/favoritelist by username:</p>
<p><font size="2"> 
username
</font></p>

<form method="GET" action="edit_user.php">
	<p>
	<input type="text" name="title" size="12">
	<input type="submit" value="search" name="searchsubmit">
	</p>
</form>

</form>

<p>*change username from User with the given username:</p>
<p><font size="2"> 
old_username / new_username
</font></p>
<form method="POST" action="edit_user.php">
	<p>
	<input type="text" name="old" size="12">
	<input type="text" name="new" size="12">
	<input type="submit" value="change" name="changesubmit">
	</p>
</form>

<p>*Delete user from User with the username below:</p>
<p><font size="2"> 
Username
</font></p>
<form method="POST" action="edit_user.php">
	<p>
	<input type="text" name="title" size="12">
	<input type="submit" value="Delete" name="deletesubmit">
	</p>
</form>

<p>*showing username/email/favoritelist:</p>

<form method="GET" action="edit_user.php">
<input type="submit" value="See users Database" name="queryAll">
</form>

<html>
<style>
    table {
        width: 20%;
        border: 1px solid black;
    }
    th {
        font-family: Arial, Helvetica, sans-serif;
        font-size: .7em;
        background: #666;
        color: #FFF;
        padding: 2px 6px;
        border-collapse: separate;
        border: 1px solid #000;
    }
    td {
        font-family: Arial, Helvetica, sans-serif;
        font-size: .7em;
        border: 1px solid #DDD;
        color: black;
    }
</style>
</html>


<form method="POST" action="edit_user.php">
    <input type="submit" value="Songs" name="goToSong">
<input type="submit" value="Artists" name="goToArtist">
<input type="submit" value="Albums" name="goToAlbum">
<input type="submit" value="Labels" name="goToLabel">
</form>


<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

// prints table of results
function printTableAll($resultFromSQL, $namesOfColumnsArray)
{
    // echo "<br>Here are all the result for edit_user you can search with:<br>";
    echo "<table>";
    echo "<tr>";
    // iterate through the array and print the string contents
    foreach ($namesOfColumnsArray as $name) {
        echo "<th>$name</th>";
    }
    echo "</tr>";
    while ($row = OCI_Fetch_Array($resultFromSQL, OCI_BOTH)) {
        echo "<tr>";
        $string = "";
        // iterates through the results returned from SQL query and
        // creates the contents of the table
        for ($i = 0; $i < sizeof($namesOfColumnsArray); $i++) {
            $string .= "<td>" . $row["$i"] . "</td>";
        }
        echo $string;
        echo "</tr>";
    }
    echo "</table>";
}

// go to designated pages
if (array_key_exists('goToArtist', $_POST)) {
	header("location: musiccreator_admin.php");
} 
else if (array_key_exists('goToAlbum', $_POST)) {
	header("location: album_admin.php");
}
else if (array_key_exists('goToLabel', $_POST)) {
	header("location: recordlabel_admin.php");
}
else if (array_key_exists('goToSong', $_POST)) {
	header("location: song_admin.php");
}
//!!!!!
// Connect Oracle...
if ($db_conn) {
	// Query the whole song database to find all the potential searchable songs
	if (array_key_exists('queryAll', $_GET)) {
		
		// $result = executePlainSQL('select u.email, u.username, fl.fl_id, ls.song_id from Users u, Favorite_List fl, Liked_Song ls where u.email = fl.email and fl.email = ls.email');
		// $result = executePlainSQL("select email, username from Users");
		//$result = executePlainSQL("select u.email, u.username, fl.id from users u, favorite_list fl where u.email = fl.email");
		$resultuser = executePlainSQL("select * from users");
		$resultfl = executePlainSQL("select * from favorite_list");
		$resultls = executePlainSQL("select * from liked_song");
		$result = executePlainSQL("select u.email, u.username, fl.fl_id from users u, favorite_list fl where u.email = fl.email");
		$result1 = executePlainSQL("select u.email, u.username, fl.fl_id, ls.song_id from users u, favorite_list fl, liked_song ls where u.email = fl.email and fl.fl_id = ls.fl_id");
		
		if ($_GET && $success) {
			$columnNamesforuser = array("email", "username", "passward", "type");
			$columnNamesforfl = array("email", "fl_id");
			$columnNamesforls = array("email", "fl_id", "song_id");
			$columnNamesforresult = array("email", "username", "fl_id");
			$columnNamesforresult1 = array("email", "username", "fl_id", "song_id");
			
			// printTableAll($result, $columnNamesforuser);
			echo "user table: will delete";
			printTableAll($resultuser, $columnNamesforuser);
			echo "favorite_list table: will delete";
			printTableAll($resultfl, $columnNamesforfl);
			echo "liked_song table: will delete";
			printTableAll($resultls, $columnNamesforls);
			echo "only showing email, username and favorite list id:";
			printTableAll($result, $columnNamesforresult);
			echo "final outcome:";
			printTableAll($result1, $columnNamesforresult1);
		} else {
			header("location: edit_user.php");
		}
	}
	// searh user by username
	else if (array_key_exists('searchsubmit', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL('select u.email, u.username, fl.fl_id from users u, favorite_list fl where u.email = fl.email and u.username=\''.$title.'\''); 
		// $result = executePlainSQL('update song set release='.$release.'where title=\''.$title.'\''); 
		if ($_GET && $success) {
			$columnNames = array("email", "username","fl_id");
			echo "showing email, username, favoritelist";
			printTableAll($result, $columnNames);
		} else {
			header("location: edit_user.php");
		}
	}
	// change user name with given username
	else if (array_key_exists('changesubmit', $_POST)) {
		$old = $_POST['old'];
		$new = $_POST['new'];

		executePlainSQL('update users set username='.$new.'where username=\''.$old.'\''); 

		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully change username</br>";
		} else {
			echo"<br>Error changing</br>";
		}
	}
	// Delete user with given username
	else if (array_key_exists('deletesubmit', $_POST)) {
		$title = $_POST['title'];
		
		executePlainSQL('delete from favorite_list where email=(select email from users where username = \''.$title.'\')');

		executePlainSQL('delete from users where username=\''.$title.'\'');
		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully Deleted user</br>";
		} else {
			echo"<br>Error Deleting</br>";
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