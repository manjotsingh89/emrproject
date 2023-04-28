<?php

/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

//echo '<pre>';print_r($_SESSION); echo '</pre>';

$pid = $this->cpid;
$patientData = getPatientData($pid);
$recid = $this->recid;
$docid = $this->docid;
$help_id = $this->help_id;
$is_module = $this->is_module;
$is_portal = $this->is_portal;
$is_dashboard = (empty($is_module) && empty($is_portal));
$category = $this->save_catid;
$new_filename = $this->new_filename;
$webroot = $GLOBALS['web_root'];
// $url = $_SERVER['web_root']
// echo $GLOBALS['web_root']; 
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$home_link = $actual_link.$webroot.'/portal/home.php';
$encounter = '';
$include_auth = true;
// for location assign
$referer = $GLOBALS['web_root'] . "/controller.php?document&upload&patient_id=" . attr_url($pid) . "&parent_id=" . attr_url($category) . "&";

if (empty($is_module)) {
    $this->assign('title', xlt("Patient Portal") . " | " . xlt("Documents"));
} else {
    $this->assign('title', xlt("Document Templates"));
}
$this->assign('nav', 'onsitedocuments');

$catname = '';
if ($category) {
    $result = sqlQuery("SELECT name FROM categories WHERE id = ?", array($category));
    $catname = $result['name'] ?: '';
}
$catname = $catname ?: xlt("Onsite Portal Reviewed");

if (!$docid) {
    $docid = 'Privacy Document';
}

$isnew = false;
$ptName = $_SESSION['ptName'] ?? $pid;
$cuser = $_SESSION['sessionUser'] ?? $_SESSION['authUserID'];

$templateService = new DocumentTemplateService();

$patient_sex = strtolower($patientData['sex']);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php
    if ($is_dashboard) {
        echo xlt("Portal Document Review");
    } elseif (empty($is_module)) {
        echo xlt("Patient Portal Documents");
    } else {
        echo xlt("Patient Document Templates");
    }
    ?>
    </title>
    <meta name="description" content="Developed By sjpadgett@gmail.com">
    <?php
    // some necessary js globals
    echo "<script>var cpid=" . js_escape($pid) . ";var cuser=" . js_escape($cuser) . ";var ptName=" . js_escape($ptName) .
        ";var catid=" . js_escape($category) . ";var catname=" . js_escape($catname) . ";</script>";
    echo "<script>var recid=" . js_escape($recid) . ";var docid=" . js_escape($docid) . ";var isNewDoc=" . js_escape($isnew) . ";var newFilename=" . js_escape($new_filename) . ";var help_id=" . js_escape($help_id) . ";</script>";
    echo "<script>var isPortal=" . js_escape($is_portal) . ";var isModule=" . js_escape($is_module) . ";var webRoot=" . js_escape($webroot) . ";var webroot_url = webRoot;</script>";
    echo "<script>var csrfTokenDoclib=" . js_escape(CsrfUtils::collectCsrfToken('doc-lib')) . ";</script>";
    // translations
    echo "<script>var alertMsg1='" . xlt("Saved to Patient Documents") . '->' . xlt("Category") . ": " . attr($catname) . "';</script>";
    echo "<script>var msgSuccess='" . xlt("Updates Successful") . "';</script>";
    echo "<script>var msgDelete='" . xlt("Delete Successful") . "';</script>";
    // list of encounter form directories/names (that are patient portal compliant) that use for whitelisting (security)
    echo "<script>var formNamesWhitelist=" . json_encode(CoreFormToPortalUtility::getListPortalCompliantEncounterForms()) . ";</script>";

    Header::setupHeader(['no_main-theme', 'patientportal-style', 'datetime-picker', 'jspdf']);

    ?>
    <link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet">
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/libs/LAB.min.js"></script>

    <!-- Include Bootstrap Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.css">
    <script src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.js"></script>
    
	
	<!---link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/fontawesome.min.css"---->

<!----link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"--->


<!---script src="https://kit.fontawesome.com/1f46896725.js" crossorigin="anonymous"></script---->

