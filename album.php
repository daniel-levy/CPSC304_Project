<!--Page containing database of albums.-->
 
<p>Enter an album and choose a recommendation filter to find similar albums.</p>

<p><font size="2">Album Title</font></p>

<form method="GET" action="album.php">
<p><input type="text" name="title" size="20">
<input type="submit" value="By Number Of Songs" name="numberSearch">
<input type="submit" value="By Release Date" name="releaseSearch">
<input type="submit" value="By Running Time" name="durationSearch">
<input type="submit" value="By Rating" name="ratingSearch">
<input type="submit" value="By Genre" name="genreSearch">
<input type="submit" value="By Artist" name="mcSearch">
<input type="submit" value="By Label" name="labelSearch"></p>
</form>

<form method="GET" action="album.php">
<input type="submit" value="See Database" name="queryAll">
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

<form method="POST" action="album.php">
<input type="submit" value="Songs" name="goToSong">    
<input type="submit" value="Artists" name="goToArtist">
<input type="submit" value="Labels" name="goToLabel">
<input type="submit" value="Favorite List" name="goToFL">
<input type="submit" value="Special Queries" name="goToSQ">
</form>

<form method="POST" action="album.php">
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

// prints table of results
function printTableAll($resultFromSQL, $namesOfColumnsArray)
{
    echo "<br>Here are all the albums you can search with:<br>";
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

// go to designated page when transfer button is clicked
if (array_key_exists('goToArtist', $_POST)) {
	header("location: musiccreator.php");
} 
else if (array_key_exists('goToSong', $_POST)) {
	header("location: song.php");
}
else if (array_key_exists('goToLabel', $_POST)) {
	header("location: recordlabel.php");
}
else if (array_key_exists('goToFL', $_POST)) {
	header("location: favoritelist.php");
}
else if (array_key_exists('goToSQ', $_POST)) {
	header("location: custom.php");
}
else if (array_key_exists('logout', $_POST)) {
	header("location: login.php");
}

// Connect Oracle...
if ($db_conn) {
	// Query the whole album database to find all the potential searchable albums
	if (array_key_exists('queryAll', $_GET)) {
		
		$result = executePlainSQL("select * from album"); 
		
		if ($_GET && $success) {
			$columnNames = array("Album ID", "Title", "Number of Songs",
								 "Release", "Running Time", "Rating",
								 "Primary Genre", "Secondary Genre",
								 "MC ID","Label ID");
			printTableAll($result, $columnNames);
		} else {
			header("location: album.php");
		}
	} 
	// Find the albums with similar number of songs to the album entered into the search bar
	else if (array_key_exists('numberSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from album where title='".$title."'"); 
		$songNum = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$songNum = $row[2]; 
		}
		
		$final = executePlainSQL('select title, number_of_songs, primary from album where title<>\''.$title.'\' and number_of_songs='.$songNum);
		if ($_GET && $success) {
			$columnNames = array("Title", "Number of Songs","Primary Genre");
			printTableAll($final, $columnNames);
		} else {
			header("location: album.php");
		}
	} 
	// Find the albums with similar release date to the album entered into the search bar
	else if (array_key_exists('releaseSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from album where title='".$title."'"); 
		$release = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$release = $row[3]; 
		}
		
		$final = executePlainSQL('select title, release, primary from album where title<>\''.$title.'\' and ('.$release.'-release) < 5 and (release-'.$release.') < 5');
		if ($_GET && $success) {
			$columnNames = array("Title", "Release Date","Primary Genre");
			printTableAll($final, $columnNames);
		} else {
			header("location: album.php");
		}
	}
	// Find the albums with similar length to the album entered into the search bar
	else if (array_key_exists('durationSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from album where title='".$title."'"); 
		$length = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$length = $row[4]; 
		}
		
		$final = executePlainSQL('select title, running_time, primary from album where title<>\''.$title.'\' and ('.$length.'-running_time) < 5 and (running_time-'.$length.') < 5');
		if ($_GET && $success) {
			$columnNames = array("Title", "Release Date","Primary Genre");
			printTableAll($final, $columnNames);
		} else {
			header("location: album.php");
		}
	}
	// Find the albums with similar rating to the album entered into the search bar
	else if (array_key_exists('ratingSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from album where title='".$title."'"); 
		$rating = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$rating = $row[5]; 
		}
		
		$final = executePlainSQL('select title, rating, primary from album where title<>\''.$title.'\' and ('.$rating.'-rating) < 0.5 and (rating-'.$rating.') < 0.5');
		if ($_GET && $success) {
			$columnNames = array("Title","Rating","Primary Genre");
			printTableAll($final, $columnNames);
		} else {
			header("location: album.php");
		}
	} 	
	// Find the albums with similar genre to the albums entered into the search bar
	else if (array_key_exists('genreSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from album where title='".$title."'"); 
		$genre = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$genre = $row[6]; 
		}
		
		$final = executePlainSQL('select title, primary, secondary from album where title<>\''.$title.'\' and primary=\''.$genre.'\'');
		if ($_GET && $success) {
			$columnNames = array("Title","Primary Genre","Secondary Genre");
			printTableAll($final, $columnNames);
		} else {
			header("location: album.php");
		}
	} 
	// Find the albums by the same artist as the album entered into the search bar
	else if (array_key_exists('mcSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from album where title='".$title."'"); 
		$artist = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$artist = $row[8]; 
		}
		
		$final = executePlainSQL('select title, primary, secondary from album where title<>\''.$title.'\' and mc_id='.$artist);
		if ($_GET && $success) {
			$columnNames = array("Title","Primary Genre","Secondary Genre");
			printTableAll($final, $columnNames);
		} else {
			header("location: album.php");
		}
	} 
	// Find the albums on the same label as the album entered into the search bar
	else if (array_key_exists('labelSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from album where title='".$title."'"); 
		$label = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$label = $row[9]; 
		}
		
		$final = executePlainSQL('select title, primary, secondary from album where title<>\''.$title.'\' and label_id='.$label);
		if ($_GET && $success) {
			$columnNames = array("Title","Primary Genre","Secondary Genre");
			printTableAll($final, $columnNames);
		} else {
			header("location: album.php");
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