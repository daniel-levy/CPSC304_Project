<!--Page containing database of record labels.-->

<p>Insert values into Record Label below:</p>

<p><font size="2"> 
Label ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Founder&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Primary Genre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Secondary Genre&nbsp;&nbsp;
Country&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Years Active</font></p>

<form method="POST" action="recordlabel_admin.php">
<p>
<input type="text" name="labelID" size="12">
<input type="text" name="name" size="12">
<input type="text" name="founder" size="12">
<input type="text" name="primary" size="12">
<input type="text" name="secondary" size="12">
<input type="text" name="country" size="12">
<input type="text" name="yearsActive" size="12">
<!-- Define two variables to pass values. -->    
<input type="submit" value="Insert" name="insertsubmit"></p>
</form>

<p>Delete values from Record Label with the name below:</p>

<p><font size="2"> 
Name
</font></p>

<form method="POST" action="recordlabel_admin.php">
<p>
<input type="text" name="name" size="12">
<input type="submit" value="Delete" name="deletesubmit">
</p>
</form>

<p>Update the founder's name given the name of record Label</p>

<p><font size="2"> 
Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Founder's Name
</font></p>

<form method="POST" action="recordlabel_admin.php">
<p>
<input type="text" name="name" size="12">
<input type="text" name="founder" size="12">
<input type="submit" value="Update" name="updatesubmit">
</p>
</form>

<p>Enter a record label and choose a recommendation filter to find similar labels.</p> 

<p><font size="2">Record Label</font></p>      

<form method="GET" action="recordlabel_admin.php">
<p><input type="text" name="title" size="20">
<input type="submit" value="By Genre" name="genreSearch">
<input type="submit" value="By Country" name="countrySearch">
<input type="submit" value="By Years Active" name="yearSearch">
</p>
</form>

<form method="GET" action="recordlabel_admin.php">
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

<form method="POST" action="recordlabel_admin.php">
<input type="submit" value="Songs" name="goToSong">    
<input type="submit" value="Artists" name="goToArtist">
<input type="submit" value="Albums" name="goToAlbum">
<input type="submit" value="Users" name="goToUsers">
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
    echo "<br>Here are all the labels you can search with:<br>";
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

// go to designated page after clicking transfer buttons
if (array_key_exists('goToArtist', $_POST)) {
	header("location: musiccreator_admin.php");
} 
else if (array_key_exists('goToAlbum', $_POST)) {
	header("location: album_admin.php");
}
else if (array_key_exists('goToSong', $_POST)) {
	header("location: song_admin.php");
}
else if (array_key_exists('goToUsers', $_POST)) {
	header("location: edit_user.php");
}

// Connect Oracle...
if ($db_conn) {
	// Query record label database
	if (array_key_exists('queryAll', $_GET)) {
		
		$result = executePlainSQL("select * from Record_Label1"); 
		
		if ($_GET && $success) {
			$columnNames = array("LabelID", "Name", "Founder",
                                "PrimaryGenre", "SecondaryGenre",
                                "Country", "YearsActive");
			printTableAll($result, $columnNames);
		} else {
			header("location: recordlabel_admin.php");
		}
	} 
    // Insert recordLabel
	else if (array_key_exists('insertsubmit', $_POST)) {
		$labelID = $_POST['labelID'];
		$name = $_POST['name'];
		$founder = $_POST['founder'];
		$primary = $_POST['primary'];
		$secondary = $_POST['secondary'];
		$country = $_POST['country'];
		$yearsActive = $_POST['yearsActive'];
		
		
		$result = executePlainSQL('insert into Record_Label1 values('.$labelID.',\''.$name.'\',\''.$founder.'\',\''.$primary.'\',\''.$secondary.'\',\''.$country.'\','.$yearsActive.')'); 
		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully Inserted</br>";
		} else {
			echo"<br>Error Inserting</br>";
		}
    }
    
    // delete recordlabel with given name
	else if (array_key_exists('deletesubmit', $_POST)) {
		$name = $_POST['name'];
		
		$result = executePlainSQL('delete from Record_Label1 where name=\''.$name.'\''); 
		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully Deleted</br>";
		} else {
			echo"<br>Error Deleting</br>";
		}
    }
    
    // Update the founder name given the name of record label
	else if (array_key_exists('updatesubmit', $_POST)) {
		$name = $_POST['name'];
		$founder = $_POST['founder'];
		
		$result = executePlainSQL('update Record_Label1 set founder=\''.$founder.'\' where name=\''.$name.'\''); 
		OCICommit($db_conn);
		if ($_POST && $success) {
			echo"<br>Successfully Updated</br>";
		} else {
			echo"<br>Error Updating</br>";
		}
	}	
    // Find record label by primary genre
    else if (array_key_exists('genreSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from Record_Label1 where name='".$title."'"); 
		$genre = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$genre = $row[3]; 
		}
		
		$final = executePlainSQL('select * from Record_Label1 where name<>\''.$title.'\' and primary=\''.$genre.'\'');
		if ($_GET && $success) {
			$columnNames = array("LabelID", "Name", "Founder",
                                "PrimaryGenre", "SecondaryGenre",
                                "Country", "YearsActive");
			printTableAll($final, $columnNames);
		} else {
			header("location: recordlabel_admin.php");
		}
	}
    // Find record label by country
    else if (array_key_exists('countrySearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from Record_Label1 where name='".$title."'"); 
		$country = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$country = $row[5]; 
		}
		
		$final = executePlainSQL('select * from Record_Label1 where name<>\''.$title.'\' and country=\''.$country.'\'');
		if ($_GET && $success) {
			$columnNames = array("LabelID", "Name", "Founder",
                                "PrimaryGenre", "SecondaryGenre",
                                "Country", "YearsActive");
			printTableAll($final, $columnNames);
		} else {
			header("location: recordlabel_admin.php");
		}
	}       
    // Find record label by years active
    else if (array_key_exists('yearSearch', $_GET)) {
		$title = $_GET['title'];
		
		$result = executePlainSQL("select * from Record_Label1 where name='".$title."'"); 
		$year = null;
		while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
			$year = $row[6]; 
		}
		
		$final = executePlainSQL('select * from Record_Label1 where name<>\''.$title.'\' and years_active='.$year.'');
		if ($_GET && $success) {
			$columnNames = array("LabelID", "Name", "Founder",
                                "PrimaryGenre", "SecondaryGenre",
                                "Country", "YearsActive");
			printTableAll($final, $columnNames);
		} else {
			header("location: recordlabel_admin.php");
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