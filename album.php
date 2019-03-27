<!--Oracle/PHP test file for UBC CPSC 304.
  Created by Jiemin Zhang, 2011.
  Modified by Simona Radu, Raghav Thakur, Ed Knorr, and others.

  This file shows the very basics of how to execute PHP commands
  on Oracle.

  Specifically, it will drop a table, create a table, insert values,
  update values, and perform select queries.
 
  NOTE:  If you have a table called "Music_Creator", it will be destroyed
         by this sample program.

  The script assumes you already have a server set up.
  All OCI commands are commands to the Oracle libraries.
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the 
  OCILogon below to be your own ORACLE username and password.

  Next, we have some sample HTML code that will appear when you run
  this script.
 -->
 
<p>Enter an album and choose a recommendation filter to find new ones</p>

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
<input type="submit" value="Artists" name="goToArtist">
<input type="submit" value="Songs" name="goToSong">
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
$db_conn = OCILogon("ora_a5a1b", "a34545153", 
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

function printResult($result) { //prints results from a select statement
	echo "<br>Got data from table Music_Creator:<br>";
	echo "<table>";
	echo "<tr><th>ID</th><th>Name</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["NID"] . "</td><td>" . $row["NAME"] .  "</td><td>" . $row["NCOL"] . "</td></tr>"; //or just use "echo $row[0]" 
	}
	echo "</table>";
}


/*
Function printTable created by Raghav Thakur on 2018-11-15.

Input:  takes in a result returned from your SQL query and an array of
        strings of the column names
Output: prints an HTML table of the results returned from your SQL query.

printTable is an easy way to iteratively print the columns of a table, 
instead of having to manually print out each column which can be
cumbersome and lead to duplicate code all over the place.

If you will be making calls to printTable multiple times and intend to
use it for multiple php files, please do the following:

Step 1) Create a new php file and copy the printTable function and the
        associated HTML styling code into the file you created, give
        this file a meaningful name such as 'print-table.php'.
        (Search for "style" above.)

Step 2) In whichever file you want to use the printTable function,
        assuming this file also contains the server code to communicate
        with the database:  Type in "include 'print-table.php'" without
        double quotes.  If the file in which you want to use printTable
        is not in the root directory, you'll need to specify the path of 
        root directory where 'print-table.php' is.  As an example:
        "include '../print-table.php'" without double quotes.

Step 3) You can now make calls to the printTable function without 
        needing to redeclare it in your current file.

Note:  You can move all the server code into a separate file called 
       'server.php' in a similar way, except whichever file needs to
       use the server code needs to have "require 'server.php'" without
       double quotes.  So, you might have something like what's shown
       below in each file:

require 'server.php';
require 'print-table.php'

Using printTable as an example:

Note: PHP uses '$' to declare variables

$result = executePlainSQL("SELECT CUST_ID, NAME, PHONE_NUM FROM CUSTOMERS");

$columnNames = array("Customer ID", "Name", "Phone Number");
printTable($result, $columnNames); // this will print the table
                                   // in the current webpage

*/

function printTable($resultFromSQL, $namesOfColumnsArray)
{
    echo "<br>Here are some albums you should check out:<br>";
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

if (array_key_exists('goToArtist', $_POST)) {
	header("location: musiccreator.php");
} 
else if (array_key_exists('goToSong', $_POST)) {
	header("location: song.php");
}
else if (array_key_exists('goToLabel', $_POST)) {
	header("location: recordlabel.php");
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
	// Find the songs with similar rating to the album entered into the search bar
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

/* OCILogon() allows you to log onto the Oracle database
     The three arguments are the username, password, and database.
     You will need to replace "username" and "password" for this to
     to work. 
     all strings that start with "$" are variables; they are created
     implicitly by appearing on the left hand side of an assignment 
     statement */
/* OCIParse() Prepares Oracle statement for execution
      The two arguments are the connection and SQL query. */
/* OCIExecute() executes a previously parsed statement
      The two arguments are the statement which is a valid OCI
      statement identifier, and the mode. 
      default mode is OCI_COMMIT_ON_SUCCESS. Statement is
      automatically committed after OCIExecute() call when using this
      mode.
      Here we use OCI_DEFAULT. Statement is not committed
      automatically when using this mode. */
/* OCI_Fetch_Array() Returns the next row from the result data as an  
     associative or numeric array, or both.
     The two arguments are a valid OCI statement identifier, and an 
     optinal second parameter which can be any combination of the 
     following constants:

     OCI_BOTH - return an array with both associative and numeric 
     indices (the same as OCI_ASSOC + OCI_NUM). This is the default 
     behavior.  
     OCI_ASSOC - return an associative array (as OCI_Fetch_Assoc() 
     works).  
     OCI_NUM - return a numeric array, (as OCI_Fetch_Row() works).  
     OCI_RETURN_NULLS - create empty elements for the NULL fields.  
     OCI_RETURN_LOBS - return the value of a LOB of the descriptor.  
     Default mode is OCI_BOTH.  */
?>
