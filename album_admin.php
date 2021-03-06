<!--Page containing database of albums.-->

<p>Insert values into Album below:</p>

<p>
<font size="2"> 
Album ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Title&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Number of songs&nbsp;&nbsp;
Release&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Running time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Rating&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Primary Genre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Secondary Genre&nbsp;&nbsp;
MC ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Label ID</font></p>

<form method="POST" action="album_admin.php">
<p>
<input type="text" name="albumID" size="12">
<input type="text" name="title" size="12">
<input type="text" name="number_of_songs" size="12">
<input type="text" name="release" size="12">
<input type="text" name="running_time" size="12">
<input type="text" name="rating" size="12">
<input type="text" name="primary" size="12">
<input type="text" name="secondary" size="12">
<input type="text" name="mc_id" size="12">
<input type="text" name="labelID" size="12">
<input type="submit" value="Insert" name="insertsubmit">
</p>
</form>

<p>Delete values from Album with the title below:</p>

<p><font size="2"> 
Title
</font></p>

<form method="POST" action="album_admin.php">
<p>
<input type="text" name="title" size="12">
<input type="submit" value="Delete" name="deletesubmit">
</p>
</form>

<p>Update the secondary genre of an album using the title entered :</p>

<p><font size="2"> 
Title&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Secondary Genre
</font></p>

<form method="POST" action="album_admin.php">
<p>
<input type="text" name="title" size="12">
<input type="text" name="secondary" size="12">
<input type="submit" value="Update" name="updatesubmit">
</p>
</form>
 
<p>Enter an album and choose a recommendation filter to find similar albums.</p>

<p><font size="2">Album Title</font></p>

<form method="GET" action="album_admin.php">
<p><input type="text" name="title" size="20">
<input type="submit" value="By Number Of Songs" name="numberSearch">
<input type="submit" value="By Release Date" name="releaseSearch">
<input type="submit" value="By Running Time" name="durationSearch">
<input type="submit" value="By Rating" name="ratingSearch">
<input type="submit" value="By Genre" name="genreSearch">
<input type="submit" value="By Artist" name="mcSearch">
<input type="submit" value="By Label" name="labelSearch"></p>
</form>

<form method="GET" action="album_admin.php">
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

<form method="POST" action="album_admin.php">
<input type="submit" value="Songs" name="goToSong">    
<input type="submit" value="Artists" name="goToArtist">
<input type="submit" value="Labels" name="goToLabel">
<input type="submit" value="Users" name="goToUsers">
</form>

<form method="POST" action="album_admin.php">
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
	header("location: musiccreator_admin.php");
} 
else if (array_key_exists('goToSong', $_POST)) {
	header("location: song_admin.php");
}
else if (array_key_exists('goToLabel', $_POST)) {
	header("location: recordlabel_admin.php");
}
else if (array_key_exists('goToUsers', $_POST)) {
	header("location: edit_user.php");
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
    // insert Album 
	else if (array_key_exists('insertsubmit', $_POST)) {
		$albumid = $_POST['albumID'];
		$title = $_POST['title'];
		$number = $_POST['number_of_songs'];
		$running_time = $_POST['running_time'];
		$release = $_POST['release'];
		$primary = $_POST['primary'];
		$secondary = $_POST['secondary'];
		$rating = $_POST['rating'];
		$mc_id = $_POST['mc_id'];
		$labelid = $_POST['labelID'];
		
		$result = executePlainSQL('insert into album values('.$albumid.',\''.$title.'\','.$number.','.$release.','.$running_time.','.$rating.',\''.$primary.'\',\''.$secondary.'\','.$mc_id.','.$labelid.')'); 
		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully Inserted</br>";
		} else {
			echo"<br>Error Inserting</br>";
		}
	}

	// delete an album with given title
	else if (array_key_exists('deletesubmit', $_POST)) {
		$title = $_POST['title'];
		
		$result = executePlainSQL('delete from album where title=\''.$title.'\''); 
		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully Deleted</br>";
		} else {
			echo"<br>Error Deleting</br>";
		}
	}

	// update secondary genre of album using title
	else if (array_key_exists('updatesubmit', $_POST)) {
		$title = $_POST['title'];
		$secondary = $_POST['secondary'];
		
		$result = executePlainSQL('update album set secondary=\''.$secondary.'\' where title=\''.$title.'\''); 
		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully Updated Secondary genre</br>";
		} else {
			echo"<br>Error Updating</br>";
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
			header("location: album_admin.php");
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
			header("location: album_admin.php");
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
			header("location: album_admin.php");
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
			header("location: album_admin.php");
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
			header("location: album_admin.php");
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
			header("location: album_admin.php");
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
			header("location: album_admin.php");
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