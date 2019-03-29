<!--Page containing queries for demo.-->

<p>Find the names of the albums released on the given label</p> 

<p><font size="2">Record Label</font></p>      

<form method="GET" action="custom.php">
<p>
<input type="text" name="label" size="20">
<input type="submit" value="Find" name="labelSearch">
</p>
</form>

<p>Find the average score of all albums released on each label</p> 

<form method="GET" action="custom.php">
<p>
<input type="submit" value="Find Average Scores" name="scoreSearch">
</p>
</form>

<p>Find the artists that released all of their songs released on the given label</p>
<form method="GET" action="custom.php">
<p>
<input type="text" name="label" size="20">
<input type="submit" value="Find the Artists" name="releaseSearch">
</p>
</form>

<form method="GET" action="custom.php">
<input type="submit" value="See Database" name="queryAll">
</form>

<form method="POST" action="custom.php">
<input type="submit" value="Logout" name="logout">
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

if (array_key_exists('logout', $_POST)) {
	header("location: login.php");
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
			header("location: custom.php");
		}
	}  
    // Find music creator by primary genre
    else if (array_key_exists('labelSearch', $_GET)) {
		$label = $_GET['label'];
		
		$result = executePlainSQL("select a.title from record_label1 r, album a where r.name='".$label."' and a.label_id = r.label_id"); 

		if ($_GET && $success) {
			$columnNames = array("Album Title");
			printTableAll($result, $columnNames);
		} else {
			header("location: custom.php");
		}
	}
	// Find music creator by primary genre
    else if (array_key_exists('scoreSearch', $_GET)) {
		
		$result = executePlainSQL("select avg(a.rating), r.name from record_label1 r, album a where a.label_id = r.label_id group by r.name"); 

		if ($_GET && $success) {
			$columnNames = array("Average Score", "Record Label Name");
			printTableAll($result, $columnNames);
		} else {
			header("location: custom.php");
		}
	}
	// Find music creator by primary genre
    else if (array_key_exists('releaseSearch', $_GET)) {
		$label = $_GET['label'];
		$result = executePlainSQL("select mc.name from music_creator mc where not exists(
									select * from record_label1 r where r.name = '$label' and not exists(
									select s.mc_id from song s where s.label_id = r.label_id and mc.mc_id = s.mc_id))"); 

		if ($_GET && $success) {
			$columnNames = array("Artist Name");
			printTableAll($result, $columnNames);
		} else {
			echo "<br>fuck<\br>";
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