<script src="https://use.fontawesome.com/releases/v5.0.1/js/all.js"></script>
    <script>
        $LAB.setGlobalDefaults({
            BasePath: "<?php $this->eprint($this->ROOT_URL); ?>"
        });
        $LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/underscore/underscore-min.js").script("<?php echo $GLOBALS['assets_static_relative']; ?>/moment/moment.js").script(
            "<?php echo $GLOBALS['assets_static_relative']; ?>/backbone/backbone-min.js").script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").script(
            "<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait().script(
            "<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/view.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
    </script>
    <style>
      @media print {
        #templatecontent {
          width: 1220px;
        }
      }
      .nav-pills-ovr > li > a {
        border: 1px solid !important;
        border-radius: .25rem !important;
      }
    </style>


    <style>
        /* your CSS goes here*/
        .questionnaire-div{
            background: #139595 !important;
            display: none;
        }

        .questionnaire-html{
            margin-top: 16px !important;
            padding-top: 35px;
            padding-bottom: 50px;
            padding-left: 0px !important;
            padding-right: 0px !important;
        }


        #regForm {
            background-color: #024747 !important;
            margin: 0px auto;
            font-family: Raleway;
            padding: 40px;
            border-radius: 10px;
            color: #fff;
            margin-top: 6%;
            position: relative;
        }

        h1 {
            text-align: center
        }

        input {
            padding: 18px 10px 10px 10px;
            width: 100%;
            font-size: 19px;
            font-family: Raleway;
            border: 1px solid #aaaaaa
        }

        input.invalid {
            background-color: #ffdddd
        }

        .tab {
            display: none
        }

        button {
            background-color: #4CAF50;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 17px;
            font-family: Raleway;
            cursor: pointer
        }

        button:hover {
            opacity: 0.8
        }

        #prevBtn {
            background-color: #bbbbbb;
            //display:none;
        }

        /*.step {*/
        /*    height: 10px;*/
        /*    width: 10px;*/
        /*    margin: 0 2px;*/
        /*    background-color: #bbbbbb;*/
        /*    border: none;*/
        /*    border-radius: 50%;*/
        /*    display: inline-block;*/
        /*    opacity: 0.5*/
        /*}*/
        
        .all-steps .step {
            height: 19px!important;
            width: 18px!important;
            margin: -6px 2px!important;
            background-color: #fff!important;
            border: none!important;
            border-radius: 50%!important;
            opacity: 1!important;
            display: table-cell!important;
            text-align: left!important;
            float: left!important;
        }
        .all-steps span.step:after{
            content: "-";
            color: white!important;
            display: block;
            margin-top: -10px;
            width: 57px;
            margin-left: 15px;
        }
        .all-steps span.active:after{
            margin-top: -6px;
        }

        .step.active {
            opacity: 1
        }

        .step.finish {
            background-color: #4CAF50
        }

        .all-steps {
            text-align: center;
            //position: absolute;
            //left: 40%;
            //bottom: 43px;
            display: inline-block !important;
            margin-bottom: 10px !important;
        }

        .thanks-message {
            display: none
        }

        .container {
            display: block;
            position: relative;
            padding-left: 35px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 22px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }


        /* Hide the browser's default radio button */

        .container input[type="radio"],.container input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }


        /* Create a custom radio button */

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 24px;
            width: 24px;
            background-color: #eee;
            //border-radius: 50%;
        }

        .checkmark_radio {
            position: absolute;
            top: 0;
            left: 0;
            height: 25px;
            width: 25px;
            background-color: #eee;
            border-radius: 50%;
        }


        /* On mouse-over, add a grey background color */

        .container:hover input~.checkmark {
            background-color: #ccc;
        }

        .checkmark_radio:hover input~.checkmark {
            background-color: #ccc;
        }


        /* When the radio button is checked, add a blue background */

        .container input:checked~.checkmark {
            background-color: #2196F3;
        }

        .container input:checked~.checkmark_radio {
            background-color: #2196F3;
            border: 3px solid #fff;
        }
        .selected_radio{
            background: #d7ebff;
            border: 2px solid #3097ff;
        }


        /* Create the indicator (the dot/circle - hidden when not checked) */

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .checkmark_radio:after {
            content: "";
            position: absolute;
            display: none;
        }


        /* Show the indicator (dot/circle) when checked */

        .container input:checked~.checkmark:after {
            display: block;
        }

        .container input:checked~.checkmark_radio:after {
            display: block;
        }


        /* Style the indicator (dot/circle) */

        .container .checkmark:after {
            top: 3px;
            left: 4px;
            width: 15px;
            height: 15px;
            background: #2196F3;
        }

        .container .checkmark_radio:after {
            top: 9px;
            left: 9px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            //background: white;
        }

        .form-error-message{
            display: none;
            margin: 0px auto;
            width: 71%;
            font-size: 0.75em;
            line-height: 1.5;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 0.4em 0.8em;
            font-weight: 300;
            border-radius: 3px;
            margin-top: 10px;
            position: absolute;
        }

        .personal_cancer_tab .row label{
            font-size: 18px;
            word-break: break-all;
        }

        . {
            padding: 0px !important;
        }
        select{
            width: 100%;
        }

        .checkmark {
               background-color: #fff!important;
        	   //border: solid 3px #fff!important;
           
        }
        .container input:checked~.checkmark {
            background-color: #ffffff!important;
        }

        label.container {
            text-align: left;
            padding: 0.6875em 1em 0.6875em 2.4em;
            line-height: 1.25em;
            border-color: #a9b3c6!important;
            border-radius: 3px;
            border: 1px solid;
            /* padding: 15px 0 9px; */
            transition: all 0.15s ease;
        }
        span.checkmark {
            /*width: 1.25em;
            height: 1.25em;*/
            display: block;
            border: 1px solid #2196F3;
            position: absolute;
            top: 0.6875em;
            left: 0.6875em;
            padding: 3px;
            margin-top: 1px;
            margin-left: 1px;
            background-color: #fff;
        }
        span.text {
            margin-top: 0;
            min-height: 1.25em;
            display: block;
            word-wrap: break-word;
        }
        span.checkmark_radio {
            width: 20px;
            height: 20px;
            display: block;
            border: 1px solid;
            position: absolute;
            top: 0.6875em;
            left: 0.6875em;
            padding: 3px;
            margin-top: 1px;
            margin-left: 1px;
            background-color: #fff;
        }
        span.checkmark_radio {
            border-color: #a9b3c6;
        }
        span.text {
            //font-size: 0.9em;
        }


        label.container.col-lg-5 {
            float: left;
            margin-right: 12px;
        }
        .label-container {
            text-align: left;
            display: block;
             height: 104px;
            padding-top: 11px;
            width:100%;
        }
        label.container.col-lg-3 {
            margin-right: 10px;
        	    float: left;
        }
        p.columu.col-lg-3 {
            float: left;
        }
        button#nextBtn:after{
            /*background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAACUklEQVR4nO3cO69MYRSH8XGLSyQUgkIhhMRxCToqnUqpU1JRKygpVC7xBaiULiEnLomEKDREoRCNQiQS1+C4e2SS3TkzZ82MPWuf/T6/D7Dnn1nZt7XX+3Y6kiRJkiRJkiRJkiRJkiRJwFz/hWTAduAm8BX4DlwHNmXnKhKwDfjMv94DO7PzFQeYpLfXwMbsjEUBvtHfC2BNds5iAFPM7CmwIjtrEYDLxDwElmbnbT1gPfAxWJQ7wMLszK0H7AK+BItyBZifnbn1gH3Az2BRLgJzsjO3HnAA+B0syrnsvEUADhN3NDtvEYATwYL8AQ5l5y0CcCZYlF/A/uy8RXR9gUvBonQbknuzM7cesAC4ESxK97F5d3bm1gMWA/eCRXkDTGRnbj1gGfAoWJSXwNrszK0HrASeBYvyHFidnbn1gHXAq2BRngDLszO3HrAFeBssygNgSeSgm4FrA3Q5NbyrfZuRwFbg0wg/oMFd6NmMBG4NcUCN7vR0xZgH/PgPB9dwjluQZjk23VnSHQjT+J33pj4bburVWTJRPY59yE5K6Y+9GuuL4V1g0Yg/qV5snczu5uKq7Mxtb78/DhbD9vsYPlDdDxbDD1R18hNugzjk0DDA2eBlyjGgugEnBxiUO1h7oJIBR4hzlLRBw9anag1TOgZbjtC/WajRAHuqNeoRLtipE7DBJW0NUk3fRLjos27d+0DwUuWy6HGohj7cOKBJZhiLcmuNcQN29FgS/c7NZ3K3Z7pdvYdMVd/B3XSmCdzATJIkSZIkSZIkSZIkSZKkzuj+Agg617ZOoJP6AAAAAElFTkSuQmCC')!important;*/
        	background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAACm0lEQVR4nO3cu49MYRjH8V3WZe2KRCEuhRDZRkNBRaORaCg1+AMkVsQlCo2OSqOhUygkGq1K5S5BloToNBIJIWERvvJmzyZs1po57znzTN75fsopZp5nfsk573kvZ2hIkiRJkiRJkiRJkiRJkgYDsCi6hoEGjAMngGfAF+A78LT6bFl0fQMF2AxM8W8ppA3RdQ4EYOl/wpj1GlgbXW/xgNN07gmwKrrmogEP6M4dYDS67mIxc/Pu1i1gJLr2IjEzoqrjGjAcXX9xgLvUdym6/uIAx8hzLrqHogAjwL3MUI5H91EUYCPwNiOQn8DB6D6KAkwA7zJCSaO1fdF9FAXYAXzKCCWN2HZH91EUYA/wNSOUj8D26D6KAuwHfmSEki59E9F9FAU4DPzKCOUNsC66j6KQ/4zyHFgd3UdRgAuZoaRnnPHoPooBDANXMkO57apjg4DFwI3MUG6m71noR0aBSeBhNVRT+y4vtIac1onVe+fnhjFW3f0VZ/LPQE4FFqIZ6fnm0GwgL6oPFSvNBOytu4asdkwZSB8G0smmMPXwknWyBz+mLm7qadjrM0i/DHurUDZVO7sV/WA4Z+okTS/fBz4EFDaI5p86Ucjk4nUPATU3/X41Mwyn35sCXMwMI21VHWusoEEGnMkMwyXcpgBH3OTQJ4ADbgPqr41y0xmXqbT6ui26jyIAO4HPmVtJd0X3UQRgK/A+Iww3WzfF4wh9BFjSwIGdvycLVV86/ZQZhkfamlS9DKAuD322ME9V9yyIx6JbmsX9ViMMXxzQlhqrp75ao01dbhz05TNtA1YCLzsI4xWwpvWCNJRC2fKfUB4B6/2veghYAZyt9q1NVzf7x8BRYHkva9E8fNOPJEmSJEmSJEmSJEmSpKHO/AaRj/s+GDyHHAAAAABJRU5ErkJggg==')!important;
        	  right: -0.7em!important;
        	content: "";
            width: 1.2em;
            height: 0.7em;
            display: inline-block;
            background-size: 1.15em;
            background-position: right;
            position: relative;
            background-repeat: no-repeat;
            top: 1px;
        	
        }

        button#nextBtn:hover:after {
            animation: bounce 1s 1; 
            //animation: animateNext 3s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100%{transform: translateX(0);}
            40%{transform: translateX(-5px);}
            60%{transform: translateX(-5px);}
        }
        @keyframes animateNext {
          25% {
            right: -1.2em;
          }
          50% {
            right: -0.7em;
          }
          75% {
            right: -1em;
          }
          100% {
            right: -0.7em;
          }
        }

        button#prevBtn:before{
        	content: "";
            width: 1.2em;
            height: 0.7em;
            display: inline-block;
            background-size: 1.15em;
            background-position: right;
            position: relative;
            background-repeat: no-repeat;
            top: 1px;
        	background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAACXBIWXMAAAsTAAALEwEAmpwYAAACy0lEQVR4nO3cS6hNcRTH8XNwPa4kKcJAeZSElAEZMJAyYEJhYKQoMVCKidHNxEhGKAMmYqAYKOUxII+Eoii3MPA24CJ0vb76Z49u3bMf67/Pyn//PnWme6+1f53Of6+z/7vVEhERERERERERERERERERERERkf8asAw4DtwD7gNngPXACO/aGgXoyYIYzhVgqnedjQCMAs6T7zYw1rvepAFt4CTF7fauOWnAIcp57F1zsoD9lPcLGOdde3KAHVQ33rv+pACbgd8Vw3jnXX9SgDXAoOHbcdi7h2QAS4EvhjDeA9O8+0gCsBD4YAhjEFjl3UcSgFnAa0MYYWW10buPJABTgCeGMP4A27z7SAIwMRsQWuzz7iMJ4eYNuGYMQyuqiJPbC8YwwnyrHaWgJsuGhSeMYZwLE2DvXpo6LBzqqkbskQB9xjDuABNi1dNowE5jGP1hiezdRxKALdn9gsUz4K4+dLoG14uEsRb4YQxDCsoLYwXwrejBxK5TGIuBgQjnkBKGC2MO8LbMgaTeQKx34RI5kAdVDyj1BLIpwjJXIv+o761yQKkpkCyUg8bjS+RA2jkPSEs3A8lCGQmcjX1iqRhIFsoY4DI2YbvBEn3odA1mFgokC6UXuGEMpa/wCSUfMBl4ZAxlj651RMAM4LkhkHB/szVmTY2XzbreGB+M29D4CxkTsAj4aAjlO7AyalFNBywHvhpC+RRWF959JAVYB/w0PvE+z7uPFP9zr7pBJ3hRag0u+YBd2PTrqZTIgAPGUC7p8dL4oRwxhrI9dk2NFt5ZApw2BHLLu4fkAKOBi4Y7+R7vHpIT9poDNyuG0utdf5KAScDDkmG81A97vaFMB56WCORonfXIv1BmF9ylOxCmybpoXQAsAF7lzLVWK4wuCm+MA44Bn4e8MOCU5ln+y+L5wFxtbxMRERERERERERERERERERERkVZa/gKAZwyORLXfwwAAAABJRU5ErkJggg==)!important;
            right: 0.7em;
        	
        }


        select.form-select,
        select.form-select * {
        	margin: 0;
        	padding: 0;
        	position: relative;
        	box-sizing: border-box;
        }
        select.form-select {
            position: relative;
            background-color: #ffffff;
            border-radius: 4px;
            border-color: #a9b3c6!important;
            border-radius: 3px;
            border: 1px solid;
        }
        select.form-select select {
        	font-size: 1rem;
        	font-weight: normal;
        	max-width: 100%;
        	padding: 8px 24px 8px 10px;
        	border: none;
        	background-color: transparent;
        		-webkit-appearance: none;
        		-moz-appearance: none;
        	appearance: none;
        }
        select.form-select select:active, select.form-select select:focus {
        	outline: none;
        	box-shadow: none;
        }
        select.form-select:after {
        	content: "";
        	position: absolute;
        	top: 50%;
        	right: 8px;
        	width: 0;
        	height: 0;
        	margin-top: -2px;
        	border-top: 5px solid #aaa;
        	border-right: 5px solid transparent;
        	border-left: 5px solid transparent;
        }
        select{
            height: 48px !important;
        }

        p.other_p{
            position: absolute;
            top: 1px;
            left: 66px;
            width: 83%;
            padding: 4px;
        }
        p.other_p input{
            border: 0px;
            height: 36px;
        } 
        p.other_p input:focus{
            outline: none;
        }

        .r_height,.col_meter{
            display: none;
        }

        div#nextprevious{
            margin-top: 30px;
            width: 100%;
        }   

        .blue_button{
            background-color: #3d80c4;
            margin-top: 10px;
        }
        .small_font{
            font-size: 18px;
            color: #514f4f;
        }
        .yes_no_container .container{
            border-color: #1776eb !important;
            color: #1776eb;
        }
        .yes_no_container .yes_no_container_checked{
            background-color: #1776eb !important;
            color: #ffffff !important;
        }
        .yes_no_container .yes_no_container_checked span.checkmark_radio{
            background-color: #1776eb !important;

        }
        .yes_no_container .yes_no_container_checked .checkmark_radio:after{
            background-color: #1776eb !important;   
        }
        .yes_no_container .container span.checkmark_radio{
            background-color: #ffffff;
            border: 0px;
            display: none;
        }
        .yes_no_container span.text{
            text-align: center;
        }
        .datepicker{
            padding: 10px;
        }
        
        .choices__list--multiple .choices__item{
            background-color: #e9f5ff;
            color: #499fff;
            border-radius: 1px;
            padding: 1px 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .choices[data-type*=select-multiple] .choices__button{
            border-radius: 1px;
            background-color: #ffffff;
            border-left: 0px;
            background-image: url(https://img.icons8.com/office/512/multiply.png);
            margin: 4px 0px 4px 5px;
        }
        
        .jfProgress-infoContent {
            background-color: rgba(0, 0, 0, .1);
            color: #fff;
            display: inline-block;
            cursor: pointer;
            padding: 0.75em 1em;
            line-height: 1;
            pointer-events: all;
            vertical-align: bottom;
            display:none;
        }
        .div_steps{
            text-align: center;
            display: block;
            margin-top: 39px;
        }
        
        .tab .content_div button {
            background-color: orange;
        }
        
     
        
        .div-sections-navbar {
          width: 100%;
          background-color: #555;
          overflow: auto;
          margin-top: 40px;
          border-radius: 7px;
          display:none;
        }
        
        .div-sections-navbar a {
            float: left;
            padding: 12px 0px 12px 0px;
            color: white !important;
            text-decoration: none;
            font-size: 14px;
            width: 7%;
            text-align: center;
        }
        
        .div-sections-navbar a:hover {
          background-color: #000;
          color: white;
        }
        
        .div-sections-navbar a.active {
          background-color: #024747;
        }
        
        .tab h3{
            color: #6f6767;
            text-align: center;
        }
        .introduction h3,.welcome h3{
            color: #fff !important;
        }

        .custom_tab h3{
            color: #fff;
        }
        
        .instruction{
            font-size: 19px;
        }
        .red{
            color:red;
        }
        
        @media screen and (max-width: 500px) {
          .div-sections-navbar a {
            float: none;
            display: block;
            width: 100%;
            text-align: left;
          }
        }

    </style>

</head>

<script>
    $(window).on('load', function() { 
        var questionnaire = '<?php echo @$_GET['questionnaire']; ?>';
        if(questionnaire == '1'){
            $('#topmenu .navbar-nav .dropdown-menu a').each(function(){
                if($(this).html() == 'Questionnaire1'){
                    $('body').addClass('questionnaire-body');
                    $(this).trigger('click');
                }
            });
            $('#onsiteDocumentModelContainer').hide();
            $('.questionnaire-div').show();
        }

        $('.back_to_home').click(function(){
            window.location.href = '<?= @$home_link; ?>'; 
        });

        $('.changeTypeInches').click(function(){
            var height_feet = $("input[name='height_feet']").val();
            if(height_feet == ''){
                showAlert('Height cannot be empty.');
                return false;
            }
            height_feet = parseInt(height_feet);
            var height_inch = $("input[name='height_inch']").val();
            if(height_inch == ''){
                height_inch = 0;
            }
            height_inch = parseInt(height_inch);
            var inches = (height_feet * 12) + height_inch;
            var cm = inches * 2.54;
            cm = cm.toFixed(4);
            $("input[name='cm']").val(cm);
            $('.r_inch').hide();
            $('.r_height').show();
            $('.col_cm').show();
            $('.col_meter').hide();
        });
        $('.changeTypeCm').click(function(){
            var cm = $("input[name='cm']").val();
            var m = cm * 0.01;
            m = m.toFixed(4);
            $("input[name='height_m']").val(m);
            $('.col_cm').hide();
            $('.col_meter').show();
        });
        $('.changeTypeM').click(function(){
            $('.r_height').hide();
            $('.r_inch').show();
        });

        $("input[name='different_cancers']").keyup(function(){
            var val = $(this).val();
            $('.initially_diagnosed_tab .content_div').empty();
            //var html = $('.initially_diagnosed_tab .content_row').html(); 
            var html = '<div class="row"> <div class="col-lg-4" id="p1"> <span class="small_font">Cancer Type</span> <input type="textbox" placeholder="Pre-Populate from previous question" name="pre_populate[]" readonly> </div> <div class="col-lg-3" id="p1"> <span class="small_font">Age when diagnosed</span> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]" fdprocessedid="1g35st"> </div> <div class="col-lg-3" id="p1"> <span class="small_font">Finished treatment?</span> <select class="form-select" id="finished_treatment" name="finished_treatment[]" style="height: 48px;" fdprocessedid="0gi1gf"> <option value="">Please choose</option> <option value="yes">Yes</option> <option value="no">No</option><option value="ongoing">Ongoing</option> </select> </div> </div>';
            for(i=0;i<val;i++){  
                //$('.initially_diagnosed_tab .content_div').append('<div class="row">'+html+'</div>');
                $('.initially_diagnosed_tab .content_div').append(html);
            }
            
        });
        $(".add_cancer_diagnosis").click(function(){
            var html = '<div class="row"><div class="col-lg-4" id="p1"> <span class="small_font">Cancer Type</span> <input type="textbox" placeholder="Pre-Populate from previous question" name="pre_populate[]" fdprocessedid="j7uh07"> </div> <div class="col-lg-3" id="p1"> <span class="small_font">Age when diagnosed</span> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]" fdprocessedid="x55xwd"> </div> <div class="col-lg-3" id="p1"> <span class="small_font">Finished treatment?</span> <select class="form-select" id="finished_treatment" name="finished_treatment[]" style="height: 48px;" fdprocessedid="o7a4ru"> <option value="">Please choose</option> <option value="yes">Yes</option> <option value="no">No</option> </select> </div><div class="col-lg-1" id="p1"> <button class="minus_initially_diagnosed_tab" style="margin-top: 30px;" type="button">X</button> </div></div>';
            $('.initially_diagnosed_tab .content_div').append(html);
        });

        $('body').on('click', '.minus_initially_diagnosed_tab', function() {
            $(this).closest('.row').remove();
        });

        $(".biospy_add_row").click(function(){
            var html = '<div class="row"><div class="col-lg-3" id="p1"> <span class="small_font">Type of biopsy</span> <input type="textbox" placeholder="Please enter each type of biopsy" name="type_of_biospy[]"> </div> <div class="col-lg-2" id="p1"> <span class="small_font">Quantity</span> <input type="textbox" class="numeric" placeholder="0" value="0" name="biospy_qty[]"> </div><div class="col-lg-1" id="p1"> <button class="minus_biospy_tab" style="margin-top: 30px;" type="button">X</button> </div></div>';
            $('.times_tab_div .content_div').append(html);
        });
        
        $(".abnormailties_add_row").click(function(){
             var html = '<div class="row"><div class="col-lg-3" id="p1"> <input type="textbox" placeholder="abnormality" name="abnormality[]"> </div><div class="col-lg-3" id="p1"><input type="date" name="date_occured[]"> </div> <div class="col-lg-1" id="p1"> <button class="minus_abnormailties" type="button">X</button> </div> </div> </div>';
            
            $('.past_medical_history_abnormailties .content_div').append(html);
        });

        $('body').on('click', '.minus_biospy_tab', function() {
            $(this).closest('.row').remove();
        });
        
        $('body').on('click', '.minus_abnormailties', function() {
            $(this).closest('.row').remove();
        });

        $(".biospy_performed_add_row").click(function(){
            var html = '<div class="row"><div class="col-lg-2" id="p1"> <span class="small_font">Biopsy kind</span> <input type="textbox" placeholder="Enter type of biopsy done" name="biopsy_kind[]"> </div> <div class="col-lg-2" id="p1"> <span class="small_font">Year performed</span> <input type="textbox" class="datepicker" placeholder="YYYY-MM-DD" name="year_performed[]"> </div> <div class="col-lg-2" id="p1"> <span class="small_font">On which side?</span> <select class="form-select" id="on_which_side" name="on_which_side[]" style="height: 48px;"> <option value="" >Please Select</option> <option value="Right" >Right</option> <option value="Left" >Left</option> <option value="Both sides">Both sides</option> </select> </div> <div class="col-lg-3" id="p1"> <span class="small_font">Abnormalities found?</span> <select class="form-select" id="on_which_side" name="abnormalities_found[]" style="height: 48px;"> <option value="">Please Select</option> <option value="No it was benign">No; it was benign</option> <option value="Yes showed invasive cancer">Yes; showed invasive cancer</option> <option value="Yes only precancerous changes">Yes; only precancerous changes</option> <option value="Yes but neither cancer nor precancer">Yes; but neither cancer nor precancer</option> </select> </div><div class="col-lg-1" id="p1"> <button class="minus_biospy_performed_tab" style="margin-top: 30px;" type="button">X</button> </div></div>';
            $('.biopsy_performed_div .content_div').append(html);
        });

        $('body').on('click', '.minus_biospy_performed_tab', function() {
            $(this).closest('.row').remove();
        });


        $(".yes_no_container input[type='radio']").click(function(){
            $(".yes_no_container input[type='radio']").parents('.container').removeClass('yes_no_container_checked');
            if ($(".yes_no_container input[type='radio']").is(':checked')) {
               $(this).parents('.container').addClass('yes_no_container_checked');
            }
        });

        $('.meds_add_row').click(function(){
            var html = '<div class="row"><div class="col-lg-2" id="p1"> <span class="small_font">Medication Name</span> <input type="textbox" placeholder="name of medication" name="name_of_medication[]" fdprocessedid="tj3ru"> </div> <div class="col-lg-2" id="p1"> <span class="small_font">Dose</span> <input type="textbox" class="numeric" placeholder="eg 250" name="med_dose[]" fdprocessedid="y1d3ai"> </div> <div class="col-lg-3" id="p1"> <span class="small_font">Units</span> <select class="form-select" id="meds_units" name="meds_units[]" style="height: 48px;" fdprocessedid="s8017j"> <option value="" class="translatable">Please Select</option> <option value="mg" class="translatable">mg</option> <option value="ug" class="translatable">ug</option> <option value="mL" class="translatable">mL</option> <option value="puffs" class="translatable">puffs</option> <option value="drops" class="translatable">drops</option> <option value="tablets" class="translatable">tablets</option> <option value="capsules" class="translatable">capsules</option> </select> </div> <div class="col-lg-3" id="p1"> <span class="small_font">How do you take it?</span> <select class="form-select" id="how_do_you_take" name="how_do_you_take[]" style="height: 48px;" fdprocessedid="bmjsqa"> <option value="Please select">Please select</option> <option value="swallow or chew a pill">swallow or chew a pill (PO)</option> <option value="I put it under tongue">I put it under tongue (SL)</option> <option value="I mix it in water and drink">I mix it in water and drink</option> <option value="mixed in water and in gastric tube">mixed in water and in gastric tube(per slurry)</option> <option value="drink it comes in liquid form">drink it; it comes in liquid form</option> <option value="inhaled">inhaled</option> <option value="self injected">self injected</option> <option value="clinician injects it">clinician injects it</option> <option value="topically on skin or scalp">topically on skin or scalp</option> <option value="ophthalmic drops">ophthalmic drops</option> <option value="otic drops">otic drops</option> <option value="suppository">suppository</option> </select> </div><div class="col-lg-1" id="p1"> <button class="minus_meds_list_dosage_tab" style="margin-top: 30px;" type="button">X</button> </div></div>';
            $('.meds_list_dosage_tab .content_div').append(html);
        });
        $('body').on('click', '.minus_meds_list_dosage_tab', function() {
            $(this).closest('.row').remove();
        });

        $('.supp_add_row').click(function(){ 
            var html = '<div class="row"><div class="col-lg-2" id="p1"> <span class="small_font">Supplement Name</span> <input type="textbox" placeholder="name of supplement" name="name_of_supp[]"> </div> <div class="col-lg-2" id="p1"> <span class="small_font">Dose</span> <input type="textbox" class="numeric" placeholder="eg 250" name="supp_dose[]"> </div> <div class="col-lg-3" id="p1"> <span class="small_font">Units</span> <select class="form-select" id="meds_units" name="supp_units[]" style="height: 48px;"> <option value="" class="translatable">Please Select</option> <option value="mg" class="translatable">mg</option> <option value="ug" class="translatable">ug</option> <option value="mL" class="translatable">mL</option> <option value="puffs" class="translatable">puffs</option> <option value="drops" class="translatable">drops</option> <option value="tablets" class="translatable">tablets</option> <option value="capsules" class="translatable">capsules</option> </select> </div> <div class="col-lg-3" id="p1"> <span class="small_font">How do you take it?</span> <select class="form-select" id="how_do_you_take_supp" name="how_do_you_take_supp[]" style="height: 48px;"> <option value="Please select">Please select</option> <option value="swallow or chew a pill">swallow or chew a pill (PO)</option> <option value="I put it under tongue">I put it under tongue (SL)</option> <option value="I mix it in water and drink">I mix it in water and drink</option> <option value="mixed in water and in gastric tube">mixed in water and in gastric tube(per slurry)</option> <option value="drink it comes in liquid form">drink it; it comes in liquid form</option> <option value="inhaled">inhaled</option> <option value="self injected">self injected</option> <option value="clinician injects it">clinician injects it</option> <option value="topically on skin or scalp">topically on skin or scalp</option> <option value="ophthalmic drops">ophthalmic drops</option> <option value="otic drops">otic drops</option> <option value="suppository">suppository</option> </select> </div><div class="col-lg-1" id="p1"> <button class="minus_supp_list_dosage_tab" style="margin-top: 30px;" type="button">X</button> </div></div>';
            $('.supp_list_dosage_tab .content_div').append(html);
        });
        $('body').on('click', '.minus_supp_list_dosage_tab', function() {
            $(this).closest('.row').remove();
        });


        $("input[name='boys']").keyup(function(){
            var value = $(this).val();
            var html = '';
            var j = 1;
            for(i=value;i>=1;i--){
                html += '<div class="row"> <div class="col-lg-3" id="p1"> <label>Child'+j+' First Name</label> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="boy_first_name'+j+'" placeholder=""></p> </div> </div><div class="row"> <div class="col-lg-3" id="p1"> <label>Child'+j+' Age Months</label> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="boy_age_months'+j+'" placeholder=""></p> </div> </div><div class="row"> <div class="col-lg-3" id="p1"> <label>Child'+j+' Age Years</label> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="boy_age_years'+j+'" placeholder=""></p> </div> </div>';
                j++;
            }
            //alert(html);
            $('.boys_div').html(html);
        });


        $("input[name='girls']").keyup(function(){
            var value = $(this).val();
            var html = '';
            var j = 1;
            for(i=value;i>=1;i--){
                html += '<div class="row"> <div class="col-lg-3" id="p1"> <label>Child'+j+' First Name</label> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="girl_first_name'+j+'" placeholder=""></p> </div> </div><div class="row"> <div class="col-lg-3" id="p1"> <label>Child'+j+' Age Months</label> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="girl_age_months'+j+'" placeholder=""></p> </div> </div><div class="row"> <div class="col-lg-3" id="p1"> <label>Child'+j+' Age Years</label> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="girl_age_years'+j+'" placeholder=""></p> </div> </div>';
                j++;
            }
            //alert(html);
            $('.girls_div').html(html);
        });

        $("input[name='siblings']").keyup(function(){
            var value = $(this).val();
            var html = '';
            var j = 1;
            for(i=value;i>=1;i--){
                html += '<h3><b>Sibling '+j+'</b></h3> <div class="row"> <div class="col-lg-3" id="p1"> <h3>Sibling`s first name?</h3> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="sibling'+j+'_first_name" placeholder="siblings`s first name"></p> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <h3>What was this sibling`s biologic gender at birth?</h3> </div> <div class="col-lg-6" id="p1"> <label class="container"><span class="text">Male </span><input type="radio" name="sibling'+j+'_biologic_gender" value="male" checked> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">Female </span><input type="radio" name="sibling'+j+'_biologic_gender" value="female"> <span class="checkmark_radio"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <h3>Did this sibling currently identify with a different gender from their biologic gender?</h3> </div> <div class="col-lg-6" id="p1"> <label class="container"><span class="text">Yes </span><input type="radio" name="sibling'+j+'_identify" value="yes" checked> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">No </span><input type="radio" name="sibling'+j+'_identify" value="no"> <span class="checkmark_radio"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <h3>Was this sibling descended from the same biologic mother and father as you?</h3> </div> <div class="col-lg-6" id="p1"> <label class="container"><span class="text">Yes, we share the same mother and father </span><input type="radio" name="sibling'+j+'_descended" value="yes_we_share" checked> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">No, we only share the same mother </span><input type="radio" name="sibling'+j+'_descended" value="no_same_mother"> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">No, we only share the same father </span><input type="radio" name="sibling'+j+'_descended" value="no_same_father"> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">No, we have completely different biologic parents </span><input type="radio" name="sibling'+j+'_descended" value="no_diff_parents"> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">I`m not sure </span><input type="radio" name="sibling'+j+'_descended" value="not_sure" checked> <span class="checkmark_radio"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <h3>Was this sibling adopted into or out of your family?</h3> </div> <div class="col-lg-6" id="p1"> <label class="container"><span class="text">Adopted In </span><input type="radio" name="sibling'+j+'_adopted" value="adopted_in" checked> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">Adopted Out </span><input type="radio" name="sibling'+j+'_adopted" value="adopted_out"> <span class="checkmark_radio"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <h3>Is this sibling still alive?</h3> </div> <div class="col-lg-6" id="p1"> <label class="container"><span class="text">Alive </span><input type="radio" name="sibling'+j+'_still_alive" value="alive" checked> <span class="checkmark_radio"></span> </label> <label class="container"><span class="text">Deceased </span><input type="radio" name="sibling'+j+'_still_alive" value="deceased"> <span class="checkmark_radio"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <h3>How old was he/she when he/she died?</h3> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="old_sibling'+j+'_died" placeholder=""></p> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <h3>How old is he/she now?</h3> </div> <div class="col-lg-6" id="p1"> <p><input type="textbox" name="old_sibling'+j+'_now" placeholder=""></p> </div> </div>';
                j++;
            }
            //alert(html);
            $('.siblings_div').html(html);
            
            
        });
        $("input[type='radio']").change(function(){
            if ($(this).parents('.label-container').length > 0 ) {
                $(this).parents('.label-container').find(".container").each(function(){
                    $(this).removeClass('selected_radio');
                });
            }else{
                $(this).parents('.tab').find(".container").each(function(){
                    $(this).removeClass('selected_radio');
                });
            }
            $(this).parents('label.container').addClass('selected_radio');
            
        });
        $("input[type='checkbox']").change(function(){
            var dont_know = 'false';
            $(this).parents('.tab').find('.not_include').each(function(){
                if($(this).is(':checked')){
                    dont_know = 'true';
                    //break;
                }
            });
            if(!$(this).hasClass('not_include') && dont_know == 'true'){
                $(this).parents('.tab').find('.not_include').removeClass('selected_radio');
                $(this).parents('.tab').find('.not_include').prop('checked',false);
                $(this).parents('.tab').find('.not_include').parents('label.container').removeClass('selected_radio');
                //return false;
            }
            if($(this).is(':checked')){
                $(this).parents('label.container').addClass('selected_radio');
            }else{
                $(this).parents('label.container').removeClass('selected_radio');
            }
        });
        $('#nextBtn').hover(function(){
            //$('button#nextBtn:after').css('right','-1.7em !important');
        });
        
        $('.div-sections-navbar a').click(function(){
            $('.div-sections-navbar a').removeClass('active'); 
            var id = $(this).attr('id');
            var section_name = $('#section_name').html();
            var current_section = section_name.replace('Section ', '');
            current_section = parseInt(current_section);
            var current_id = id.replace('#section', '');
            current_id = parseInt(current_id);
            if(current_id > current_section){
                showAlert('All questions are required for thorough and complete diagnosis. Please complete each question');
                return false;
            }
            $(this).addClass('active');
            if(currentTab < 4){
                return false;
            }
            
            
            $('form#regForm').attr('style','background-color : #ffffff !important;color:black;');
            // if(id == '#section2'){
            //     $('#regForm .tab').hide();
            //     $('#regForm .demographics2').show();
            //     currentTab = $('#regForm .demographics2').index();
            // }else 
            if(id == '#section1'){
                $('#regForm .tab').hide();
                $('#regForm .personal_cancer_history_1').show();
                currentTab = $('#regForm .personal_cancer_history_1').index();
            }else if(id == '#section2'){
                $('#regForm .tab').hide();
                $('#regForm .past_medical_history_1').show();
                currentTab = $('#regForm .past_medical_history_1').index();
            }else if(id == '#section3'){
                $('#regForm .tab').hide();
                $('#regForm .genetic_testing_history_1').show();
                currentTab = $('#regForm .genetic_testing_history_1').index();
            }else if(id == '#section4'){
                $('#regForm .tab').hide();
                $('#regForm .past_surgrical_history_1').show();
                currentTab = $('#regForm .past_surgrical_history_1').index();
            }else if(id == '#section5'){
                $('#regForm .tab').hide();
                $('#regForm .medications_and_supplements1').show();
                currentTab = $('#regForm .medications_and_supplements1').index();
            }else if(id == '#section6'){
                $('#regForm .tab').hide();
                $('#regForm .allergies1').show();
                currentTab = $('#regForm .allergies1').index();
            }else if(id == '#section7'){
                $('#regForm .tab').hide();
                $('#regForm .reproductive_history1').show();
                currentTab = $('#regForm .reproductive_history1').index();
            }else if(id == '#section8'){
                $('#regForm .tab').hide();
                $('#regForm .structure1').show();
                currentTab = $('#regForm .structure1').index();
            }else if(id == '#section9'){
                $('#regForm .tab').hide();
                $('#regForm .people_with_cancer1').show();
                currentTab = $('#regForm .people_with_cancer1').index();
            }else if(id == '#section10'){
                $('#regForm .tab').hide();
                $('#regForm .people_with_pre_cancer1').show();
                currentTab = $('#regForm .people_with_pre_cancer1').index();
            }else if(id == '#section11'){
                $('#regForm .tab').hide();
                $('#regForm .social_support1').show();
                currentTab = $('#regForm .social_support1').index();
            }else if(id == '#section12'){
                $('#regForm .tab').hide();
                $('#regForm .socioeconomics1').show();
                currentTab = $('#regForm .socioeconomics1').index();
            }else if(id == '#section13'){
                $('#regForm .tab').hide();
                $('#regForm .perceived_risk').show();
                currentTab = $('#regForm .perceived_risk').index();
            }
            
            setTimeout(function(){
                var section_name = $('#section_name').html().toLowerCase().trim();
                var h4 = 0;
                $('#regForm .introduction').each(function(){
                    if($(this).find('h3').html().toLowerCase().trim() == section_name){
                        h4 = $(this).find('h4').html();
                        h4 = h4.replace(' Questions','');
                        h4 = parseInt(h4);
                        var html = '';
                        var z;
                        for(z=0;z<=h4;z++){
                            if(z == 1){
                                html += '<span class="step active"></span>';
                            }else{
                                html += '<span class="step"></span>';
                            }
                        }
                        $('#all-steps').html(html);
                    }
                });
            }, 300);
            
        });
        
        $('#show_sections').change(function(){
           var value = $(this).val();
           $('.div-sections-navbar a').removeClass('active'); 
           if(value == 'show_sections'){
               $('#section_name').html();
               $('.div-sections-navbar a').each(function(){
                   if($(this).html() == $('#section_name').html()){
                       $(this).addClass('active');
                   }
               });
               $('.div-sections-navbar').show();
           }else{
               $('.div-sections-navbar').hide();
           }
        });
        
        $('.other_p').click(function(){
           $(this).closest('div').find('input[type=checkbox]').prop('checked', true);
           $(this).siblings('label').addClass('selected_radio');
           $(this).find('input[type=textbox]').css('background','#d7ebff');
        });

        $('.not_include').click(function(){
            if($(this).is(":checked")){
                $(this).parents('.tab').find('input[type=checkbox]').each(function(){
                    if($(this).is(":checked")){
                        $(this).trigger('click');
                    }
                });
            }
            //$(this).parents('.tab').find('input[type=checkbox]').attr("disabled", true);
            //$(this).parents('.tab').find('input[type=textbox]').val('');
        });
    });
	
	var multipleCancelButton2;
	$(document).ready(function(){
        //$('#myModal').modal({backdrop: 'static', keyboard: false}, 'show');
        //$('#myModal').modal('hide');

        $('#myModal #nextBtn').click(function(){
            $('#myModal').modal('hide');
            $('#regForm').show();
            $('.div_steps').show();
            //return false;
        });
        $('#myModal #prevBtn').click(function(){
            //nextPrev(-1,'next');
            $('#myModal').modal('hide');
            $('#regForm').show();
            $('.div_steps').show();
            $('#regForm #prevBtn').trigger('click');
        });

		var multipleCancelButton1 = new Choices('.choices-multiple-remove-button', {
        removeItemButton: true,
        maxItemCount:50,
        searchResultLimit:50,
        renderChoiceLimit:50
      }); 
      
       multipleCancelButton2 = new Choices('.choices-multiple-remove-button1', {
            removeItemButton: true,
            maxItemCount:50,
            searchResultLimit:50,
            renderChoiceLimit:50,
            duplicateItemsAllowed:false,
            maxItemText: (maxItemCount) => {
              return `Only ${maxItemCount} values can be added`;
            }
        }); 
        
		//$("button#nextBtn").after('<i class="fas fa-arrow-right"></i>');
		
		//$("#welcome button#nextBtn").text("start");
		$('body').on('click', '.choices__item--selectable', function() {
	        alert('hii'); 
	    });
	});
	
	

</script>

<body class="p-0 m-0">
    <script>

        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4-alternate.js.php'); ?>
        $LAB.script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsitedocuments.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait().script(
            "<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app/onsiteportalactivities.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").
        wait(function () {
            page.init();
            pageAudit.init();
            if (isPortal) {
                $('#Help').on('click', function (e) {
                    e.preventDefault();
                    $(".helpHide").addClass("d-none");
                });
                $("#Help").click();
                $(".helpHide").addClass("d-none");

                $('#showNav').on('click', () => {
                    parent.document.getElementById('topNav').classList.toggle('collapse');
                });
            }
            console.log('init done template');

            setTimeout(function () {
                if (!page.isInitialized) {
                    page.init();
                    if (!pageAudit.isInitialized) {
                        pageAudit.init();
                    }
                }
            }, 2000);
        });

        function printaDoc(divName) {
            flattenDocument();
            divName = 'templatediv';
            let printContents = document.getElementById(divName).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function templateText(el) {
            $(el).data('textvalue', $(el).val());
            $(el).attr("data-textvalue", $(el).val())
            return false;
        }

        function templateCheckMark(el) {
            if ($(el).data('value') === 'Yes') {
                $(el).data('value', 'No');
                $(el).attr('data-value', 'No');
            } else {
                $(el).data('value', 'Yes');
                $(el).attr('data-value', 'Yes');
            }
            return false;
        }

        function templateRadio(el) {
            var rid = $(el).data('id')
            $('#rgrp' + rid).data('value', $(el).val());
            $('#rgrp' + rid).attr('data-value', $(el).val());
            $(el).prop('checked', true)
            return false;
        }

        function tfTemplateRadio(el) {
            var rid = $(el).data('id')
            $('#tfrgrp' + rid).data('value', $(el).val());
            $('#tfrgrp' + rid).attr('data-value', $(el).val());
            $(el).prop('checked', true);
            return false;
        }

        function replaceTextInputs() {
            $('.templateInput').each(function () {
                var rv = $(this).data('textvalue');
                $(this).replaceWith(rv);
            });
        }

        function replaceRadioValues() {
            $('.ynuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var rv = $('input:radio[name="ynradio' + gid + '"]:checked').val();
                $(this).replaceWith(rv);
            });

            $('.tfuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var rv = $('input:radio[name="tfradio' + gid + '"]:checked').val();
                $(this).replaceWith(rv);
            });
        }

        function replaceCheckMarks() {
            $('.checkMark').each(function () {
                var ckid = $(this).data('id');
                var v = $('#' + ckid).data('value');
                if (v === 'Yes')
                    $(this).replaceWith('[\u2713]')
                else {
                    $(this).replaceWith("[ ]")
                }
            });
        }

        function restoreTextInputs() {
            $('.templateInput').each(function () {
                var rv = $(this).data('textvalue');
                $(this).val(rv)
            });
        }

        function restoreRadioValues() {
            $('.ynuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var value = $(this).data('value');
                $("input[name=ynradio" + gid + "][value='" + value + "']").prop('checked', true);
            });

            $('.tfuGroup').each(function () {
                var gid = $(this).data('id');
                var grpid = $(this).prop('id');
                var value = $(this).data('value');
                $("input[name=tfradio" + gid + "][value='" + value + "']").prop('checked', true);
            });
        }

        function restoreCheckMarks() {
            $('.checkMark').each(function () {
                var ckid = $(this).data('id');
                if ($('#' + ckid).data('value') === 'Yes')
                    $('#' + ckid).prop('checked', true);
                else
                    $('#' + ckid).prop('checked', false);
            });
        }

        function replaceSignatures() {
            $('.signature').each(function () {
                let type = $(this).data('type');
                if ($(this).attr('src') !== signhere && $(this).attr('src')) {
                    $(this).removeAttr('data-action');
                }
                if (!isPortal) {
                    $(this).attr('data-user', cuser);
                }
            });
        }

        function flattenDocument() {
            replaceCheckMarks();
            replaceRadioValues();
            replaceTextInputs();
            replaceSignatures();
        }

        function restoreDocumentEdits() {
            restoreCheckMarks();
            restoreRadioValues();
            restoreTextInputs();
        }
    </script>

    <script>
        //your javascript goes here
        var currentTab = 0;
        document.addEventListener("DOMContentLoaded", function(event) {
            showTab(currentTab);
        });

        function equalHeight(class_name){
            return;
            var arr = [];
            $('.'+class_name+' label.container').each(function(){
                arr.push($(this).find('.text').text().length);
            });
            var h = Math.max.apply(Math, arr);
            h = parseInt(h);
            if(class_name == 'personal_cancer_history_3'){
                h = h+14;
            }else if(class_name == 'past_medical_history_1' || class_name == 'past_medical_history_1' || class_name == 'past_medical_history_2' || class_name == 'past_medical_history_3' || class_name == 'past_medical_history_5' || class_name == 'past_medical_history_7' || class_name == 'past_medical_history_9' || class_name == 'past_medical_history_12' || class_name == 'past_medical_history_13' || class_name == 'past_surgrical_history_8'){
                h = h+20;
            }else if(class_name == 'past_medical_history_4' || class_name == 'past_surgrical_history_9' || class_name == 'people_with_cancer1' || class_name == 'people_with_cancer2' || class_name == 'people_with_cancer3'){
                h = h+40;
            }
            else if(class_name == 'people_with_pre_cancer1' || class_name == 'people_with_pre_cancer2'){
               h = h+50; 
            }
            $('.'+class_name+' label.container').each(function(){
                $(this).css('height',h);
            });
        }
        
        function test(){
            var j = 1000;
            for ( var i= 1000; i>0; i-- ) {
                (function(i){
                    setTimeout(function(){
                        //console.log(j);
                        $('#regForm').css('left',j);
                        j--;
                    }, 0.3*i);
                 })(i);
            }
        }

        function showTab(n) {
            test();
            if(n >= 2){
                $('.jfProgress-infoContent').show();
                $('.jfProgress-infoContent #cardProgress-currentIndex').html(n-1+' ');
            }else{
                $('.jfProgress-infoContent').hide();
            }
            
            var currentTabClass = '';
            var i = n+1;
                
            
            currentTabClass = $("#regForm .tab:nth-child("+i+")").attr('class');
            if(currentTabClass.indexOf('introduction') != -1){
                $('form#regForm').attr('style','background-color : #024747 !important;color:#fff;');
            }else{
                $('form#regForm').attr('style','background-color : #ffffff !important;color:black;');
            }
            
            //Step
            
            
            // if(currentTabClass.indexOf('demographics') != -1){
            //     $('#section_name').html('Section 1');
            // }else 
            if(currentTabClass.indexOf('personal_cancer_history') != -1){
                $('#section_name').html('Section 3');
            }else if(currentTabClass.indexOf('past_medical_history') != -1){
                $('#section_name').html('Section 4');
            }else if(currentTabClass.indexOf('genetic_testing_history') != -1){
                $('#section_name').html('Section 5');
            }else if(currentTabClass.indexOf('past_surgrical_history') != -1){
                $('#section_name').html('Section 6');
            }else if(currentTabClass.indexOf('medications_and_supplements') != -1){
                $('#section_name').html('Section 7');
            }else if(currentTabClass.indexOf('allergies') != -1){
                $('#section_name').html('Section 8');
            }else if(currentTabClass.indexOf('reproductive_history') != -1){
                $('#section_name').html('Section 9');
            }else if(currentTabClass.indexOf('structure') != -1){
                $('#section_name').html('Section 10');
            }else if(currentTabClass.indexOf('people_with_cancer') != -1){
                $('#section_name').html('Section 11');
            }else if(currentTabClass.indexOf('people_with_pre_cancer') != -1){
                $('#section_name').html('Section 12');
            }else if(currentTabClass.indexOf('social_support') != -1){
                $('#section_name').html('Section 13');
            }else if(currentTabClass.indexOf('socioeconomics') != -1){
                $('#section_name').html('Section 14');
            }else if(currentTabClass.indexOf('perceived_risk') != -1){
                $('#section_name').html('Section 15');
            }
            else if($("#regForm .tab:nth-child("+i+")").hasClass('welcome') || $("#regForm .tab:nth-child("+i+")").hasClass('disclamer1')){
                $('#all-steps').html('');
            }
            else if($("#regForm .tab:nth-child("+i+")").hasClass('introduction')){
                var a = $("#regForm .tab:nth-child("+i+")").find('h3').html();
                $('#section_name').html(a);
                
                var h4 = $("#regForm .tab:nth-child("+i+")").find('h4').html();
                h4 = h4.replace(' Questions','');
                h4 = parseInt(h4);
                
                var html = '';
                var z;
                for(z=0;z<=h4;z++){
                    if(z == 0){
                        html += '<span class="step active"></span>';
                    }else{
                        html += '<span class="step"></span>';
                    }
                }
                $('#all-steps').html(html);
            }else{
                $('#section_name').html('');
            }
            
            if(currentTabClass.indexOf('personal_cancer_history_3') != -1){
                var class_name = 'personal_cancer_history_3';
                equalHeight(class_name);
            }

            if(currentTabClass.indexOf('past_medical_history_1') != -1){
                var class_name = 'past_medical_history_1';
                equalHeight(class_name);
            }

            if(currentTabClass.indexOf('past_medical_history_2') != -1){
                var class_name = 'past_medical_history_2';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_medical_history_3') != -1){
                var class_name = 'past_medical_history_3';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_medical_history_4') != -1){
                var class_name = 'past_medical_history_4';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_medical_history_5') != -1){
                var class_name = 'past_medical_history_5';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_medical_history_7') != -1){
                var class_name = 'past_medical_history_7';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_medical_history_9') != -1){
                var class_name = 'past_medical_history_9';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_medical_history_12') != -1){
                var class_name = 'past_medical_history_12';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_medical_history_13') != -1){
                var class_name = 'past_medical_history_13';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_surgrical_history_8') != -1){
                var class_name = 'past_surgrical_history_8';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('past_surgrical_history_9') != -1){
                var class_name = 'past_surgrical_history_9';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('people_with_cancer1') != -1){
                var class_name = 'people_with_cancer1';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('people_with_cancer2') != -1){
                var class_name = 'people_with_cancer2';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('people_with_cancer3') != -1){
                var class_name = 'people_with_cancer3';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('people_with_pre_cancer1') != -1){
                var class_name = 'people_with_pre_cancer1';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('people_with_pre_cancer2') != -1){
                var class_name = 'people_with_pre_cancer2';
                equalHeight(class_name);
            }

            if(currentTabClass.indexOf('past_medical_history_10') != -1){
                var str = [];
                var classes = ['past_medical_history_1','past_medical_history_2','past_medical_history_3','past_medical_history_4','past_medical_history_lifetime','past_medical_history_5','past_medical_history_6','past_medical_history_7','past_medical_history_8','past_medical_history_adrenal_conditions','past_medical_history_9'];  
                //console.log(classes);
                $.each(classes, function(index, value) {
                    $('#regForm .'+value+' input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .'+value+' input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str.push($(this).val());
                            }
                        }
                    });        
                });
                var required_analysis = $("input[name='required_analysis']:checked").parents('label').find('.text').html();
                required_analysis = required_analysis.replace(/ /g, '').trim();
                if($("input[name='urinary_chronic_renal_insufficiency']").is(":checked")){
                    if(required_analysis == 'Yes'){
                        str.push('Require dialysis');
                    }
                }

                if($("input[name='pylori_infection']").is(":checked")){
                    var treated_pylori = $("input[name='treated_pylori']:checked").parents('label').find('.text').html();
                    treated_pylori = treated_pylori.replace(/ /g, '').trim();
                    if(treated_pylori == 'yes'){
                        if(required_analysis == 'yes'){
                            str.push('H. Pylori infection');
                        }
                    }
                }

                

                
                var html = '';
                $.each(str, function(index, value) {
                    if(value != ''){
                        html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                    }
                });
                
                if($("input[name='neurological_headache']").is(":checked")){
                    var str1 = [];
                    $('#regForm .past_medical_history_headache input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str1.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_headache input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str1.push($(this).val());
                            }
                        }
                    }); 
                    html += '<b>Headache:</b><br>';
                    $.each(str1, function(index, value) {
                        if(value != ''){
                        html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                        }
                    });
                }
                if($("input[name='pulmonary_chronic']").is(":checked")){
                    var str1 = [];
                    $('#regForm .past_medical_history_pulmonary input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str1.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_pulmonary input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str1.push($(this).val());
                            }
                        }
                    }); 
                    html += '<b>COPD:</b><br>';
                    $.each(str1, function(index, value) {
                        if(value != ''){
                        html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                        }
                    });
                }
                
                if($("input[name='endocrine_thyroid_disease']").is(":checked")){
                    var str1 = [];
                    $('#regForm .past_medical_history_thyroid_disease input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str1.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_thyroid_disease input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str1.push($(this).val());
                            }
                        }
                    }); 
                    html += '<b>Thyroid disease:</b><br>';
                    $.each(str1, function(index, value) {
                        if(value != ''){
                        html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                        }
                    });
                }

                if($("input[name='colon_polyp']").is(":checked")){
                    var str1 = [];
                    $('#regForm .past_medical_history_polyps input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str1.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_polyps input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str1.push($(this).val());
                            }
                        }
                    }); 
                    html += '<b>Polyps:</b><br>';
                    $.each(str1, function(index, value) {
                        if(value != ''){
                            html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                        }
                    });
                }
                
                if($("input[name='inflammatory_bowel_disease']").is(":checked")){
                    var str1 = [];
                    $('#regForm .past_medical_history_ibd_inflammatory input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str1.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_ibd_inflammatory input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str1.push($(this).val());
                            }
                        }
                    }); 
                    html += '<b>IBD:</b><br>';
                    $.each(str1, function(index, value) {
                        if(value != ''){
                            html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                        }
                    });
                }

                if($("input[name='viral_hepatitis']").is(":checked")){
                    var str2 = [];
                    $('#regForm .past_medical_history_hepatitis input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str2.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_hepatitis input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str2.push($(this).val());
                            }
                        }
                    }); 
                    html += '<b>Viral Hepatis:</b>';
                    $.each(str2, function(index, value) {
                        if(value != ''){
                            html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                        }
                    });
                }

                if($("input[name='pancreatitis']").is(":checked")){
                    var str3 = [];
                    $('#regForm .past_medical_history_pancreatitis input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str3.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_pancreatitis input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str3.push($(this).val());
                            }
                        }
                    }); 
                    html += '<b>Pancreatitis:</b>';
                    $.each(str3, function(index, value) {
                        if(value != ''){
                            html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]"> </div> <div class="col-lg-3" id="p1"> <input type="textbox" class="numeric" placeholder="eg 23" name="finished_treatment[]"> </div> </div><br>';
                        }
                    });
                }

                $('#regForm .past_medical_history_10 .content_div').html(html);
                console.log(str);

            }
            
            if(currentTabClass.indexOf('past_medical_history_aggregate') != -1){
                var str = [];
                $('#regForm .past_medical_history_12 input[type=checkbox]').each(function(){
                    if($(this).is(":checked")){
                        if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                            
                        }else{
                            if($(this).siblings('.text').html().trim() != ''){
                                str.push($(this).siblings('.text').html().trim());
                            }
                        }
                    }
                });
                
                $('#regForm .past_medical_history_12 input[type=textbox]').each(function(){
                    if(!$(this).hasClass('not_include')){
                        if($(this).val != ''){
                            str.push($(this).val());
                        }
                    }
                });
                
                
                if($("input[name='abnormal_test']").is(":checked")){ 
                    $('#regForm .past_medical_history_what_abnormal input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    
                    $('#regForm .past_medical_history_what_abnormal input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str.push($(this).val());
                            }
                        }
                    });
                }
                
                var html = '';
                $.each(str, function(index, value) {
                    if(value != ''){
                        html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-6" id="p1"> <input type="date" data-date="" data-date-format="MMMM YYYY" class="" placeholder="MM/Year" name="abnormal_mm_year[]"> </div> </div><br>';
                    }
                });
                $('#regForm .past_medical_history_aggregate .content_div').html(html);
                
            }
            if(currentTabClass.indexOf('reasons_for_surgeries') != -1){
                var str = [];
                var classes = ['type_of_surgeries_container','type_of_urologic_surgeries','type_of_gynelogic_surgeries','type_of_breast_surgeries','type_of_cardiothoracic_surgeries','type_of_neurologic_surgeries','type_of_eye_surgeries','type_of_ent_surgeries','type_of_orthopedic_surgeries','type_of_endocrine_surgeries'];  
                //console.log(classes);
                $.each(classes, function(index, value) {
                    $('#regForm .'+value+' input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .'+value+' input[type=textbox]').each(function(){
                        if(!$(this).hasClass('not_include')){
                            if($(this).val != ''){
                                str.push($(this).val());
                            }
                        }
                    });        
                });
                var html = '';
                $.each(str, function(index, value) {
                    if(value != ''){
                        html += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="textbox" placeholder="" name="reason_surgery[]"> </div> <div class="col-lg-3" id="p1"> <input type="date" data-date="" data-date-format="MMMM YYYY" class="" placeholder="MM/Year" name="mm_year[]"> </div> </div><br>';
                    }
                });
                $('#regForm .reasons_for_surgeries .content_div').html(html);
                
                var html2 = '';
                $.each(str, function(index, value) {
                    if(value != ''){
                        html2 += '<div class="row"> <div class="col-lg-6" id="p1"> <span>'+value+'</span> </div> <div class="col-lg-3" id="p1"> <input type="date" data-date="" data-date-format="MMMM YYYY" class="" placeholder="Year" name="surgery_year[]"> </div> </div><br>';
                    }
                });
                $('#regForm .past_surgrical_history_10 .content_div').html(html2);
                
            }
            var x = document.getElementsByClassName("tab");
            x[n].style.display = "block";
            // $(x[n]).animate({
            //     width: "toggle"
            // }, 1000);
            
            //alert(n);
            if (n == 0) {
                $('.footer-buttons #prevBtn').hide();
                //document.getElementById("prevBtn").style.display = "none";
            } else {
                //console.log(n);
                $('.footer-buttons #prevBtn').show();
                document.getElementById("prevBtn").style.display = "inline";
                $('#nextBtn').css({'padding-right':'43px','margin-left':'0px'});
            }
            if (n == (x.length - 1)) {
                document.getElementById("nextBtn").innerHTML = "Submit";
            } else {
                document.getElementById("nextBtn").innerHTML = "Next";
            }
            if (n == 0) {
                document.getElementById("nextBtn").innerHTML = "START";
                $('#nextBtn').css({'padding-right':'0px','margin-left':'30px'});
                $('form#regForm').attr('style','background-color : #024747 !important;color:#fff;');
            }
            if(n == 1){
                $('form#regForm').attr('style','background-color : #024747 !important;color:#fff;');
            }
            
        }

        $(document).on("input", ".numeric", function() {
            this.value = this.value.replace(/\D/g,'');
        });

        

        var skip = 0;
        function nextPrev(n,next='') {
            //alert(skip);
            //$(".form-error-message").hide('');
            //$('#regForm .tab').hide();
            fixStepIndicator(n);
            var i = 1;
            $('#register').html('');
            var currentStep = 1;
            var getCurrentStep = getCurrentStep();
            //alert(getCurrentStep);
            
            setTimeout(function() {
                var value = $('#show_sections :selected').val();
                $('.div-sections-navbar a').removeClass('active'); 
                $('.div-sections-navbar a').each(function(){
                   if($(this).html() == $('#section_name').html()){
                       $(this).addClass('active');
                   }
                });
            
                $('.all-steps .step').each(function(){
                    //console.log(i);
                    if($(this).hasClass('active')){
                        currentStep = i;
                        nextStep = currentStep;
                        var html = '';
                        
                        $('#register').html(html);
                    }
                    i++;
                });

            }, 50);
            var x = document.getElementsByClassName("tab");
            
            //currentStep = currentStep - 2;
            var url = '<?=$actual_link.$webroot.'/portal/save_questionnaire.php';?>';
            var pid = '<?=$pid;?>';
            var patient_gender = '<?=$patientData['sex'];?>';
            patient_gender = patient_gender.toLowerCase();
            var currentTabClass = '';
            $('#regForm .tab').each(function(){
                if($(this).css('display') == 'block'){
                    currentTabClass = ($(this).attr('class'));
                }
            });
            //if (n == 1 && !validateForm()) return false;
            if(n == -1 && next == ''){
            //if(0 == 1){
                console.log('currentStep-',currentStep);
                
                if(currentTabClass.indexOf('personal_cancer_history_1') != -1){
                    return false;
                }
                
                if(currentTabClass.indexOf('introduction') != -1){
                    
                    setTimeout(function(){
                        var section_name = $('#section_name').html().toLowerCase().trim();
                        var h4 = 0;
                        $('#regForm .introduction').each(function(){
                            if($(this).find('h3').html().toLowerCase().trim() == section_name){
                                h4 = $(this).find('h4').html();
                                h4 = h4.replace(' Questions','');
                                h4 = parseInt(h4);
                                var html = '';
                                var z;
                                for(z=0;z<=h4;z++){
                                    if(z == h4){
                                        html += '<span class="step active"></span>';
                                    }else{
                                        html += '<span class="step"></span>';
                                    }
                                }
                                $('#all-steps').html(html);
                            }
                        });
                    }, 300);
                    
                }
                
                if(currentTabClass.indexOf('introduction_pmh') != -1){
                    var cancer = $("input[name='cancer']:checked").val();
                    if(cancer == 'no'){
                        nextPrev(-7);
                        return false; 
                    }
                }
                if(currentTabClass.indexOf('past_medical_history_3') != -1){
                    if(!$("input[name='neurological_headache']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_4') != -1){
                    if(!$("input[name='pulmonary_chronic']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_5') != -1){
                    if($("input[name='colon_polyp']").is(":checked")){
                        // nextPrev(-7);
                        nextPrev(-1,'next');
                        return false;
                    }else if($("input[name='pylori_infection']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }else if($("input[name='pancreatitis']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }else if($("input[name='viral_hepatitis']").is(":checked")){
                        nextPrev(-5);
                        return false;
                    }else if($("input[name='inflammatory_bowel_disease']").is(":checked")){
                        nextPrev(-6);
                        return false;
                    }else{
                        nextPrev(-7);
                        return false;
                    }
                }
                if(currentTabClass.indexOf('past_medical_history_aggregate') != -1){
                    
                    if($("input[name='abnormal_test']").is(":checked")){ 
                        
                    }else{
                        if($("input[name='abnormal_mammogram']").is(":checked")){
                            nextPrev(-1,'next');
                        }else{
                            nextPrev(-2);
                        }
                    } 
                }
                
                if(currentTabClass.indexOf('past_medical_history_what_abnormal') != -1){
                    if(!$("input[name='abnormal_mammogram']").is(":checked")){
                        nextPrev(-1,'next');
                    }
                }
                
                if(currentTabClass.indexOf('past_surgical_history_intro') != -1){
                    var genetic_testing = $("input[name='genetic_testing']:checked").parents('label').find('.text').html();
                    genetic_testing = genetic_testing.replace(/ /g, '').trim();
                    if(genetic_testing == 'No'){
                        nextPrev(-5,'next');
                        return false;
                    }
                }
                

                if(currentTabClass.indexOf('past_medical_history_lifetime') != -1){
                    if($("input[name='pylori_infection']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }else if($("input[name='pancreatitis']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }else if($("input[name='viral_hepatitis']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }else if($("input[name='inflammatory_bowel_disease']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }else{
                        nextPrev(-5);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_pylori') != -1){
                    if($("input[name='pancreatitis']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }else if($("input[name='viral_hepatitis']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }else if($("input[name='inflammatory_bowel_disease']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }else{
                        nextPrev(-4);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_pancreatitis') != -1){
                    if($("input[name='viral_hepatitis']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }else if($("input[name='inflammatory_bowel_disease']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }else{
                        nextPrev(-3);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_hepatitis') != -1){
                    if($("input[name='inflammatory_bowel_disease']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }else{
                        nextPrev(-2);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('personal_cancer_history_1') != -1){
                    var cancer = $("input[name='cancer']:checked").val();
                    if(cancer == 'no'){
                        nextPrev(-5);
                    }
                }
                if(currentTabClass.indexOf('personal_cancer_history_6') != -1){
                    var selectedValues = $('#cancer_multiple_choices').val();
                    if(!selectedValues.includes('breast')){
                        nextPrev(-2);
                    }
                }

                if(currentTabClass.indexOf('genetic_testing_history_4') != -1){
                    if(!$("input[name='deleterious']").is(":checked")){
                        nextPrev(-1,'next');
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_6') != -1){
                        if(!$("input[name='urinary_chronic_renal_insufficiency']").is(":checked")){
                            nextPrev(-1,'next');
                        }
                }

                if(currentTabClass.indexOf('past_medical_history_10') != -1){
                    if($("input[name='adrenal_problems']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }else if($("input[name='endocrine_thyroid_disease']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }else{
                        nextPrev(-3);
                        return false;
                    }
                }
                
                // if(currentTabClass.indexOf('past_medical_history_10') != -1){
                //     if($("input[name='endocrine_thyroid_disease']").is(":checked")){
                //         nextPrev(-1,'next');
                //         return false;
                //     }else{
                //         nextPrev(-2);
                //         return false;
                //     }
                    
                // }
                
                if(currentTabClass.indexOf('past_medical_history_adrenal_conditions') != -1){
                    if($("input[name='endocrine_thyroid_disease']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }else{
                        nextPrev(-2);
                        return false;
                    }
                }
                if(currentTabClass.indexOf('reasons_for_surgeries') != -1){
                    if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(-5);
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(-6);
                        return false;
                    }
                    else if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(-7);
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-9);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-10);
                        return false;
                    }
                    
                }
                
                
                if(currentTabClass.indexOf('type_of_endocrine_surgeries') != -1){
                    if($("input[name='type_of_orthopedic_surgeries']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(-5);
                        return false;
                    }
                    else if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(-6);
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-8);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-9);
                        return false;
                    }else{
                        nextPrev(-10);
                        return false;  
                    }
                }
                
                if(currentTabClass.indexOf('type_of_orthopedic_surgeries') != -1){
                    if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }
                    else if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(-5);
                        return false;
                    }
                    else if($("input[name='type_of_gynelogic_surgeries']").is(":checked")){
                        nextPrev(-6);
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-7);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-8);
                        return false;
                    }
                    else{
                        nextPrev(-9);
                        return false; 
                    }
                }
                
                if(currentTabClass.indexOf('type_of_ent_surgeries') != -1){
                    if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }
                    else if($("input[name='type_of_gynelogic_surgeries']").is(":checked")){
                        nextPrev(-5);
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-6);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-7);
                        return false;
                    }
                    else{
                        nextPrev(-8);
                        return false; 
                    }
                }
                if(currentTabClass.indexOf('type_of_eye_surgeries') != -1){
                    if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else if($("input[name='type_of_gynelogic_surgeries']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-5);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-6);
                        return false;
                    }else{
                        nextPrev(-7);
                        return false; 
                    }
                }
                if(currentTabClass.indexOf('type_of_neurologic_surgeries') != -1){
                    if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='type_of_gynelogic_surgeries']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-5);
                        return false;
                    }
                    else{
                        nextPrev(-6);
                        return false; 
                    }
                }
                if(currentTabClass.indexOf('type_of_cardiothoracic_surgeries') != -1){
                    if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='type_of_gynelogic_surgeries']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-4);
                        return false;
                    }
                    else{
                        nextPrev(-5);
                        return false; 
                    }
                }
                if(currentTabClass.indexOf('type_of_breast_surgeries') != -1){
                    if(patient_gender == 'female'){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-3);
                        return false;
                    }
                    else{
                        nextPrev(-4);
                        return false; 
                    }
                }
                if(currentTabClass.indexOf('type_of_gynelogic_surgeries') != -1){
                    if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-2);
                        return false;
                    }
                    else{
                        nextPrev(-3);
                        return false; 
                    }
                }
                if(currentTabClass.indexOf('type_of_urologic_surgeries') != -1){
                    if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(-1,'next');
                        return false;
                    }else{
                        nextPrev(-2);
                        return false; 
                    }
                }
                

                if(currentStep == 20){
                    if(patient_gender == 'female'){
                        nextPrev(-1,'next');
                    }
                }
            }
            console.log('currentStep',currentStep);
            if(currentStep == 2){
                // $('form#regForm').css('background-color','#ffffff!important');
                $('form#regForm').attr('style','background-color : #ffffff !important;color:black;');
            }
            if(n == 1 && next == ''){
            // if(0 == 1){
                

                if(!$('#terms_conditions').is(':checked')  && currentTabClass.indexOf('disclamer1') != -1){
                    showAlert('Agree with terms and conditions.');
                    //alert('Agree with terms and conditions.');
                    return false;
                }

                if(patient_gender != 'undefined' && currentTabClass.indexOf('demographics1') != -1){
                    var gender_flag = '';                    
                    var gender = patient_gender;
                    if(gender == 'male'){
                        gender_flag = 'M';
                        //return false;
                    }else{
                        gender_flag = 'F';
                    }
                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'gender_flag':gender_flag,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('custom_tab') != -1){
                    $('$regForm .custom_tab').remove();
                }

                var arr = currentTabClass.split(" ");
                var class_name = arr[arr.length-1];
                var blank = 'true';
                if(currentTabClass.indexOf('disclamer1') != -1 || currentTabClass.indexOf('introduction') != -1 || currentTabClass.indexOf('welcome') != -1){
                }else{
                    $('.'+class_name+" input[type=text]").each(function(){
                        if($(this).val() != ''){
                            blank = 'false';
                        }
                    });
                    $('.'+class_name+" input[type=textbox]").each(function(){
                        if($(this).val() != ''){
                            blank = 'false';
                        }
                    });
                    $('.'+class_name+" input[type=date]").each(function(){
                        if($(this).val() != ''){
                            blank = 'false';
                        }
                    });
                    $('.'+class_name+" input[type=checkbox]").each(function(){
                        if($(this).is(":checked")){
                            blank = 'false';
                        }
                    });
                    $('.'+class_name+" input[type=radio]").each(function(){
                        if($(this).is(":checked")){
                            blank = 'false';
                        }
                    });
                    $('.'+class_name+" select").each(function(){
                        if($(this).find(":selected").val() != ''){
                            blank = 'false';
                        }
                    });
                    if(blank == 'true'){ 
                        showAlert('Above feilds are required.');
                        return false;
                    }
                }

                if(currentTabClass.indexOf('demographics2') != -1){
                    var gender = patient_gender;
                    //nextPrev(55);
                    var pronous = $("input[name='pronous']:checked").val();
                    var flag_pronous = '';
                    if(gender == 'male' && pronous == 'her'){
                        flag_pronous = 'LGBTQ';
                    }
                    if(gender == 'female' && pronous == 'his'){
                         flag_pronous = 'LGBTQ';    
                    }
                    if(pronous == 'their'){
                         flag_pronous = 'LGBTQ';     
                    }

                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_pronous':flag_pronous,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('demographics3') != -1){
                    if(requiredTexts(currentStep)){
                        return false;
                    }
                    var birth_day = $("input[name='birth_day']").val()
                    var birth_month = $("input[name='birth_month']").val()
                    var birth_year = $("input[name='birth_year']").val()
                    var age = getAge(birth_day+'-'+birth_month+'-'+birth_year);
                    var flagbirth = '';

                    if(age < 13){
                        showAlert('Pediatric');
                        flagbirth = 'Pediatric';
                        //return false;
                    }else if(age >= 13 && age <= 17){
                        showAlert('Adolescent');
                        flagbirth = 'Adolescent';
                        //return false;
                    }else if(patient_gender == 'female' && age >= 25 && age <= 39){
                        showAlert('Eligible for Proactive');
                        flagbirth = 'Eligible for Proactive';
                        //return false;
                    }else if(patient_gender == 'male' && age >= 40 && age <= 49){
                        showAlert('Eligible for Proactive');
                        flagbirth = 'Eligible for Proactive';
                        //return false;
                    }else if(patient_gender == 'male' && age >= 40 && age <= 79){
                        showAlert('Eligible for screening');
                        flagbirth = 'Eligible for screening';
                        //return false;
                    }
                    else{
                        showAlert('Eligible for Proactive');
                        flagbirth = 'Eligible for Proactive';
                        //return false;
                    }

                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flagbirth':flagbirth,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('demographics5') != -1){
                    var url = '<?=$actual_link.$webroot.'/portal/save_questionnaire.php';?>';
                    var section = 'demographics';
                    var pid = '<?=$pid;?>';
                    var gender = patient_gender;
                    var pronous = $("input[name='pronous']:checked").parents('label').find('.text').html();
                    pronous = pronous.replace(/ /g, '');
                    var race = $("input[name='race']:checked").parents('label').find('.text').html();
                    race = race.replace(/ /g, '');
                    var hispanic = $("input[name='hispanic']:checked").parents('label').find('.text').html();
                    hispanic = hispanic.replace(/ /g, '');
                    $.ajax({
                        type:'POST',
                        data: {'gender':gender,'pronous':pronous,'race':race,'hispanic':hispanic,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('personal_cancer_history_1') != -1){
                    //nextPrev(40);
                    var cancer = $("input[name='cancer']:checked").val();
                    $.ajax({
                        type:'POST',
                        data: {'cancer':cancer,'section':'personal_cancer_history_1','pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    if(cancer == 'yes'){
                        var message = "Efax of pathology report to secure FTP prior to OCC appt (or email screenshot to efax, or bring print out to appt and WA/GC/RN will scan & efax)";
                        addTab(message);
                        //return false;
                    }else{
                        //showAlert('message: "You will be asked to submit a copy of your pathology report(s) prior to your consultation."');
                        var message = "You will be asked to submit a copy of your pathology report(s) prior to your consultation.";
                        addTab(message);
                        nextPrev(6);
                        return false;
                    }
                }
                if(currentTabClass.indexOf('personal_cancer_history_2') != -1){
                    var different_cancers = $("input[name='different_cancers']").val();
                    $.ajax({
                        type:'POST',
                        data: {'different_cancers':different_cancers,'section':'personal_cancer_history_2','pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    multipleCancelButton2.config.maxItemCount = different_cancers;
                    //var multipleCancelButton.destroy();
                    
                }
                if(currentTabClass.indexOf('personal_cancer_history_3') != -1){
                    var flagBreast = '';
                    var flagProstate = '';
                    var flagColorectal = '';
                    var flagLung = '';
                    var flagMelanoma = '';
                    var flagOvarian = '';
                    var flagUterine = '';
                    var flagPancreatic = '';
                    var flagStomach = '';
                    var flagEsophageal = '';
                    var flagSarcoma = '';
                    var flagH_N = '';
                    var flagBrain = '';
                    var flagAdrenal = '';
                    var flagKidney = '';
                    var flagThyroid = '';
                    if($("input[name='breast']").is(":checked")){
                        flagBreast = 'BC';
                    }else if($("input[name='prostate']").is(":checked")){
                        flagProstate = 'PC';
                    }else if($("input[name='colorectal']").is(":checked")){
                        flagColorectal = 'CRC';
                    }else if($("input[name='lung']").is(":checked")){
                        flagLung = 'Lung';
                    }else if($("input[name='melanoma']").is(":checked")){
                        flagMelanoma = 'Mel';
                    }else if($("input[name='ovarian']").is(":checked")){
                        flagOvarian = 'OvCa';
                    }else if($("input[name='uterine']").is(":checked")){
                        flagUterine = 'UtCa';
                    }else if($("input[name='pancreatic']").is(":checked")){
                        flagPancreatic = 'PanCa';
                    }else if($("input[name='stomach']").is(":checked")){
                        flagStomach = 'GastricCa';
                    }else if($("input[name='esophageal']").is(":checked")){
                        flagEsophageal = 'EsophCa';
                    }else if($("input[name='sarcoma']").is(":checked")){
                        flagSarcoma = 'Sarc';
                    }else if($("input[name='h_n']").is(":checked")){
                        flagH_N = 'H&N';
                    }else if($("input[name='bnrain']").is(":checked")){
                        flagBrain = 'CNS';
                    }else if($("input[name='adrenal']").is(":checked")){
                        flagAdrenal = 'ACC';
                    }else if($("input[name='kidney']").is(":checked")){
                        flagKidney = 'RCC';
                    }else if($("input[name='thyroid']").is(":checked")){
                        flagThyroid = 'ThyCa';
                    }

                    var flagBreast = '';
                    var flagProstate = '';
                    var flagColorectal = '';
                    var flagLung = '';
                    var flagUterine = '';
                    var flagPancreatic = '';
                    var flagStomach = '';
                    var flagEsophageal = '';
                    var flagSarcoma = '';
                    var flagH_N = '';
                    var flagBrain = '';
                    var flagAdrenal = '';
                    var flagKidney = '';
                    var flagThyroid = '';

                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flagBreast':flagBreast,'flagProstate':flagProstate,'flagColorectal':flagColorectal,'flagLung':flagLung,'flagUterine':flagUterine,'flagPancreatic':flagPancreatic,'flagStomach':flagStomach,'flagEsophageal':flagEsophageal,'flagSarcoma':flagSarcoma,'flagH_N':flagH_N,'flagBrain':flagBrain,'flagAdrenal':flagAdrenal,'flagKidney':flagKidney,'flagThyroid':flagThyroid,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    
                    
                    var selectedValues = $('#cancer_multiple_choices').val();
                    var selectedArr = $('#cancer_multiple_choices option:selected').toArray().map(item => item.text);
                    var selectedValuesArr = $('#cancer_multiple_choices option:selected').toArray().map(item => item.value);
                    var j=0;
                    $('.initially_diagnosed_tab .content_div .row').each(function(){
                         $(this).find('.col-lg-4').find('input[type=textbox]').val(selectedArr[j]);
                         j++;
                    });
                    
                    $.ajax({
                        type:'POST',
                        data: {'selectedArr':selectedValuesArr,'section':'personal_cancer_history_3','pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    var different_cancers = $("input[name='different_cancers']").val();
                    different_cancers = parseInt(different_cancers);
                    
                    var other_text = $('.personal_cancer_history_3 input[name=other_text]').val();
                    if(different_cancers > selectedValues.length){
                         $('.initially_diagnosed_tab .content_div .row:last-child').find('.col-lg-4').find('input[type=textbox]').val(other_text);
                    }else{
                        if(other_text != ''){
                            showAlert('Number of cancers exceed.');
                            return false;
                        }
                    }
                    
                    //if($.inArray("breast", selectedValues) !== -1)
                    if(!selectedValues.includes('breast')){
                        nextPrev(2);
                    }
                }
                
                
                if(currentTabClass.indexOf('personal_cancer_history_4') != -1){
                    var hr2_status = $("input[name='hr2_status']:checked").val();
                    $.ajax({
                        type:'POST',
                        data: {'hr2_status':hr2_status,'section':'personal_cancer_history_4','pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });  
                }
                if(currentTabClass.indexOf('personal_cancer_history_5') != -1){
                    var hr2_status = $("input[name='hr2_status']:checked").val();
                    var hr_status = $("input[name='hr_status']:checked").val();
                    var flag_hr_status = '';
                    if(hr2_status == 'Her2 negative' && hr_status == 'Hormone receptor negative'){
                        flag_hr_status = 'TNBC';
                    }
                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_hr_status':flag_hr_status,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    $.ajax({
                        type:'POST',
                        data: {'hr_status':hr_status,'section':'personal_cancer_history_5','pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                
                if(currentTabClass.indexOf('personal_cancer_history_6') != -1){
                    var cancer_type = $("input[name='pre_populate[]']").map(function(){return $(this).val();}).get();
                    var age_diagnosed = $("input[name='age_diagnosed[]']").map(function(){return $(this).val();}).get();
                    var finished_treatment=[]; 
                    $('select[name="finished_treatment[]"] option:selected').each(function() {
                      finished_treatment.push($(this).val());
                    });
                    
              
                    $.ajax({
                        type:'POST',
                        data: {'cancer_type':cancer_type,'age_diagnosed':age_diagnosed,'finished_treatment':finished_treatment,'section':'personal_cancer_history_6','pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    //return false;
                    
                    var section = 'save_flag_first';
                    
                    if($.inArray("ongoing", finished_treatment) !== -1 || $.inArray("no", finished_treatment) !== -1){
                        var flag1 = '';
                    }else{
                        var flag1 = 'cancer survivor';
                    }
                    if(flag1 == ''){
                        if($.inArray("no", finished_treatment) !== -1){
                            flag1 = 'cancer patient';
                        }
                    }
                    
                    $.ajax({
                        type:'POST',
                        data: {'EndCaRx':flag1,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });


                }
                
                
                if(currentTabClass.indexOf('personal_cancer_pre_cancerous') != -1){
                    if($("input[name='precancerous_none']").is(":checked")){
                        var message = "Efax of pathology report to secure FTP prior to OCC appt (or email screenshot to efax, or bring print out to appt and WA/GC/RN will scan & efax)";
                        addTab(message);
                    }
                    
                    
                }
                
                if(currentTabClass.indexOf('vascular_container') != -1){
                    var section = 'past_medical_history_pmh';
                    var HTN = '0';
                    if($("input[name='high_bp_pressure']").is(":checked")){
                        var HTN = "1";
                    }
                    var Dyslipidemia = '0';
                    if($("input[name='bad_cholesterol']").is(":checked")){
                        var Dyslipidemia = "1";
                    }
                    var MI = '0';
                    if($("input[name='heart_attack_vascular']").is(":checked")){
                        var MI = "1";
                    }
                    var ChestPain = '0';
                    if($("input[name='cardiac_chest']").is(":checked")){
                        var ChestPain = "1";
                    }
                    var CHF = '0';
                    if($("input[name='cardiomyopathy_vascular']").is(":checked")){
                        var CHF = "1";
                    }
                    var Arrhythmia = '0';
                    if($("input[name='cardiac_arrhythmia_vascular']").is(":checked")){
                        var Arrhythmia = "1";
                    }
                    var Claudication = '0';
                    if($("input[name='pain_calves']").is(":checked")){
                        var Claudication = "1";
                    }
                    var OthCVD = '';
                    if($("input[name='high_bp_pressure']").is(":checked")){
                        var OthCVD = $("input[name='something_not_on_list_text']").val();
                    }
                    
                    $.ajax({
                        type:'POST',
                        data: {'HTN':HTN,'Dyslipidemia':Dyslipidemia,'MI':MI,'ChestPain':ChestPain,'CHF':CHF,'Arrhythmia':Arrhythmia,'Claudication':Claudication,'OthCVD':OthCVD,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentTabClass.indexOf('neurological_container') != -1){
                    var section = 'past_medical_history_pmh2';
                    
                    var cva = '0';
                    if($("input[name='neurological_stroke']").is(":checked")){
                        var cva = "1";
                    }
                    var tia = '0';
                    if($("input[name='neurological_mini_stroke']").is(":checked")){
                        var tia = "1";
                    }
                    var hearing = '0';
                    if($("input[name='heairng']").is(":checked")){
                        var hearing = "1";
                    }
                    var vision = $("input[name='vision_difficulties_text']").val();
                    var oth_neuro = $("input[name='neurological_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'cva':cva,'tia':tia,'hearing':hearing,'vision':vision,'oth_neuro':oth_neuro,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    if($("input[name='neurological_headache']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else{
                        nextPrev(2);
                        return false;
                    }
                }
                
                if(currentTabClass.indexOf('past_medical_history_headache') != -1){
                    var migraine = '0';
                    if($("input[name='headache_migraine']").is(":checked")){
                        var migraine = "1";
                    }
                    var tension = '0';
                    if($("input[name='headache_tension']").is(":checked")){
                        var tension = "1";
                    }
                    
                    var hanos = $("input[name='headache_something_not_on_list_text']").val();

                    var section = 'past_medical_history_headache';
                    $.ajax({
                        type:'POST',
                        data: {'migraine':migraine,'tension':tension,'hanos':hanos,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('past_medical_history_3') != -1){
                    var section = 'past_medical_history_pmh3';
                    var asthma = '0';
                    if($("input[name='pulmonary_asthma']").is(":checked")){
                        var asthma = "1";
                    }
                    var pulmonary_chronic = '0';
                    if($("input[name='pulmonary_chronic']").is(":checked")){
                        var pulmonary_chronic = "1";
                    }
                    var OthPulm = $("input[name='pulmonary_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'asthma':asthma,'pulmonary_chronic':pulmonary_chronic,'OthPulm':OthPulm,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='pulmonary_chronic']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else{
                        nextPrev(2);
                        return false;
                    }

                }
                if(currentTabClass.indexOf('past_medical_history_pulmonary') != -1){
                    var section = 'past_medical_history_pulmonary';
                    var emphysema = '0';
                    if($("input[name='pulmonary_emphysema']").is(":checked")){
                        var emphysema = "1";
                    }
                    var ChrBronch = '0';
                    if($("input[name='pulmonary_chronic_bronchitis']").is(":checked")){
                        var ChrBronch = "1";
                    }
                    var COPDNOS = '0';
                    if($("input[name='pulmonary_i_m_not_sure']").is(":checked")){
                        var COPDNOS = "1";
                    }

                    $.ajax({
                        type:'POST',
                        data: {'emphysema':emphysema,'ChrBronch':ChrBronch,'COPDNOS':COPDNOS,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentTabClass.indexOf('past_medical_history_4') != -1){
                    $('#require_dialysis').change(function(){
                        if($(this).val() == 'yes'){
                            nextPrev(1,'next');
                        }
                    });
                    var section = 'past_medical_history_pmh4';
                    var gerd = '0';
                    if($("input[name='gastroesophageal_reflux']").is(":checked")){
                        var gerd = "1";
                    }
                    var barretts = '0';
                    if($("input[name='barretts_esophagus']").is(":checked")){
                        var barretts = "1";
                    }
                    var ibd = '';
                    if($('#select_inflammatory :selected').val() != ''){
                        var ibd = $('#select_inflammatory :selected').html();
                    }
                    var ibs = '0';
                    if($("input[name='irritable_bowel_syndrome']").is(":checked")){
                        var ibs = "1";
                    }
                    var nash = '0';
                    if($("input[name='nonalcoholic_steatohepatitis']").is(":checked")){
                        var nash = "1";
                    }
                    
                    var cirrhosis = '0';
                    if($("input[name='cirrhosis']").is(":checked")){
                        var cirrhosis = "1";
                    }
                   
                    var ColonPolypNum = $("input[name='gastrointestinal_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'gerd':gerd,'barretts':barretts,'ibs':ibs,'nash':nash,'cirrhosis':cirrhosis,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='inflammatory_bowel_disease']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else if($("input[name='viral_hepatitis']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }else if($("input[name='pancreatitis']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }else if($("input[name='pylori_infection']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }else if($("input[name='colon_polyp']").is(":checked")){
                        nextPrev(5);
                        return false;
                    }else{
                        nextPrev(7);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_ibd_inflammatory') != -1){
                    var section = 'past_medical_history_ibd_inflammatory';
                    var ibd = [];
                    $('.past_medical_history_ibd_inflammatory input[type=checkbox]').each(function(){
                        ibd.push($(this).siblings('.text').html().trim());
                    });
                    ibd = ibd.join(',');
                    $.ajax({
                        type:'POST',
                        data: {'ibd':ibd,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='viral_hepatitis']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else if($("input[name='pancreatitis']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }else if($("input[name='pylori_infection']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }else if($("input[name='colon_polyp']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }else{
                        nextPrev(6);
                        return false;
                    }

                    
                }

                if(currentTabClass.indexOf('past_medical_history_hepatitis') != -1){
                    var section = 'past_medical_history_hepatitis';
                    var vhep = [];
                    $('.past_medical_history_hepatitis input[type=checkbox]').each(function(){
                        vhep.push($(this).siblings('.text').html().trim());
                    });
                    vhep = vhep.join(',');
                    $.ajax({
                        type:'POST',
                        data: {'vhep':vhep,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='pancreatitis']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else if($("input[name='pylori_infection']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }else if($("input[name='colon_polyp']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }else{
                        nextPrev(5);
                        return false;
                    }
                }
                if(currentTabClass.indexOf('past_medical_history_pancreatitis') != -1){
                    var section = 'past_medical_history_pancreatitis';
                    var pancreatitis = [];
                    $('.past_medical_history_hepatitis input[type=checkbox]').each(function(){
                        pancreatitis.push($(this).siblings('.text').html().trim());
                    });
                    pancreatitis = pancreatitis.join(',');
                    $.ajax({
                        type:'POST',
                        data: {'pancreatitis':pancreatitis,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='pylori_infection']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else if($("input[name='colon_polyp']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }else{
                        nextPrev(4);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_pylori') != -1){
                    var section = 'past_medical_history_pylori';
                    var treated_pylori = $("input[name='treated_pylori']:checked").parents('label').find('.text').html();
                    treated_pylori = treated_pylori.replace(/ /g, '');
                    
                    //pancreatitis = pancreatitis.join(',');
                    $.ajax({
                        type:'POST',
                        data: {'hpylori':treated_pylori,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='colon_polyp']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else{
                        nextPrev(3);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_5') != -1){
                    var section = 'past_medical_history_pmh5';
                    //var cri = $('#require_dialysis :selected').html();
                    var cri = '0';
                    if($("input[name='urinary_chronic_renal_insufficiency']").is(":checked")){
                        var cri = "1";
                    }

                    var nocturia = '0';
                    if($("input[name='get_up_at_night']").is(":checked")){
                        var nocturia = "1";
                    }
                    var freq_uti = '0';
                    if($("input[name='urinary_tract_infections']").is(":checked")){
                        var freq_uti = "1";
                    }
                    
                    var OthGU = $("input[name='urinary_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'cri':cri,'nocturia':nocturia,'freq_uti':freq_uti,'OthGU':OthGU,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if(cri == '0'){
                        nextPrev(1,'next');
                    }

                }
                if(currentTabClass.indexOf('past_medical_history_required') != -1){
                    var section = 'past_medical_history_required';
                    var required_analysis = $("input[name='required_analysis']:checked").parents('label').find('.text').html();
                    required_analysis = required_analysis.replace(/ /g, '').trim();
                    if(required_analysis == 'Yes'){
                        // $("input[name='get_up_at_night']").trigger('click');
                        $("input[name='get_up_at_night']").prop('checked', false);
                    }

                    $.ajax({
                        type:'POST',
                        data: {'cri':required_analysis,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }


                if(currentTabClass.indexOf('past_medical_history_6') != -1){
                    var section = 'past_medical_history_pmh6';
                    var cri = $('#require_dialysis :selected').html();
                    var psoriasis = '0';
                    if($("input[name='dermatologic_psoriasis']").is(":checked")){
                        var psoriasis = "1";
                    }
                    var eczema = '0';
                    if($("input[name='dermatologic_eczema']").is(":checked")){
                        var eczema = "1";
                    }
                    
                    var OthDerm = $("input[name='dermatologic_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'psoriasis':psoriasis,'eczema':eczema,'OthDerm':OthDerm,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentTabClass.indexOf('past_medical_history_7') != -1){
                    // if(patient_gender != 'female'){
                    //     nextPrev(4,'next');
                    // }
                    var section = 'past_medical_history_pmh7';
                    var gout = '0';
                    if($("input[name='musculoskeletal_gout']").is(":checked")){
                        var gout = "1";
                    }
                    var lupus = '0';
                    if($("input[name='musculoskeletal_lupus']").is(":checked")){
                        var lupus = "1";
                    }
                    var ra = '0';
                    if($("input[name='rheumatoid_arthritis']").is(":checked")){
                        var ra = "1";
                    }
                    var PsorArth = '0';
                    if($("input[name='psoriatic_arthritis']").is(":checked")){
                        var PsorArth = "1";
                    }
                    var OA = $("input[name='musculoskeletal_osteoarthritis']").val();
                    
                    var Fibromyalgia = '0';
                    if($("input[name='musculoskeletal_fibromyalgia']").is(":checked")){
                        var Fibromyalgia = "1";
                    }
                    var OthRheum = $("input[name='musculoskeletal_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'gout':gout,'lupus':lupus,'ra':ra,'PsorArth':PsorArth,'OA':OA,'Fibromyalgia':Fibromyalgia,'OthRheum':OthRheum,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('past_medical_history_8') != -1){
                    var section = 'past_medical_history_pmh8';
                    var Diabetes = '0';
                    if($("input[name='endocrine_diabetes']").is(":checked")){
                        var Diabetes = "1";
                    }
                    var pre_diabetes = '0';
                    if($("input[name='endocrine_pre_diabetes']").is(":checked")){
                        var pre_diabetes = "1";
                    }
                
                    var OthEndo = $("input[name='endocrine_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'Diabetes':Diabetes,'pre_diabetes':pre_diabetes,'OthEndo':OthEndo,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='endocrine_thyroid_disease']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else if($("input[name='adrenal_problems']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }else{
                        nextPrev(3);
                        return false;
                    }

                }

                if(currentTabClass.indexOf('past_medical_history_thyroid_disease') != -1){
                    var str = [];
                    $('#regForm .past_medical_history_thyroid_disease input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_thyroid_disease input[type=textbox]').each(function(){
                        if($(this).val != ''){
                            str.push($(this).val());
                        }
                    });
                    str = str.join(',');
                    str = str.replace(/"|'/g,'');

                    var section = 'past_medical_history_thyroid_disease';
                    $.ajax({
                        type:'POST',
                        data: {'hyperthyroid':str,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if($("input[name='adrenal_problems']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else{
                        nextPrev(2);
                        return false;
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_adrenal_conditions') != -1){
                    var str = [];
                    $('#regForm .past_medical_history_adrenal_conditions input[type=checkbox]').each(function(){
                        if($(this).is(":checked")){
                            if($(this).hasClass('not_include') || $(this).hasClass('not_include_agg')){
                                
                            }else{
                                if($(this).siblings('.text').html().trim() != ''){
                                    str.push($(this).siblings('.text').html().trim());
                                }
                            }
                        }
                    });
                    $('#regForm .past_medical_history_adrenal_conditions input[type=textbox]').each(function(){
                        if($(this).val != ''){
                            str.push($(this).val());
                        }
                    });
                    str = str.join(',');

                    var section = 'past_medical_history_adrenal_conditions';
                    $.ajax({
                        type:'POST',
                        data: {'adrena_id':str,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }


                if(currentTabClass.indexOf('past_medical_history_9') != -1){
                    var section = 'past_medical_history_pmh9';
                    var Menorrhagia = '0';
                    if($("input[name='heavy_periods']").is(":checked")){
                        var Menorrhagia = "1";
                    }
                    var Metrorrhagia = '0';
                    if($("input[name='too_frequent_periods']").is(":checked")){
                        var Metrorrhagia = "1";
                    }
                    var Dysmeno = '0';
                    if($("input[name='exceptionally_painful_periods']").is(":checked")){
                        var Dysmeno = "1";
                    }
                    var DUB = '0';
                    if($("input[name='bleeding_after_menopause']").is(":checked")){
                        var DUB = "1";
                    }
                    var HotFlash = '0';
                    if($("input[name='flashes_associated_menopause']").is(":checked")){
                        var HotFlash = "1";
                    }
                    var NiteSweats = '0';
                    if($("input[name='night_sweats_menopause']").is(":checked")){
                        var NiteSweats = "1";
                    }
                    var HPVinfect = '0';
                    if($("input[name='human_papillomavirus_virus']").is(":checked")){
                        var HPVinfect = "1";
                    }
                    var HiBrDens = '0';
                    if($("input[name='high_breast_density']").is(":checked")){
                        var HiBrDens = "1";
                    }
                    
                    var OthGyn = $("input[name='gynecologic_something_not_on_list_text']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'Menorrhagia':Menorrhagia,'Metrorrhagia':Metrorrhagia,'Dysmeno':Dysmeno,'DUB':DUB,'HotFlash':HotFlash,'NiteSweats':NiteSweats,'HPVinfect':HPVinfect,'HiBrDens':HiBrDens,'OthGyn':OthGyn,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }

                
                if(currentTabClass.indexOf('past_medical_history_10') != -1){
                    var section = 'past_medical_history_pmh10';
                    var age_dx = $("input[name='initially_diagnosed']").val();
                    var vascular_arr = [];
                    $(".vascular_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            vascular_arr.push($(this).attr('name'));
                        }
                    });
                    $(".vascular_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            vascular_arr.push($(this).val());
                        }
                    });  
                    
                    var neurological_arr = [];
                    $(".neurological_container  input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            neurological_arr.push($(this).attr('name'));
                        }
                    });
                    $(".neurological_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            neurological_arr.push($(this).val());
                        }
                    });
                    
                    var headache_arr = [];
                    $(".past_medical_history_headache input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            headache_arr.push($(this).attr('name'));
                        }
                    });
                    $(".past_medical_history_headache input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            headache_arr.push($(this).val());
                        }
                    });
                    
                    var pulmonary_arr = [];
                    $(".pulmonary_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            pulmonary_arr.push($(this).attr('name'));
                        }
                    });
                    $(".pulmonary_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            pulmonary_arr.push($(this).val());
                        }
                    });
                    
                    var copd_arr = [];
                    $(".past_medical_history_pulmonary input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            copd_arr.push($(this).attr('name'));
                        }
                    });
                    $(".past_medical_history_pulmonary input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            copd_arr.push($(this).val());
                        }
                    });
                    
                    var gastrointestinal_arr = [];
                    $(".gastrointestinal_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            gastrointestinal_arr.push($(this).attr('name'));
                        }
                    });
                    $(".gastrointestinal_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            gastrointestinal_arr.push($(this).val());
                        }
                    });
                    
                    var ibd_inflammatory_arr = [];
                    $(".past_medical_history_ibd_inflammatory input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            ibd_inflammatory_arr.push($(this).attr('name'));
                        }
                    });
                    $(".past_medical_history_ibd_inflammatory input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            ibd_inflammatory_arr.push($(this).val());
                        }
                    });
                    var hepatitis_arr = [];
                    $(".past_medical_history_hepatitis input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            hepatitis_arr.push($(this).attr('name'));
                        }
                    });
                    $(".past_medical_history_hepatitis input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            hepatitis_arr.push($(this).val());
                        }
                    });
                    
                    var pancreatitis_arr = [];
                    $(".past_medical_history_pancreatitis input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            pancreatitis_arr.push($(this).attr('name'));
                        }
                    });
                    $(".past_medical_history_pancreatitis input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            pancreatitis_arr.push($(this).val());
                        }
                    });
                    
                    var treated_pylori = $("input[name='treated_pylori']:checked").parents('label').find('.text').html();
                    treated_pylori = treated_pylori.replace(/ /g, '').trim();
                    var how_many_colon = $("input[name='how_many_colon']").val();
                    
                    var polyps_arr = [];
                    $(".past_medical_history_polyps input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            polyps_arr.push($(this).attr('name'));
                        }
                    });
                    $(".past_medical_history_polyps input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            polyps_arr.push($(this).val());
                        }
                    });
                    var urinary_arr = [];
                    $(".urinary_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            urinary_arr.push($(this).attr('name'));
                        }
                    });
                    $(".urinary_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            urinary_arr.push($(this).val());
                        }
                    });
                    var required_analysis = $("input[name='required_analysis']:checked").parents('label').find('.text').html();
                    required_analysis = required_analysis.replace(/ /g, '').trim();
                     
                    var dermatologic_arr = [];
                    $(".dermatologic_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            dermatologic_arr.push($(this).attr('name'));
                        }
                    });
                    $(".dermatologic_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            dermatologic_arr.push($(this).val());
                        }
                    });
                    var musculoskeletal_arr = [];
                    $(".musculoskeletal_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            musculoskeletal_arr.push($(this).attr('name'));
                        }
                    });
                    $(".musculoskeletal_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            musculoskeletal_arr.push($(this).val());
                        }
                    });
                    var endocrine_arr = [];
                    $(".endocrine_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            endocrine_arr.push($(this).attr('name'));
                        }
                    });
                    $(".endocrine_container input[type='textbox']").each(function(){
                        if($(this).val() != ''){
                            endocrine_arr.push($(this).val());
                        }
                    }); 
                    
                    
                    
                    var condition_types = [];
                    $('.past_medical_history_10 .content_div .col-lg-6').each(function(){
                        condition_types.push($(this).find('span').html());  
                    });
                    
                    var age_diagnosed = $(".past_medical_history_10 input[name='age_diagnosed[]']").map(function(){return $(this).val();}).get();
                    var finished_treatment = $(".past_medical_history_10 input[name='finished_treatment[]']").map(function(){return $(this).val();}).get();
                    
                    console.log(condition_types);
                    console.log(age_diagnosed);
                    console.log(finished_treatment);
                    $.ajax({
                        type:'POST',
                        data: {'vascular_arr':vascular_arr,'headache_arr':headache_arr,'pulmonary_arr':pulmonary_arr,'copd_arr':copd_arr,'gastrointestinal_arr':gastrointestinal_arr,'ibd_inflammatory_arr':ibd_inflammatory_arr,'hepatitis_arr':hepatitis_arr,'pancreatitis_arr':pancreatitis_arr,'polyps_arr':polyps_arr,'urinary_arr':urinary_arr,'dermatologic_arr':dermatologic_arr,'musculoskeletal_arr':musculoskeletal_arr,'endocrine_arr':endocrine_arr,'condition_types':condition_types,'age_diagnosed':age_diagnosed,'finished_treatment':finished_treatment,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentTabClass.indexOf('past_medical_history_11') != -1){
                    var section = 'past_medical_history_pmh11';
                    var age_stop = $("input[name='when_it_stopped']").val();
                    $.ajax({
                        type:'POST',
                        data: {'age_stop':age_stop,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentTabClass.indexOf('past_medical_history_12') != -1){
                    var section = 'past_medical_history_abnormal';
                    var AbnlMammo = '0';
                    if($("input[name='abnormal_mammogram']").is(":checked")){
                        var AbnlMammo = "1";
                    }
                    var AbnlPap = '0';
                    if($("input[name='abnormal_pap_smear']").is(":checked")){
                        var AbnlPap = "1";
                    }
                    var AbnlHPV = '0';
                    if($("input[name='abnormal_test']").is(":checked")){
                        var AbnlHPV = "1";
                    }
                    var AbnlPSA = '0';
                    if($("input[name='abnormal_psa']").is(":checked")){
                        var AbnlPSA = "1";
                    }
                    var AbnlColo = '0';
                    if($("input[name='abnormal_colonoscopy']").is(":checked")){
                        var AbnlColo = "1";
                    }
                    var AbnlOthScope = '0';
                    if($("input[name='abnormal_endoscopy']").is(":checked")){
                        var AbnlOthScope = "1";
                    }
                    var AbnlMole = '0';
                    if($("input[name='abnormal_mole']").is(":checked")){
                        var AbnlMole = "1";
                    }
                    
                    $.ajax({
                        type:'POST',
                        data: {'AbnlMammo':AbnlMammo,'AbnlPap':AbnlPap,'AbnlHPV':AbnlHPV,'AbnlPSA':AbnlPSA,'AbnlColo':AbnlColo,'AbnlOthScope':AbnlOthScope,'AbnlMole':AbnlMole,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    
                    if($("input[name='none_of_above']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }
                    
                    if($("input[name='abnormal_mammogram']").is(":checked")){ 
                        
                    }else{
                        if($("input[name='abnormal_test']").is(":checked")){
                            nextPrev(1,'next');
                        }else{
                            nextPrev(2);
                        }
                    } 
                }
                
                if(currentTabClass.indexOf('past_medical_history_abnormailties') != -1){
                    if(!$("input[name='abnormal_test']").is(":checked")){
                        nextPrev(1,'next');
                    }
                }

                if(currentTabClass.indexOf('past_medical_history_16') != -1){

                    var section = 'past_medical_history_htwt';
                    var weight = $("#select_weight :selected").val();
                    var height_inch = $("input[name='height_inch']").val();
                    var height_feet = $("input[name='height_feet']").val();
                    var height_m = $("input[name='height_m']").val();
                    var weight_text = $("input[name='weight_text']").val();
                    var weight_at_age_18 = $("input[name='weight_at_age_18']").val();
                    var cm = $("input[name='cm']").val();
                    $.ajax({
                        type:'POST',
                        data: {'weight':weight,'weight_text':weight_text,'height_inch':height_inch,'height_feet':height_feet,'height_m':height_m,'cm':cm,'weight_at_age_18':weight_at_age_18,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentTabClass.indexOf('genetic_testing_history_1') != -1){
                    var genetic_testing = $("input[name='genetic_testing']:checked").val();
                    var gender = patient_gender;
                    var flag_genetic_testing = '';
                    if(gender == 'female' && age < 50){
                        flag_genetic_testing = 'GT eligible';
                    }
                    if(gender == 'male' && $("input[name='breast']").is(":checked")){
                        flag_genetic_testing = 'GT eligible';
                    }
                    if(flag_hr_status == 'TNBC' || flagOvarian == 'OvCa' || flagColorectal == 'CRC'){
                        flag_genetic_testing = 'GT eligible';
                    }
                    if(gender == 'female' && (age > 25 && age < 35)){
                        flag_genetic_testing = 'GT eligible';
                    }
                    if(genetic_testing == 'no'){
                        flag_genetic_testing = 'GT eligible';
                    }

                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_genetic_testing':flag_genetic_testing,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    
                    if(genetic_testing == 'no'){
                        //move to next section
                        nextPrev(5);
                        return false;
                    }
                }
                if(currentTabClass.indexOf('genetic_testing_history_2') != -1){
                    if($("input[name='remember']").is(":checked")){
                        var message = "That's ok. You will be asked to submit a copy of your genetic testing results prior to your consultation.";
                        addTab(message);
                    }
                    if($("input[name='undetermined']").is(":checked")){
                        var message = "You will be asked to submit a copy of your genetic testing results prior to your consultation.";
                        addTab(message);
                    }
                    if(!$("input[name='deleterious']").is(":checked")){
                        nextPrev(1,'next');
                    }
                    
                }
                
                if(currentTabClass.indexOf('genetic_testing_history_3') != -1){
                    var gene_list = $(".genetic_testing_history_3 select :selected").val();
                    if(gene_list == 'gene_dont_remember'){
                        var message = "That's ok. You will be asked to submit a copy of your genetic testing results prior to your consultation.";
                        addTab(message);
                    }
                }
                if(currentTabClass.indexOf('genetic_testing_history_4') != -1){
                    if($("input[name='not_sure']").is(":checked")){
                        var message = "That's ok. You will be asked to submit a copy of your genetic testing results prior to your consultation.";
                        addTab(message);
                    }
                }

                if(currentTabClass.indexOf('genetic_testing_history_5') != -1){
                    var genetic_risk = $("input[name='genetic_risk']:checked").val();
                    var flag_genetic_risk = '';
                    if(genetic_risk == 'no'){
                        flag_genetic_risk = "Needs GC";
                    }
                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_genetic_risk':flag_genetic_risk,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var section = 'genetic_testing_history_gt';
                    var genetic_testing = $("input[name='genetic_testing']:checked").parents('label').find('.text').html();
                    genetic_testing = genetic_testing.replace(/ /g, '');
                    // var clearly_meets = $("input[name='clearly_meets']:checked").parents('label').find('.text').html();
                    // clearly_meets = clearly_meets.replace(/ /g, '');
                    var v_no = $("input[name='v_no']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'genetic_testing':genetic_testing,'v_no':v_no,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var section = 'genetic_testing_history_gtresult';
                    var results = $("#results :selected").html();
                    var arr = [];
                    $(".lab_tab input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    
                    var arr_results = [];
                    $(".genetic_testing_history_2 input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr_results.push($(this).attr('name'));
                        }
                    });
                    
                    var selectedGenesArr = $(' .genetic_testing_history_3 .choices-multiple-remove-button option:selected').toArray().map(item => item.value);
                    
                    var genetic_risk = $("input[name='genetic_risk']:checked").parents('label').find('.text').html();
                    genetic_risk = genetic_risk.replace(/ /g, '');
                    $.ajax({
                        type:'POST',
                        data: {'arr':arr,'arr_results':arr_results,'selectedGenesArr':selectedGenesArr,'genetic_risk':genetic_risk,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                

                if(currentTabClass.indexOf('past_surgrical_history_1') != -1){
                    var biopsy = $("input[name='biopsy']:checked").val();
                    if(biopsy == 'no'){
                        nextPrev(4);
                        return false;   
                    }
                    var html = '<div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Breast </span><input type="checkbox" id="terms_conditions" name="bisopy_breast"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Cervix </span><input type="checkbox" id="terms_conditions" name="bisopy_cervix"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Uterus </span><input type="checkbox" id="terms_conditions" name="bisopy_uterus"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Lung </span><input type="checkbox" id="terms_conditions" name="bisopy_lung"> <span class="checkmark"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Colon or Rectum </span><input type="checkbox" id="terms_conditions" name="bisopy_colorectum"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Esophagus </span><input type="checkbox" id="terms_conditions" name="bisopy_esophagus"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Stomach </span><input type="checkbox" id="terms_conditions" name="bisopy_stomach"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Liver </span><input type="checkbox" id="terms_conditions" name="bisopy_liver"> <span class="checkmark"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Pancreas </span><input type="checkbox" id="terms_conditions" name="bisopy_pancreas"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Gallbladder or Bile Duct </span><input type="checkbox" id="terms_conditions" name="bisopy_gallbladder"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Thyroid </span><input type="checkbox" id="terms_conditions" name="bisopy_thyroid"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Adrenal Gland </span><input type="checkbox" id="terms_conditions" name="bisopy_adrenal"> <span class="checkmark"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Kidney </span><input type="checkbox" id="terms_conditions" name="bisopy_kidney"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Bladder </span><input type="checkbox" id="terms_conditions" name="bisopy_bladder"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Skin </span><input type="checkbox" id="terms_conditions" name="bisopy_skin"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Bone Marrow </span><input type="checkbox" id="terms_conditions" name="bisopy_bm"> <span class="checkmark"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Lymph Node </span><input type="checkbox" id="terms_conditions" name="bisopy_ln"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Other </span><input type="checkbox" id="terms_conditions" name="bisopy_oth"> <span class="checkmark"></span> </label> </div> <p><input placeholder="Other" type="textbox" name="bisopy_oth_text"></p> </div>';
                        var gender = patient_gender;
                        if(gender == 'female'){
                            $('.div_biospy').html(html);
                        }
                }
                if(currentTabClass.indexOf('past_surgrical_history_2') != -1){  
                    var fag_bisopy_breast = '';
                    if($("input[name='fag_bisopy_breast']").is(":checked")){
                        var fag_bisopy_breast = "Consider SERM/AI";
                    }
                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'fag_bisopy_breast':fag_bisopy_breast,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var html = '';
                    $('.times_tab_div').append(html);
                    $(".div_biospy input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            var name = $(this).siblings('span').html();
                            var id = $(this).attr('name');
                            // alert($(this).attr('name'));
                            html += '<div class="row"> <div class="col-lg-3" id="p1"> <label>'+name+'</label> </div> <div class="col-lg-3" id="p1"> <p><input placeholder="'+name+'" type="textbox" name="times_'+id+'"></p> </div> </div>';
                        }
                    });
                    //$('.times_tab_div').html(html);                    
                }
                
                if(currentTabClass.indexOf('past_surgrical_history_4') != -1){
                    var html = '';
                    $('.years_tab_div').append(html);
                    $(".div_biospy input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            var name = $(this).siblings('span').html();
                            var id = $(this).attr('name');
                            // alert($(this).attr('name'));
                            html += '<div class="row"> <div class="col-lg-3" id="p1"> <label>'+name+'</label> </div> <div class="col-lg-3" id="p1"> <p><input placeholder="'+name+'" type="textbox" name="times_'+id+'"></p> </div> </div>';
                        }
                    });
                    $('.years_tab_div').html(html);                    
                }

                // if(currentTabClass.indexOf('past_surgrical_history_8') != -1){
                //     var html = '<div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Bilateral mastectomy (removal of both breasts)</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_bimast"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Unilateral mastectomy or lumpectomy (removal of one breast or part of a breast)</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_unimastLmp"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Bilateral salpingectomy or salpingo-oophorectomy (removal of both fallopian tubes +/- ovaries)</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_bilateral_salpingectomy"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Unilateral salpingectomy or salpingo-oophorectomy (removal of one fallopian tube or ovary)</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_unilateral_salpingectomy"> <span class="checkmark"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Hysterectomy (removal of uterus)</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_hysterectomy"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Total colectomy (removal of the full large intestine)</span> </span><input type="checkbox" id="terms_conditions" name="urgeries_colectomy"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Partial colectomy (removal of part of the full large intestine)</span> </span><input type="checkbox" id="terms_conditions" name="urgeries_partial_colectomy"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Polypectomy (removal of a polyp, usually in the GI tract)</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_polypectomy"> <span class="checkmark"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Gastrectomy (removal of part or all the stomach)</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_gastrectomy"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Pancreatectomy/Whipple (removal of part or all of the pancreas)</span> </span><input type="checkbox" id="terms_conditions" name="urgeries_whipple"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Prostatectomy (removal of the prostate)</span> </span><input type="checkbox" id="terms_conditions" name="urgeries_prostatectomy"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Wide local excision of a skin cancer</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_local"> <span class="checkmark"></span> </label> </div> </div> <div class="row"> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Lymph node resection for any reason</span> </span><input type="checkbox" id="terms_conditions" name="surgeries_lymph"> <span class="checkmark"></span> </label> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Other surgery for removal of a cancer or precancer</span> </span><input type="checkbox" id="terms_conditions" name="urgeries_precancer"> <span class="checkmark"></span> </label> <p><input placeholder="" type="textbox" name="urgeries_precancer_text"></p> </div> <div class="col-lg-3" id="p1"> <label class="container"><span class="text">Unlisted surgery for a non-cancer reason</span> </span><input type="checkbox" id="terms_conditions" name="urgeries_reason"> <span class="checkmark"></span> </label> <p><input placeholder="" type="textbox" name="urgeries_reason_text"></p> </div> </div>';
                //         var gender = patient_gender;
                //         if(gender == 'female'){
                //             $('.div_surgeries').html(html);
                //         }
                // }
                
                if(currentTabClass.indexOf('past_surgrical_history_8') != -1){
                    if($("input[name='surgeries_digestive']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }
                    if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }
                    if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(5);
                        return false;
                    }
                    if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(6);
                        return false;
                    }
                    if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(7);
                        return false;
                    }
                    if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(8);
                        return false;
                    }
                    if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(9);
                        return false;
                    }
                    if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(10);
                        return false;
                    }
                }
                
                 
                if(currentTabClass.indexOf('type_of_surgeries_container') != -1){
                    if($("input[name='surgeries_urologic']").is(":checked")){
                        nextPrev(1,'false');
                        return false;
                    }
                    else if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(5);
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(6);
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(7);
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(8);
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(9);
                        return false;
                    }else{
                        nextPrev(10);
                        return false;
                    }
                }
                if(currentTabClass.indexOf('type_of_urologic_surgeries') != -1){
                    if(patient_gender == 'female'){
                        nextPrev(1,'next');
                        return false;
                    }
                    if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(5);
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(6);
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(7);
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(8);
                        return false;
                    }else{
                        nextPrev(9);
                        return false;
                    }
                }
                
                if(currentTabClass.indexOf('type_of_gynelogic_surgeries') != -1){
                    if($("input[name='surgeries_breast']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(5);
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(6);
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(7);
                        return false;
                    }else{
                        nextPrev(8);
                        return false;
                    }
                }
                
                if(currentTabClass.indexOf('type_of_breast_surgeries') != -1){
                    if($("input[name='surgeries_cardiothoracic']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(5);
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(6);
                        return false;
                    }else{
                        nextPrev(7);
                        return false;
                    }
                }
                
                if(currentTabClass.indexOf('type_of_cardiothoracic_surgeries') != -1){
                    if($("input[name='surgeries_neurologic']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(5);
                        return false;
                    }else{
                        nextPrev(6);
                        return false;
                    }
                }
                
                if(currentTabClass.indexOf('type_of_neurologic_surgeries') != -1){
                    if($("input[name='surgeries_eye']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    else if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(4);
                        return false;
                    }else{
                        nextPrev(5);
                        return false;
                    }
                }
                
                if(currentTabClass.indexOf('type_of_eye_surgeries') != -1){
                    if($("input[name='urgeries_ent']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(3);
                        return false;
                    }else{
                        nextPrev(4);
                        return false;
                    }
                }
                if(currentTabClass.indexOf('type_of_ent_surgeries') != -1){
                    if($("input[name='surgeries_orthopedic']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    else if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(2);
                        return false;
                    }else{
                        nextPrev(3);
                        return false;
                    }
                }
                
                if(currentTabClass.indexOf('type_of_orthopedic_surgeries') != -1){
                    if($("input[name='surgeries_endocrine']").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }else{
                        nextPrev(2);
                        return false;
                    }
                }
                
                
                if(currentTabClass.indexOf('past_surgrical_history_11') != -1){
                    var section = 'genetic_testing_history_psh';
                    var biopsy = $("input[name='biopsy']:checked").parents('label').find('.text').html();
                    biopsy = biopsy.replace(/ /g, '');
                    var surgery_forany = $("input[name='surgery_forany']:checked").parents('label').find('.text').html();
                    surgery_forany = surgery_forany.replace(/ /g, '');
                    
                    $.ajax({
                        type:'POST',
                        data: {'biopsy':biopsy,'surgery_forany':surgery_forany,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });


                    var section = 'genetic_testing_history_biopsy';
                    var results = $("#results :selected").html();
                    var arr = [];
                    $(".biopsy_tab input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    //var genetic_risk = $("input[name='genetic_risk']:checked").parents('label').find('.text').html();
                    //genetic_risk = genetic_risk.replace(/ /g, '');
                    $.ajax({
                        type:'POST',
                        data: {'arr':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var section = 'genetic_testing_history_surgery';
                    var surgery_year = $("input[name='surgery_year']").val();
                    var what_side_performed = $("input[name='what_side_performed']:checked").parents('label').find('.text').html();
                    what_side_performed = what_side_performed.replace(/ /g, '');
                    var arr = [];
                    $(".div_surgeries input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    $.ajax({
                        type:'POST',
                        data: {'surgery_year':surgery_year,'what_side_performed':what_side_performed,'div_surgeries':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentTabClass.indexOf('medications_and_supplements1') != -1){
                    var medications = $("input[name='medications']:checked").val();
                    if(medications == 'no'){
                        nextPrev(4);
                    }
                }
                if(currentTabClass.indexOf('medications_and_supplements13') != -1){
                    var section = 'medication_supplement';
                    var medications = $("input[name='medications']:checked").parents('label').find('.text').html();
                    medications = medications.replace(/ /g, '');
                    var list_medications_text = $("input[name='list_medications_text']").val();
                    
                    var arr = [];
                    $(".how_tak_it_div input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var select_unit = $("#select_unit :selected").val();
                    var time_text = $("input[name='time_text_supp']").val();
                    var stop_this_medication = $("input[name='stop_this_medication']").val();
                    var start_this_medication = $("input[name='start_this_medication']").val();

                    // var clearly_meets = $("input[name='clearly_meets']:checked").parents('label').find('.text').html();
                    // clearly_meets = clearly_meets.replace(/ /g, '');
                    // var v_no = $("input[name='v_no']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'medications':medications,'list_medications_text':list_medications_text,'arr':arr,'select_unit':select_unit,'time_text':time_text,'start_this_medication':start_this_medication,'stop_this_medication':stop_this_medication,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });


                    var section = 'medication_supplement_supp';
                    var supplements = $("input[name='supplements']:checked").parents('label').find('.text').html();
                    supplements = supplements.replace(/ /g, '');
                    var supp_name = $("input[name='supp_name']").val();
                    var arr = [];
                    $(".how_tak_it_supp input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var select_unit_supp = $("#select_unit_supp :selected").val();
                    var time_text_supp = $("input[name='time_text_supp']").val();
                    var start_this_supplement = $("input[name='start_this_supplement']").val();
                    var stop_this_supplement = $("input[name='stop_this_supplement']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'supplements':supplements,'supp_name':supp_name,'arr':arr,'select_unit_supp':select_unit_supp,'time_text_supp':time_text_supp,'start_this_supplement':start_this_supplement,'stop_this_supplement':stop_this_supplement,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentTabClass.indexOf('allergies1') != -1){
                    var allergic = $("input[name='allergic']:checked").val();
                    if(allergic == 'no'){
                        nextPrev(4);
                    }
                }

                if(currentTabClass.indexOf('allergies2') != -1){
                    var list_medications = $("input[name='list_medications']").val();
                    var flag_list_medications = '';
                    if(list_medications != ''){
                        flag_list_medications = "DrugAllergy";
                    }
                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_list_medications':flag_list_medications,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('allergies5') != -1){
                    
                    var section = 'allergy';
                    var allergic = $("input[name='allergic']:checked").parents('label').find('.text').html();
                    allergic = allergic.replace(/ /g, '');
                    var list_medications = $("input[name='list_medications']").val();
                    var arr = [];
                    $(".what_happens_allergies input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var othallergy = $("input[name='othallergy']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'allergic':allergic,'list_medications':list_medications,'arr':arr,'othallergy':othallergy,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                
                if(currentTabClass.indexOf('reproductive_history4') != -1){
                    var children = $("input[name='children']").val();
                    var gender = patient_gender;
                    if(children == '0' && gender=='male'){
                        nextPrev(7);
                    }
                    if(children == '0' && gender=='female'){
                        nextPrev(1,'next');
                    }
                    
                }
                if(currentTabClass.indexOf('reproductive_history5') != -1){

                    var gender = patient_gender;
                    if(gender=='male'){
                        nextPrev(4);
                    }
                    
                    
                }
                
                
                
                
                if(currentStep == 75){
                    // $("body").on("keydown", "input[name='girls']", function (e) {
                    
                }

                if(currentTabClass.indexOf('reproductive_history11') != -1){
                    var section = 'reproductive_history';
                    var children  = $("input[name='children']").val();
                    var adopted  = $("input[name='adopted']").val();
                    var pregnancies  = $("input[name='pregnancies']").val();
                    var live_birth  = $("input[name='live_birth']").val();
                    var miscarried  = $("input[name='miscarried']").val();
                    var voluntarily  = $("input[name='voluntarily']").val();
                    var boys  = $("input[name='boys']").val();
                    var girls  = $("input[name='girls']").val();
                    var first_menstrual_period  = $("input[name='first_menstrual_period']").val();
                    var MenoAge  = $("input[name='entered_menopause']").val();
                    var household_income = $('#household_income :selected').html();

                    $.ajax({
                        type:'POST',
                        data: {'children':children,'adopted':adopted,'pregnancies':pregnancies,'live_birth':live_birth,'miscarried':miscarried,'voluntarily':voluntarily,'boys':boys,'girls':girls,'first_menstrual_period':first_menstrual_period,'household_income':household_income,'MenoAge':MenoAge,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                
                if(currentTabClass.indexOf('structure3') != -1){
                    var biologic = $("input[name='biologic']:checked").val();
                    if(biologic == 'no'){
                        nextPrev(5);
                    }

                    $("input[name='biologic_father_alive']").click(function(){
                        var value = $(this).val();
                        if(value == 'no'){
                            $("input[name='died']").prop('disabled',false);
                        }else{
                            $("input[name='died']").prop('disabled',true);    
                        }
                        if(value == 'i_dont_know'){
                            nextPrev(1);
                        }
                    });
                }

                if(currentTabClass.indexOf('structure4') != -1){
                    $("input[name='biologic_mother_alive']").click(function(){
                        var value = $(this).val();
                        if(value == 'no'){
                            $("input[name='she_died']").prop('disabled',false);
                        }else{
                            $("input[name='she_died']").prop('disabled',true);    
                        }
                        if(value == 'i_dont_know'){
                            nextPrev(1);
                        }
                    });
                }
                if(currentStep == 80){
                    
                    
                }

                if(currentTabClass.indexOf('structure7') != -1){
                    var section = 'family_history';
                    var adopted = $("input[name='adopted']:checked").parents('label').find('.text').html();
                    adopted = adopted.replace(/ /g, '');
                    var biologic = $("input[name='biologic']:checked").parents('label').find('.text').html();
                    biologic = biologic.replace(/ /g, '');
                    var biologic_father = $("input[name='biologic_father']:checked").parents('label').find('.text').html();
                    biologic_father = biologic_father.replace(/ /g, '');
                    var biologic_father_alive = $("input[name='biologic_father_alive']:checked").parents('label').find('.text').html();
                    biologic_father_alive = biologic_father_alive.replace(/ /g, '');
                    var died = $("input[name='died']").val();
                    var old = $("input[name='old']").val();
                    var father_first_name = $("input[name='father_first_name']").val();

                    var biologic_mother = $("input[name='biologic_mother']:checked").parents('label').find('.text').html();
                    biologic_mother = biologic_mother.replace(/ /g, '');
                    var biologic_mother_alive = $("input[name='biologic_mother_alive']:checked").parents('label').find('.text').html();
                    biologic_mother_alive = biologic_mother_alive.replace(/ /g, '');
                    var she_died = $("input[name='she_died']").val();
                    var she_old_now = $("input[name='she_old_now']").val();
                    var mother_first_name = $("input[name='mother_first_name']").val();

                    var siblings = $("input[name='siblings']").val();
                    var grandmother_alive = $("input[name='grandmother_alive']:checked").parents('label').find('.text').html();
                    grandmother_alive = grandmother_alive.replace(/ /g, '');
                    var mom_old_died = $("input[name='mom_old_died']").val();
                    var mom_old_now = $("input[name='mom_old_now']").val();
                    var mom_descend = $("input[name='mom_descend']").val();
                    var family_jewish = $("input[name='family_jewish']:checked").parents('label').find('.text').html();
                    family_jewish = family_jewish.replace(/ /g, '');

                    var grandfather_alive = $("input[name='grandfather_alive']:checked").parents('label').find('.text').html();
                    grandfather_alive = grandfather_alive.replace(/ /g, '');
                    var grandfather_old_died = $("input[name='grandfather_old_died']").val();
                    var grandfather_old_now = $("input[name='grandfather_old_now']").val();
                    var country_grandfather_descend = $("input[name='country_grandfather_descend']").val();
                    var paternal_family_jewish = $("input[name='paternal_family_jewish']:checked").parents('label').find('.text').html();
                    paternal_family_jewish = paternal_family_jewish.replace(/ /g, '');

                    var paternal_uncles = $("input[name='paternal_uncles']").val();
                    var paternal_aunts = $("input[name='paternal_aunts']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'adopted':adopted,'biologic':biologic,'biologic_father':biologic_father,'biologic_father_alive':biologic_father_alive,'died':died,'old':old,'father_first_name':father_first_name,'biologic_mother':biologic_mother,'biologic_mother_alive':biologic_mother_alive,'she_died':she_died,'she_old_now':she_old_now,'mother_first_name':mother_first_name,'siblings':siblings,'grandmother_alive':grandmother_alive,'mom_old_died':mom_old_died,'mom_old_now':mom_old_now,'mom_descend':mom_descend,'family_jewish':family_jewish,'grandfather_alive':grandfather_alive,'grandfather_old_now':grandfather_old_now,'grandfather_old_died':grandfather_old_died,'country_grandfather_descend':country_grandfather_descend,'paternal_family_jewish':paternal_family_jewish,'paternal_uncles':paternal_uncles,'paternal_aunts':paternal_aunts,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('people_with_cancer2') != -1){
                    if($(".which_family_memeber_div input[name='sister']").is(":checked") || $(".which_family_memeber_div input[name='mother']").is(":checked") || $(".which_family_memeber_div input[name='maternal_grandmother']").is(":checked") || $(".which_family_memeber_div input[name='paternal_grandmother']").is(":checked") || $(".which_family_memeber_div input[name='maternal_aunt']").is(":checked") || $(".which_family_memeber_div input[name='paternal_aunt']").is(":checked") || $(".which_family_memeber_div input[name='niece']").is(":checked") || $(".which_family_memeber_div input[name='maternal_female_cousin']").is(":checked") || $(".which_family_memeber_div input[name='paternal_female_cousin']").is(":checked") ){
                        $('.which_specific_cancer_div2').show();
                        $('.which_specific_cancer_div1').hide();
                    }else{
                        $('.which_specific_cancer_div2').hide();
                        $('.which_specific_cancer_div1').show();
                    }
                }   

                  
                if(currentTabClass.indexOf('people_with_cancer3') != -1){ 
                    var boys_names = [];
                    if($('.boys_div').html() != ''){
                        $('.boys_div input').each(function(){
                            var namee = $(this).attr('name');
                            
                            if(namee.indexOf('boy_first_name') != -1){
                                boys_names.push($(this).val());
                            }
                        });
                    }


                    //var girls_names = [];
                    if($('.girls_div').html() != ''){
                        $('.girls_div input').each(function(){
                            var namee = $(this).attr('name');
                            if(namee.indexOf('girl_first_name') != -1){
                                boys_names.push($(this).val());
                            }
                        });
                    }

                    //var siblings_names = [];
                    if($('.siblings_div').html() != ''){
                        $('.siblings_div input').each(function(){
                            var namee = $(this).attr('name');
                            if(namee.indexOf('first_name') != -1){
                                boys_names.push($(this).val());
                            }
                        });
                    }

                    //console.log(boys_names.length);
                    var html = '';
                    var str = '';
                    if (boys_names.length > 0) {
                        html = '<div class="row"><div class="row">';
                        $.each(boys_names, function(index, value) {
                            str = value.replace("'", "`");
                            str = value.replace(" ", "_");
                            html += '<div class="col-lg-3" id="p1"> <label class="container"><span class="text">'+value+'</span> <input type="checkbox" id="'+str+'" name="'+str+'"> <span class="checkmark"></span> </label> </div>'
                        });
                        html += '</div></div>';
                    }
                    $('.div_specific_family').html(html);
                }          

                if(currentTabClass.indexOf('people_with_cancer5') != -1){
                    var household_income = $("#household_income :selected").val();
                    var flag_household_income = '';
                    if(household_income == 'less_than_26'){
                        flag_household_income = 'Below poverty line for family of 4'
                    }else if(household_income == '26-55'){
                        flag_household_income = 'At poverty line for a family of 4-10';
                    }else if(household_income == '56-88'){
                        flag_household_income = '22% tax bracket';
                    }else if(household_income == '89-169'){
                        flag_household_income = '24% tax bracket';
                    }else if(household_income == '170-215'){
                        flag_household_income = '32% tax bracket';
                    }else if(household_income == '216-399'){
                        flag_household_income = '35% tax bracket';
                    }

                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_household_income':flag_household_income,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('people_with_pre_cancer5') != -1){
                    var section = 'people_with_cancer';
                    var adequate = $("input[name='adequate']:checked").parents('label').find('.text').html();
                    adequate = adequate.trim();
                    var arr = [];
                    $(".which_family_memeber_div input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });

                    var arr1 = [];
                    $(".which_specific_cancer_div input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr1.push($(this).attr('name'));
                        }
                    });
                    var old_initially_diagnosed = $('#old_initially_diagnosed :selected').html();
                    $.ajax({
                        type:'POST',
                        data: {'adequate':adequate,'which_family_memeber_div ':arr,'which_specific_cancer_div':arr1,'old_initially_diagnosed':old_initially_diagnosed,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentTabClass.indexOf('perceived_risk') != -1){
                    var section = 'social_history';
                    var adequate = $("input[name='adequate']:checked").parents('label').find('.text').html();
                    adequate = adequate.replace(/ /g, '');
                    var please_elaborate = $("textarea[name='please_elaborate']").html();
                    
                    var coworkers = $("input[name='coworkers']:checked").parents('label').find('.text').html();
                    coworkers = coworkers.replace(/ /g, '');
                    var friends_impact = $("textarea[name='friends_impact']").html();
                    var living = $("input[name='living']").val();
                    

                    var arr = [];
                    $(".highest_level_div input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var household_income = $("#household_income :selected").html();
                    var live_household = $("input[name='live_household']").val();
                    var adequate_social_support = $("input[name='adequate_social_support']:checked").parents('label').find('.text').html();
                    adequate_social_support = adequate_social_support.replace(/ /g, '');
                    var concerned_risk = $("input[name='concerned_risk']:checked").parents('label').find('.text').html();
                    concerned_risk = concerned_risk.replace(/ /g, '');
                    var why_you_feel = $("input[name='why_you_feel']").val();
                    
                    
                    $.ajax({
                        type:'POST',
                        data: {'soc_support':adequate,'soc_support_detail':please_elaborate,'ca_friends':coworkers,'friend_effect':friends_impact,'Occupation':living,'Education':arr,'Income':household_income,'Household':live_household,'PercRisk':concerned_risk,'PercReasons':why_you_feel,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                showAlert('Your form has been succesfully submitted');
                            }
                        }
                    });
                    return false;
                }
            }

            setTimeout(function(){
                // var step_i = 1;
                // var index = 0;
                // $('.all-steps .step').each(function(){
                //     if($(this).hasClass('completed')){
                //         index = $(this).index() + 1;
                //     }
                //     $(this).removeClass('completed');
                    
                //     // if(!$(this).hasClass('active')){
                //     //     if(step_i <= currentTab){
                //     //         $(this).addClass('completed');
                //     //     }
                //     //     step_i++;
                //     // }
                // });
                // $('.all-steps .step:nth-child('+index+')').addClass('completed');
            }, 100);
            
            function addTab(message){
                setTimeout(function(){
                    $('#myModal').modal('show');
                    $('#myModal #small_text').text(message);
                    $('#regForm').hide();
                    $('.div_steps').hide();
                }, 300);
                
            }

            function getAge(dateString) {

                var dates = dateString.split("-");
                var d = new Date();

                var userday = dates[0];
                var usermonth = dates[1];
                var useryear = dates[2];

                var curday = d.getDate();
                var curmonth = d.getMonth()+1;
                var curyear = d.getFullYear();
                var age = curyear - useryear;

                if((curmonth < usermonth) || ( (curmonth == usermonth) && curday < userday   )){

                    age--;

                }

                return age;
            }

            function requiredTexts(currentStep){
                //alert(currentStep);
                var c = currentStep - 1;
                var validate = false;
                
                $(".questionnaire-html #regForm .tab:eq("+c+") input[type='text']").each(function(){
                    $(this).css('border','1px solid #aaaaaa');
                    if($(this).val() == ''){
                        //alert($(this).val());
                        $(this).css('border','1px solid red');
                        validate = true;
                    }
                });
                return validate;
            }

            $('.datepicker1').datepicker({
                format: "mm",
                viewMode: "months", 
                viewYear: false,
                minViewMode: "months"
            });

            $('.datepicker2').datepicker({
                format: "dd",
                viewYear: false,
            }).on('show', function() {
                //console.log('hh');
                $('.datepicker-days thead tr:nth-child(2)').css({"visibility":"hidden"});
                //$(".datepicker-months .datepicker-switch").css({"visibility":"hidden"});
            });

            $('.datepicker3').datepicker({
                format: "yyyy",
                viewMode: "years", 
                minViewMode: "years"
            });

            $('.datepicker').datepicker({
                format: "yyyy-mm-dd"
            });
            $('.datepicker4').datepicker({
                format: "yyyy-mm"
            });

            function getCurrentStep(){
                var i = 1;
                currentStep = 0;
                $('.all-steps .step').each(function(){
                    //console.log(i);
                    if($(this).hasClass('active')){
                        currentStep = i;
                    }
                    i++;
                });
                return currentStep;
            }

            x[currentTab].style.display = "none";
            currentTab = currentTab + n;
            if (currentTab >= x.length) {
                // document.getElementById("regForm").submit();
                // return false;
                //alert("sdf");
                document.getElementById("nextprevious").style.display = "none";
                document.getElementById("all-steps").style.display = "none";
                document.getElementById("register").style.display = "none";
                document.getElementById("text-message").style.display = "block";
            }
            showTab(currentTab);
        }

        function showAlert(msg){
            $(".form-error-message").show('');
            $(".form-error-message").html(msg);
            $(".form-error-message").fadeOut(100).fadeIn(100);
            setTimeout(function () {
                $(".form-error-message").hide();
            }, 5000);
        }

        function validateForm() {
            var x, y, i, valid = true;
            x = document.getElementsByClassName("tab");
            y = x[currentTab].getElementsByTagName("input");
            for (i = 0; i < y.length; i++) {
                if (y[i].value == "") {
                    y[i].className += " invalid";
                    valid = false;
                }
            }
            if (valid) { document.getElementsByClassName("step")[currentTab].className += " finish"; }
            return valid;
        }

        function fixStepIndicator(n) {
            var i, x = document.getElementsByClassName("step");
            if(n == 1){
                var index = parseInt($('.all-steps .active').index()) + 2;
            }else{
                var index = parseInt($('.all-steps .active').index());
            }
            for (i = 0; i < x.length; i++) { x[i].className = x[i].className.replace(" active", ""); }
            //alert('.all-steps .step:nth-child('+index+')');
            
            $('.all-steps .step:nth-child('+index+')').addClass('active');
            //x[n].className += " active";
        }

    </script>


    <div class="container-xl px-1">
        <?php if(isset($_GET['questionnaire'])){ 
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            $webroot = $GLOBALS['web_root'];
            $link = $actual_link.$webroot.'/portal/home.php';
        ?>
        <nav id="topNav" class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
            <a class="navbar-brand" href="<?=$link;?>">
                <img class="img-fluid" width="140" src="<?php echo $actual_link.$webroot.'/portal/images/new_logo.png'; ?>">
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="nav">
                <div style="position: absolute;left: 42%;">
                    <h3 id="section_name"></h3>
                </div>
                <ul class="navbar-nav mt-2 mt-lg-0">
                    <li class="nav-item">
                        <select id="show_sections" name="show_sections" style="height: 33px !important;color: #504e4e;">
                            <option value="show_sections">Show Sections</option>
                            <option value="hide_sections" selected>Hide Sections</option>
                        </select>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link back_to_home" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-backward"></i>  Back To Home
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <?php } ?>
        <?php 
        $style="z-index:1030;";
        if(isset($_GET['questionnaire'])){ 
            $style="z-index:1030;display:none;";
        }
        ?>
            <nav id="verytop" class="navbar navbar-expand-lg navbar-light bg-light px-1 pt-3 pb-1 m-0 sticky-top" style="<?=$style;?>">
                <a class="navbar-brand mt-1 mr-1"><h3><?php echo xlt("My Documents") ?></h3></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#topmenu" aria-controls="topmenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div id="topmenu" class="collapse navbar-collapse">
                    <ul class="navbar-nav navCollapse mr-auto">
                        <!-- Sticky actions toolbar -->
                        <div class='helpHide d-none'>
                            <ul class="navbar-nav">
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="signTemplate" href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal" data-type="patient-signature"><?php echo xlt('Edit Signature'); ?></a></li>
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="saveTemplate" href="#"><?php echo xlt('Save'); ?></a></li>
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="printTemplate" href="javascript:;" onclick="printaDoc('templatecontent');"><?php echo xlt('Print'); ?></a></li>
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="submitTemplate" href="#"><?php echo xlt('Download'); ?></a></li>
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="sendTemplate" href="#"><?php echo xlt('Submit Document'); ?></a></li>
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="chartTemplate" href="#"><?php echo xlt('Chart to') . ' ' . text($catname); ?></a></li>
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="downloadTemplate" href="#"><?php echo xlt('Download'); ?></a></li>
                                <li class="nav-item"><a class="nav-link btn btn-outline-primary" id="chartHistory" href="#"><?php echo xlt('Chart History'); ?></a></li>
                            </ul>
                        </div>
                        <?php if (!empty($is_module) || !empty($is_portal)) { ?>
                            <div class="dropdown mb-1">
                                <a class="dropdown-toggle nav-link btn btn-outline-success text-success" href="#" role="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo xlt('Select Documents') ?>
                                </a>
                                <div class="dropdown-menu document-menu" aria-labelledby="dropdownMenu">
                                    <?php echo $templateService->renderPortalTemplateMenu($pid, $cuser, true); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <li class='nav-item mb-1'>
                            <a class='nav-link text-success btn btn-outline-success' onclick="page.handleHistoryView()">
                                <?php echo xlt('History') ?>
                                <i class="history-direction ml-1 fa fa-arrow-down"></i>
                            </a>
                        </li>
                        <?php if (empty($is_module)) { ?>
                            <li class="nav-item mb-1">
                                <a id="Help" class="nav-link text-primary btn btn-outline-primary d-none" onclick='page.newDocument(cpid, cuser, "Help", help_id);'><?php echo xlt('Help'); ?></a>
                            </li>
                        <?php } else { ?>
                            <li class="nav-item mb-1">
                                <a class="nav-link text-danger btn btn-secondary" id="a_docReturn" href="#" onclick='window.location.replace("<?php echo $referer ?>")'><?php echo xlt('Return'); ?></a>
                            </li>
                        <?php } ?>
                        <li class='nav-item mb-1'>
                            <a class='nav-link btn btn-secondary' data-toggle='tooltip' title='Refresh' id='refreshPage' href='javascript:' onclick='window.location.reload()'> <span class='fa fa-sync fa-lg'></span></a>
                        </li>
                        <li class='nav-item mb-1'>
                            <a id='showNav' class='nav-link btn btn-secondary'><span class='navbar-toggler-icon mr-1'></span><?php echo xlt('Menu'); ?></a>
                        </li>
                    </ul>
                </div>
            </nav>
        
        <div class="doc-div d-flex flex-row justify-content-center">
            <!-- Pending documents left menu Deprecated and removed 01/13/22 -->
            <div class="clearfix" id="topnav">
                <div id="collectionAlert"></div>
            </div>
            <!-- Right editor container -->
            <div id="editorContainer" class="d-flex flex-column w-100 h-auto">
                <!-- document editor and action toolbar template -->
                <script type="text/template" id="onsiteDocumentModelTemplate">
                    <div class="card m-0 p-0" id="docpanel">
                        <!-- Document edit container -->
                        <header class="card-header bg-dark text-light helpHide" id='docPanelHeader'><?php echo xlt('Editing'); ?></header>
                        <!-- editor form -->
                        <form class="container-xl p-0" id='template' name='template' role="form" action="./../lib/doc_lib.php" method="POST">
                            <div id="templatediv" class="card-body border overflow-auto">
                                <div id="templatecontent" class="template-body">
                                    <div class="text-center overflow-hidden"><i class="fa fa-circle-notch fa-spin fa-2x ml-auto"></i></div>
                                </div>
                            </div>
                            <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('doc-lib')); ?>" />
                            <input type="hidden" name="content" id="content" value="" />
                            <input type="hidden" name="cpid" id="cpid" value="" />
                            <input type="hidden" name="docid" id="docid" value="" />
                            <input type='hidden' name='template_id' id='template_id' value='' />
                            <input type="hidden" name="handler" id="handler" value="download" />
                            <input type="hidden" name="status" id="status" value="Open" />
                        </form>
                        <div class="clearfix">
                            <span>
                                <button id="dismissOnsiteDocumentButton" class="btn btn-secondary float-right" onclick="window.location.reload()"><?php echo xlt('Dismiss Form'); ?></button>
                            </span>
                            <!-- delete button is a separate form to prevent enter key from triggering a delete-->
                            <form id="deleteOnsiteDocumentButtonContainer" class="form-inline" onsubmit="return false;">
                                <fieldset>
                                    <div class="form-group">
                                        <label class="col-form-label"></label>
                                        <div class="controls">
                                            <button id="deleteOnsiteDocumentButton" class="btn btn-sm btn-danger"><i class="icon-trash icon-white"></i><?php echo xlt('Delete Document'); ?></button>
                                            <span id="confirmDeleteOnsiteDocumentContainer">
                                                <button id="cancelDeleteOnsiteDocumentButton" class="btn btn-link btn-sm"><?php echo xlt('Cancel'); ?></button>
                                                <button id="confirmDeleteOnsiteDocumentButton" class="btn btn-sm btn-danger"><?php echo xlt('Confirm'); ?></button>
                                          </span>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                </script>
                <div id="onsiteDocumentModelContainer" class="modelContainer">
                    <!-- rendered edit document and action toolbar template -->
                </div>
            </div><!-- close flex right-->
        </div><!-- close flex row -->

        <!-- Now history table container template -->
        <script type="text/template" id="onsiteDocumentCollectionTemplate">
            <div class="table-responsive pt-3">
                <h4 class="text-sm-center"><?php echo xlt('Your Document History') ?><small> (Click on label to sort.)</small></h4>
                <table class="collection table table-sm table-hover">
                    <thead class='thead-dark'>
                    <tr class='cursor-pointer'>
                        <th scope="col" id="header_Id"><?php echo xlt('Id'); ?><% if (page.orderBy == 'Id') { %> <i class='icon-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_DocType"><?php echo xlt('Document'); ?><% if (page.orderBy == 'DocType') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_CreateDate"><?php echo xlt('Create Date'); ?><% if (page.orderBy == 'CreateDate') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_ReviewDate"><?php echo xlt('Reviewed Date'); ?><% if (page.orderBy == 'ReviewDate') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_DenialReason"><?php echo xlt('Review Status'); ?><% if (page.orderBy == 'DenialReason') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_PatientSignedStatus"><?php echo xlt('Signed'); ?><% if (page.orderBy == 'PatientSignedStatus') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                        <th scope="col" id="header_PatientSignedTime"><?php echo xlt('Signed Date'); ?><% if (page.orderBy == 'PatientSignedTime') { %> <i class='fa fa-arrow-<%= page.orderDesc ? ' up' : 'down' %>' /><% } %></th>
                    </tr>
                    </thead>
                    <tbody>
                    <% items.each(function(item) {  %>
                    <tr id="<%= _.escape(item.get('id')) %>">
                        <th scope="row"><%= _.escape(item.get('id') || '') %></th>
                        <td>
                            <button class='btn btn-sm btn-outline-success history-btn'><%= _.escape(item.get('docType') || '') %></button>
                        </td>
                        <td><%if (item.get('createDate')) { %><%= item.get('createDate') %><% } else { %>NULL<% } %></td>
                        <td><%if (item.get('reviewDate') > '1969-12-31 24') { %><%= item.get('reviewDate') %><% } else { %>Pending<% } %></td>
                        <td><%= _.escape(item.get('denialReason') || 'Pending') %></td>
                        <td><%if (item.get('patientSignedStatus')=='1') { %><%= 'Yes' %><% } else { %>No<% } %></td>
                        <td><%if (item.get('patientSignedTime') > '1969-12-31 24') { %><%= item.get('patientSignedTime') %><% } else { %>Pending<% } %></td>
                    </tr>
                    <% }); %>
                    </tbody>
                </table>
                <%= view.getPaginationHtml(page) %>
            </div>
            </div>
        </script>
        <div class="container-lg px-3 pt-3 historyHide d-none" id="historyTable">
            <div id="onsiteDocumentCollectionContainer" class="collectionContainer"><!-- rendered history template --></div>
        </div>

        <div class="questionnaire-div">
            <div class="div-sections-navbar col-md-12">
                  <a id="#section1">Section 3</a> 
                  <a id="#section2">Section 4</a> 
                  <a id="#section3">Section 5</a> 
                  <a id="#section4">Section 6</a>
                  <a id="#section5">Section 7</a>
                  <a id="#section6">Section 8</a>
                  <a id="#section7">Section 9</a>
                  <a id="#section8">Section 10</a>
                  <a id="#section9">Section 11</a>
                  <a id="#section10">Section 12</a>
                  <a id="#section11">Section 13</a>
                  <a id="#section12">Section 14</a>
                  <a id="#section13">Section 15</a>
                  <a id="#section14">Section 16</a>
            </div>
            <div class="questionnaire-html container mt-5">
                <?php //echo '<pre>';print_r($_SERVER); echo '</pre>'; ?>
                <div class="row d-flex justify-content-center align-items-center">
                    <div class="col-md-12">
                        <div class="modal fade" id="myModal" role="dialog" data-backdrop="static" data-keyboard="false">
                       <div class="modal-dialog modal-xl modal-dialog-centered">
                              <div class="modal-content">
                                <div class="modal-body">
                                  <p id="small_text"></p>
                                </div>
                                <div class="modal-footer">
                                    <div style="overflow:auto;" id="nextprevious">
                                        <div class="buttoncontainer"> 
                                            <button type="button" id="prevBtn" fdprocessedid="a93wy" style="display: inline;">Previous</button> 
                                            <button type="button" id="nextBtn" fdprocessedid="sja2b" style="padding-right: 43px; margin-left: 0px;">Next</button> 
                                        </div>
                                    </div>
                                </div>
                              </div>
                            </div>
                        </div>

                        <form id="regForm">
                            <div class="tab welcome" id="welcome" style="text-align: center;">
                                <div>
                                    <img style="width:250px;" src="<?php echo $actual_link.$webroot.'/portal/images/new_logo.png'; ?>">
                                    <h1 id="register">Welcome to OpenEmr</h1>
                                    <h3>Please fill out and submit this form.</h3>
                                </div>
                            </div> 
                            <div class="tab welcome" style="text-align: center;">
                                <div>
                                    <h1 id="register">Intake</h1>
                                    <h3>Visit 1</h3>
                                </div>
                            </div>                            
                            <div class="tab disclamer1">
                                <h1 id="register">Terms and Conditions</h1>
                                <label class="container"><span class="text">I agree to terms & conditions
                                        </span><input type="checkbox" id="terms_conditions" name="radio">
                                        <span class="checkmark"></span>
                                </label>

                            </div>
                            <!--div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Personal Cancer History</h1>
                                    <h3>Section 2</h3>
                                    <h4>4 Questions</h4>
                                </div>
                            </div>
                            <div class="tab demographics2">
                                <h1 id="register">Demographics</h1>
                                <h3>What is your preferred Gender pronous?</h3>
								
								<div class="label-container">
                                <label class="container col-lg-3 selected_radio"><span class="text">He/his
                                        </span><input type="radio" name="pronous" value="his" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-3"><span class="text">She/her
                                        </span><input type="radio" name="pronous" value="her">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-3"><span class="text">They/Their
                                        </span><input type="radio" name="pronous" value="their">
                                        <span class="checkmark_radio"></span>
                                </label>
								</div>

                            </div>

                            <div class="tab demographics3">
                                <h1 id="register">Demographics</h1>
                                <h3>What is your Date of Birth?</h3>
								<div class="label-container">
                                <p class="columu col-lg-3"><input type="text" class="datepicker1" placeholder="Birth Month"  name="birth_month" autocomplete="off"></p>
                                <p class="columu col-lg-3"><input type="text" class="datepicker2" placeholder="Birth Day"  name="birth_day" autocomplete="off"></p>
                                <p class="columu col-lg-3"><input type="text" class="datepicker3" placeholder="Birth Year" name="birth_year" autocomplete="off"></p>
								</div>
                            </div>

                            <div class="tab demographics4">
                                <h1 id="register">Demographics</h1>
                                <h3>What Race do you identify most with?</h3>
                                <label class="container selected_radio"><span class="text">White/Caucasian
                                        </span><input type="radio" name="race" value="white_caucasian" checked>
                                        <span class="checkmark_radio"></span>
                                </label>

                                <label class="container"><span class="text">Black/African descent
                                        </span><input type="radio" name="race" value="black_african">
                                        <span class="checkmark_radio"></span>
                                </label>

                                <label class="container"><span class="text">Asian/Pacific Islander
                                        </span><input type="radio" name="race" value="asian_pacific">
                                        <span class="checkmark_radio"></span>
                                </label>

                                <label class="container"><span class="text">Native American
                                        </span><input type="radio" name="race" value="native_american">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Other/Mixed: Specify
                                        </span><input type="radio" name="race" value="other_mixed">
                                        <span class="checkmark_radio"></span>
                                </label>
                            </div>
                            <div class="tab demographics5">
                                <h1 id="register">Demographics</h1>
                                <h3>Are you of Hispanic descent on either or both sides of your family?</h3>
								<div class="label-container">
                                <label class="container col-lg-5 selected_radio"><span class="text">Hispanic
                                        </span><input type="radio" name="hispanic" value="yes" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-5"><span class="text">Non-Hispanic
                                        </span><input type="radio" name="hispanic" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
								</div>
                            </div-->

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Personal Cancer History</h1>
                                    <h3>Section 3</h3>
                                    <h4>7 Questions</h4>
                                </div>
                            </div>

                            <div class="tab personal_cancer_history_1">
                                <h1 id="register">Have you ever had cancer?</h1>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="cancer" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="cancer" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab personal_cancer_tab personal_cancer_history_2">
                                <h1 id="register">How many different cancers have you had?</h1>
                                <i class="red instruction">instructions</i><i class="instruction">: Choose as many that apply to you from the dropdown list below. There are more than 100 different cancers, so it is possible that you had something that is not on the list. You will have a chance to specify that on the next page.</i>
                                <h3>Count how many cancers you had in your life until now, and enter it below</h3>
                                <p><input type="textbox" class="numeric" name="different_cancers" value="1"></p>
                            </div>
                            <div class="tab personal_cancer_tab personal_cancer_history_3">
                                <h1 id="register">What type of cancer you had?</h1>
                                <h3>Choose as many that apply from the dowpdown list below</h3>
                                <div class="">
                                    
                                    <div class="row d-flex justify-content-center mt-100">
                                        <div class="col-md-6">
                                            <?php if($patient_sex == 'male'){ ?> 
                                                <select class="choices-multiple-remove-button1" id="cancer_multiple_choices" placeholder="Select cancer type" multiple>
                                                    <option value="adrenal">Adrenal Gland (started in adrenal gland, did not metastasize there; eg. adrenocortical carcinoma)</option>
                                                    <option value="bladder_ueteral">Bladder or ureteral (tube connecting kidney to bladder)</option>
                                                    <option value="brain">Brain(started in the brain, did not metastasize there; eg: glioblastoma)</option>
                                                    <option value="breast">Breast</option>
                                                    <option value="colorectal">Colorectal</option>
                                                    <option value="esophageal">Esophageal</option>
                                                    <option value="gbduct">Gallbladder or Bile Duct (eg. cholangiocarcinoma)</option>
                                                    <option value="h_n">Head & Neck (other than brain or skin)</option>
                                                    <option value="kidney">Kidney (Renal)</option>
                                                    <option value="leukemia">Leukemia (Blood / Bone Marrow)</option>
                                                    <option value="lymphoma">Lymphoma</option>
                                                    <option value="liver">Liver (started in brain, did not metastasize there; eg. hepatocellular carcinoma)</option>
                                                    <option value="lung">Lung (started in lung, did not metastasize there)</option>
                                                    <option value="melanoma">Melanoma</option>
                                                    <option value="non_melanoma">Non-Melanoma Skin Cancer (eg. basal cell or squamous cell carcinoma)</option>
                                                    <option value="pancreatic">Pancreas</option>
                                                    <option value="prostate">Prostate</option>
                                                    <option value="sarcoma">Sarcoma (soft tissue or bone [osteosarcoma])</option>
                                                    <option value="stomach">Stomach (Gastric)</option>
                                                    <option value="testicular">Testicular</option>
                                                    <option value="thyroid">Thyroid</option>
                                                </select> 
                                            <?php }else if($patient_sex == 'female'){ ?>
                                                <select class="choices-multiple-remove-button1" id="cancer_multiple_choices" placeholder="Select cancer type" multiple>
                                                    <option value="adrenal">Adrenal Gland (started in adrenal gland, did not metastasize there; eg. adrenocortical carcinoma)</option>
                                                    <option value="bladder_ueteral">Bladder or ureter (tube connecting kidney to bladder)</option>
                                                    <option value="brain">Brain(started in the brain, did not metastasize there. eg: “glioblastoma”)</option>
                                                    <option value="breast">Breast</option>
                                                    <option value="cervical">Cervical</option>
                                                    <option value="colorectal">Colorectal</option>
                                                    <option value="esophageal">Esophageal</option>
                                                    <option value="gbduct">Gallbladder or Bile Duct (eg. cholangiocarcinoma)</option>
                                                    <option value="h_n">Head & Neck (includes cancers of the tongue, nasal or oral cavity [nasopharynx/oropharynx], or voice box [larynx])</option>
                                                    <option value="kidney">Kidney (Renal)</option>
                                                    <option value="leukemia">Leukemia (Blood / Bone Marrow)</option>
                                                    <option value="lymphoma">Lymphoma</option>
                                                    <option value="liver">Liver (started in Liver, did not metastasize there, eg: “hepatocellular carcinoma”)</option>
                                                    <option value="lung">Lung (primary lung cancer, not a cancer that started elsewhere and spread to the lung)</option>
                                                    <option value="melanoma">Melanoma</option>
                                                    <option value="non_melanoma">Non-Melanoma Skin Cancer (eg: squamous cell or basal cell carcinoma)</option>
                                                    <option value="ovary_fallopian">Ovary or fallopian tube</option>
                                                    <option value="pancreatic">Pancreas</option>
                                                    <option value="sarcoma">Sarcoma (soft tissue, eg. leiomyosarcoma/rhabdomyosarcoma or bone  [osteosarcoma])</option>
                                                    <option value="stomach">Stomach (Gastric)</option>
                                                    <option value="thyroid">Thyroid</option>
                                                    <option value="uterus">Uterus (eg: Endometrial)</option>
                                                </select>
                                            <?php } ?>
                                        </div>
                                        <div class="col-lg-6 col-half-offset" id="p5" style="position: relative;">
                                            <label style="height: 52px;!important" class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="other">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input placeholder="Other Cancer" type="textbox" name="other_text"></p>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="tab personal_cancer_history_4">
                                <h1 id="register">What markers did your breast cancer have?</h1>
                                <h3></h3>
                                <h4>Her2 Status</h4>
                                <label class="container"><span class="text">Her2 positive
                                        </span><input type="radio" name="hr2_status" value="Her2 positive" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Her2 negative
                                        </span><input type="radio" name="hr2_status" value="Her2 negative">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Her2 status unknown
                                        </span><input type="radio" name="hr2_status" value="Her2 status unknown">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <p>Hint: if you treated with Herceptin, then you were Her2 positive.</p>
                            </div>

                            <div class="tab personal_cancer_history_5">
                                <h1 id="register">What marker did your cancer have?</h1>
                                <h3></h3>
                                <label class="container"><span class="text">Hormone receptor (ER and/or PR) positive
                                        </span><input type="radio" name="hr_status" value="Hormone receptor positive" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Hormone receptor negative
                                        </span><input type="radio" name="hr_status" value="Hormone receptor negative">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Unknown hormone status
                                        </span><input type="radio" name="hr_status" value="Unknown hormone status">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <p>Hint: if you were given endocrine therapy (hormone treatments), then your breast cancer was hormone receptor positive.</p>
                            </div>

                            <div class="tab initially_diagnosed_tab personal_cancer_history_6">
                                <h1 id="register">Tell us more about your cancers(s)</h1>
                                <h3></h3>
                                <div class="content_div">
                                    <div class="row content_row">
                                        <div class="col-lg-4" id="p1">
                                            <span class="small_font">Cancer Type</span>
                                            <input type="textbox" placeholder="Pre-Populate from previous question" name="pre_populate[]">
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <span class="small_font">Age when diagnosed</span>
                                            <input type="textbox" class="numeric" placeholder="eg 23" name="age_diagnosed[]">
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <span class="small_font">Finished treatment?</span>
                                            <select class="form-select" id="finished_treatment" name="finished_treatment[]" style="height: 48px;">
                                                <option value="">Please choose</option>
                                                <option value="yes">Yes</option>
                                                <option value="no">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- <p><input type="text" class="numeric" placeholder="AgeDx" name="age_dx"></p> -->
                            </div>
                            
                            <div class="tab initially_diagnosed_tab personal_cancer_pre_cancerous">
                                <h1 id="register">Have you had any of the following pre-cancerous conditions?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Barrett’s Esophagus
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_barretts_esophagus">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Gastric Dysplasia
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_gastric _dysplasia">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Advanced adenoma (colon polyp with precancerous cells)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_advanced_adenoma">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Pancreatic Intraepithelial Neoplasia (PanIN)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_pancreatic">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Erythroplakia (red patch in the mouth or throat)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_erythroplakia">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Leukoplakia (white patch in the mouth or throat)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_leukoplakia">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Melanoma in situ (abnormal mole with non-invasive melanoma cells)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_melanoma">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Ductal carcinoma in situ (DCIS) of the breast
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_ductal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Lobular carcinoma in situ (LCIS) of the breast
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_lobular">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Atypical ductal or lobular hyperplasia (ADH/ALH) of the breast
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_atypical">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Borderline ovarian tumor
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_borderline">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Endometrial dysplasia of the uterus
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_endometrial">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">High-Grade Cervical Intraepithelial Neoplasia (HGCIN)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_cervical">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">High-Grade Prostate Intraepithelial Neoplasia (HGPIN)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_prostate">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Borderline ovarian tumor
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_borderline">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="precancerous_other">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" name="precancerous_other_text" placeholder="Other precancer, type here"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">None of the above
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="precancerous_none">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="tab personal_cancer_history_7">
                                <h1 id="register">Have you finished treatment for this cancer?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                        </span><input type="radio" name="finished_cancer" value="yes" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-5"><span class="text">No
                                        </span><input type="radio" name="finished_cancer" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
								</div>
                            </div> -->

                            <div class="tab introduction introduction_pmh" style="text-align: center;">
                                <div>
                                    <h1 id="register">Past Medical History</h1>
                                    <h3>Section 4</h3>
                                    <h4>25 Questions</h4>
                                </div>
                            </div>

                            <div class="tab vascular_container past_medical_history_1">
                                <h1 id="register">Do you, or have you had, any heart and/or vascular conditions?</h1>
                                <h3>Check all that apply</h3>
                                   <br/>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">High blood pressure (hypertension)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="high_bp_pressure">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Bad cholesterol (dyslipidemia or hypercholesterolemia)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="bad_cholesterol">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Heart attack (myocardial infarct or MI)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="heart_attack_vascular">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p4">
                                        <label class="container"><span class="text">Cardiac chest pain without heart attack (myocardial ischemia)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="cardiac_chest">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Cardiomyopathy (CHF/congestive heart failure)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="cardiomyopathy_vascular">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Cardiac arrhythmia
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="cardiac_arrhythmia_vascular">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Pain in your calves when you walk due to poor circulation (claudication)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="pain_calves">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I dont't have heart and/or vascular condition
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_heart">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" name="something_not_on_list_text" placeholder="Other hear / vaacular condition:"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab neurological_container past_medical_history_2">
                                <h1 id="register">Do you, or have you had, any neurological conditions?</h1>
                                <h3>Check all that apply to indicate the surgery/ies you have had.</h3>
                                   <br/>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Headache
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="neurological_headache">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Stroke (CVA/cerebrovascular accident)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="neurological_stroke">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Mini-stroke (TIA/transient ischemic attack)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="neurological_mini_stroke">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p4">
                                        <label class="container"><span class="text">Difficulty hearing
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="difficulty_hearing">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="vision_difficulties">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Vision difficulties" name="vision_difficulties_text"></p>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p4">
                                        <label class="container"><span class="text">I don't have neurological condition
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_neurological">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="neurological_something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other neurological condition" name="neurological_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab past_medical_history_headache">
                                <h1 id="register">Which type of headache?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Migraine
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="headache_migraine">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Tension
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="headache_tension">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="headache_something">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Something else" name="headache_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab pulmonary_container past_medical_history_3">
                                <h1 id="register">Do you, or have you had, any pulmonary (lung) conditions?</h1>
                                <h3>Check all that apply</h3>
                                   <br/>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">COPD (chronic obstructive pulmonary disease)
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="pulmonary_chronic">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Asthma
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="pulmonary_asthma">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">I don't have pulmonary (lung) condition
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_pulmonary">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>

                                    <div class="col-lg-6 col-half-offset" id="p3" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="pulmonary_something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other lung condition" name="pulmonary_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>


                            <div class="tab past_medical_history_pulmonary">
                                <h1 id="register">Which type of COPD?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Emphysema
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="pulmonary_emphysema">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Chronic Bronchitis
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="pulmonary_chronic_bronchitis">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">I’m not sure
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="pulmonary_i_m_not_sure">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab gastrointestinal_container past_medical_history_4">
                                <h1 id="register">Do you, or have you had, any gastrointestinal (GI) conditions?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">GERD/gastroesophageal reflux disease
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="gastroesophageal_reflux">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Barrett’s esophagus
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="barretts_esophagus">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p3">
                                        <label class="container"><span class="text">IBD/inflammatory bowel disease
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="inflammatory_bowel_disease">
                                            <span class="checkmark"></span>
                                            
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p4">
                                        <label class="container"><span class="text">IBS/irritable bowel syndrome 
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="irritable_bowel_syndrome">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">NASH or NAFLD/nonalcoholic steatohepatitis or fatty liver disease
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="nonalcoholic_steatohepatitis">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Alcoholic hepatitis
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="alcoholic_hepatitis">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Viral hepatitis
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="viral_hepatitis">
                                            <span class="checkmark"></span>
                                            
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Cirrhosis
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="cirrhosis">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Pancreatitis
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="pancreatitis">
                                            <span class="checkmark"></span>
                                            
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">H. Pylori Infection
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="pylori_infection">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Colon polyp(s)
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="colon_polyp">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">I don't have GI conditions
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_GI">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="gastrointestinal_something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other GI conditions" name="gastrointestinal_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab past_medical_history_ibd_inflammatory">
                                <h1 id="register">Which type of IBD/inflammatory bowel disease?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Ulcerative colitis (UC)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="inflammatory_ulcerative">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Crohn's Disease
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="inflammatory_crohn">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">I’m not sure
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="inflammatory_i_m_not_sure">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab past_medical_history_hepatitis">
                                <h1 id="register">Which type of Viral hepatitis?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Hep A
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hepatitis_hep_a">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Hep B
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hepatitis_hep_b">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Hep C
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hepatitis_hep_c">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Hep D
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hepatitis_hep_d">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Hep E
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hepatitis_hep_e">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">I’m not sure
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="hepatitis_i_m_not_sure">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab past_medical_history_pancreatitis">
                                <h1 id="register">Which type of Pancreatitis?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Chronic
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="pancreatitis_chronic">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Acute
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="pancreatitis_acute">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">I’m not sure
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="pancreatitis_chronic_i_m_not_sure">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="tab past_medical_history_pylori">
                                <h1 id="register">Have you been you treated for your H. Pylori infection?</h1>
                                <h3></h3>
                                <div class="label-container yes_no_container">
                                <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                        </span><input type="radio" name="treated_pylori" value="yes" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-5"><span class="text">No
                                        </span><input type="radio" name="treated_pylori" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-5"><span class="text">No. I don't know
                                        </span><input type="radio" name="treated_pylori" value="i_dont_know">
                                        <span class="checkmark_radio"></span>
                                </label>
                                </div>
                            </div>
                            
                            <div class="tab past_medical_history_lifetime">
                                <h1 id="register">How many colon polyps have you had in your life time?</h1>
                                <h3></h3>
                                <p><input type="textbox" class="numeric not_include" name="how_many_colon" placeholder=""></p>
                            </div>

                            <div class="tab past_medical_history_polyps">
                                <h1 id="register">Tell us what kind of polyps you have had:</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">adenomatous (tubular, villous, or tubulovillous)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="polyps_adenomatous">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">hamartomatous
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="polyps_hamartomatous">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">hyperplastic
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="polyps_hyperplastic">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">None
                                            </span><input type="checkbox" class="not_include" id="hpv_subtype_testing" name="polyps_none">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p4" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="other_kinds_polyps">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other kinds of polyps, type here to specify" name="other_kinds_polyps_text"></p>
                                    </div>
                                    
                                </div>
                            </div>


                            <div class="tab urinary_container past_medical_history_5">
                                <h1 id="register">Do you, or have you had, any urinary conditions?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Chronic renal insufficiency
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="urinary_chronic_renal_insufficiency">
                                            <span class="checkmark"></span>
                                        </label>
                                        
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Have to get up at night to urinate (nocturia)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="get_up_at_night">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">Frequent urinary tract infections (UTIs)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="urinary_tract_infections">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">I don't have urinary conditions
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_urinary">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p4" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="urinary_something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other urinary cnditions" name="urinary_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab past_medical_history_required">
                                <h1 id="register">Do you required anaysis?</h1>
                                <h3></h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="required_analysis" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="required_analysis" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab dermatologic_container past_medical_history_6">
                                <h1 id="register">Do you, or have you had, any dermatologic (skin) conditions requiring medical care?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Psoriasis
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="dermatologic_psoriasis">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Eczema
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="dermatologic_eczema">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">I don't have dermatologic conditions
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_dermatologic">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p3" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="dermatologic_something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other skin conditions" name="dermatologic_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab musculoskeletal_container past_medical_history_7">
                                <h1 id="register">Do you, or have you had, any musculoskeletal or rheumatologic conditions?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Gout
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="musculoskeletal_gout">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Lupus
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="musculoskeletal_lupus">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">Rheumatoid arthritis
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="rheumatoid_arthritis">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">Psoriatic arthritis
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="psoriatic_arthritis">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="musculoskeletal_osteoarthritis">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Osteoarthritis (this is the most typical type of arthritis)" name="musculoskeletal_osteoarthritis_text"></p>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Fibromyalgia
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="musculoskeletal_fibromyalgia">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">I don't have musculoskeletal or rheumatologic conditions
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_musculoskeletal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">I don't have endocrine condition
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_endocrine">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>

                                    <div class="col-lg-6 col-half-offset" id="p3" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="musculoskeletal_something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other musculoskeletal or rheumatologic condition" name="musculoskeletal_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab endocrine_container past_medical_history_8">
                                <h1 id="register">Do you, or have you had, any endocrine (hormonal) conditions?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Diabetes
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="endocrine_diabetes">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Pre-diabetes
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="endocrine_pre_diabetes">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">Thyroid disease
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="endocrine_thyroid_disease">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">Adrenal problems
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include_agg" name="adrenal_problems">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">I don't have endocrine condition
                                            </span><input type="checkbox" id="hpv_subtype_testing" class="not_include" name="i_dont_have_endocrine">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other endocrine condition" name="endocrine_something_not_on_list_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab past_medical_history_thyroid_disease">
                                <h1 id="register">Which type of Thyroid disease?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Hyperthyroidism
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hyroid_hyperthyroidism">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Hypothyroidism
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hyroid_acute">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Hashimoto’s
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hyroid_hashimoto">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>

                                    <div class="col-lg-6 col-half-offset" id="p3" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="hyroid_something_else">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Something else" name="something_else_text"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab past_medical_history_adrenal_conditions">
                                <h1 id="register">Which type of Adrenal conditions?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Cushing’s disease (hyperactive adrenals)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="adrenal_conditions_cushing">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset " id="p3">
                                        <label class="container"><span class="text">Addison’s disease (hypoactive adrenals)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="adrenal_conditions_addison">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="adrenal_conditions_something_else">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Something else" name="adrenal_conditions_something_else_text"></p>
                                    </div>
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="adrenal_conditions_something_not">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Something not on the list" name="adrenal_conditions_something_not_text"></p>
                                    </div>
                                </div>
                            </div>

                            <?php if($patient_sex == 'female'){ ?>
                            <div class="tab gynecologic_container past_medical_history_9">
                                <h1 id="register">Do you, or have you had, any gynecologic conditions?</h1>
                                <h3>Check all tha apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Heavy periods (menorrhagia)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="heavy_periods">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Too frequent periods (metrorrhagia)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="too_frequent_periods">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">Exceptionally painful periods (dysmenorrhea)
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="exceptionally_painful_periods">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6 col-half-offset  " id="p3">
                                        <label class="container"><span class="text">Uterine bleeding after menopause
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="bleeding_after_menopause">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Hot flashes associated with menopause
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="flashes_associated_menopause">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Night sweats associated with menopause
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="night_sweats_menopause">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">HPV (human papillomavirus virus) infection
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="human_papillomavirus_virus">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">High breast density
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="high_breast_density">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="gynecologic_something_not_on_list">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Something not on the list" name="gynecologic_something_not_on_list_text"></p>
                                    </div>
                                    
                                </div>
                            </div>
                            <?php } ?>
                            
                            <div class="tab past_medical_history_10">
                                <h1 id="register">How long have you had these conditions?</h1>
                                <h3></h3>

                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <span>How old were you when you were initially diagnosed?</span>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <span>How old were you when it stopped?</span>
                                    </div>
                                </div>
                                <div class="content_div">
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <span class="small_font">Cancer Type</span>
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <input type="textbox" class="numeric" placeholder="eg 23" name="age_cancer_diagnosed[]">
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <input type="textbox" class="numeric" placeholder="eg 23" name="finished_cancer_treatment[]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab personal_cancer_tab tabbingg past_medical_history_12">
                            	<h1 id="register">Which of the following have you experienced?</h1>
                            	<h3>Check all that apply</h3>
                            	<?php
                            	if($patient_sex == 'female'){ ?> 
                            	<div class="row">
                            	    <div class="col-lg-6" id="p1">
                            	        <label class="container"><span class="text">Abnormal mammogram
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_mammogram">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	    <div class="col-lg-6 col-half-offset" id="p2">
                            	        <label class="container"><span class="text">Abnormal pap smear
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_pap_smear">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	</div>
                            	<div class="row">
                            	    <div class="col-lg-6 col-half-offset  " id="p3">
                            	        <label class="container"><span class="text">Abnormal HPV test
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_test">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	    <div class="col-lg-6 col-half-offset  " id="p5">
                            	        <label class="container"><span class="text">Abnormal colonoscopy
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_colonoscopy">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	</div>
                            	<div class="row">
                            	    <div class="col-lg-6" id="p1">
                            	        <label class="container"><span class="text">Abnormal endoscopy (any type of scope besides colonoscopy)
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_endoscopy">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	    <div class="col-lg-6" id="p1">
                            	        <label class="container"><span class="text">Abnormal mole
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_mole">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	</div>
                            	<div class="row">
                            	    <div class="col-lg-6 col-half-offset" id="p2">
                            	        <label class="container"><span class="text">None of the above
                            	            </span><input type="checkbox" id="terms_conditions" class="not_include" name="none_of_above">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            
                            	    <div class="col-lg-6" id="p1" style="position:relative;">
                            	        <label class="container"><span class="text">
                            	            </span><input type="checkbox" id="terms_conditions" name="other_experience">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	        <p class="other_p"><input type="text" placeholder="Test that you want to tell us about?"  name="other_experience_text"></p>
                            	    </div>
                            	</div>
                            	<?php } ?>
                            
                            	<?php if($patient_sex == 'male'){ ?> 
                            	<div class="row">
                            	    <div class="col-lg-6" id="p1">
                            	        <label class="container"><span class="text">Abnormal prostate biopsy
                            	            </span><input type="checkbox" id="terms_conditions" name="experienced_abnormal_prostate">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	    <div class="col-lg-6 col-half-offset" id="p2">
                            	        <label class="container"><span class="text">Abnormal HPV test
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_test">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	</div>
                            	<div class="row">
                            	    <div class="col-lg-6 col-half-offset  " id="p3">
                            	        <label class="container"><span class="text">Abnormal PSA (a blood tumor marker for prostate cancer)
                            	            </span><input type="checkbox" id="terms_conditions" name="experienced_psa">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	    <div class="col-lg-6 col-half-offset  " id="p5">
                            	        <label class="container"><span class="text">Abnormal colonoscopy
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_colonoscopy">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	</div>
                            	<div class="row">
                            	    <div class="col-lg-6" id="p1">
                            	        <label class="container"><span class="text">Abnormal endoscopy (any type of scope besides colonoscopy)
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_endoscopy">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	    <div class="col-lg-6" id="p1">
                            	        <label class="container"><span class="text">Abnormal mole
                            	            </span><input type="checkbox" id="terms_conditions" name="abnormal_mole">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            	</div>
                            	<div class="row">
                            	    <div class="col-lg-6 col-half-offset" id="p2">
                            	        <label class="container"><span class="text">None of the above
                            	            </span><input type="checkbox" id="terms_conditions" class="not_include" name="none_of_above">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	    </div>
                            
                            	    <div class="col-lg-6" id="p1" style="position:relative;">
                            	        <label class="container"><span class="text">
                            	            </span><input type="checkbox" id="terms_conditions" name="other_experience">
                            	            <span class="checkmark"></span>
                            	        </label>
                            	        <p class="other_p"><input type="text" placeholder="Test that you want to tell us about?"  name="other_experience_text"></p>
                            	    </div>
                            	</div>
                            	<?php } ?>
                            
                            </div>
                            
                            <div class="tab past_medical_history_abnormailties">
                                <h1 id="register">What were the abnormailties in your mammogram?</h1>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <span class="small_font">Describe the abnormalities</span>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <span class="small_font">Date it occured</span>
                                    </div>
                                </div>
                                <div class="content_div">
                                    <div class="row content_row">
                                        <div class="col-lg-3" id="p1">
                                            <input type="textbox" placeholder="abnormality" name="abnormality[]">
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <input type="date" name="date_occured[]">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <button type="button" class="abnormailties_add_row blue_button">+ Add Row</button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab past_medical_history_what_abnormal">
                                <h1 id="register">What was abnormal?</h1>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">HPV 16
                                            </span><input type="checkbox" id="terms_conditions" name="abnormal_hpv16">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">HPV 18
                                            </span><input type="checkbox" id="terms_conditions" name="abnormal_hpv18">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other_abnormal_hpv">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="text" placeholder="some other form of HPV"  name="other_abnormal_hpv_text"></p>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I’m not sure 
                                            </span><input type="checkbox" id="terms_conditions" class="not_include" name="abnormal_not_include">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab past_medical_history_aggregate">
                                <h1 id="register">How long have you had these conditions?</h1>
                                <h3></h3>

                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <span>Month / Year</span>
                                    </div>
                                </div>
                                <div class="content_div">
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <span class="small_font">Cancer Type</span>
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <input type="textbox" class="numeric" placeholder="eg 23" name="age_cancer_diagnosed[]">
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <input type="textbox" class="numeric" placeholder="eg 23" name="finished_cancer_treatment[]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab past_medical_history_14">
                                <h1 id="register">What is your current weight?</h1>
                                <h3>Enter value in pounds or click on the circular icon to change to kilograms:</h3>
                                <div class="row">
                                    <div class="col-lg-1" id="p1">
                                        <select class="form-select" id="select_weight" name="weight" style="height: 48px;">
                                            <option value="lb" selected>LB</option>
                                            <option value="kg">KG</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        
                                        <p><input type="text" placeholder="Weight" name="weight_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab past_medical_history_15">
                                <h1 id="register">How tall are you?</h1>
                                <h3>Enter values in feet and inches, or click on the circular icon to change to centimeters:</h3>
                                <div class="row r_inch">
                                    <div class="col-lg-12" id="p1">
                                        <p><input type="text" class="numeric" placeholder="Feet" name="height_feet">
                                        </p>
                                    </div>
                                    <div class="col-lg-12" id="p1">
                                        <p><input type="text" class="numeric" placeholder="Inch" name="height_inch">
                                            <span class="changeTypeInches">
                                                <span class="type">Convert</span>
                                                <i class="fa fa-retweet"></i>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="row r_height">
                                    <div class="col-lg-12 col_cm" id="p1">
                                        <p><input type="text" placeholder="CM" name="cm" readonly>
                                            <span class="changeTypeCm">
                                                <span class="type">CM</span>
                                                <i class="fa fa-retweet"></i>
                                            </span>
                                        </p>
                                    </div>
                                    
                                    <div class="col-lg-12 col_meter" id="p1">
                                        <p><input type="text" placeholder="Meter" name="height_m" readonly>
                                            <span class="changeTypeM">
                                                <span class="type">Meter</span>
                                                <i class="fa fa-retweet"></i>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <div class="tab past_medical_history_16">
                                <h1 id="register">Estimate your weight at the age 18</h1>
                                <h3>Enter value in lbs, or click on the circular icon to change to kilogram:</h3>
                                <div class="row">
                                    <div class="col-lg-1" id="p1">
                                        <select class="form-select" id="select_weight" name="select_weight_at_age_18" style="height: 48px;">
                                            <option value="lb" selected>LB</option>
                                            <option value="kg">KG</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" class="numeric" placeholder="" name="weight_at_age_18"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Genetic Testing History</h1>
                                    <h3>Section 5</h3>
                                    <h4>5 Questions</h4>
                                </div>
                            </div>

                            <div class="tab genetic_testing_history_1">
                                <h1 id="register">Have you ever had genetic testing to assess your risk of cancer?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="genetic_testing" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="genetic_testing" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>


                            <div class="tab genetic_testing_history_2">
                                <h1 id="register">What were your results?</h1>
                                <h3>Indicate what your report showed below.</h3>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label class="container"><span class="text">One or more deleterious mutations were found
                                            </span><input type="checkbox" id="terms_conditions" name="deleterious">
                                            <span class="checkmark"></span>
                                        </label> 
                                        <label class="container"><span class="text">One or more variants of undetermined significance (VUS) were found
                                            </span><input type="checkbox" id="terms_conditions" name="undetermined">
                                            <span class="checkmark"></span>
                                        </label> 
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="container"><span class="text">Negative, nothing was found
                                            </span><input type="checkbox" id="terms_conditions" name="negative">
                                            <span class="checkmark"></span>
                                        </label> 
                                        <label class="container"><span class="text">I don't remember
                                            </span><input type="checkbox" id="terms_conditions" name="remember">
                                            <span class="checkmark"></span>
                                        </label> 
                                    </div>
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other_genetic_test">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="text" placeholder="Other genetic test, specify here"  name="other_genetic_test_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab genetic_testing_history_3">
                                <h1 id="register">What genes were involved?</h1>
                                <h3>Choose as many that apply from the dowpdown list below</h3>
                                <div class="row">
								    <div class="label-container">
                                        <div class="col-lg-12" id="p1">
                                            
                                            <select class="choices-multiple-remove-button" placeholder="Select Gene" multiple>
                                                <option value="ATM">ATM</option><option value="BAP">BAP</option>
                                                <option value="BARD1">BARD1</option>
                                                <option value="BRCA1">BRCA1</option>
                                                <option value="BRCA2">BRCA2</option>
                                                <option value="BRIP1">BRIP1</option>
                                                <option value="CDH1">CDH1</option>
                                                <option value="CDKN2A/p16">CDKN2A/p16</option>
                                                <option value="CHEK2">CHEK2</option>
                                                <option value="EPCAM">EPCAM</option>
                                                <option value="MEN1">MEN1</option>
                                                <option value="MET">MET</option>
                                                <option value="MLH1">MLH1</option>
                                                <option value="MSH2">MSH2</option>
                                                <option value="MSH3">MSH3</option>
                                                <option value="MSH6">MSH6</option>
                                                <option value="MUTYH/MYH">MUTYH/MYH</option>
                                                <option value="NF1">NF1</option>
                                                <option value="NF2">NF2</option>
                                                <option value="PALB2">PALB2</option>
                                                <option value="PMS2">PMS2</option>
                                                <option value="PTEN">PTEN</option>
                                                <option value="RAD51C">RAD51C</option>
                                                <option value="RAD51D">RAD51D</option>
                                                <option value="RET">RET</option>
                                                <option value="SDHB">SDHB</option>
                                                <option value="SDHC">SDHC</option>
                                                <option value="SDHD">SDHD</option>
                                                <option value="SMAD4">SMAD4</option>
                                                <option value="STK11/LKB1">STK11/LKB1</option>
                                                <option value="TP53/p53">TP53/p53</option>
                                                <option value="VHL">VHL</option>
                                                <option value="gene_dont_remember">Don't remember</option>
                                            </select>
                                        </div>
									</div>
                                </div>
                            </div>

                            <div class="tab lab_tab personal_cancer_tab genetic_testing_history_4">
                                <h1 id="register">What lab(s) did your testing?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Ambry
                                            </span><input type="checkbox" id="terms_conditions" name="ambry">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">GeneDx
                                            </span><input type="checkbox" id="terms_conditions" name="genedx">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Invitae
                                            </span><input type="checkbox" id="terms_conditions" name="invitae">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">LabCorp
                                            </span><input type="checkbox" id="terms_conditions" name="labcorp">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Myriad
                                            </span><input type="checkbox" id="terms_conditions" name="myriad">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Natera
                                            </span><input type="checkbox" id="terms_conditions" name="natera">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Quest
                                            </span><input type="checkbox" id="terms_conditions" name="quest">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">23andMe
                                            </span><input type="checkbox" id="terms_conditions" name="23andme">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1" style="position:relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="text" placeholder="Other"  name="other_text"></p>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Not Sure
                                            </span><input type="checkbox" id="terms_conditions" class="not_include" name="not_sure">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab genetic_testing_history_5">
                                <h1 id="register">Did you receive genetic counseling regarding your results?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                <label class="container col-lg-5"><span class="text">Yes
                                        </span><input type="radio" name="genetic_risk" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container  col-lg-5"><span class="text">No
                                        </span><input type="radio" name="genetic_risk" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
								</div>
                            </div>

                            <div class="tab introduction past_surgical_history_intro" style="text-align: center;">
                                <div>
                                    <h1 id="register">Past Surgical History</h1>
                                    <h3>Section 6</h3>
                                    <h4>11 Questions</h4>
                                </div>
                            </div>

                            <div class="tab past_surgrical_history_1">
                                <h1 id="register">Have you ever had a biopsy for any reason?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                <label class="container col-lg-5"><span class="text">Yes
                                        </span><input type="radio" name="biopsy" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-5"><span class="text">No
                                        </span><input type="radio" name="biopsy" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
								</div>
                            </div>

                            <div class="tab biopsy_tab personal_cancer_tab past_surgrical_history_2">
                                <h1 id="register">Where have you had a biopsy?</h1>
                                <h3>Check all that apply</h3>
                                <?php if($patient_sex == 'male'){ ?> 
                                <div class="div_biospy">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Breast
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_breast">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Prostate
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_prostate">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Lung
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_lung">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Colon or Rectum
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_colorectum">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Esophagus
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_esophagus">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Stomach
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_stomach">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Liver
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_liver">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Pancreas
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_pancreas">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Gallbladder or Bile Duct
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_gallbladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Thyroid
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_thyroid">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Adrenal Gland
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_adrenal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Kidney
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_kidney">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Bladder
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_bladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Skin
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_skin">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Bone Marrow
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_bm">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Lymph Node
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_ln">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-lg-3" id="p1" style="position:relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_oth">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input placeholder="Other" type="textbox" name="bisopy_oth_text"></p>
                                        </div>
                                        
                                    </div>
                                </div>
                                <?php }else{ ?>
                                <div class="div_biospy">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Breast
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_breast">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Cervix
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_cervix">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Uterus
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_uterus">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Lung
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_lung">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Colon or Rectum
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_colorectum">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Esophagus
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_esophagus">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Stomach
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_stomach">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Liver
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_liver">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Pancreas
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_pancreas">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Gallbladder or Bile Duct
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_gallbladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Thyroid
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_thyroid">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Adrenal Gland
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_adrenal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Kidney
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_kidney">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Bladder
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_bladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Skin
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_skin">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Bone Marrow
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_bm">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Lymph Node
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_ln">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1" style="position:relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="bisopy_oth">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input placeholder="Other" type="textbox" name="bisopy_oth_text"></p>
                                        </div>
                                        
                                    </div>
                                </div>
                                <?php } ?>
                            </div>

                            <div class="tab times_tab past_surgrical_history_3">
                                <h1 id="register">How many times have you had this kind of biopsy?</h1>
                                <h3></h3>
                                <div class="times_tab_div">
                                    <div class="content_div">
                                        <div class="row content_row">
                                            <div class="col-lg-3" id="p1">
                                                <span class="small_font">Type of biopsy</span>
                                                <input type="textbox" placeholder="Please enter each type of biopsy" name="type_of_biospy[]">
                                            </div>
                                            <div class="col-lg-3" id="p1">
                                                <span class="small_font">Quantity</span>
                                                <input type="textbox" class="numeric" placeholder="0" value="0" name="biospy_qty[]">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4" id="p1">
                                            <button type="button" class="biospy_add_row blue_button">+ Add Row</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab years_tab past_surgrical_history_4">
                                <h1 id="register">What year(s) were this kind of biopsy performed?</h1>
                                <h3></h3>
                                <div class="biopsy_performed_div">
                                    <div class="content_div">
                                        <div class="row content_row">
                                            <div class="col-lg-2" id="p1">
                                                <span class="small_font">Biopsy kind</span>
                                                <input type="textbox" placeholder="Enter type of biopsy done" name="biopsy_kind[]">
                                            </div>
                                            <div class="col-lg-2" id="p1">
                                                <span class="small_font">Year performed</span>
                                                <input type="textbox" class="datepicker" placeholder="YYYY-MM-DD" name="year_performed[]">
                                            </div>
                                            <div class="col-lg-2" id="p1">
                                                <span class="small_font">On which side?</span>
                                                <select class="form-select" id="on_which_side" name="on_which_side[]" style="height: 48px;">
                                                    <option value="" >Please Select</option>
                                                    <option value="Right" >Right</option>
                                                    <option value="Left" >Left</option>
                                                    <option value="Both sides">Both sides</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3" id="p1">
                                                <span class="small_font">Abnormalities found?</span>
                                                <select class="form-select" id="on_which_side" name="abnormalities_found[]" style="height: 48px;">
                                                    <option value="">Please Select</option>
                                                    <option value="No it was benign">No; it was benign</option>
                                                    <option value="Yes showed invasive cancer">Yes; showed invasive cancer</option>
                                                    <option value="Yes only precancerous changes">Yes; only precancerous changes</option>
                                                    <option value="Yes but neither cancer nor precancer">Yes; but neither cancer nor precancer</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4" id="p1">
                                            <button type="button" class="biospy_performed_add_row blue_button">+ Add Row</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        
                            <div class="tab past_surgrical_history_5">
                                <h1 id="register">On what side was it performed in the year ___?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="select_unit" name="select_performed" style="height: 48px;">
                                            <option value="" selected>Select Unit</option>
                                            <option value="right">Right</option>
                                            <option value="left">Left</option>
                                            <option value="both_sides">BothSides</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab past_surgrical_history_6">
                                <h1 id="register">Were any abnormalities found?</h1>
                                <h3></h3>
                                <label class="container"><span class="text">No, it was completely benign
                                        </span><input type="radio" name="surgery_abnormalities" value="no_benign">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Yes, and it showed an invasive cancer
                                        </span><input type="radio" name="surgery_abnormalities" value="yes_invasive">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Yes, but it showed only precancerous changes
                                        </span><input type="radio" name="surgery_abnormalities" value="yes_precancerous">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Yes, but it was neither cancer nor precancer
                                        </span><input type="radio" name="surgery_abnormalities" value="yes_neither">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <p><input type="textbox" placeholder="Please describe" name="abnormalities_text"></p>
                            </div>


                            
                            <div class="tab past_surgrical_history_7">
                                <h1 id="register">Have you ever had a surgery forany reason?</h1>
                                <h3></h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="surgery_forany" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="surgery_forany" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>


                            </div>


                            <div class="tab personal_cancer_tab tabbingg past_surgrical_history_8">
                                <h1 id="register">On what part(s) of your body have you had surgery?</h1>
                                <h3>Check all that apply</h3>
                                <?php if($patient_sex == 'femmale'){ ?> 
                                <div class="div_surgeries">
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Neurologic (brain, spinal cord, or peripheral nerves)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_neurologic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Eye (including muscles around the eye)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_eye">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">ENT (ear, nose, throat, including mouth, tongue, and larynx or voice box)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="urgeries_ent">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Digestive (esophagus, stomach, pancreas, liver, intestine, rectum)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_digestive">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Cardiothoracic (chest, heart or lungs)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_cardiothoracic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Breast</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_breast">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Gynecologic (vagina, cervix, uterus, ovaries, fallopian tubes)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_gynecologic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Urologic (kidney, bladder)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_urologic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Endocrine (thyroid, parathyroid, adrenals)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_endocrine">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Orthopedic (bones, joints, tendons, or ligaments)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_orthopedic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1" style="position: relative;">
                                            <label class="container"><span class="text"></span>
                                                </span><input type="checkbox" id="terms_conditions" name="urgeries_precancer">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input placeholder="Something else (you will have a chance to specify this later)" type="textbox" name="surgeries_something_else_text"></p>
                                        </div>
                                        
                                    </div>
                                </div>
                                <?php }else{ ?>
                                <div class="div_surgeries">
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Neurologic (brain, spinal cord, or peripheral nerves)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_neurologic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Eye (including muscles around the eye)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_eye">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">ENT (ear, nose, throat, including mouth, tongue, and larynx or voice box)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="urgeries_ent">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Digestive (esophagus, stomach, pancreas, liver, intestine, rectum)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_digestive">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Cardiothoracic (chest, heart or lungs)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_cardiothoracic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Breast (for men with male breast cancer only)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_breast">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Urologic (kidney, bladder, prostate, testicular)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_urologic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Endocrine (thyroid, parathyroid, adrenals)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_endocrine">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Orthopedic (bones, joints, tendons, or ligaments)</span>
                                                </span><input type="checkbox" id="terms_conditions" name="surgeries_orthopedic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1" style="position: relative;">
                                            <label class="container"><span class="text"></span>
                                                </span><input type="checkbox" id="terms_conditions" name="urgeries_precancer">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input placeholder="Something else (you will have a chance to specify this later)" type="textbox" name="surgeries_something_else_text"></p>
                                        </div>
                                        
                                    </div>
                                </div>
                                <?php } ?>
                            </div>

                            <div class="tab type_of_surgeries_container past_surgrical_history_9">
                                <h1 id="register">Tell us about your Digestive surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Esophagectomy (removal of part of the esophagus)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_esophagectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Hiatal Hernia Repair</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_hiatal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Lap Band, gastric bypass, or other bariatric surgery</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_lap">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Gastrectomy, total (all of stomach removed)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_gastrectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Gastrectomy, partial (part of stomach removed)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_gastrectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Hepatectomy (part of the liver removed)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_hepatectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Cholecystectomy (removal of the gallbladder)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_cholecystectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Pancreatectomy/Whipple (removal of pancreas)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_pancreatectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Colectomy, total (all of the large intestine removed)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_colectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Colectomy, partial (part of the large intestine removed)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_colectomy_partial">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_surgery_for_removal">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="If Other type of digestive surgery, specify" name="surgeries_other_digestive_sugery_text"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab type_of_urologic_surgeries">
                                <h1 id="register">Tell us about your Urologic surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <?php if($patient_sex == 'female'){ ?> 
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Nephrectomy (removal of all or part of a kidney)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_nephrectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Cystectomy (removal of all or part of the bladder)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_cystectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_surgery_for_removal">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other type of urologic surgery" name="surgeries_other_urologic_sugery_text"></p>
                                    </div>
                                </div>
                                <?php }else{ ?>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Nephrectomy (removal of all or part of a kidney)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_nephrectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Cystectomy (removal of all or part of the bladder)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_cystectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Prostatectomy (removal of the prostate)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_prostatectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Orchiectomy (removal of one or both testicles)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_orchiectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_surgery_for_removal">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other type of urologic surgery" name="surgeries_other_urologic_sugery_text"></p>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <?php //if($patient_sex == 'female'){ ?> 
                            <div class="tab type_of_gynelogic_surgeries">
                                <h1 id="register">Tell us about your Gynelogic surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Hysterectomy (removal of the whole uterus, not including ovaries)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_hysterectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Myomectomy (removal of only part of the uterus)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_myomectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Salpingo-oophrectomy (removal of one or both ovaries)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_salpingo">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_surgery_for_removal">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other type of gynecologic surgery" name="surgeries_other_gynecologic_sugery_text"></p>
                                    </div>
                                </div>
                            </div>
                            <?php //} ?>
                            <div class="tab type_of_breast_surgeries">
                                <h1 id="register">Tell us about your Breast surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1" style="    margin-top: 30px;">
                                        <label class="container"><span class="text">Lumpectomy (removal of part of the breast)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_lumpectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <span class="small_font">Mastectomy (removal of all of the breast)</span>
                                        <select class="form-select" id="surgeries_mastectomy" name="surgeries_mastectomy[]" style="height: 48px;">
                                                <option value="">Please choose</option>
                                                <option value="mastectomy_implant">Breast reconstruction after mastectomy using implant(s)</option>
                                                <option value="mastectomy_tissue">Breast reconstruction after mastectomy using natural tissue, e.g. TRAM or DIEP</option>
                                            </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_surgery_for_removal">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="If other type of breast surgery, specify" name="surgeries_other_breast_sugery_text"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab type_of_cardiothoracic_surgeries">
                                <h1 id="register">Tell us about your Cardiothoracic surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Coronary artery bypass graft, or CABG</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_coronary">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Angioplasty, atherectomy, placement of a coronary stent, or other form of percutaneous coronary intervention (PCI)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_angioplasty">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Placement of a pacemaker</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_pacemaker">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Pneumonectomy (removal of a full lung)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_pneumonectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Lobectomy or wedge resection (removal of part of a lung)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_lobectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_surgery_performed">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="If Other surgery performed on the heart, lungs, or chest, specify" name="surgeries_surgery_performed_text"></p>
                                    </div>
                                </div>
                            
                            </div>
                            
                            <div class="tab type_of_neurologic_surgeries">
                                <h1 id="register">Tell us about your Neurologic surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Brain surgery (craniotomy)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_brain">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Spinal surgery</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_spinal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Carpal tunnel release</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_carpal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_other neurologic_surgery">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other neurologic surgery, specify" name="surgeries_other neurologic_surgery_text"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab type_of_eye_surgeries">
                                <h1 id="register">Tell us about your Eye surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Cataract surgery</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_cataract">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Glaucoma surgery</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_glaucoma">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Lasik or other refractory procedure to correct or improve vision</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_lasik">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_other_eye_surgery">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other eye surgery, specify" name="surgeries_other_eye_surgery_text"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab type_of_ent_surgeries">
                                <h1 id="register">Tell us about your ENT surgery/ies</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Tonsillectomy/Adenoidectomy</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_tonsillectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Glossectomy (removal of part of the tongue)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_glossectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Laryngectomy (removal of the larynx</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_laryngectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_other_ent_surgery">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other ENT surgery, specify" name="surgeries_other_ent_surgery_text"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab type_of_orthopedic_surgeries">
                                <h1 id="register">Tell us about your orthopedic surgery/ies:</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <h4>Type of Surgery</h4>
                                    </div>
                                    <div class="col-lg-2" id="p1">
                                        <h4>Choose condition</h4>
                                    </div>
                                    <div class="col-lg-4" id="p1">
                                        <h4>Which bone, joint or extremity?</h4>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <span>1. Arthroscopic surgery (minimally invasive joint repair) </span>
                                    </div>
                                    <div class="col-lg-2" id="p1">
                                        <label class=""><span class="text"></span>
                                            <input type="checkbox" id="terms_conditions" name="condition1">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-4" id="p1">
                                        <select class="form-select" id="bone_joint" name="bone_joint[]" style="height: 48px;">
                                                <option value="" class="translatable">Please Select</option>
                                                <option value="mg" class="translatable">Right shoulder</option>
                                                <option value="ug" class="translatable">Left shoulder</option>
                                                <option value="mL" class="translatable">Right humerus or upper arm</option>
                                                <option value="puffs" class="translatable">Left humerus or upper arm</option>
                                                <option value="drops" class="translatable">Right ulna, radius, or forearm</option>
                                                <option value="tablets" class="translatable">Left ulna, radius, or forearm</option>
                                                <option value="right_wrist" class="translatable">Right wrist, hand, or finger</option>
                                                <option value="left_wrist" class="translatable">Left wrist, hand, or finger</option>
                                                <option value="right_hip" class="translatable">Right hip</option>
                                                <option value="left_hip" class="translatable">Left hip</option>
                                                <option value="right_knee" class="translatable">Right knee</option>
                                                <option value="left_knee" class="translatable">Left knee</option>
                                                <option value="right_femur" class="translatable">Right femur or thigh</option>
                                                <option value="left_femur" class="translatable">Left femur or thigh</option>
                                                <option value="right_tibia" class="translatable">Right tibia, fibula, or lower leg</option>
                                                <option value="left_tibia" class="translatable">Left tibia, fibula, or lower leg</option>
                                                <option value="right_ankle" class="translatable">Right ankle, foot, or toe</option>
                                                <option value="left_ankle" class="translatable">Left ankle, foot, or toe</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <span>2. Joint replacement (usually hip or knee)</span>
                                    </div>
                                    <div class="col-lg-2" id="p1">
                                        <label class=""><span class="text"></span>
                                            <input type="checkbox" id="terms_conditions" name="condition2">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-4" id="p1">
                                        <select class="form-select" id="bone_joint" name="bone_joint[]" style="height: 48px;">
                                                <option value="" class="translatable">Please Select</option>
                                                <option value="mg" class="translatable">Right shoulder</option>
                                                <option value="ug" class="translatable">Left shoulder</option>
                                                <option value="mL" class="translatable">Right humerus or upper arm</option>
                                                <option value="puffs" class="translatable">Left humerus or upper arm</option>
                                                <option value="drops" class="translatable">Right ulna, radius, or forearm</option>
                                                <option value="tablets" class="translatable">Left ulna, radius, or forearm</option>
                                                <option value="right_wrist" class="translatable">Right wrist, hand, or finger</option>
                                                <option value="left_wrist" class="translatable">Left wrist, hand, or finger</option>
                                                <option value="right_hip" class="translatable">Right hip</option>
                                                <option value="left_hip" class="translatable">Left hip</option>
                                                <option value="right_knee" class="translatable">Right knee</option>
                                                <option value="left_knee" class="translatable">Left knee</option>
                                                <option value="right_femur" class="translatable">Right femur or thigh</option>
                                                <option value="left_femur" class="translatable">Left femur or thigh</option>
                                                <option value="right_tibia" class="translatable">Right tibia, fibula, or lower leg</option>
                                                <option value="left_tibia" class="translatable">Left tibia, fibula, or lower leg</option>
                                                <option value="right_ankle" class="translatable">Right ankle, foot, or toe</option>
                                                <option value="left_ankle" class="translatable">Left ankle, foot, or toe</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <span>3. Open reduction, internal fixation (ORIF) of a fractured bone</span>
                                    </div>
                                    <div class="col-lg-2" id="p1">
                                        <label class=""><span class="text"></span>
                                            <input type="checkbox" id="terms_conditions" name="condition3">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-4" id="p1">
                                        <select class="form-select" id="bone_joint" name="bone_joint[]" style="height: 48px;">
                                                <option value="" class="translatable">Please Select</option>
                                                <option value="mg" class="translatable">Right shoulder</option>
                                                <option value="ug" class="translatable">Left shoulder</option>
                                                <option value="mL" class="translatable">Right humerus or upper arm</option>
                                                <option value="puffs" class="translatable">Left humerus or upper arm</option>
                                                <option value="drops" class="translatable">Right ulna, radius, or forearm</option>
                                                <option value="tablets" class="translatable">Left ulna, radius, or forearm</option>
                                                <option value="right_wrist" class="translatable">Right wrist, hand, or finger</option>
                                                <option value="left_wrist" class="translatable">Left wrist, hand, or finger</option>
                                                <option value="right_hip" class="translatable">Right hip</option>
                                                <option value="left_hip" class="translatable">Left hip</option>
                                                <option value="right_knee" class="translatable">Right knee</option>
                                                <option value="left_knee" class="translatable">Left knee</option>
                                                <option value="right_femur" class="translatable">Right femur or thigh</option>
                                                <option value="left_femur" class="translatable">Left femur or thigh</option>
                                                <option value="right_tibia" class="translatable">Right tibia, fibula, or lower leg</option>
                                                <option value="left_tibia" class="translatable">Left tibia, fibula, or lower leg</option>
                                                <option value="right_ankle" class="translatable">Right ankle, foot, or toe</option>
                                                <option value="left_ankle" class="translatable">Left ankle, foot, or toe</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <span>4. Surgical repair of a torn tendon or ligament, eg. rotator cuff or achilles tendon repair</span>
                                    </div>
                                    <div class="col-lg-2" id="p1">
                                        <label class=""><span class="text"></span>
                                            <input type="checkbox" id="terms_conditions" name="condition4">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-4" id="p1">
                                        <select class="form-select" id="bone_joint" name="bone_joint[]" style="height: 48px;">
                                                <option value="" class="translatable">Please Select</option>
                                                <option value="mg" class="translatable">Right shoulder</option>
                                                <option value="ug" class="translatable">Left shoulder</option>
                                                <option value="mL" class="translatable">Right humerus or upper arm</option>
                                                <option value="puffs" class="translatable">Left humerus or upper arm</option>
                                                <option value="drops" class="translatable">Right ulna, radius, or forearm</option>
                                                <option value="tablets" class="translatable">Left ulna, radius, or forearm</option>
                                                <option value="right_wrist" class="translatable">Right wrist, hand, or finger</option>
                                                <option value="left_wrist" class="translatable">Left wrist, hand, or finger</option>
                                                <option value="right_hip" class="translatable">Right hip</option>
                                                <option value="left_hip" class="translatable">Left hip</option>
                                                <option value="right_knee" class="translatable">Right knee</option>
                                                <option value="left_knee" class="translatable">Left knee</option>
                                                <option value="right_femur" class="translatable">Right femur or thigh</option>
                                                <option value="left_femur" class="translatable">Left femur or thigh</option>
                                                <option value="right_tibia" class="translatable">Right tibia, fibula, or lower leg</option>
                                                <option value="left_tibia" class="translatable">Left tibia, fibula, or lower leg</option>
                                                <option value="right_ankle" class="translatable">Right ankle, foot, or toe</option>
                                                <option value="left_ankle" class="translatable">Left ankle, foot, or toe</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_other_orthopedic_surgery">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other orthopedic surgery, specify" name="surgeries_other_orthopedic_surgery_text"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab type_of_endocrine_surgeries">
                                <h1 id="register">Tell us about your Endocrine surgery/ies</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Thyroidectomy (removal of the full thyroid)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_thyroidectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Thyroid lobectomy (removal of part of the thyroid)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_thyroid_lobectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Parathyroidectomy (removal of the parathyroid glands)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_parathyroidectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Adrenalectomy (removal of an entire adrenal gland)</span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_adrenalectomy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container" style="position:relative;"><span class="text"></span>
                                            </span><input type="checkbox" id="terms_conditions" name="surgeries_other_endocrine_surgery">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other endocrine surgery, specify" name="surgeries_other_endocrine_surgery_text"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab reasons_for_surgeries">
                                <h1 id="register">Tell us the reasons for your surgeries and when they were performed:</h1>
                                <h3></h3>

                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <b>Surgery</b>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <b>What was the reason for surgery?</b>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <b>MM/Year</b> 
                                    </div>
                                </div>
                                <div class="content_div">
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <span class="small_font">Cancer Type</span>
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <input type="textbox" class="numeric" placeholder="eg 23" name="reason_surgery[]">
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <input type="textbox" class="datepicker3" placeholder="MM/Year" name="mm_year[]">
                                        </div>
                                    </div>
                                </div>
                                
                                
                            </div>

                            
                            <div class="tab personal_cancer_tab past_surgrical_history_10">
                                <h1 id="register">What year(s) was/were this surgery performed?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <b>Surgery</b>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <b>Year</b>
                                    </div>
                                </div>
                                <div class="content_div">
                                    <p><input type="text" class="datepicker3" placeholder="Select Year" name="surgery_year" autocomplete="off"></p>
                                </div>
                            </div>
                            
                            <div class="tab past_surgrical_history_11">
                                <h1 id="register">On what side was it performed in the year ___?</h1>
                                <h3></h3>
								<div class="label-container">
                                <label class="container col-lg-3"><span class="text">Right
                                        </span><input type="radio" name="what_side_performed" value="right">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-3"><span class="text">Left
                                        </span><input type="radio" name="what_side_performed" value="left">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container col-lg-3"><span class="text">BothSides
                                        </span><input type="radio" name="what_side_performed" value="both_sides">
                                        <span class="checkmark_radio"></span>
                                </label>
								</div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Medications and Supplements</h1>
                                    <h3>Section 7</h3>
                                    <h4>13 Questions</h4>
                                </div>
                            </div>
                            

                            <div class="tab medications_and_supplements1">
                                <h1 id="register">Do you currently take any medications?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="medications" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="medications" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>
                            <div class="tab meds_list_dosage_tab medications_and_supplements2">
                                <h1 id="register">Medication List and dosage</h1>
                                <h3></h3>
                                <div class="content_div">
                                    <div class="row content_row">
                                        <div class="col-lg-2" id="p1">
                                            <span class="small_font">Medication Name</span>
                                            <input type="textbox" placeholder="name of medication" name="name_of_medication[]">
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <span class="small_font">Dose</span>
                                            <input type="textbox" class="numeric" placeholder="eg 250" name="med_dose[]">
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <span class="small_font">Units</span>
                                            <select class="form-select" id="meds_units" name="meds_units[]" style="height: 48px;">
                                                <option value="" class="translatable">Please Select</option>
                                                <option value="mg" class="translatable">mg</option>
                                                <option value="ug" class="translatable">ug</option>
                                                <option value="mL" class="translatable">mL</option>
                                                <option value="puffs" class="translatable">puffs</option>
                                                <option value="drops" class="translatable">drops</option>
                                                <option value="tablets" class="translatable">tablets</option>
                                                <option value="capsules" class="translatable">capsules</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <span class="small_font">How do you take it?</span>
                                            <select class="form-select" id="how_do_you_take" name="how_do_you_take[]" style="height: 48px;">
                                                <option value="Please select">Please select</option>
                                                <option value="swallow or chew a pill">swallow or chew a pill (PO)</option>
                                                <option value="I put it under tongue">I put it under tongue (SL)</option>
                                                <option value="I mix it in water and drink">I mix it in water and drink</option>
                                                <option value="mixed in water and in gastric tube">mixed in water and in gastric tube(per slurry)</option>
                                                <option value="drink it comes in liquid form">drink it; it comes in liquid form</option>
                                                <option value="inhaled">inhaled</option>
                                                <option value="self injected">self injected</option>
                                                <option value="clinician injects it">clinician injects it</option>
                                                <option value="topically on skin or scalp">topically on skin or scalp</option>
                                                <option value="ophthalmic drops">ophthalmic drops</option>
                                                <option value="otic drops">otic drops</option>
                                                <option value="suppository">suppository</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <button type="button" class="meds_add_row blue_button">+ Add Row</button>
                                    </div>
                                </div>
                            </div>

                            <div class="tab medications_and_supplements3">
                                <h1 id="register">Please list which medications you are currently taking.</h1>
                                <h3></h3>
                                <p><input type="textbox" name="list_medications_text" placeholder="MedName"></p>
                            </div>
                            <!-- <div class="tab how_tak_it_div personal_cancer_tab tabbingg">
                                <h1 id="register">Medications and Supplements</h1>
                                <h3>How do you take it?</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I swallow or chew a pill (PO)</span>
                                            </span><input type="checkbox" id="medication_swallo" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I put it under my tongue (SL)</span>
                                            </span><input type="checkbox" id="medication_tongue" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I mix it in water and either drink it or put it in a gastric tube (per slurry)</span>
                                            </span><input type="checkbox" id="medication_water" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">It comes as a liquid, and I drink it (PO)</span>
                                            </span><input type="checkbox" id="medication_liquid" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I inhale it (inhaled)</span>
                                            </span><input type="checkbox" id="medication_inhale" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I inject it into myself (SC)</span>
                                            </span><input type="checkbox" id="medication_inject" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I go to the clinic to receive an injection (IM/IV)</span>
                                            </span><input type="checkbox" id="medication_clinic" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I rub it onto my skin or scalp (topically)</span>
                                            </span><input type="checkbox" id="medication_rub" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I put drops into my eyes (ophthalmic drops)</span>
                                            </span><input type="checkbox" id="medication_drops" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I put drops into my ears (otic drops)</span>
                                            </span><input type="checkbox" id="medication_ears" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I insert it as a suppository (suppository)</span>
                                            </span><input type="checkbox" id="medication_suppository" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab">
                                <h1 id="register">Medications and Supplements</h1>
                                <h3>How much do you take at a time?</h3>
                                <select class="form-select" id="select_unit" name="select_unit" style="height: 48px;">
                                    <option value="" selected>Select Unit</option>
                                    <option value="mg" selected>mg</option>
                                    <option value="ug">ug</option>
                                    <option value="mL">mL</option>
                                    <option value="puffs">puffs</option>
                                    <option value="drops">drops</option>
                                    <option value="tablets">tablets</option>
                                    <option value="capsules">capsules</option>
                                </select>
                                <p><input type="textbox" name="time_text" placeholder="Dose"></p>
                            </div> -->
                            <div class="tab medications_and_supplements4">
                                <h1 id="register">Approximately when did you start this medication?</h1>
                                <h3></h3>
                                <p><input type="textbox" name="start_this_medication" placeholder="Approximately when did you start this medication?" class="datepicker"></p>
                            </div>
                            <div class="tab medications_and_supplements5">
                                <h1 id="register">Approximately when did you stop this medication? (if your still taking it, leave this blank)</h1>
                                <h3></h3>
                                <p><input type="textbox" name="stop_this_medication" placeholder="Approximately when did you stop this medication?" class="datepicker"></p>
                            </div>

                            <div class="tab medications_and_supplements6">
                                <h1 id="register">Add another medication</h1>
                                <h3></h3>
                                <p><input type="textbox" name="another_medication" placeholder="Add another medication"></p>
                            </div>
                            <div class="tab medications_and_supplements7">
                                <h1 id="register">Do you currently take any supplements?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="supplements" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="supplements" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>

                            <div class="tab supp_list_dosage_tab medications_and_supplements8">
                                <h1 id="register">Medications and Supplements</h1>
                                <div class="content_div">
                                    <div class="row content_row">
                                        <div class="col-lg-2" id="p1">
                                            <span class="small_font">Supplement Name</span>
                                            <input type="textbox" placeholder="name of supplement" name="name_of_supp[]">
                                        </div>
                                        <div class="col-lg-2" id="p1">
                                            <span class="small_font">Dose</span>
                                            <input type="textbox" class="numeric" placeholder="eg 250" name="supp_dose[]">
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <span class="small_font">Units</span>
                                            <select class="form-select" id="meds_units" name="supp_units[]" style="height: 48px;">
                                                <option value="" class="translatable">Please Select</option>
                                                <option value="mg" class="translatable">mg</option>
                                                <option value="ug" class="translatable">ug</option>
                                                <option value="mL" class="translatable">mL</option>
                                                <option value="puffs" class="translatable">puffs</option>
                                                <option value="drops" class="translatable">drops</option>
                                                <option value="tablets" class="translatable">tablets</option>
                                                <option value="capsules" class="translatable">capsules</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <span class="small_font">How do you take it?</span>
                                            <select class="form-select" id="how_do_you_take_supp" name="how_do_you_take_supp[]" style="height: 48px;">
                                                <option value="Please select">Please select</option>
                                                <option value="swallow or chew a pill">swallow or chew a pill (PO)</option>
                                                <option value="I put it under tongue">I put it under tongue (SL)</option>
                                                <option value="I mix it in water and drink">I mix it in water and drink</option>
                                                <option value="mixed in water and in gastric tube">mixed in water and in gastric tube(per slurry)</option>
                                                <option value="drink it comes in liquid form">drink it; it comes in liquid form</option>
                                                <option value="inhaled">inhaled</option>
                                                <option value="self injected">self injected</option>
                                                <option value="clinician injects it">clinician injects it</option>
                                                <option value="topically on skin or scalp">topically on skin or scalp</option>
                                                <option value="ophthalmic drops">ophthalmic drops</option>
                                                <option value="otic drops">otic drops</option>
                                                <option value="suppository">suppository</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4" id="p1">
                                        <button type="button" class="supp_add_row blue_button">+ Add Row</button>
                                    </div>
                                </div>
                            </div>

                            <div class="tab medications_and_supplements9">
                                <h1 id="register">Please list which supplements you are currently taking.</h1>
                                <h3></h3>
                                <p><input type="textbox" name="supp_name" placeholder="Supp Name"></p>
                            </div>

                            <!-- <div class="tab how_tak_it_supp personal_cancer_tab tabbingg">
                                <h1 id="register">Medications and Supplements</h1>
                                <h3>How do you take it?</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I swallow or chew a pill (PO)</span>
                                            </span><input type="checkbox" id="medication_swallo" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I put it under my tongue (SL)</span>
                                            </span><input type="checkbox" id="medication_tongue" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I mix it in water and either drink it or put it in a gastric tube (per slurry)</span>
                                            </span><input type="checkbox" id="medication_water" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">It comes as a liquid, and I drink it (PO)</span>
                                            </span><input type="checkbox" id="medication_liquid" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I inhale it (inhaled)</span>
                                            </span><input type="checkbox" id="medication_inhale" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I inject it into myself (SC)</span>
                                            </span><input type="checkbox" id="medication_inject" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I go to the clinic to receive an injection (IM/IV)</span>
                                            </span><input type="checkbox" id="medication_clinic" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I rub it onto my skin or scalp (topically)</span>
                                            </span><input type="checkbox" id="medication_rub" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I put drops into my eyes (ophthalmic drops)</span>
                                            </span><input type="checkbox" id="medication_drops" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I put drops into my ears (otic drops)</span>
                                            </span><input type="checkbox" id="medication_ears" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I insert it as a suppository (suppository)</span>
                                            </span><input type="checkbox" id="medication_suppository" name="surgeries_bimast">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tab">
                                <h1 id="register">Medications and Supplements</h1>
                                <h3>How much do you take at a time?</h3>
                                <select class="form-select" id="select_unit_supp" name="select_unit_supp" style="height: 48px;">
                                    <option value="" selected>Select Unit</option>
                                    <option value="mg" selected>mg</option>
                                    <option value="ug">ug</option>
                                    <option value="mL">mL</option>
                                    <option value="puffs">puffs</option>
                                    <option value="drops">drops</option>
                                    <option value="tablets">tablets</option>
                                    <option value="capsules">capsules</option>
                                </select>
                                <p><input type="textbox" name="time_text_supp" placeholder="Dose"></p>
                            </div> -->

                            <div class="tab medications_and_supplements10">
                                <h1 id="register">Approximately when did you start this supplement?</h1>
                                <h3></h3>
                                <p><input type="textbox" name="start_this_supplement" placeholder="Supp Name"></p>
                            </div>
                            <div class="tab medications_and_supplements11">
                                <h1 id="register">Approximately when did you stop this supplement? (if your still taking it, leave this blank)</h1>
                                <h3></h3>
                                <p><input type="textbox" name="stop_this_supplement" placeholder="Supp Name"></p>
                            </div>

                            <div class="tab medications_and_supplements12">
                                <h1 id="register">Add another supplement</h1>
                                <h3></h3>
                                <p><input type="textbox" name="another_medication" placeholder="Add another supplement"></p>
                            </div>
                            
                            <div class="tab medications_and_supplements13">
                                <h1 id="register">Have you had the HPV (human papillomavirus virus) vaccine?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="papillomavirus" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="papillomavirus" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Allergies</h1>
                                    <h3>Section 8</h3>
                                    <h4>5 Questions</h4>
                                </div>
                            </div>

                            <div class="tab allergies1">
                                <h1 id="register">Are you allergic to anything?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="allergic" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="allergic" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>
                            <div class="tab allergies2">
                                <h1 id="register">Please list medications to which you are allergic.</h1>
                                <h3></h3>
                                <p><input type="textbox" name="list_medications" placeholder="Add another supplement"></p>
                            </div>
                            
                            <div class="tab what_happens_allergies personal_cancer_tab tabbingg allergies3">
                                <h1 id="register">What happens when you take it?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I can't breathe</span>
                                            </span><input type="checkbox" id="medication_swallo" name="cant_breath">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">My face swells</span>
                                            </span><input type="checkbox" id="medication_tongue" name="face_swells">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I get hives</span>
                                            </span><input type="checkbox" id="medication_water" name="give_hives">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I get another kind of rash</span>
                                            </span><input type="checkbox" id="medication_liquid" name="another_kind">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I get abdominal pain</span>
                                            </span><input type="checkbox" id="medication_swallo" name="abdominal_pain">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I get diarrhea</span>
                                            </span><input type="checkbox" id="medication_tongue" name="get_diarrhea">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">I get constipated</span>
                                            </span><input type="checkbox" id="medication_water" name="get_constipated">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1" style="position:relative;">
                                        <label class="container"><span class="text"></span>
                                            </span><input type="checkbox" id="medication_liquid" name="other_pain">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="text" placeholder="Something else"  name="other_pain_text"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" placeholder="Add another drug allergy"  name="another_drug_allergy"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab allergies4">
                                <h1 id="register">Please list other things to which you are allergic.</h1>
                                <h3></h3>
                                <p><input type="textbox" name="othallergy" placeholder="Other Allergy"></p>
                            </div>

                            <div class="tab allergies5">
                                <h1 id="register">Add another nondrug allergy</h1>
                                <h3></h3>
                                <p><input type="textbox" name="nondrug" placeholder="Nondrug Allergy"></p>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Reproductive History</h1>
                                    <h3>Section 9</h3>
                                    <h4>11 Questions</h4>
                                </div>
                            </div>

                            <div class="tab reproductive_history1">
                                <h1 id="register">How old were you when you had your first menstrual period?</h1>
                                <h3></h3>
                                <p><input type="textbox" name="first_menstrual_period" placeholder="first menstrual period"></p>
                            </div>
                            <div class="tab reproductive_history2">
                                <h1 id="register">When was your last menstrual period?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="household_income" name="household_income" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="less_than_1month" selected>≤ 1 month ago</option>
                                            <option value="2-11">2 to 11 months ago</option>
                                            <option value="12-24">12 to 24 months ago</option>
                                            <option value="2_years_ago">>2 years ago</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="tab reproductive_history3">
                                <h1 id="register">It appears that you have entered menopause. If that's true, enter your age at menopause. If not, leave blank.</h1>
                                <h3></h3>
                                <p><input type="textbox" name="entered_menopause"></p>
                            </div>

                            <div class="tab reproductive_history4">
                                <h1 id="register">How many children do you have? (biologic or adopted)</h1>
                                <h3>Include both biologic and adopted children</h3>
                                <p><input type="textbox" class="numeric" name="children" placeholder=""></p>
                            </div>

                            <div class="tab reproductive_history5">
                                <h1 id="register">How many were adopted?</h1>
                                <h3></h3>
                                <p><input type="textbox" class="numeric" name="adopted" placeholder=""></p>
                            </div>
                            <div class="tab reproductive_history6">
                                <h1 id="register">How many pregnancies have you had? </h1>
                                <h3>if none enter 0 (zero)</h3>
                                <p><input type="textbox" class="numeric" name="pregnancies" placeholder=""></p>
                            </div>
                            <div class="tab reproductive_history7">
                                <h1 id="register">How many led to a live birth?</h1>
                                <h3></h3>
                                <p><input type="textbox" class="numeric" name="live_birth" placeholder=""></p>
                            </div>
                            <div class="tab reproductive_history8">
                                <h1 id="register">How many spontaneously miscarried?</h1>
                                <h3></h3>
                                <p><input type="textbox" class="numeric" name="miscarried" placeholder=""></p>
                            </div>
                            <div class="tab reproductive_history9">
                                <h1 id="register">How many were voluntarily terminated?</h1>
                                <h3></h3>
                                <p><input type="textbox" class="numeric" name="voluntarily" placeholder=""></p>
                            </div>
                            <div class="tab reproductive_history10">
                                <h1 id="register">How many Boys?</h1>
                                <h3></h3>
                                <p><input type="textbox" class="numeric" id="repro_boys" name="boys" placeholder=""></p>
                                <div class="boys_div"></div>
                            </div>

                            <div class="tab reproductive_history11">
                                <h1 id="register">How many Girls?</h1>
                                <h3></h3>
                                <p><input type="textbox" class="numeric" id="repro_girls" name="girls" placeholder=""></p>
                                <div class="girls_div"></div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Family History: Structure</h1>
                                    <h3>Section 10</h3>
                                    <h4>7 Questions</h4>
                                </div>
                            </div>

                            <div class="tab structure1">
                                <h1 id="register">Were you adopted?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="adopted" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="adopted" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>
                            <div class="tab structure2">
                                <h1 id="register">Do you know your biologic family?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="biologic" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="biologic" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>
                            <div class="tab structure3">
                                <h1 id="register">Was your biologic father adopted?</h1>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label></label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Yes
                                                </span><input type="radio" name="biologic_father" value="yes">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">No
                                                </span><input type="radio" name="biologic_father" value="no">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">I don't know
                                                </span><input type="radio" name="biologic_father" value="i_dont_know">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>Is your biologic father still alive?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Yes
                                                </span><input type="radio" name="biologic_father_alive" value="yes">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">No
                                                </span><input type="radio" name="biologic_father_alive" value="no">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">I don't know
                                                </span><input type="radio" name="biologic_father_alive" value="i_dont_know">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old was he when he died?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="textbox" class="numeric" name="died" placeholder="" disabled></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old is he now?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="textbox" class="numeric" name="old" placeholder=""></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>What is your father's first name?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="textbox" name="father_first_name" placeholder="father's first name"></p>
                                    </div>
                                </div>                                
                            </div>

                            <div class="tab structure4">
                                <h1 id="register">Was your biologic mother adopted?</h1>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label></label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Yes
                                                </span><input type="radio" name="biologic_mother" value="yes">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">No
                                                </span><input type="radio" name="biologic_mother" value="no">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">I don't know
                                                </span><input type="radio" name="biologic_mother" value="i_dont_know">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>Is your biologic mother still alive?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Yes
                                                </span><input type="radio" name="biologic_mother_alive" value="yes">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">No
                                                </span><input type="radio" name="biologic_mother_alive" value="no">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">I don't know
                                                </span><input type="radio" name="biologic_mother_alive" value="i_dont_know">
                                                <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old was she when she died?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="textbox" class="numeric" name="she_died" placeholder="" disabled></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old is she now?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="textbox" class="numeric" name="she_old_now" placeholder=""></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>What is your mother's first name?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="textbox" name="mother_first_name" placeholder="Mother's first name"></p>
                                    </div>
                                </div>                                
                            </div>

                            <div class="tab structure5">
                                <h1 id="register">How many siblings do/did you have?</h1>
                                <h3>Include full and half siblings. If you don't have any siblings, enter 0 (zero). If you don't know your siblings, proceed to the next question.</h3>
                                <p><input type="textbox" class="numeric" name="siblings" placeholder=""></p>
                                <div class="siblings_div"></div>
                            </div>

                            <div class="tab structure6">
                                <h1 id="register">Is you maternal grandmother still alive?</h1>
                                <h3>Complete information about your maternal grandmother and grandfather below. Make sure to scroll all the way to right to respond to all questions.</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label></label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Alive
                                            </span><input type="radio" name="grandmother_alive" value="alive">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">Deceased
                                            </span><input type="radio" name="grandmother_alive" value="deceased">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old was she when she died?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" class="numeric" placeholder="" name="mom_old_died"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old is she now?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" class="numeric" placeholder="" name="mom_old_now"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>From what country did your paternal grandmother descend?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" placeholder="" name="mom_descend"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>Was their family Jewish?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Ashkenazi Jewish
                                            </span><input type="radio" name="family_jewish" value="ashkenazi">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">Sephardic Jewish
                                            </span><input type="radio" name="family_jewish" value="sephardic">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">Non-Jewish
                                            </span><input type="radio" name="family_jewish" value="non_Jewish">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="tab structure7">
                                <h1 id="register">Family History: Structure</h1>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>Is you paternal grandfather still alive?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Alive
                                            </span><input type="radio" name="grandfather_alive" value="alive">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">Deceased
                                            </span><input type="radio" name="grandfather_alive" value="deceased">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old was he when he died?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" class="numeric" placeholder="" name="grandfather_old_died"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How old is he now?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" class="numeric" placeholder="" name="grandfather_old_now"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>From what country did your paternal grandfather descend?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" placeholder="" name="country_grandfather_descend"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>Was their family Jewish?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Ashkenazi Jewish
                                            </span><input type="radio" name="paternal_family_jewish" value="ashkenazi">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">Sephardic Jewish
                                            </span><input type="radio" name="paternal_family_jewish" value="sephardic">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                        <label class="container"><span class="text">Non-Jewish
                                            </span><input type="radio" name="paternal_family_jewish" value="non_Jewish">
                                            <span class="checkmark_radio"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How many paternal uncles do/did you have?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" class="numeric" placeholder="" name="paternal_uncles"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label>How many paternal aunts do/did you have?</label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <p><input type="text" class="numeric" placeholder="" name="paternal_aunts"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Family History: people with cancer</h1>
                                    <h3>Section 11</h3>
                                    <h4>6 Questions</h4>
                                </div>
                            </div>

                            
                            <div class="tab people_with_cancer1">
                                <h1 id="register">To your knowledge, has anyone in your family ever had a cancer?</h1>
                                <h3></h3>
								<div class="label-container">
                                <label class="container col-lg-3"><span class="text">Yes
                                        </span><input type="radio" name="adequate" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container  col-lg-3"><span class="text">No
                                        </span><input type="radio" name="adequate" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container  col-lg-3"><span class="text">I am adopted/orphaned at a young age and don't know my family history
                                        </span><input type="radio" name="adequate" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
								</div>
                            </div>

                            <div class="which_family_memeber_div tab people_with_cancer2">
                                <h1 id="register">Which of the following family members had cancer?</h1>
                                <h3>Indicate what relation(s) in your family had a cancer or precancer in the first column below. Then tell us what type of cancer or precancer they had and how old when diagnosed. Make sure to scroll right to let us know if they are still living.</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Child</span>
                                            </span><input type="checkbox" id="never_went" name="child">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Brother</span>
                                            </span><input type="checkbox" id="some_hight" name="brother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Sister</span>
                                            </span><input type="checkbox" id="high_school" name="sister">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Niece</span>
                                            </span><input type="checkbox" id="never_went" name="niece">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Nephew</span>
                                            </span><input type="checkbox" id="never_went" name="nephew">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Mother</span>
                                            </span><input type="checkbox" id="never_went" name="mother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Father</span>
                                            </span><input type="checkbox" id="some_hight" name="father">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Maternal grandmother</span>
                                            </span><input type="checkbox" id="high_school" name="maternal_grandmother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Maternal grandfather</span>
                                            </span><input type="checkbox" id="never_went" name="maternal_grandfather">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Paternal grandmother</span>
                                            </span><input type="checkbox" id="never_went" name="paternal_grandmother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Paternal grandfather</span>
                                            </span><input type="checkbox" id="never_went" name="paternal_grandfather">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Maternal aunt</span>
                                            </span><input type="checkbox" id="some_hight" name="maternal_aunt">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Maternal uncle</span>
                                            </span><input type="checkbox" id="high_school" name="maternal_uncle">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Paternal aunt</span>
                                            </span><input type="checkbox" id="never_went" name="paternal_aunt">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Paternal uncle</span>
                                            </span><input type="checkbox" id="never_went" name="paternal_uncle">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Maternal female cousin</span>
                                            </span><input type="checkbox" id="never_went" name="maternal_female_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Maternal male cousin</span>
                                            </span><input type="checkbox" id="some_hight" name="maternal_male_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Paternal female cousin</span>
                                            </span><input type="checkbox" id="high_school" name="paternal_female_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Paternal male cousin</span>
                                            </span><input type="checkbox" id="never_went" name="paternal_male_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="which_specific_cancer_div tab people_with_cancer3">
                                <h1 id="register">What specific cancer(s) did they have?</h1>
                                <h3>Check all that apply</h3>
                                <div class="which_specific_cancer_div1">
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Breast</span>
                                                </span><input type="checkbox" id="never_went" name="specific_breast">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Prostate</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_prostate">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Testicular</span>
                                                </span><input type="checkbox" id="high_school" name="specific_testicular">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Primary Lung (started in the lung, instead of spread to the lung)</span>
                                                </span><input type="checkbox" id="never_went" name="specific_primary_lung">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Colorectal</span>
                                                </span><input type="checkbox" id="never_went" name="specific_colorectal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Esophageal</span>
                                                </span><input type="checkbox" id="never_went" name="specific_esophageal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Stomach</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_stomach">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Primary Liver (started in the liver, instead of spread to the liver)</span>
                                                </span><input type="checkbox" id="high_school" name="specific_primary_liver">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Pancreatic</span>
                                                </span><input type="checkbox" id="never_went" name="specific_pancreatic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Gallbladder or Bile Duct</span>
                                                </span><input type="checkbox" id="never_went" name="specific_gallbladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Thyroid</span>
                                                </span><input type="checkbox" id="never_went" name="specific_thyroid">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Adrenal Gland</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_adernal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Kidney</span>
                                                </span><input type="checkbox" id="high_school" name="specific_kidney">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Bladder</span>
                                                </span><input type="checkbox" id="never_went" name="specific_bladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Melanoma</span>
                                                </span><input type="checkbox" id="never_went" name="specific_melanoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Non-melanoma skin cancer</span>
                                                </span><input type="checkbox" id="never_went" name="specific_non_melanoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Sarcoma</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_sarcoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Primary Brain (started in the brain, instead of spread to the brain)</span>
                                                </span><input type="checkbox" id="high_school" name="specific_primary_brain">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Head & Neck (other than brain or skin)</span>
                                                </span><input type="checkbox" id="never_went" name="specific_h_n">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Lymphoma</span>
                                                </span><input type="checkbox" id="never_went" name="specific_lymphoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Leukemia</span>
                                                </span><input type="checkbox" id="never_went" name="specific_leukemia">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1" style="position:relative;">
                                            <label class="container"><span class="text">other</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_other">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input type="textbox" placeholder="other" name="specific_other_text"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="which_specific_cancer_div2" style="display: none;">
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Breast</span>
                                                </span><input type="checkbox" id="never_went" name="specific_breast">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Cervical</span>
                                                </span><input type="checkbox" id="some_hight" name="cervical">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Uterine</span>
                                                </span><input type="checkbox" id="high_school" name="uterine">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Ovarian</span>
                                                </span><input type="checkbox" id="high_school" name="ovarian">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Primary Lung (started in the lung, instead of spread to the lung)</span>
                                                </span><input type="checkbox" id="never_went" name="specific_primary_lung">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Colorectal</span>
                                                </span><input type="checkbox" id="never_went" name="specific_colorectal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Esophageal</span>
                                                </span><input type="checkbox" id="never_went" name="specific_esophageal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Stomach</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_stomach">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Primary Liver (started in the liver, instead of spread to the liver)</span>
                                                </span><input type="checkbox" id="high_school" name="specific_primary_liver">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Pancreatic</span>
                                                </span><input type="checkbox" id="never_went" name="specific_pancreatic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Gallbladder or Bile Duct</span>
                                                </span><input type="checkbox" id="never_went" name="specific_gallbladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Thyroid</span>
                                                </span><input type="checkbox" id="never_went" name="specific_thyroid">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Adrenal Gland</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_adernal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Kidney</span>
                                                </span><input type="checkbox" id="high_school" name="specific_kidney">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Bladder</span>
                                                </span><input type="checkbox" id="never_went" name="specific_bladder">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Melanoma</span>
                                                </span><input type="checkbox" id="never_went" name="specific_melanoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Non-melanoma skin cancer</span>
                                                </span><input type="checkbox" id="never_went" name="specific_non_melanoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Sarcoma</span>
                                                </span><input type="checkbox" id="some_hight" name="specific_sarcoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Primary Brain (started in the brain, instead of spread to the brain)</span>
                                                </span><input type="checkbox" id="high_school" name="specific_primary_brain">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Head & Neck (other than brain or skin)</span>
                                                </span><input type="checkbox" id="never_went" name="specific_h_n">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row"> 
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Lymphoma</span>
                                                </span><input type="checkbox" id="never_went" name="specific_lymphoma">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-6" id="p1">
                                            <label class="container"><span class="text">Leukemia</span>
                                                </span><input type="checkbox" id="never_went" name="specific_leukemia">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6" id="p1" style="position:relative;">
                                            <label class="container"><span class="text"></span>
                                                </span><input type="checkbox" id="some_hight" name="specific_other">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input type="textbox" placeholder="other" name="specific_other_text"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="which_family_memeber_was_div tab people_with_cancer4">
                                <h1 id="register">Which specific family member was this?</h1>
                                <h3></h3>
                                <div class="div_specific_family"></div>
                            </div>
                            
                            <div class="tab people_with_cancer5">
                                <h1 id="register">How old were they when they were initially diagnosed?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="old_initially_diagnosed" name="old_initially_diagnosed" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="less_than_18" selected><18 years old</option>
                                            <option value="18-24">18-24</option>
                                            <option value="25-29">25-29</option>
                                            <option value="30-34">30-34</option>
                                            <option value="35-39">35-39</option>
                                            <option value="40-44">40-44</option>
                                            <option value="45-49">45-49</option>
                                            <option value="50-59">50-59</option>
                                            <option value="60-69">60-69</option>
                                            <option value="70-79">70-79</option>
                                            <option value="80+">80+</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab people_with_cancer6">
                                <h1 id="register">Are they still alive?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="still_alive" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="still_alive" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Family History: people with pre-cancer</h1>
                                    <h3>Section 12</h3>
                                    <h4>5 Questions</h4>
                                </div>
                            </div>
                            
                            <div class="anyone_in_family_div tab people_with_pre_cancer1">
                                <h1 id="register">As far as you know, has anyone in your family ever had any of the following conditions?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">ductal carcinoma in-situ (DCIS), a pre-cancer of the breast</span>
                                            </span><input type="checkbox" id="never_went" name="ductal_carcinoma">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">lobular carcinoma in-situ (LCIS), a pre-cancer of the breast in-situ (LCIS), a pre-cancer of the breast</span>
                                            </span><input type="checkbox" id="some_hight" name="lobular_carcinoma">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">atypical ductal or lobular hyperplasia (ADH or ALH), a possible pre-cancer of the breast</span>
                                            </span><input type="checkbox" id="high_school" name="atypical_ductal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">borderine ovarian tumor</span>
                                            </span><input type="checkbox" id="never_went" name="borderine_ovarian_tumor">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">endometrial dysplasia of the uterine lining</span>
                                            </span><input type="checkbox" id="never_went" name="endometrial_dysplasia">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">high-grade cervical intraepithelial neoplasia (often discovered by pap smear)</span>
                                            </span><input type="checkbox" id="some_hight" name="high_grade_cervical">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">high-grade prostate intraepithelial neoplasia (HGPIN)</span>
                                            </span><input type="checkbox" id="high_school" name="high_grade_prostate">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">erythroplakia (red appearing mucosal patch that is precancerous)</span>
                                            </span><input type="checkbox" id="never_went" name="erythroplakia">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">leukoplakia (white appearing mucosal patch that is precancerous)</span>
                                            </span><input type="checkbox" id="never_went" name="leukoplakia">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">abnormal mole (dysplastic nevus or melanoma in-situ)</span>
                                            </span><input type="checkbox" id="some_hight" name="abnormal_mole_dysplastic">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">colon polyp(s)</span>
                                            </span><input type="checkbox" id="high_school" name="colon_polyp_s">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Barrett's esophagus</span>
                                            </span><input type="checkbox" id="never_went" name="anyone_barretts_esophagus">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">gastric dysplasia</span>
                                            </span><input type="checkbox" id="never_went" name="gastric_dysplasia" checked>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">panceatic intraepithelial neoplasia (PanIN)</span>
                                            </span><input type="checkbox" id="some_hight" name="panceatic_intraepithelial">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">none of the above</span>
                                            </span><input type="checkbox" id="high_school" name="anyone_none_of_above">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="anyone_in_family_div tab people_with_pre_cancer2">
                                <h1 id="register">Family History: people with pre-cancer</h1>
                                <h3>What relationship were they to you?</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Child</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_child">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Brother</span>
                                            </span><input type="checkbox" id="some_hight" name="relationship_brother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Sister</span>
                                            </span><input type="checkbox" id="high_school" name="relationship_sister">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Niece</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_niece">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Nephew</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_nephew">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Mother</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_mother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Father</span>
                                            </span><input type="checkbox" id="some_hight" name="relationship_father">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Maternal grandmother</span>
                                            </span><input type="checkbox" id="high_school" name="relationship_maternal_grandmother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Maternal grandfather</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_maternal_grandfather">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Paternal grandmother</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_paternal_grandmother">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Paternal grandfather</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_paternal_grandfather">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Maternal aunt</span>
                                            </span><input type="checkbox" id="some_hight" name="relationship_maternal_aunt">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Maternal uncle</span>
                                            </span><input type="checkbox" id="high_school" name="relationship_maternal_uncle">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Paternal aunt</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_paternal_aunt">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Paternal uncle</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_paternal_uncle">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Maternal female cousin</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_maternal_female_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Maternal male cousin</span>
                                            </span><input type="checkbox" id="some_hight" name="relationship_maternal_male_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Paternal female cousin</span>
                                            </span><input type="checkbox" id="high_school" name="relationship_paternal_female_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <label class="container"><span class="text">Paternal male cousin</span>
                                            </span><input type="checkbox" id="never_went" name="relationship_paternal_male_cousin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="anyone_in_family_div tab people_with_pre_cancer3">
                                <h1 id="register">Family History: people with pre-cancer</h1>
                                <h3>Which specific family member was this?</h3>
                            </div>
                            <div class="tab people_with_pre_cancer4">
                                <h1 id="register">How old were they when they were initially diagnosed?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="household_income" name="structure_old_initially_diagnosed" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="less_than_18" selected><18 years old</option>
                                            <option value="18-24">18-24</option>
                                            <option value="25-29">25-29</option>
                                            <option value="30-34">30-34</option>
                                            <option value="35-39">35-39</option>
                                            <option value="40-44">40-44</option>
                                            <option value="45-49">45-49</option>
                                            <option value="50-59">50-59</option>
                                            <option value="60-69">60-69</option>
                                            <option value="70-79">70-79</option>
                                            <option value="80+">80+</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="tab people_with_pre_cancer5">
                                <h1 id="register">Are they still alive?</h1>
                                <h3></h3>
								<div class="label-container yes_no_container">
                                    <label class="container col-lg-5"><span class="text">Yes
                                            </span><input type="radio" name="pre_cancer_still_alive" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="pre_cancer_still_alive" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
								</div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Social History: social support</h1>
                                    <h3>Section 13</h3>
                                    <h4>2 Questions</h4>
                                </div>
                            </div>
                            
                            <div class="tab social_support1">
                                <h1 id="register">Do you feel that you have adequate social support with friends, family, and/or community?</h1>
                                <div class="col-lg-10" id="p1">
                                    <h3></h3>
                                    <label class="container"><span class="text">Yes
                                            </span><input type="radio" name="adequate_social_support" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container"><span class="text">No
                                            </span><input type="radio" name="adequate_social_support" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <h3>Please elaborate</h3>
                                    <textarea name="please_elaborate" cols="10" style="width:100%;"></textarea>
                                </div>
                            </div>   
                            <div class="tab social_support2">
                                <h1 id="register">Do you have any friends or coworkers who have had cancer?</h1>
                                <div class="col-lg-10" id="p1">
                                    <h3></h3>
                                    <label class="container"><span class="text">Yes
                                            </span><input type="radio" name="coworkers" value="yes">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container"><span class="text">No
                                            </span><input type="radio" name="coworkers" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>

                                    <h3>How did this impact you?</h3>
                                
                                    <textarea name="friends_impact" cols="10" style="width:100%;"></textarea>
                                </div>
                            </div>

                            

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Perceived Risk & Motivation</h1>
                                    <h3>Section 15</h3>
                                    <h4>1 Question</h4>
                                </div>
                            </div>
                            
                            <div class="tab perceived_risk">
                                <h1 id="register">How concerned are you about your cancer risk?</h1>
                                <h3></h3>
                                <label class="container"><span class="text">I am highly concerned
                                        </span><input type="radio" name="concerned_risk" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">I am somewhat concerned
                                        </span><input type="radio" name="concerned_risk" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">I'm not too concerned, but I want to learn more
                                        </span><input type="radio" name="concerned_risk" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <br>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <h3>Tell us why you feel this way.</h3>
                                        <p><input type="text" placeholder="" name="why_you_feel"></p>
                                    </div>
                                </div>
                                 
                            </div>

                            <!-- <div class="tab">
                                <div class="thanks-message text-center" id="text-message"> <img src="https://i.imgur.com/O18mJ1K.png" width="100" class="mb-4">
                                    <h3>Thanks!</h3>
                                </div>
                            </div> -->
                            <div style="overflow:auto;" id="nextprevious">
                                <div class="buttoncontainer footer-buttons"> <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button> <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button> </div>
                            </div>

                            <div class="form-error-message" role="alert">This field is required.</div>
                            
                        </form>
                        <div class="div_steps">
                            <div class="all-steps" id="all-steps" style="display:block;"> 
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span>-->
                                    <!-- <span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span>-->
                                    <!-- <span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span>-->
                                    <!-- <span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span>-->
                                    <!-- <span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span>-->
                                    <!-- <span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span>-->
                                    <!-- <span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span> -->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                    <!--<span class="step"></span>-->
                                     
                                </div>
                                <br><br>
                                <span class="jfProgress-infoContent" id="cardProgressToggle" role="button" aria-label="See all">
                                  <span class="jfProgress-infoContentText">
                                    <span class="cardProgress-currentIndex" id="cardProgress-currentIndex">1 </span>
                                    <span class="cardProgress-questionCount cardProgress-middleText">of</span>
                                    <span class="cardProgress-questionCount" id="cardProgress-questionCount"> 92</span>
                                  </span>
                                </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <style>
    .modal-footer {
        padding: 0px!important;
    }

    .modal-footer div#nextprevious {
        padding: 0px;
        padding: 0px!important;
        margin: 0px!important;
    }
    div#myModal .modal-content {
          border-radius: 10px;
    }
    .modal-backdrop.in {
        filter: alpha(opacity=0.95);
        opacity: 0.93;
    } 

    .modal-backdrop {
       background-color: transparent;
    }
    </style>
    <?php
    // footer close body html
    //$this->display('_Footer.tpl.php');
    ?>
