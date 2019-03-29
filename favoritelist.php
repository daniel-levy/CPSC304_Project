<!--Page containing favoritelist.-->

<p><font size="2">Enter your email to get favorite lists:</font></p>      
<form method="GET" action="favoritelist.php">
<p><input type="text" name="email3" size="12">
<input type="submit" value="View List" name="viewList">
</p>
</form>

<p><font size="2">Enter your email and favorite list ID to see songs:</font></p>      
<form method="GET" action="favoritelist.php">
<p><input type="text" name="email" size="12"><input type="text" name="flID" 
size="12">
<input type="submit" value="View Songs" name="viewSong">
</p>
</form>

<p><font size="2">Enter your email and favorite list ID to create a list:</font></p>      
<form method="GET" action="favoritelist.php">
<p><input type="text" name="email4" size="12"><input type="text" name="flID4" 
size="12">
<input type="submit" value="Make List" name="makeList">
</p>
</form>

<p>Select your favorite list ID and insert a song ID into the list:</p>

<p><font size="2"> Email&nbsp;FL_ID&nbsp;Song</font></p>

<form method="POST" action="favoritelist.php">
   <p><input type="text" name="email2" size="12"><input type="text" name="flID2" 
size="12"><input type="text" name="song" size="12">
<!-- Define two variables to pass values. -->    
<input type="submit" value="Insert" name="insertsubmit"></p>
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

<form method="POST" action="favoritelist.php">
    <input type="submit" value="Songs" name="goToSong">
<input type="submit" value="Artists" name="goToArtist">
<input type="submit" value="Albums" name="goToAlbum">
<input type="submit" value="Labels" name="goToLabel">
<input type="submit" value="Special Queries" name="goToSQ">
</form>

<form method="POST" action="favoritelist.php">
<input type="submit" value="Logout" name="logout">
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


function printTableAll($resultFromSQL, $namesOfColumnsArray)
{
    // echo "<br>Here are all the songs you can search with:<br>";
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

if (array_key_exists('goToSong', $_POST)) {
	header("location: song.php");
}
else if (array_key_exists('goToArtist', $_POST)) {
	header("location: musiccreator.php");
} 
else if (array_key_exists('goToAlbum', $_POST)) {
	header("location: album.php");
}
else if (array_key_exists('goToLabel', $_POST)) {
	header("location: recordlabel.php");
}
else if (array_key_exists('goToSQ', $_POST)) {
	header("location: custom.php");
}
else if (array_key_exists('logout', $_POST)) {
	header("location: login.php");
}


// Connect Oracle...
if ($db_conn) {
	// Query music creator database to find all the potential searchable songs
	if (array_key_exists('viewList', $_GET)) {
	    $email = $_GET['email3'];
		
		$result = executePlainSQL("select distinct fl_id from favorite_list where email='$email'"); 
		
		if ($_GET && $success) {
			$columnNames = array("FL_ID");
			printTableAll($result, $columnNames);
		} else {
			header("location: favoritelist.php");
		}
	}
	// Insert values into music creator
	else if (array_key_exists('makeList', $_GET)) {
	    // Insert values into table
	    $email = $_GET['email4'];
	    $flid = $_GET['flID4'];

        executePlainSQL("insert into favorite_list values ('$email', $flid)");
        OCICommit($db_conn);
		$result = executePlainSQL("select fl_id from favorite_list where email='$email'");
		if ($_GET && $success) {
			$columnNames = array("FL_ID");
			printTableAll($result, $columnNames);
		} else {
			header("location: favoritelist.php");
		}		
		
	}
	// Insert values into music creator
	else if (array_key_exists('viewSong', $_GET)) {
	    // Insert values into table
	    $email = $_GET['email'];
	    $flid = $_GET['flID'];

		$result = executePlainSQL("select song_id from liked_song where email='$email' and fl_id=$flid");
		if ($_GET && $success) {
			$columnNames = array("Song_ID");
			printTableAll($result, $columnNames);
		} else {
			header("location: favoritelist.php");
		}		
		
	}
	// Insert values into music creator
	else if (array_key_exists('insertsubmit', $_POST)) {
	    // Insert values into table
	    $email = $_POST['email2'];
	    $flid = $_POST['flID2'];
	    
        $tuple = array (
			":bind1" => $_POST['email2'],
			":bind2" => $_POST['flID2'],
			":bind3" => $_POST['song']
		);
		$alltuples = array (
			$tuple
		);
		executeBoundSQL("insert into liked_song values (:bind1, :bind2, :bind3)", $alltuples);
		OCICommit($db_conn);
		$result = executePlainSQL("select fl_id, song_id from liked_song where email='$email' and fl_id=$flid");
		if ($_POST && $success) {
			$columnNames = array("FL_ID", "Song_ID");
			printTableAll($result, $columnNames);
		} else {
			header("location: favoritelist.php");
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

