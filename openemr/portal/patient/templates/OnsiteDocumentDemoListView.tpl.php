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

$pid = $this->cpid;
$patientData = getPatientData($pid);
//echo '<pre>';print_r($patientData);echo '</pre>';
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
            background: #a9bec9 !important;
            display: none;
        }

        .questionnaire-html{
            margin-top: 16px !important;
            padding-top: 50px;
            padding-bottom: 50px;
            padding-left: 0px !important;
            padding-right: 0px !important;
        }


        #regForm {
            background-color: #253942 !important;
            margin: 0px auto;
            font-family: Raleway;
            padding: 40px;
            border-radius: 10px;
            color: #fff;
        }

        h1 {
            text-align: center
        }

        input {
            padding: 10px;
            width: 100%;
            font-size: 17px;
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
            background-color: #bbbbbb
        }

        .step {
            height: 10px;
            width: 10px;
            margin: 0 2px;
            background-color: #bbbbbb;
            border: none;
            border-radius: 50%;
            display: inline-block;
            opacity: 0.5
        }

        .step.active {
            opacity: 1
        }

        .step.finish {
            background-color: #4CAF50
        }

        .all-steps {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 0px
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
            height: 25px;
            width: 25px;
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
                top: 7px;
                left: 7px;
                width: 12px;
                height: 12px;
                background: white;
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
               border:solid 1px #ddd!important;
           
        }
        .container input:checked~.checkmark {
            background-color: #2196F3!important;
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
            width: 1.25em;
            height: 1.25em;
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
            font-size: 0.9em;
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

        button#nextBtn:focus:after {
            animation: animateNext 1s 1;
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

        .tab.biopsy_tab.personal_cancer_tab .container .checkmark:after {
            top: 5px!important;
            left: 4px!important;
            width: 12px;
            height: 12px;
            background: white;
            right: 5px;
        }
        .tabbingg .container .checkmark:after {
            top: 5px!important;
            left: 4px!important;
            width: 12px;
            height: 12px;
            background: white;
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
            right: 17px;
            width: 181px;
        }
        p.other_p input{
            border: 0px;
            height: 45px;
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
        div#nextprevious{
            background-color:#445e6b !important;
        }
        
        .form-control{
            border: 1px solid #aaaaaa;
            padding: 10px;
            width: 100%;
            font-size: 17px;
            font-family: Raleway;
            border: 1px solid #aaaaaa;
        }
        .instruction{
            font-size: 19px;
        }
        .red{
            color:red;
        }
    </style>

</head>

<script>
    $(window).on('load', function() {
        $("input[type='checkbox']").change(function(){
            $(this).parents('.label-container').find(".container").each(function(){
                $(this).removeClass('selected_radio');
            });
            $(this).parents('label.container').addClass('selected_radio');
            
        });
        
        var questionnaire = '<?php echo @$_GET['questionnaire']; ?>';
        if(questionnaire == '1'){
            $('#topmenu .navbar-nav .dropdown-menu a').each(function(){
                if($(this).html() == 'Questionnaire1'){
                    $('body').addClass('questionnaire-body2');
                    //$(this).trigger('click');
                }
            });
            $('#onsiteDocumentModelContainer').hide();
            $('.questionnaire-div').show();
        }

        $('.back_to_home').click(function(){
            window.location.href = '<?= @$home_link; ?>'; 
        });

        $(".yes_no_container input[type='radio']").click(function(){
            $(".yes_no_container input[type='radio']").parents('.container').removeClass('yes_no_container_checked');
            if ($(".yes_no_container input[type='radio']").is(':checked')) {
               $(this).parents('.container').addClass('yes_no_container_checked');
            }
        });

    });
    
    $(document).ready(function(){ 
		var multipleCancelButton = new Choices('#choices-multiple-remove-button', {
        removeItemButton: true,
        maxItemCount:50,
        searchResultLimit:50,
        renderChoiceLimit:50
      }); 
		//$("button#nextBtn").after('<i class="fas fa-arrow-right"></i>');
		
		//$("#welcome button#nextBtn").text("start");
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
            var arr = [];
            $('.'+class_name+' label.container').each(function(){
                arr.push($(this).find('.text').text().length);
            });
            var h = Math.max.apply(Math, arr);
            h = parseInt(h);
            if(class_name == 'breast_cancer11' || class_name == 'pelvic_head_neck_cancers2' || class_name == 'prostate_cancer12' || class_name == 'upper_gastrointestinal_cancer3'){
                h = h;
            }
            if(class_name == 'upper_gastrointestinal_cancer2' || class_name == 'multi_cancer2' || class_name == 'multi_cancer4'){
                h = h+40;
            }
            $('.'+class_name+' label.container').each(function(){
                $(this).css('height',h);
            });
        }

        function showTab(n) {
            var x = document.getElementsByClassName("tab");
            x[n].style.display = "block";
            var currentTabClass = '';
            var i = n+1;
            currentTabClass = $("#regForm .tab:nth-child("+i+")").attr('class');
            //console.log(currentTabClass);
            if(currentTabClass.indexOf('breast_cancer11') != -1){
                var class_name = 'breast_cancer11';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('pelvic_head_neck_cancers2') != -1){
                var class_name = 'pelvic_head_neck_cancers2';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('prostate_cancer12') != -1){
                var class_name = 'prostate_cancer12';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('upper_gastrointestinal_cancer2') != -1){
                var class_name = 'upper_gastrointestinal_cancer2';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('upper_gastrointestinal_cancer3') != -1){
                var class_name = 'upper_gastrointestinal_cancer3';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('upper_gastrointestinal_cancer2') != -1){
                var class_name = 'upper_gastrointestinal_cancer2';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('multi_cancer2') != -1){
                var class_name = 'multi_cancer2';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('multi_cancer4') != -1){
                var class_name = 'multi_cancer4';
                equalHeight(class_name);
            }




            if (n == 0) {
                document.getElementById("prevBtn").style.display = "none";
            } else {
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
                $('form#regForm').attr('style','background-color : #253942 !important;color:#fff;');
            }
            if(n == 1){
                $('form#regForm').attr('style','background-color : #fff !important;color:black;');
            }
            fixStepIndicator(n)
        }
        var skip = 0;
        function nextPrev(n,next='') {
            //alert(skip);
            //$(".form-error-message").hide('');
            var i = 1;
            $('#register').html('');
            var currentStep = 1;
            var getCurrentStep = getCurrentStep();
            //alert(getCurrentStep);
            setTimeout(function() {
                
                $('.all-steps .step').each(function(){
                    //console.log(i);
                    if($(this).hasClass('active')){
                        currentStep = i;
                        nextStep = currentStep;
                        var html = '';
                        
                    }
                    i++;
                });

            }, 50);
            var x = document.getElementsByClassName("tab");
            
            var currentTabClass = '';
            $('#regForm .tab').each(function(){
                if($(this).css('display') == 'block'){
                    currentTabClass = ($(this).attr('class'));
                }
            });
            //if (n == 1 && !validateForm()) return false;
            if(n == -1){
                console.log('currentStep',currentStep);
                if(currentStep == 22){
                    nextPrev(-19);
                }
                
                if(currentTabClass.indexOf('socioeconomics2') != -1){
                    var employment_status = $("input[name='employment_status']:checked").val();
                    if(employment_status != 'employed_homemaker'){
                        nextPrev(-2);
                    }
                }
            }
           if(n == 1 && next == ''){
                console.log('currentStep',currentStep);
                var url = '<?=$actual_link.$webroot.'/portal/save_questionnaire.php';?>';
                var pid = '<?=$pid;?>';
                var gender = '<?=$patientData['sex'];?>';
                var currentTabClass = '';
                $('#regForm .tab').each(function(){
                    if($(this).css('display') == 'block'){
                        currentTabClass = ($(this).attr('class'));
                    }
                });
                
                if(currentTabClass.indexOf('demographics1') != -1){
                    var section = 'save_demographics1';
                    var data = $("#regForm").serialize();
                    data += '&section='+section+'&pid='+pid;
                    //console.log(data);
                    $.ajax({
                        type:'POST',
                        data: data,
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    //return false;
                }
                if(currentTabClass.indexOf('demographics2') != -1){
                    var section = 'save_demographics2';
                    var data = $("#regForm").serialize();
                    data += '&section='+section+'&pid='+pid;
                    //console.log(data);
                    $.ajax({
                        type:'POST',
                        data: data,
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    //return false;
                }
                if(currentTabClass.indexOf('demographics3') != -1){
                    var section = 'save_demographics3';
                    var data = $("#regForm").serialize();
                    data += '&section='+section+'&pid='+pid;
                    //console.log(data);
                    $.ajax({
                        type:'POST',
                        data: data,
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    //return false;
                }
                
                if(currentTabClass.indexOf('socioeconomics_income') != -1){
                    var employment_status = $("input[name='employment_status']:checked").val();
                    if(employment_status != 'employed_homemaker'){
                        nextPrev(2);
                    }
                }
                if(currentTabClass.indexOf('socioeconomics3') != -1){
                    var section = 'socioeconomics';
                    var living = $("input[name='living']").val();
                    var arr = [];
                    $(".highest_level_div input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var household_income = $("#household_income :selected").html();
                    var live_household = $("input[name='live_household']").val();
                    
                    //console.log(data);
                    $.ajax({
                        type:'POST',
                        data: {'Occupation':living,'Education':arr,'Income':household_income,'Household':live_household,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                showAlert('Saved');
                            }
                        }
                    });
                    return false;
                }
                
                //gender  = 'female';

            }

            setTimeout(function(){
                var step_i = 1;
                $('.all-steps .step').each(function(){
                    $(this).removeClass('completed');
                    //console.log(i);
                    if(!$(this).hasClass('active')){
                        if(step_i <= currentTab){
                            $(this).addClass('completed');
                        }
                        step_i++;
                    }
                });
            }, 100);
            

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

            $('.datepicker').datepicker({
                format: "yyyy-mm-dd"
            });

            $('.datepicker1').datepicker({
                format: "yyyy-mm"
            });

            $('.datepicker2').datepicker({
                format: "dd",
                viewYear: false,
            }).on('show', function() {
                console.log('hh');
                $('.datepicker-days thead tr:nth-child(2)').css({"visibility":"hidden"});
                //$(".datepicker-months .datepicker-switch").css({"visibility":"hidden"});
            });

            $('.datepicker3').datepicker({
                format: "yyyy",
                viewMode: "years", 
                minViewMode: "years"
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
            }, 3000);
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
            for (i = 0; i < x.length; i++) { x[i].className = x[i].className.replace(" active", ""); }
            x[n].className += " active";
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
                <img class="img-fluid" width="140" src="/openemr/public/images/logo-full-con.png">
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="nav">
                <ul class="navbar-nav mt-2 mt-lg-0">
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
            <div class="questionnaire-html container mt-5">
                <?php //echo '<pre>';print_r($_SERVER); echo '</pre>'; ?>
                <div class="row d-flex justify-content-center align-items-center">
                    <div class="col-md-12">
                        <form id="regForm">
                            <div class="tab" id="welcome" style="text-align: center;">
                                <div>
                                    <img style="width:150px;" src="<?php echo $actual_link.$webroot.'/portal/images/new_logo.png'; ?>">
                                    <h1 id="register">Welcome to OpenEmr</h1>
                                    <h3>Please fill out and submit this form.</h3>
                                </div>
                            </div> 
                            <div class="tab demographics1" style="text-align: center;">
                                    <h1 id="register">Demographics 1</h1>
                                    <h3></h3>
                                    <div id="div_1" class="bg-light collapse show" aria-labelledby="header_1">
                                      <div class="container-xl card-body">
                                            <div class="form-group row">
                                              <div class="col-12 col-md-auto col-lg-3 " id="label_title">Title:</div>
                                              <div class="col-12 col-md-auto col-lg-9" id="text_title">
                                                <select name="form_title" id="form_title" class="form-control form-control-sm mw-100" onchange="checkSkipConditions();" title="Title" style="">
                                                  <option value="">Unassigned</option>
                                                  <option <?php echo $patientData['title'] == 'Mr.'? 'selected' : ''; ?> value="Mr.">Mr.</option>
                                                  <option <?php echo $patientData['title'] == 'Mrs.'? 'selected' : ''; ?> value="Mrs.">Mrs.</option>
                                                  <option <?php echo $patientData['title'] == 'Ms.'? 'selected' : ''; ?> value="Ms.">Ms.</option>
                                                  <option <?php echo $patientData['title'] == 'Dr.'? 'selected' : ''; ?> value="Dr.">Dr.</option>
                                                </select>
                                              </div>
                                            </div>
                                            <!-- End BS row -->
                                            <div class="form-group row">
                                              <div class="col-12 col-md-auto col-lg-3 required" id="label_fname">Name:</div>
                                              <div class="col-12 col-md-auto col-lg-9" id="text_fname">
                                                <div class="row">
                                                  <div class="col-lg-4" id="p1">
                                                    <input type="text" class="form-control form-control-sm mw-100" name="form_fname" id="form_fname" size="15" maxlength="63" placeholder="First Name" title="First Name" value="<?php echo $patientData['fname']; ?>" onchange="checkSkipConditions();capitalizeMe(this);">
                                                  </div>
                                                  <div class="col-lg-4" id="p1">
                                                    <input type="text" class="form-control form-control-sm mw-100 mb-1" name="form_mname" id="form_mname" size="5" maxlength="63" placeholder="Middle Name" title="Middle Name" value="<?php echo $patientData['mname']; ?>" onchange="checkSkipConditions();capitalizeMe(this);">
                                                  </div>
                                                  <div class="col-lg-4" id="p1">
                                                    <input type="text" class="form-control form-control-sm mw-100 mb-1" name="form_lname" id="form_lname" size="20" maxlength="63" placeholder="Last Name" title="Last Name" value="<?php echo $patientData['lname']; ?>    " onchange="checkSkipConditions();capitalizeMe(this);">
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                            <!-- End BS row -->
                                            <div class="form-group row" style="display: none;">
                                              <div class="col-12 col-md-auto col-lg-3 " id="label_birth_fname">Birth Name:</div>
                                              <div class="col-12 col-md-auto col-lg-9" id="text_birth_fname">
                                                <input type="text" class="form-control form-control-sm mw-100" name="form_birth_fname" id="form_birth_fname" size="15" maxlength="63" placeholder="Birth First Name" title="Birth First Name" value="<?php echo $patientData['birth_fname']; ?>" onchange="checkSkipConditions();capitalizeMe(this);">
                                                <span class="text-nowrap mr-2">&nbsp;&nbsp; <input type="text" class="form-control form-control-sm mw-100 mb-1" name="form_birth_mname" id="form_birth_mname" size="5" maxlength="63" placeholder="Middle Name" title="Middle Name" value="<?php echo $patientData['birth_mname']; ?>" onchange="checkSkipConditions();capitalizeMe(this);">
                                                </span>
                                                <span class="text-nowrap mr-2">&nbsp;&nbsp; <input type="text" class="form-control form-control-sm mw-100 mb-1" name="form_birth_lname" id="form_birth_lname" size="20" maxlength="63" placeholder="Birth Last Name" title="Birth Last Name" value="<?php echo $patientData['birth_lname']; ?>" onchange="checkSkipConditions();capitalizeMe(this);">
                                                </span>
                                              </div>
                                            </div>
                                            <!-- End BS row -->
                                            
                                        </div>
                                    </div>
                            </div>
                            <div class="tab demographics2" style="text-align: center;">
                                <h1 id="register">Demographics 2</h1>
                                <h3></h3>
                                <!-- End BS row -->
                                <div class="form-group row">
                                  <div class="col-12 col-md-auto col-lg-3 required" id="label_DOB">DOB:</div>
                                  <div class="col-12 col-md-auto col-lg-3" id="text_DOB">
                                    <input type="text" size="10" class="datepicker" name="form_DOB" id="form_DOB" value="<?php echo $patientData['DOB']; ?>" title="Date of Birth" autocomplete="off" onchange="checkSkipConditions();">
                                  </div>
                                  <div class="col-12 col-md-auto col-lg-3 required" id="label_sex">Sex:</div>
                                  <div class="col-12 col-md-auto col-lg-3" id="text_sex">
                                    <select name="form_sex" id="form_sex" class="form-control  form-control-sm mw-100" onchange="checkSkipConditions();" title="Sex">
                                      <option value="">Unassigned</option>
                                      <option <?php echo $patientData['sex'] == 'Female' ? 'selected': ''; ?> value="Female">Female</option>
                                      <option <?php echo $patientData['sex'] == 'Male' ? 'selected': ''; ?> value="Male">Male</option>
                                      <option <?php echo $patientData['sex'] == 'UNK' ? 'selected': ''; ?> value="UNK">Unknown</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="form-group row">
                                  <div class="col-12 col-md-auto col-lg-3 " id="label_gender_identity">Gender Identity:</div>
                                  <div class="col-12 col-md-auto col-lg-3" id="text_gender_identity">
                                    <select name="form_gender_identity" id="form_gender_identity" class="form-control  form-control-sm mw-100" onchange="processCommentField(&quot;gender_identity&quot;);checkSkipConditions();" title="Gender Identity">
                                      <option value="">Unassigned</option>
                                      <option <?php echo $patientData['gender_identity'] == '446151000124109' ? 'selected': ''; ?> value="446151000124109">Identifies as Male</option>
                                      <option <?php echo $patientData['gender_identity'] == '446141000124107' ? 'selected': ''; ?> value="446141000124107">Identifies as Female</option>
                                      <option <?php echo $patientData['gender_identity'] == '407377005' ? 'selected': ''; ?> value="407377005">Female-to-Male (FTM)/Transgender Male/Trans Man</option>
                                      <option <?php echo $patientData['gender_identity'] == '407376001' ? 'selected': ''; ?> value="407376001">Male-to-Female (MTF)/Transgender Female/Trans Woman</option>
                                      <option <?php echo $patientData['gender_identity'] == '446131000124102' ? 'selected': ''; ?> value="446131000124102">Genderqueer, neither exclusively male nor female</option>
                                      <option <?php echo $patientData['gender_identity'] == 'comment_OTH' ? 'selected': ''; ?> value="comment_OTH">Additional gender category or other, please specify</option>
                                      <option <?php echo $patientData['gender_identity'] == 'ASKU' ? 'selected': ''; ?> value="ASKU">Choose not to disclose</option>
                                    </select>
                                    <input type="text" name="form_text_gender_identity" id="form_text_gender_identity" size="0" class="form-control" maxlength="100" style="display:none" value="">
                                  </div>
                                  <div class="col-12 col-md-auto col-lg-3 " id="label_sexual_orientation">Sexual Orientation:</div>
                                  <div class="col-12 col-md-auto col-lg-3" id="text_sexual_orientation">
                                    <select name="form_sexual_orientation" id="form_sexual_orientation" class="form-control  form-control-sm mw-100" onchange="processCommentField(&quot;sexual_orientation&quot;);checkSkipConditions();" title="Sexual Orientation">
                                      <option value="">Unassigned</option>
                                      <option <?php echo $patientData['sexual_orientation'] == '20430005' ? 'selected': ''; ?> value="20430005">Straight or heterosexual</option>
                                      <option <?php echo $patientData['sexual_orientation'] == '20430005' ? 'selected': ''; ?> value="20430005">Lesbian, gay or homosexual</option>
                                      <option <?php echo $patientData['sexual_orientation'] == '42035005' ? 'selected': ''; ?> value="42035005">Bisexual</option>
                                      <option <?php echo $patientData['sexual_orientation'] == 'comment_OTH' ? 'selected': ''; ?> value="comment_OTH">Something else, please describe</option>
                                      <option <?php echo $patientData['sexual_orientation'] == 'UNK' ? 'selected': ''; ?> value="UNK">Don't know</option>
                                      <option <?php echo $patientData['sexual_orientation'] == 'ASKU' ? 'selected': ''; ?> value="ASKU">Choose not to disclose</option>
                                    </select>
                                    <input type="text" name="form_text_sexual_orientation" id="form_text_sexual_orientation" size="0" class="form-control" maxlength="100" style="display:none" value="">
                                  </div>
                                </div>
                                <!-- End BS row -->
                                
                                <!-- End BS row -->
                                <div class="form-group row">
                                  <div class="col-12 col-md-auto col-lg-3 " id="label_drivers_license">License/ID:</div>
                                  <div class="col-12 col-md-auto col-lg-3" id="text_drivers_license">
                                    <input type="text" class="form-control form-control-sm mw-100" name="form_drivers_license" id="form_drivers_license" size="15" maxlength="63" title="Drivers License or State ID" value="<?php echo $patientData['drivers_license']; ?>" onchange="checkSkipConditions();">
                                  </div>
                                  <div class="col-12 col-md-auto col-lg-3 " id="label_status">Marital Status:</div>
                                  <div class="col-12 col-md-auto col-lg-3" id="text_status">
                                    <select name="form_status" id="form_status" class="form-control  form-control-sm mw-100" onchange="checkSkipConditions();" title="Marital Status">
                                      <option value="">Unassigned</option>
                                      <option <?php echo $patientData['status'] == 'married' ? 'selected': ''; ?> value="married">Married</option>
                                      <option <?php echo $patientData['status'] == 'single' ? 'selected': ''; ?> value="single">Single</option>
                                      <option <?php echo $patientData['status'] == 'divorced' ? 'selected': ''; ?> value="divorced">Divorced</option>
                                      <option <?php echo $patientData['status'] == 'widowed' ? 'selected': ''; ?> value="widowed">Widowed</option>
                                      <option <?php echo $patientData['status'] == 'separated' ? 'selected': ''; ?> value="separated">Separated</option>
                                      <option <?php echo $patientData['status'] == 'domestic partner' ? 'selected': ''; ?> value="domestic partner">Domestic Partner</option>
                                    </select>
                                  </div>
                                </div>
                            </div>
                            <div class="tab demographics3" style="text-align: center;">
                                <h1 id="register">Demographics 3</h1>
                                <h3></h3>
                                <div class="form-group row">
                                    <div class="col-12 col-md-auto col-lg-6" id="label_status">What is your preferred Gender pronous?</div>
                                    <div class="col-12 col-md-auto col-lg-6" id="text_status">
                                        <select name="pronous" id="pronous" class="form-control  form-control-sm mw-100" title="Marital Status">
                                          <option <?php echo $patientData['ethnoracial'] == 'his' ? 'selected': ''; ?> value="his">He/his</option>
                                          <option <?php echo $patientData['ethnoracial'] == 'her' ? 'selected': ''; ?> value="her">She/her</option>
                                          <option <?php echo $patientData['ethnoracial'] == 'their' ? 'selected': ''; ?> value="their">They/Their</option>
                                        </select>
                                    </div>
                                </div>
                                
    
                                <div class="form-group row">
                                    <div class="col-12 col-md-auto col-lg-6" id="label_status">What Race do you identify most with? <br> <i class="red instruction">instructions</i><i class="instruction">: Please select the best option from the dropdown list. You will have an opportunity to indicate your ethnicity, such as Hispanic or Latino, on the following screen.</i></div>
                                    <div class="col-12 col-md-auto col-lg-6" id="text_status">
                                        <select name="race" id="race" class="form-control  form-control-sm mw-100" title="Race">
                                          <option <?php echo $patientData['race'] == 'white_caucasian' ? 'selected': ''; ?> value="white_caucasian">White/Caucasian</option>
                                          <option <?php echo $patientData['race'] == 'black_african' ? 'selected': ''; ?> value="black_african">Black/African descent</option>
                                          <option <?php echo $patientData['race'] == 'asian_pacific' ? 'selected': ''; ?> value="asian_pacific">Asian/Pacific Islander</option>
                                          <option <?php echo $patientData['race'] == 'native_american' ? 'selected': ''; ?> value="native_american">Native American</option>
                                          <option <?php echo $patientData['race'] == 'other_mixed' ? 'selected': ''; ?> value="other_mixed">Other/Mixed: Specify</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-12 col-md-auto col-lg-6" id="label_status">Are you of Hispanic descent on either or both sides of your family?</div>
                                    <div class="col-12 col-md-auto col-lg-6" id="text_status">
                                        <select name="hispanic" id="hispanic" class="form-control  form-control-sm mw-100" title="Race">
                                          <option <?php echo $patientData['ethnicity'] == 'yes' ? 'selected': ''; ?> value="yes">Hispanic</option>
                                          <option <?php echo $patientData['ethnicity'] == 'no' ? 'selected': ''; ?> value="no">Black/African descent</option>
                                        </select>
                                    </div>
                                </div> 
                            </div>
                            
                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Social History: socioeconomics</h1>
                                    <h3>Section 2</h3>
                                    <h4>3 Questions</h4>
                                </div>
                            </div> 
                            
                            <div class="tab socioeconomics_working">
                                <h1 id="register">Are you currently working?</h1>
                                <h3></h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="currently_working" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="currently_working" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="tab socioeconomics_income">
                                <h1 id="register">What is your What is your current employment status?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Employed full time</span>
                                            </span><input type="radio" id="never_went" value="employed_full_time" name="employment_status">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Employed part-time</span>
                                            </span><input type="radio" id="never_went" value="employed_part_time" name="employment_status">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Self-Employed</span>
                                            </span><input type="radio" id="never_went" value="employed_self" name="employment_status">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Homemaker</span>
                                            </span><input type="radio" id="never_went" value="employed_homemaker" name="employment_status">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Unemployed/Between jobs</span>
                                            </span><input type="radio" id="never_went" value="employed_unemployed" name="employment_status">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Retired</span>
                                            </span><input type="radio" id="never_went" value="employed_retired" name="employment_status">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab socioeconomics1">
                                <h1 id="register">What do you do for a living?</h1>
                                <h3></h3>
                                <p><input type="text" placeholder="" name="living"></p>
                            </div>

                            <div class="highest_level_div tab socioeconomics2">
                                <h1 id="register">What is your highest level of education?</h1>
                                <h3>Check all that apply</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Never went to high school</span>
                                            </span><input type="checkbox" id="never_went" name="abdominal_pain">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Attended some high school</span>
                                            </span><input type="checkbox" id="some_hight" name="get_diarrhea">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">High school graduate</span>
                                            </span><input type="checkbox" id="high_school" name="get_constipated">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Attended some college or technical school</span>
                                            </span><input type="checkbox" id="some_college" name="other_pain">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Associate degree</span>
                                            </span><input type="checkbox" id="never_went" name="associate_degree">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Bachelor degree</span>
                                            </span><input type="checkbox" id="some_hight" name="bachelor_degree">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Master degree</span>
                                            </span><input type="checkbox" id="high_school" name="master_degree">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Doctorate degree</span>
                                            </span><input type="checkbox" id="some_college" name="doctorate_degree">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="tab socioeconomics3">
                                <h1 id="register">What is your approximate annual household income?</h1>
                                <h3></h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="household_income" name="household_income" style="height: 48px;">
                                            <option value="less_than_26" selected>$26,499 or less</option>
                                            <option value="26-55">$26,500-55,999</option>
                                            <option value="56-88">$56,000-88,999</option>
                                            <option value="89-169">$89,000-169,999</option>
                                            <option value="170-215">$170,000-215,999</option>
                                            <option value="216-399">$216,000-$399,999</option>
                                            <option value=">400">>$400,000 or more</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <h3>How many people live in your household?</h3>
                                        <p><input type="text" placeholder="" class="numeric" name="live_household"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="thanks-message text-center" id="text-message"> <img src="https://i.imgur.com/O18mJ1K.png" width="100" class="mb-4">
                                <h3>Thanks for your Donation!</h3> <span>Your donation has been entered! We will contact you shortly!</span>
                            </div> -->
                            <div style="overflow:auto;" id="nextprevious">
                                <div class="buttoncontainer"> <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button> <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button> </div>
                            </div>

                            <div class="form-error-message" role="alert">This field is required.</div>
                            
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php
    // footer close body html
    //$this->display('_Footer.tpl.php');
    ?>
