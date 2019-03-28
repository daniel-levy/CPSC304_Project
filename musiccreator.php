<!--Page containing database of artists.-->
 
<p>Enter an artist from the database and choose a recommendation filter to find similar artists.</p>

<p><font size="2">Artist</font></p>      
<form method="GET" action="musiccreator.php">
<p><input type="text" name="title" size="20">
<input type="submit" value="By Number of Members" name="memberSearch">
<input type="submit" value="By Number of Releases" name="releaseSearch">
<input type="submit" value="By Years Active" name="yearSearch">
<input type="submit" value="By Country of Origin" name="originSearch">
<input type="submit" value="By Genre" name="genreSearch">
</p>
</form>

<form method="GET" action="musiccreator.php">
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

<form method="POST" action="musiccreator.php">
<input type="submit" value="Songs" name="goToSong">
<input type="submit" value="Albums" name="goToAlbum">
<input type="submit" value="Labels" name="goToLabel">
<input type="submit" value="Favorite List" name="goToFL">
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
    echo "<br>Here are all the songs you can search with:<br>";
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
if (array_key_exists('goToSong', $_POST)) {
	header("location: song.php");
} 
else if (array_key_exists('goToAlbum', $_POST)) {
	header("location: album.php");
}
else if (array_key_exists('goToLabel', $_POST)) {
	header("location: recordlabel.php");
}
else if (array_key_exists('goToFL', $_POST)) {
	header("location: favoritelist.php");
}
// Connect Oracle...
if ($db_conn) {
	// Query music creator database to find all the potential music creators
	if (array_key_exists('queryAll', $_GET)) {
		
		$result = executePlainSQL("select * from music_creator order by mc_id"); 
		
		if ($_GET && $success) {
			$columnNames = array("MusicCreatorID", "Name", "NumberofMembers",
                                "NumberofReleases", "YearsActive",
                                "CountryofOrigin", "PrimaryGenre", "SecondaryGenre");
			printTableAll($result, $columnNames);
		} else {
			header("location: musiccreator.php");
		}
	}    
	// Find music creator by number of members
	else if (array_key_exists('memberSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from music_creator where name='".$title."'"); 
		$member = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$member = $row[2]; 
		}
		
		$final = executePlainSQL('select * from music_creator where name<>\''.$title.'\' and number_of_members='.$member.'');
		if ($_GET && $success) {
			$columnNames = array("MusicCreatorID", "Name", "NumberofMembers",
                                "NumberofReleases", "YearsActive",
                                "CountryofOrigin", "PrimaryGenre", "SecondaryGenre");
			printTable($final, $columnNames);
		} else {
			header("location: musiccreator.php");
		}
	}    
	// Find music creator by number of releases
	else if (array_key_exists('releaseSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from music_creator where name='".$title."'"); 
		$release = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$release = $row[3]; 
		}
		
		$final = executePlainSQL('select * from music_creator where name<>\''.$title.'\' and number_of_releases='.$release.'');
		if ($_GET && $success) {
			$columnNames = array("MusicCreatorID", "Name", "NumberofMembers",
                                "NumberofReleases", "YearsActive",
                                "CountryofOrigin", "PrimaryGenre", "SecondaryGenre");
			printTableAll($final, $columnNames);
		} else {
			header("location: musiccreator.php");
		}
	}       
    // Find music creator by years active
    else if (array_key_exists('yearSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from music_creator where name='".$title."'"); 
		$year = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$year = $row[4]; 
		}
		
		$final = executePlainSQL('select * from music_creator where name<>\''.$title.'\' and years_active='.$year.'');
		if ($_GET && $success) {
			$columnNames = array("MusicCreatorID", "Name", "NumberofMembers",
                                "NumberofReleases", "YearsActive",
                                "CountryofOrigin", "PrimaryGenre", "SecondaryGenre");
			printTableAll($final, $columnNames);
		} else {
			header("location: musiccreator.php");
		}
	}
    // Find music creator by country of origin
    else if (array_key_exists('originSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from music_creator where name='".$title."'"); 
		$origin = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$origin = $row[5]; 
		}
		
		$final = executePlainSQL('select * from music_creator where name<>\''.$title.'\' and country_of_origin=\''.$origin.'\'');
		if ($_GET && $success) {
			$columnNames = array("MusicCreatorID", "Name", "NumberofMembers",
                                "NumberofReleases", "YearsActive",
                                "CountryofOrigin", "PrimaryGenre", "SecondaryGenre");
			printTableAll($final, $columnNames);
		} else {
			header("location: musiccreator.php");
		}
	}  
        // Find music creator by primary genre
    else if (array_key_exists('genreSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from music_creator where name='".$title."'"); 
		$genre = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$genre = $row[6]; 
		}
		
		$final = executePlainSQL('select * from music_creator where name<>\''.$title.'\' and primary=\''.$genre.'\'');
		if ($_GET && $success) {
			$columnNames = array("MusicCreatorID", "Name", "NumberofMembers",
                                "NumberofReleases", "YearsActive",
                                "CountryofOrigin", "PrimaryGenre", "SecondaryGenre");
			printTableAll($final, $columnNames);
		} else {
			header("location: musiccreator.php");
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
