<!--Oracle/PHP test file for UBC CPSC 304.
  Created by Jiemin Zhang, 2011.
  Modified by Simona Radu, Raghav Thakur, Ed Knorr, and others.

  This file shows the very basics of how to execute PHP commands
  on Oracle.

  Specifically, it will drop a table, create a table, insert values,
  update values, and perform select queries.
 
  NOTE:  If you have a table called "Record_Label1", it will be destroyed
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
 

<p>Insert values into Record Label below:</p>

<p><font size="2"> 
Label ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Founder&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Primary Genre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Secondary Genre&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Country&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Founder's Name
</font></p>

<form method="POST" action="recordlabel_admin.php">
<p>
<input type="text" name="name" size="12">
<input type="text" name="founder" size="12">
<input type="submit" value="Update" name="updatesubmit">
</p>
</form>


<!-- Create a form to pass the values.  
     See below for how to get the values. --> 
     
<p>Enter a record label and choose a recommendation filter to find ones similar to it</p> 

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
<input type="submit" value="Artists" name="goToArtist">
<input type="submit" value="Albums" name="goToAlbum">
<input type="submit" value="Songs" name="goToSong">
</form>


<?php

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
	echo "<br>Got data from table Record_Label1:<br>";
	echo "<table>";
	echo "<tr><th>ID</th><th>Name</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["mc_id"] . "</td><td>" .$row["name"] . "</td><td>" .
		$row["number_of_members"] . "</td><td>" .
		$row["number_of_releases"] ."</td><td>" .
		$row["years_active"] . "</td><td>" .
		$row["country_of_origin"] . "</td><td>" .
		$row["primary"] . "</td><td>" .$row["secondary"] . "</td></tr>"; //or just use "echo $row[0]" 
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
    echo "<br>Here is the output, nicely formatted:<br>";
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

if (array_key_exists('goToArtist', $_POST)) {
	header("location: musiccreator_admin.php");
} 
else if (array_key_exists('goToAlbum', $_POST)) {
	header("location: album_admin.php");
}
else if (array_key_exists('goToSong', $_POST)) {
	header("location: song_admin.php");
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
    // Find music creator by primary genre
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
			header("location: recordlabel.php");
		}
	}
    // Find music creator by country
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
			header("location: recordlabel.php");
		}
	}       
    // Find music creator by years active
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
			header("location: recordlabel.php");
		}
	}
	// Insert values into music creator
	else if (array_key_exists('insertsubmit', $_POST)) {
	    // Insert values into table
        $tuple = array (
			":bind1" => $_POST['labelID'],
			":bind2" => $_POST['name'],
			":bind3" => $_POST['founder'],
			":bind4" => $_POST['primary'],
			":bind5" => $_POST['secondary'],
			":bind6" => $_POST['country'],
			":bind7" => $_POST['yearsActive']
		);
		$alltuples = array (
			$tuple
		);
		executeBoundSQL("insert into Record_Label1 values (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6, :bind7)", $alltuples);
		$result = executePlainSQL("select * from Record_Label1");
		if ($_POST && $success) {
			$columnNames = array("LabelID", "Name", "Founder",
                                "PrimaryGenre", "SecondaryGenre",
                                "Country", "YearsActive");
			printTableAll($result, $columnNames);
		} else {
			header("location: recordlabel.php");
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

