<?php
include "top.php";

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1 Initialize variables
//
// SECTION: 1a.
// variables for the classroom purposes to help find errors.

$debug = false;

if (isset($_GET["debug"])) { // ONLY do this in a classroom environment
    $debug = true;
}

if ($debug)
    print "<p>DEBUG MODE IS ON</p>";


/* ##### Step one
*
* create your database object using the appropriate database username
*/



//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1b Security
//
// define security variable to be used in SECTION 2a.
$yourURL = $domain . $phpSelf;


//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1c form variables
//
// Initialize variables one for each form element
// in the order they appear on the form
$Book_Title = "";
$Book_Author = "";
$Book_Genre = "";


//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1d form error flags
//
// Initialize Error Flags one for each form element we validate
// in the order they appear in section 1c.
$Book_Title_ERROR = false;
$Book_Author_ERROR = false;
$Book_Genre_ERROR = false;

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1e misc variables
//
// create array to hold error messages filled (if any) in 2d displayed in 3c.
$errorMsg = array();

// array used to hold form values that will be written to a CSV file
//$dataRecord = array();

//$mailed=false; // have we mailed the information to the user?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2 Process for when the form is submitted
//
if (isset($_POST["btnSubmit"])) {

    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    // SECTION: 2a Security
    // 
    if (!securityCheck(true)) {
        $msg = "<p>Sorry you cannot access this page. ";
        $msg.= "Security breach detected and reported</p>";
        die($msg);
    }
    
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    // SECTION: 2b Sanitize (clean) data 
    // remove any potential JavaScript or html code from users input on the
    // form. Note it is best to follow the same order as declared in section 1c.

    $Book_Title = htmlentities($_POST["txtBook_Title"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $Book_Title;


    $Book_Author = htmlentities($_POST["txtBook_Author"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $Book_Author;
    
    $Book_Genre = htmlentities($_POST["lstBook_Genre"],ENT_QUOTES,"UTF-8");
    $dataRecord[] = $Book_Genre;




    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    // SECTION: 2c Validation
    //
    // Validation section. Check each value for possible errors, empty or
    // not what we expect. You will need an IF block for each element you will
    // check (see above section 1c and 1d). The if blocks should also be in the
    // order that the elements appear on your form so that the error messages
    // will be in the order they appear. errorMsg will be displayed on the form
    // see section 3b. The error flag ($emailERROR) will be used in section 3c.

    
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    //
    // SECTION: 2d Process Form - Passed Validation
    //
    // Process for when the form passes validation (the errorMsg array is empty)
    //
    if (!$errorMsg) {
        if ($debug)
            print "<p>Form is valid</p>";

        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        //
        // SECTION: 2e Save Data
        //
        // This block saves the data to a CSV file.
            
        $query  = "SELECT pmkItem_ID, fldBook_Title, fldBook_Author, fldBook_Genre, fldPrice, fldAvailable ";
	$query .= "FROM tblBooks ";
	$query .= "WHERE fldBook_Title LIKE '%$Book_Title%' ";
	$query .= "AND fldBook_Author LIKE '%$Book_Author%' ";	
	$query .= "AND fldBook_Genre LIKE '%$Book_Genre%' ";	
				
       
        //$data = array($number);

    } // end form is valid
    
} // ends if form was submitted.

//#############################################################################
//
// SECTION 3 Display Form
//
?>

<article id="main">

    <?php
    //####################################
    //
    // SECTION 3a.
    //
    // 
    // 
    // 
    // If its the first time coming to the form or there are errors we are going
    // to display the form.
    if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) { // closing of if marked with: end body submit
        print "<h1>Your Request has ";
     
    $results = $thisDatabase->select($query, $data);

    
     /* ##### Step four
     * prepare output and loop through array

     *      */
    $numberRecords = count($results);

    print "<h2>Total Records: " . $numberRecords . "</h2>";
    print "<h3>SQL: " . $query . "</h3>";

    print "<table>";

    $firstTime = true;

    /* since it is associative array display the field names */
    foreach ($results as $row) {
        if ($firstTime) {
            print "<thead><tr>";
            $keys = array_keys($row);
            foreach ($keys as $key) {
                if (!is_int($key)) {
                    print "<th>" . $key . "</th>";
                }
            }
            print "</tr>";
            $firstTime = false;
        }
        
        /* display the data, the array is both associative and index so we are
         *  skipping the index otherwise records are doubled up */
        print "<tr>";
        foreach ($row as $field => $value) {
            if (!is_int($field)) {
                print "<td>" . $value . "</td>";
            }
        }
        print "</tr>";
    }
    print "</table>";

    } else {


        //####################################
        //
        // SECTION 3b Error Messages
        //
        // display any error messages before we print out the form

        if ($errorMsg) {
            print '<div id="errors">';
            print "<ol>\n";
            foreach ($errorMsg as $err) {
                print "<li>" . $err . "</li>\n";
            }
            print "</ol>\n";
            print '</div>';
        }


        //####################################
        //
        // SECTION 3c html Form
        //
        /* Display the HTML form. note that the action is to this same page. $phpSelf
          is defined in top.php
          NOTE the line:

          value="<?php print $email; ?>

          this makes the form sticky by displaying either the initial default value (line 35)
          or the value they typed in (line 84)

          NOTE this line:

          <?php if($emailERROR) print 'class="mistake"'; ?>

          this prints out a css class so that we can highlight the background etc. to
          make it stand out that a mistake happened here.

         */
        ?>

        <form action="<?php print $phpSelf; ?>"
              method="post"
              id="frmBook_Search">

            <fieldset class="wrapper">
                <legend>Search for your favorite books!</legend>
                <p>Search for books here!</p>

                <fieldset class="wrapperTwo">
                    <legend>Fill out one or more of the fields</legend>

                    <fieldset class="search">
                        
                        <legend>Book Information</legend>
                        
                        
                        <label for="txtBook_Title">Class Title
                            <input type="text" id="txtBook_Title" name="txtBook_Title"
                                   value="<?php print $Book_Title; ?>"
                                   tabindex="100" maxlength="45" placeholder="Enter Book Title (i.e. Of Mice and Men)"
                                   <?php if ($Book_Title_ERROR) print 'class="mistake"'; ?>
                                   onfocus="this.select()"
                                   autofocus>
                        </label>
                        
                        <label for="txtBook_Author">Class Author
                            <input type="text" id="txtBook_Author" name="txtBook_Author"
                                   value="<?php print $Book_Author; ?>"
                                   tabindex="200" maxlength="45" placeholder="Enter the Author's Last Name (i.e. Steinbeck)"
                                   <?php if ($Book_Author_Error) print 'class="mistake"'; ?>
                                   onfocus="this.select()" 
                                   >
                        </label>
                   
                        <label for="lstBook_Genre">Genre
                            <select id="lstBook_Genre" 
                                    name="lstBook_Genre"
                                    tabindex="300">
                                    <option selected value="">PICK</option><option value="Classic">Classic</option><option value="Crime">Crime</option><option value="Fantasy">Fantasy</option><option value="Science Fiction">Science Fiction</option><option value="Mystery">Mystery</option><option value="Tragedy">Tragedy</option>
                                    <option value="Comedy">Comedy</option><option value="Romance">Romance</option><option value="Thriller">Thriller</option>
                            </select>
                        
                        
                        
                        
                        
                     
                    </fieldset>   
                    <fieldset class="buttons">
                        <legend></legend>
                        <input type="submit" id="btnSubmit" name="btnSubmit" value="Find a book" tabindex="900" class="button">
                    </fieldset><!-- ends buttons -->
                    
                        
                        
                    </fieldset> <!-- ends contact -->
                    
                </fieldset> <!-- ends wrapper Two -->
                
               
                
            <!-- Ends Wrapper -->
        </form>

    <?php
    } // end body submit
    ?>

</article>


</body>
</html>
