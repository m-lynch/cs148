<?php
/* the purpose of this page is to display a form to allow a poet and allow us
 * to add a new poet or update an existing poet 
 * 
 * Written By: Robert Erickson robert.erickson@uvm.edu
 * Last updated on: November 20, 2014
 * 
 */

include "top.php";
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1 Initialize variables


// SECTION: 1a.
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
$First_Name = "";
$Last_Name = "";
$Address = "";
$Email = "";

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1d form error flags
//
// Initialize Error Flags one for each form element we validate
// in the order they appear in section 1c.
$First_Name_ERROR = false;
$Last_Name_ERROR = false;
$Address_ERROR = false;
$Email_ERROR = false;
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1e misc variables
//
// create array to hold error messages filled (if any) in 2d displayed in 3c.
$errorMsg = array();
$data = array();
$dataEntered = false;

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
    $First_Name = htmlentities($_POST["txtFirst_Name"], ENT_QUOTES, "UTF-8");
    $data[] = $First_Name;

    $Last_Name = htmlentities($_POST["txtLast_Name"], ENT_QUOTES, "UTF-8");
    $data[] = $Last_Name;

    $Address = htmlentities($_POST["txtAddress"], ENT_QUOTES, "UTF-8");
    $data[] = $Address;
    
    $Email = filter_var($_POST["txtEmail"], FILTER_SANITIZE_EMAIL);

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2c Validation
//

    if ($First_Name == "") {
        $errorMsg[] = "Please enter your first name";
        $First_Name_ERROR = true;
    } 

    if ($Last_Name == "") {
        $errorMsg[] = "Please enter your last name";
        $Last_Name_ERROR = true;
    }
    
    if ($Address == "") {
        $errorMsg[] = "Please enter your address";
        $Address_ERROR = true;
    }
    
    if ($Email == "") {
        $errorMsg[] = "Please enter your email address";
        $Email_ERROR = true;
    } elseif (!verifyEmail($email)) {
        $errorMsg[] = "Your email address appears to be incorrect.";
        $Email_ERROR = true;
    }  
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2d Process Form - Passed Validation
//
// Process for when the form passes validation (the errorMsg array is empty)
//
    if (!$errorMsg) {
        if ($debug){
            print "<p>Form is valid</p>";
        }
        
        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        //
        // SECTION: 2e Save Data
        //

        $dataEntered = false;
        try {
            $thisDatabase->db->beginTransaction();
            $max = 'Select MAX(pmkMember_ID) FROM tblMembers';
            $query  = 'INSERT INTO tblMembers SET ';
            $query .= 'fldFirst_Name = $First_Name, ';
            $query .= 'fldLast_Name = $Last_Name, ';
            $query .= 'fldAddress = $Address, ';
	    $query .= 'fldEmail = $Email, ';
            $query .= 'pmkMember_ID = $max + 1 ';

          
            if ($debug) {
                print "<p>sql " . $query;
                print"<p><pre>";
                print_r($data);
                print"</pre></p>";
            }
            $results = $thisDatabase->insert($query, $data);


$numberRecords = count($results);

    print "<h2>Total Records: " . $numberRecords . "</h2>";
    print "<h3>SQL: " . $query . "</h3>";

            $primaryKey = $thisDatabase->lastInsert();
            if ($debug){
                print "<p>pmk= " . $primaryKey;
            }
// all sql statements are done so lets commit to our changes
            $dataEntered = $thisDatabase->db->commit();
            
            if ($debug)
                print "<p>transaction complete ";
        } catch (PDOExecption $e) {
            $thisDatabase->db->rollback();
            if ($debug)
                print "Error!: " . $e->getMessage() . "</br>";
            $errorMsg[] = "There was a problem with accpeting your data please contact us directly.";
        } 
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
    if ($dataEntered) { // closing of if marked with: end body submit
        print "<h1>Record Saved</h1> ";
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
          value="<?php print $Email; ?>
          this makes the form sticky by displaying either the initial default value (line 35)
          or the value they typed in (line 84)
          NOTE this line:
          <?php if($EmailERROR) print 'class="mistake"'; ?>
          this prints out a css class so that we can highlight the background etc. to
          make it stand out that a mistake happened here.
         */
        ?>
        <form action="<?php print $phpSelf; ?>"
              method="post"
              id="frmRegister">
            <fieldset class="wrapper">
                <legend>Members</legend>
<label for="txtFirst_Name" class="required">First Name
<input type="text" id="txtFirst_Name" name="txtFirst_Name"
value="<?php print $First_Name; ?>"
tabindex="100" maxlength="45" placeholder="Enter your first name"
<?php if ($First_Name_ERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
autofocus>
</label>
                
                <label for="txtLast_Name" class="required">Last Name
<input type="text" id="txtLast_Name" name="txtLast_Name"
value="<?php print $Last_Name; ?>"
tabindex="100" maxlength="45" placeholder="Enter your last name"
<?php if ($Last_Name_ERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
>
</label>
                
  <label for="txtAddress" class="required">Address
<input type="text" id="txtAddress" name="txtAddress"
value="<?php print $Address; ?>"
tabindex="100" maxlength="100" placeholder="Enter your Address"
<?php if ($Address_ERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
>
</label>
</label>
                
  <label for="txtEmail" class="required">Email
<input type="text" id="txtEmail" name="txtEmail"
value="<?php print $Email; ?>"
tabindex="100" maxlength="45" placeholder="Enter your Email"
<?php if ($Email_ERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
>
</label>                
                    </fieldset> <!-- ends contact -->
                </fieldset> <!-- ends wrapper Two -->
                <fieldset class="buttons">
                    <legend></legend>
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="900" class="button">
                </fieldset> <!-- ends buttons -->
            </fieldset> <!-- Ends Wrapper -->
        </form>
        <?php
    } // end body submit
    ?>
</article>

<?php
include "footer.php";
if ($debug)
    print "<p>END OF PROCESSING</p>";
?>
</article>
</body>
</html>
