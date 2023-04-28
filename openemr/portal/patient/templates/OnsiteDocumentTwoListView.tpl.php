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
// echo '<pre>';print_r($result);echo '</pre>';
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
        .introduction h3,.welcome h3{
            color: #fff !important;
        }
    </style>

</head>

<script>
    $(window).on('load', function() {
        $("input[type='radio']").change(function(){
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

            if(currentTabClass.indexOf('introduction') != -1){
                $('form#regForm').attr('style','background-color : #024747 !important;color:#fff;');
            }else{
                $('form#regForm').attr('style','background-color : #ffffff !important;color:black;');
            }

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
            //if (n == 1 && !validateForm()) return false;
            if(n == -1){
                console.log('currentStep',currentStep);
                if(currentStep == 22){
                    nextPrev(-19);
                }
            }
           if(n == 1 && next == ''){
                
                console.log('currentStep',currentStep);
                var url = '<?=$actual_link.$webroot.'/portal/save_questionnaire.php';?>';
                var pid = '<?=$pid;?>';
                var gender = '<?=$patientData['sex'];?>';
                //gender  = 'female';
                if( currentStep == 1){
                    var section = 'cancer_screening';

                    var low_dose_spiral = $("input[name='low_dose_spiral']:checked").parents('label').find('.text').html();
                    low_dose_spiral = low_dose_spiral.replace(/ /g, '');
                       
                    $.ajax({
                        type:'POST',
                        data: {'low_dose_spiral':low_dose_spiral,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                ////showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 2){
                    var section = 'cancer_screening';
                    var last_perfomed = $("input[name='last_perfomed']").val();
                    $.ajax({
                        type:'POST',
                        data: {'last_perfomed':last_perfomed,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });    
                    if(gender == 'Male'){
                        //nextPrev(19);
                    }
                   
                }

                if(currentStep == 3){
                    var section = 'cancer_screening';
                    var mammogram = $("input[name='mammogram']:checked").parents('label').find('.text').html();
                    mammogram = mammogram.replace(/ /g, '');                    
                    $.ajax({
                        type:'POST',
                        data: {'mammogram':mammogram,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                ////showAlert('Saved');
                            }
                        }
                    });
                    if(mammogram == 'yes'){
                        showAlert("Great. You will be asked to submit a copy of your last mammogram report prior to your consultation.");
                    }
                  
                }

                if(currentStep == 4){
                    var section = 'cancer_screening';
                    var performing_mammograms = $("#performing-mammograms :selected").html();
                    $.ajax({
                        type:'POST',
                        data: {'performing_mammograms':performing_mammograms,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                 
                }

                if(currentStep == 5){
                    var section = 'cancer_screening';
                    var need_mommography = $("#need_mommography :selected").html();
                    $.ajax({
                        type:'POST',
                        data: {'need_mommography':need_mommography,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                }
                if(currentStep == 6){
                    var section = 'cancer_screening';
                    var last_mammogram = $("input[name='last_mammogram']").val();

                    $.ajax({
                        type:'POST',
                        data: {'last_mammogram':last_mammogram,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                }
                if(currentStep == 7){
                    var section = 'cancer_screening';                    
                    var abnormal_mammogram = $("input[name='abnormal_mammogram']:checked").parents('label').find('.text').html();
                    abnormal_mammogram = abnormal_mammogram.replace(/ /g, ''); 
                    $.ajax({
                        type:'POST',
                        data: {'abnormal_mammogram':abnormal_mammogram,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    nextPrev(1,'next');
                }

                if(currentStep == 8){
                    var section = 'cancer_screening';                    
                    var when_was_cancer = $("input[name='when_was_cancer']").val();
                    $.ajax({
                        type:'POST',
                        data: {'when_was_cancer':when_was_cancer,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                }

                if(currentStep == 9){
                    var section = 'cancer_screening'; 
                    var breasts_mammogram = $("input[name='breasts_mammogram']:checked").parents('label').find('.text').html();
                    breasts_mammogram = breasts_mammogram.replace(/ /g, '');                  
                    $.ajax({
                        type:'POST',
                        data: {'breasts_mammogram':breasts_mammogram,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 10){
                    var section = 'cancer_screening'; 
                    var arr = [];
                    $(".breastimaging_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });

                    $.ajax({
                        type:'POST',
                        data: {'breastimaging_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    if($("input[name=none_of_the_above]").is(":checked")){
                        nextPrev(1,'next');
                    }
                }

                if(currentStep == 11){
                    var section = 'cancer_screening'; 
                    var dateofultrasound = $("input[name='dateofultrasound']").val();

                    $.ajax({
                        type:'POST',
                        data: {'dateofultrasound':dateofultrasound,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 12){
                    var section = 'cancer_screening'; 
                    var select_breastselfexams = $("#select_breastselfexams :selected").html();
                    //var dateofultrasound = $("input[name='dateofultrasound']").val();

                    $.ajax({
                        type:'POST',
                        data: {'select_breastselfexams':select_breastselfexams,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 13){
                    var section = 'cancer_screening'; 
                    var arr = [];
                    $(".performing_bse_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });

                    $.ajax({
                        type:'POST',
                        data: {'performing_bse_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 14){
                    var section = 'cancer_screening'; 
                    var pelvic_exam = $("input[name='pelvic_exam']:checked").parents('label').find('.text').html();
                    pelvic_exam = pelvic_exam.replace(/ /g, '');   
                    $.ajax({
                        type:'POST',
                        data: {'pelvic_exam':pelvic_exam,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    if(gender == 'Male'){
                        nextPrev(7);
                    }
                }

                if(currentStep == 15){
                    var select_screening_pelvic = $("#select_screening_pelvic :selected").val();
                    flag_screening_pelvic = "";
                    if(select_screening_pelvic == 'No, I am still a virgin'){
                        flag_screening_pelvic = "Hymen"; 
                    }else if(select_screening_pelvic == 'Yes, and I am happy with my sex life' || select_screening_pelvic == 'Yes, but I have trouble'){
                        flag_screening_pelvic = "Sexually Active"; 
                    }else if(select_screening_pelvic == 'Yes, but I have trouble'){
                        flag_screening_pelvic = "Consider Sex Counseling"; 
                    }

                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_screening_pelvic':flag_screening_pelvic,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var select_screening_pelvic = $("#select_screening_pelvic :selected").val();
                    var section = 'cancer_screening';   
                    $.ajax({
                        type:'POST',
                        data: {'select_screening_pelvic':select_screening_pelvic,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    if(select_screening_pelvic=='No, I am still a virgin'){

                        var flag_screening_pelvic='Hymen';
                         nextPrev(1,'next');
                    }
                    else if(select_screening_pelvic=='Yes, and I am happy with my sex life'){
                        var flag_screening_pelvic='Sexually Active';
                         nextPrev(1,'next');
                    }
                    else if(select_screening_pelvic=='Yes, but I have trouble'){
                        var flag_screening_pelvic='Consider Sex Counselling';
                    }
                    else{
                         nextPrev(1,'next');
                    }
                    console.log(flag_screening_pelvic);

                }
                if(currentStep == 16){
                    var arr = [];
                    $(".trouble_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });

                    var section = 'cancer_screening';   
                    $.ajax({
                        type:'POST',
                        data: {'trouble_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var select_trouble_having = $("#select_trouble_having :selected").val();
                    var flag_select_trouble_having = '';
                    if($("input[name='it_is_painful']").is(":checked")){
                        flag_select_trouble_having = "dyspareunia";
                    }else if($("input[name='no_desire']").is(":checked")){
                        flag_select_trouble_having = "impaired libido";
                    }

                    if($("input[name='prefer_not_answer']").is(":checked")){
                        showAlert('We understand that this is a personal question, but every sexually active person deserves happiness with their sex life, and we can only help you if you let us know how.');
                    }

                    var section = 'save_flag_first';
                    $.ajax({
                        type:'POST',
                        data: {'flag_select_trouble_having':flag_select_trouble_having,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    //if(!$("input[name='breast']").is(":checked")){
                        //nextPrev(2);
                    //}
                }
                if(currentStep == 17){
                    var dateofpelvicexam = $("input[name='pelvic_exam']").val();
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'dateofpelvicexam':dateofpelvicexam,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentStep == 18){
                    var last_pelvic_exam = $("input[name='last_pelvic_exam']:checked").parents('label').find('.text').html();
                    last_pelvic_exam = last_pelvic_exam.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'last_pelvic_exam':last_pelvic_exam,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var last_pelvic_exam = $("input[name='last_pelvic_exam']:checked").val();

                    if(last_pelvic_exam=='yes'){
                        
                        console.log(last_pelvic_exam);
                        //alert(last_pelvic_exam);
                        nextPrev(1,'next');
                    }
                }

                if(currentStep == 19){
                    var dateof_lastpap = $("input[name='dateof_lastpap']").val();
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'dateof_lastpap':dateof_lastpap,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 20){
                    var abnormal_pap_smear = $("input[name='abnormal_pap_smear']:checked").parents('label').find('.text').html();
                    abnormal_pap_smear = abnormal_pap_smear.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'abnormal_pap_smear':abnormal_pap_smear,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    
                    var abnormal_pap_smear =$("input[name='abnormal_pap_smear']:checked").val(); 
                   

                    if(abnormal_pap_smear=='yes'){                       
                        console.log(abnormal_pap_smear);
                        //alert(abnormal_pap_smear);
                    }
                    else{
                        console.log(abnormal_pap_smear);
                         nextPrev(1,'next');
                         //alert(abnormal_pap_smear);
                   }
               

                }

                if(currentStep == 21){
                    var when_was_that = $("input[name='when_was_that']").val();
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'when_was_that':when_was_that,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 22){
                    var hpv_virus_test = $("input[name='hpv_virus_test']:checked").parents('label').find('.text').html();
                    hpv_virus_test = hpv_virus_test.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'hpv_virus_test':hpv_virus_test,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 23){
                    var arr = [];
                    $(".hpv_subtype_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'hpv_subtype_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 24){
                    var digital_rectal_exam = $("input[name='digital_rectal_exam']:checked").parents('label').find('.text').html();
                    digital_rectal_exam = digital_rectal_exam.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'digital_rectal_exam':digital_rectal_exam,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                   var digital_rectal_exam =$("input[name='digital_rectal_exam']:checked").val(); 
                    if(digital_rectal_exam=='no'){                       
                        console.log(digital_rectal_exam);
                        nextPrev(1,'next');
                    }
                    
                }
                if(currentStep == 25){
                    // var digital_rectal_exam = $("input[name='digital_rectal_exam']:checked").parents('label').find('.text').html();
                    // digital_rectal_exam = digital_rectal_exam.replace(/ /g, '');
                    var last_dre = $("input[name='last_dre']").val();
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'last_dre':last_dre,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 26){
                    var arr = [];
                    $(".tabocoo_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).siblings('span').html());
                        }
                    });

                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'tabocoo_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 27){
                    var psa = $("input[name='psa']:checked").parents('label').find('.text').html();
                    psa = psa.replace(/ /g, '');

                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'psa':psa,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 28){
                    var last_psa = $("input[name='last_psa']").val();
                    last_psa = last_psa.replace(/ /g, '');

                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'last_psa':last_psa,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 29){
                    var last_use = $("#last_use :selected").html();

                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'last_use':last_use,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 30){
                    var colonoscopy_psa = $("input[name='colonoscopy_psa']:checked").parents('label').find('.text').html();
                    colonoscopy_psa = colonoscopy_psa.replace(/ /g, '');

                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'colonoscopy_psa':colonoscopy_psa,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 31){
                    var last_colonoscopy = $("input[name='last_colonoscopy']").val();

                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'last_colonoscopy':last_colonoscopy,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 32){
                    var polyps = $("input[name='polyps']:checked").parents('label').find('.text').html();
                    polyps = polyps.replace(/ /g, '');

                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'polyps':polyps,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 33){
                    var polyps_so_far = $("input[name='polyps_so_far']").val();
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'polyps_so_far':polyps_so_far,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 34){
                    var colorectal_cancer = $("input[name='colorectal_cancer']:checked").parents('label').find('.text').html();
                    colorectal_cancer = colorectal_cancer.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'colorectal_cancer':colorectal_cancer,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 35){
                    var arr = [];
                    $(".alcohlic_drinks_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'alcohlic_drinks_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 36){
                    var home_based_tests = $("input[name='home_based_tests']").val();
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'home_based_tests':home_based_tests,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                
                if(currentStep == 37){
                    var cancer_stomach = $("input[name='cancer_stomach']:checked").parents('label').find('.text').html();
                    cancer_stomach = cancer_stomach.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'cancer_stomach':cancer_stomach,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 38){
                    var arr = [];
                    $(".gastrointestinal_cancer input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'gastrointestinal_cancer':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 39){
                    var arr = [];
                    $(".blood_test_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'blood_test_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 40){                    
                    var screen_for_cancer = $("input[name='screen_for_cancer']:checked").parents('label').find('.text').html();
                    screen_for_cancer = screen_for_cancer.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'screen_for_cancer':screen_for_cancer,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 41){
                    var arr = [];
                    $(".body_image_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'body_image_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 42){                    
                    var blood_test = $("input[name='blood_test']:checked").parents('label').find('.text').html();
                    blood_test = blood_test.replace(/ /g, '');
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'blood_test':blood_test,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                
                if(currentStep == 43){
                    var arr = [];
                    $(".once_had_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            arr.push($(this).attr('name'));
                        }
                    });
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'once_had_container':arr,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 44){
                    var multi_cancer_last_performed = $("input[type='multi_cancer_last_performed']").val();
                    var section = 'cancer_screening';
                    $.ajax({
                        type:'POST',
                        data: {'multi_cancer_last_performed':multi_cancer_last_performed,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                

                
                

                

                
                

                if(currentStep == 57){

                }


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
                            <div class="tab" style="text-align: center;">
                                <div>
                                    <h1 id="register">Intake</h1>
                                    <h3>Visit 2</h3>
                                </div>
                            </div>
                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Cancer Screening History: Lung Cancer</h1>
                                    <h3>Section 1</h3>
                                    <h4>2 Questions</h4>
                                </div>
                            </div>

                            <div class="tab lung_cancer1">
                                <h1 id="register">Cancer Screening History: Lung Cancer</h1>
                                <h3>Have you ever had a low-dose spiral CT to screen for lung cancer?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="low_dose_spiral" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="low_dose_spiral" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab lung_cancer2">
                                <h1 id="register">Cancer Screening History: Lung Cancer</h1>
                                <h3>When was that last performed?  (If you only remember the month and year, leave the date blank.)</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="last_perfomed" autocomplete="off"></p>
                            </div>   

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                    <h3>Section 2</h3>
                                    <h4>11 Questions</h4>
                                </div>
                            </div>

                            <div class="tab breast_cancer1">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>Have you ever had a mammogram?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="mammogram" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="mammogram" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab breast_cancer2">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>What is/are your reason(s) for not performing mammograms? (check all that apply)</h3>
                                <select class="form-select myselect" id="performing-mammograms" name="performing_mammograms" style="height: 48px;">
                                    <option value="">Select</option>
                                    <option value="scared" selected>I'm scared of what I might find</option>
                                    <option value="afraid">I'm too busy; I'm afraid it might hurt</option>
                                    <option value="dont_think">I don't think I need it</option>
                                    <option value="something_else">I do something else instead</option>
                                    <option value="other_reason">other reason (tell us about it)</option>
                                </select>
                            </div>
                            <div class="tab breast_cancer3">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>Why do you not need mammography? (check all that apply)</h3>
                                <select class="form-select myselect" id="need_mommography" name="need_mommography" style="height: 48px;">
                                    <option value="">Select</option>
                                    <option value="young" selected>I'm too young</option>
                                    <option value="too_old">I'm too old</option>
                                    <option value="bilateral_mastectomy">I had bilateral mastectomy</option>
                                    <option value="implants">I have implants</option>
                                    <option value="dont_believe">I don't believe in it</option>
                                    <option value="prefer_another">I prefer another form of screening (tell us which)</option>
                                    <option value="other_reason">other reason (tell us about it)</option>
                                </select>
                            </div>

                            <div class="tab breast_cancer4">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>When was your last mammogram?  (If you only remember the month and year, leave the date blank.)</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="last_mammogram" autocomplete="off"></p>
                            </div>

                            <div class="tab breast_cancer5">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>Have you ever had an abnormal mammogram?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="abnormal_mammogram" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="abnormal_mammogram" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="tab breast_cancer6">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>When was that?</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="when_was_cancer" autocomplete="off"></p>
                            </div>
                            <div class="tab breast_cancer7">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>Have you been told that you have dense breasts on mammogram?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="breasts_mammogram" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="breasts_mammogram" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>
                    
                            <div class="tab breastimaging_container breast_cancer8">
                                <h1 id="register">Cancer Screening History : Breast Cancer</h1>
                                <h3>Have you ever had any other form of breast imaging? (check all that apply)</h3>
                                <div class="">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Ultrasound (US)
                                                </span><input type="checkbox" id="terms_conditions" name="ultrasound">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">MRI
                                                </span><input type="checkbox" id="other_forms_tobaco" name="mri">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Thermography
                                                </span><input type="checkbox" id="terms_conditions" name="thermography">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="other">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input type="textbox" placeholder="Other type of imaging" name="imaging"></p>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">None of the above
                                                </span><input type="checkbox" id="terms_conditions" name="none_of_the_above">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="tab breast_cancer9">
                                <h1 id="register">Cancer Screening History : Breast Cancer</h1>
                                <h3> When was your last ultrasound or MRI?  (If you only remember the month and year, leave the date blank.)</h3>
                                <input type="date" id="mydate" name="dateofultrasound">
                            </div>

                            <div class="tab breast_cancer10">
                                <h1 id="register">Cancer Screening History : Breast Cancer</h1>
                                <h3>How often do you perform breast self exams (BSE)? (choose the best answer)</h3>
                                 <select class="form-select myselect" id="select_breastselfexams" name="select_breastselfexams" style="height: 48px;">
                                            <option value="Daily or several times a week" selected>Daily or several times a week</option>
                                            <option value="Several times a month">Several times a month</option>
                                            <option value="Every 1-3 months">Every 1-3 months</option>
                                            <option value="A few times a year when I remember">A few times a year when I remember</option>
                                            <option value="I donot do BSE">I don't do BSE</option>
                                            

                                </select>
                            </div>

                            
                            <div class="tab performing_bse_container breast_cancer11">
                                <h1 id="register">Cancer Screening History: Breast Cancer</h1>
                                <h3>What is/are your reason(s) for not performing BSE? (check all that apply)</h3>
                                <div class="">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">My breasts are too lumpy
                                                </span><input type="checkbox" id="terms_conditions" name="breasts_lumpy">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">I'm scared of what I might find
                                                </span><input type="checkbox" id="other_forms_tobaco" name="scared_find">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">I'm not sure how to do it
                                                </span><input type="checkbox" id="terms_conditions" name="sure_how_do">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p5" style="position: relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="i_dont_think">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input placeholder="I don't think I need to" type="textbox" name="i_dont_think_text"></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="other_reason">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input type="textbox" placeholder="Other reason" name="other_reason_text"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Cancer Screening History: Gynecologic Cancers</h1>
                                    <h3>Section 3</h3>
                                    <h4>8 Questions</h4>
                                </div>
                            </div>

                            <div class="tab gynecologic_cancers1">
                                <h1>Cancer Screening History: Gynecologic Cancers</h1>
                                <br/>
                                <h3>Have you ever had a pelvic exam?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="pelvic_exam" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="pelvic_exam" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>

                            </div>
                            <div class="tab gynecologic_cancers2">
                                <h1>Cancer Screening History: Gynecologic Cancers</h1>
                                <br/>
                                <h3>"Are you sexually active? (Note: We ask this to determine whether you need a screening pelvic exam.)"</h3>
                                 <select class="form-select myselect" id="select_screening_pelvic" name="select_screening_pelvic" style="height: 48px;">
                                            <option value="Yes, and I am happy with my sex life" selected>Yes, and I'm happy with my sex life</option>
                                            <option value="Yes, but I have trouble">Yes, but I have trouble</option>
                                            <option value="I have been in the past">I have been in the past</option>
                                            <option value="but not for the past year">but not for the past year</option>
                                            <option value="No, I am still a virgin">No, I'm still a virgin</option>
                                            

                                        </select>



                                        <input type="hidden" name="flags_screening_pelvic" id="" value="flags_screening_pelvic">

                                       

                            </div>


                            <div class="tab trouble_container gynecologic_cancers3">
                                <h1 id="register">Cancer Screening History: Gynecologic Cancers</h1>
                                
                                <h3>What kind of trouble are you having?</h3>
                                <div class="">
                                    <div class="row">
                                        <div class="col-lg-2" id="p1">
                                            <label class="container"><span class="text">It is painful
                                                </span><input type="checkbox" id="terms_conditions" name="it_is_painful">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-2 col-half-offset" id="p2">
                                            <label class="container"><span class="text">It doesn't hurt, but I don't enjoy it
                                                </span><input type="checkbox" id="other_forms_tobaco" name="it_doesnt_hurt">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-2 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">I have no desire
                                                </span><input type="checkbox" id="terms_conditions" name="no_desire">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="other_trouble">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input type="textbox" placeholder="Other" name="other_about"></p>
                                        </div>
                                        <div class="col-lg-2 col-half-offset" id="p4">
                                            <label class="container"><span class="text">I prefer not to answer
                                                </span><input type="checkbox" id="terms_conditions" name="prefer_not_answer">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="tab personal_cancer_tab gynecologic_cancers4">
                                <h1 id="register">Cancer Screening History: Gynecologic Cancers</h1>
                                <h3>When was your last pelvic exam? (If you only remember the month and year, leave the date blank.)</h3>
                              <label class="container"><input type="date" id="mydate" name="dateofpelvicexam">
                            </div>

                            <div class="tab gynecologic_cancers5">
                                <h1 id="register">Cancer Screening History: Gynecologic Cancers</h1>
                                <h3>Was a Pap smear performed with your last pelvic exam?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="last_pelvic_exam" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="last_pelvic_exam" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>

                            </div>

                            <div class="tab gynecologic_cancers6">
                                <h1 id="register">Cancer Screening History: Gynecologic Cancers</h1>
                                <h3>When was your last Pap smear that you recall? (If you only remember the month and year, then leave the date blank.)</h3>
                                 <label class="container"><input type="date" id="mydate" name="dateof_lastpap">
                                       
                                </label>
                            </div>

                            <div class="tab gynecologic_cancers7">
                                <h1 id="register">Cancer Screening History: Gynecologic Cancers</h1>
                                <h3>Have you ever had an abnormal Pap smear?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="abnormal_pap_smear" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="abnormal_pap_smear" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab gynecologic_cancers8">
                                <h1 id="register">Cancer Screening History: Gynecologic Cancers</h1>
                                <h3>When was that?</h3>
                                <label class="container"><input type="date" id="mydate" name="when_was_that">
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Cancer Screening History: Pelvic or Head & Neck Cancers</h1>
                                    <h3>Section 4</h3>
                                    <h4>13 Questions</h4>
                                </div>
                            </div>

                            <div class="tab pelvic_head_neck_cancers1">
                                <h1 id="register">Cancer Screening History: Pelvic or Head & Neck Cancers</h1>
                                <h3>Have you ever had an HPV (human papilloma virus) test?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="hpv_virus_test" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="hpv_virus_test" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                                
                            </div>

                            <div class="tab hpv_subtype_container pelvic_head_neck_cancers2">
                                <h1 id="register">Cancer Screening History: Pelvic or Head & Neck Cancers</h1>
                                <h3>If you had HPV subtype testing, please indicate the test?</h3>
                                   <br/>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">HPV 16
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="HPV_16">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2">
                                        <label class="container"><span class="text">HPV 18
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="HPV_18">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">a different type, please specify:
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="different_type">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <br/>
                                    <div class="col-lg-3 col-half-offset" id="p4">
                                        <label class="container"><span class="text">no subtype testina was performed
                                            </span><input type="checkbox" id="hpv_subtype_testing" name="no_subtype">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                   
                                    
                                </div>



                      
                            </div>
                            
                            <div class="tab prostate_cancer1">
                                <h1 id="register">Cancer Screening History: Prostate Cancer</h1>
                                <h3>Have you ever had a digital rectal exam (DRE)? This is a rectal exam performed by a physician, physician assistant, or nurse practitioner for the purpose of examining the prostate.</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="digital_rectal_exam" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="digital_rectal_exam" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>


                            <div class="tab prostate_cancer2">
                                <h1 id="register">Cancer Screening History: Prostate Cancer</h1>
                                <h3>When was your last DRE?</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="last_dre" autocomplete="off"></p>
                            </div>
                            
                            <div class="tab tabocoo_container personal_cancer_tab prostate_cancer3">
                                <h1 id="register">Cancer Screening History: Prostate Cancer</h1>
                                
                                <h3>What was the result?</h3>
                                <div class="row">
                                    <div class="col-lg-2" id="p1">
                                        <label class="container"><span class="text">Normal
                                            </span><input type="checkbox" id="terms_conditions" name="result_normal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="other_forms_tobaco" name="result_abnormal">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Adbnormal" name="result_abnormal_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab prostate_cancer4">
                                <h1 id="register">Cancer Screening History: Prostate Cancer</h1>
                                <h3>Have you ever had a PSA (prostate specific antigen) test? PSA is a blood test that is performed to check the health of the prostate gland.</h3>
                               
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="psa" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="psa" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="tab prostate_cancer5">
                                <h1 id="register">Cancer Screening History: Prostate Cancer</h1>
                                <h3>When was your last PSA?</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="last_psa" autocomplete="off"></p>
                            </div>

                            <div class="tab prostate_cancer6">
                                <h1 id="register">Cancer Screening History: Prostate Cancer</h1>
                                <h3>What was the result?</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="last_use" name="weight" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="4_ng_ml" selected><4 ng/ml (normal)</option>
                                            <option value="4_10_ng_ml">4-10 ng/ml (borderline)</option>
                                            <option value="10_ng_ml">>10 ng/ml (high)</option>
                                            <option value="dont_remeber">don't remember</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="tab prostate_cancer7">
                                <h1 id="register">Cancer Screening History: Colorectal Cancer</h1>
                                <h3>Have you ever had a colonoscopy?</h3>
                               
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="colonoscopy_psa" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="colonoscopy_psa" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="tab prostate_cancer8">
                                <h1 id="register">Cancer Screening History: Colorectal Cancer</h1>
                                <h3>When was your last colonoscopy? (If you only remember the month and year, leave the date blank.)</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="last_colonoscopy" autocomplete="off"></p>
                            </div>
                            <div class="tab prostate_cancer9">
                                <h1 id="register">Cancer Screening History: Colorectal Cancer</h1>
                                <h3>Did they see any polyps?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="polyps" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="polyps" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="tab prostate_cancer10">
                                <h1 id="register">Cancer Screening History: Colorectal Cancer</h1>
                                <h3>How many polyps have been found so far?</h3>
                                <p><input type="textbox" name="polyps_so_far"></p>
                            </div>
                            <div class="tab prostate_cancer11">
                                <h1 id="register">Cancer Screening History: Colorectal Cancer</h1>
                                <h3>Have you ever had any other form of colorectal cancer screening, eg. Cologuard or other test performed at home? </h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="colorectal_cancer" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="colorectal_cancer" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab alcohlic_drinks_container personal_cancer_tab prostate_cancer12">
                                <h1 id="register">Cancer Screening History: Colorectal Cancer</h1>
                                
                                <h3>Which ones have you had? (check all that apply)</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">fecal DNA tests: Cologuard, some other fecal DNA test
                                            </span><input type="checkbox" id="terms_conditions" name="cologuard">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2">
                                        <label class="container"><span class="text">fecal immunochemical tests (FIT): Quest, Pixel, Everlywell, LetsGetChecked, Pinnacle Biolabs, some other FIT test
                                            </span><input type="checkbox" id="other_forms_tobaco" name="immunochemical">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">Guaiac fecal occult blood test (gFOBT)
                                            </span><input type="checkbox" id="terms_conditions" name="occult_blood">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p4">
                                        <label class="container"><span class="text">Some other type of test/Not sure
                                            </span><input type="checkbox" id="terms_conditions" name="other_test_Not">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="tab prostate_cancer13">
                                <h1 id="register">Cancer Screening History: Colorectal Cancer</h1>
                                <h3>When was the last time that any of these home-based tests were done? (If you only remember the month and year, leave the date blank.)</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="home_based_tests" autocomplete="off"></p>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Cancer Screening History: Upper Gastrointestinal Cancer</h1>
                                    <h3>Section 5</h3>
                                    <h4>3 Questions</h4>
                                </div>
                            </div>

                            <div class="tab upper_gastrointestinal_cancer1">
                                <h1 id="register">Cancer Screening History: Upper Gastrointestinal Cancer</h1>
                                <h3>Have you ever had a procedure to screen for cancers of the stomach, esophagus, pancreas, liver, or small bowel?</h3>
                               
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="cancer_stomach" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="cancer_stomach" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            
                            <div class="tab gastrointestinal_cancer upper_gastrointestinal_cancer2">
                                <h1 id="register">Cancer Screening History: Upper Gastrointestinal Cancer</h1>
                                <h3>Which ones have you had? (check all that apply)</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">esophagogastroduodenoscopy (EGD)
                                            </span><input type="checkbox" id="terms_conditions" name="esophagogastroduodenoscopy">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2">
                                        <label class="container"><span class="text">endoscopic ultrasound (EUS)
                                            </span><input type="checkbox" id="other_forms_tobaco" name="endoscopic_ultrasound">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">right upper quadrant [liver] ultrasound (RUQ US)
                                            </span><input type="checkbox" id="terms_conditions" name="right_upper_quadrant">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p4">
                                        <label class="container"><span class="text">endoscopic retrograde cholangiopancreatography (ERCP)
                                            </span><input type="checkbox" id="terms_conditions" name="endoscopic_retrograde">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other_gastrointestinal">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input placeholder="Other Gastrointestinal" type="textbox" name="other_gastrointestinal_text"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab blood_test_container upper_gastrointestinal_cancer3">
                                <h1 id="register">Cancer Screening History: Upper Gastrointestinal Cancer</h1>
                                <h3>Have you had a blood test to screen for liver cancer? (check all that apply)</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">alpha fetoprotein (AFP)
                                            </span><input type="checkbox" id="terms_conditions" name="alpha_fetoprotein">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Oncoguard Liver
                                            </span><input type="checkbox" id="other_forms_tobaco" name="oncoguard_liver">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other_blood_test">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other" name="other_blood_test_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Cancer Screening History: Multi-Cancer</h1>
                                    <h3>Section 6</h3>
                                    <h4>5 Questions</h4>
                                </div>
                            </div>

                            <div class="tab multi_cancer1">
                                <h1 id="register">Cancer Screening History: Multi-Cancer</h1>
                                <h3>Have you ever had a body scan to screen for cancer?</h3>
                               
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="screen_for_cancer" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="screen_for_cancer" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab body_image_container multi_cancer2">
                                <h1 id="register">Cancer Screening History: Multi-Cancer</h1>
                                <h3>Which ones have you had? (check all that apply)</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">CT scan
                                            </span><input type="checkbox" id="terms_conditions" name="ct_scan">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2">
                                        <label class="container"><span class="text">PET scan
                                            </span><input type="checkbox" id="other_forms_tobaco" name="pet_scan">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">PET/CT (both PET and CT performed at the same time)
                                            </span><input type="checkbox" id="terms_conditions" name="pet_and_ct">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">body MRI
                                            </span><input type="checkbox" id="terms_conditions" name="body_mri">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other_multi_cancer">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="other" name="other_multi_cancer_text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab multi_cancer3">
                                <h1 id="register">Cancer Screening History: Multi-Cancer</h1>
                                <h3>Have you ever had a blood test to screen for multiple cancers at once?</h3>
                               
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="blood_test" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="blood_test" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab once_had_container multi_cancer4">
                                <h1 id="register">Cancer Screening History: Multi-Cancer</h1>
                                <h3>Which ones have you had? (check all that apply)</h3>
                                <div class="row">
                                    <div class="col-lg-2" id="p1">
                                        <label class="container"><span class="text">Grail Galleri
                                            </span><input type="checkbox" id="terms_conditions" name="grail_galleri">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-2 col-half-offset" id="p2">
                                        <label class="container"><span class="text">Thrive CancerSEEK
                                            </span><input type="checkbox" id="other_forms_tobaco" name="thrive_cancerseek">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other_one">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="other" name="other_one_text"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab multi_cancer5">
                                <h1 id="register">Cancer Screening History: Multi-Cancer</h1>
                                <h3>When were any of these last performed?  (If you only remember the month and year, leave the date blank.)</h3>
                                <p><input type="text" class="datepicker1" placeholder=""  name="multi_cancer_last_performed" autocomplete="off"></p>
                            </div>




                            
                            
                            


                            <!-- <div class="thanks-message text-center" id="text-message"> <img src="https://i.imgur.com/O18mJ1K.png" width="100" class="mb-4">
                                <h3>Thanks for your Donation!</h3> <span>Your donation has been entered! We will contact you shortly!</span>
                            </div> -->
                            <div style="overflow:auto;" id="nextprevious">
                                <div class="buttoncontainer"> <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button> <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button> </div>
                            </div>

                            <div class="form-error-message" role="alert">This field is required.</div>
                            
                        </form>
                        <div class="div_steps">
                                <div class="all-steps" id="all-steps"> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span>
                                     <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span>
                                     <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span>
                                     <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span>
                                     <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span>
                                     <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span>
                                     <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span> 
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                    <span class="step"></span>
                                     
                                </div>
                            </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php
    // footer close body html
    //$this->display('_Footer.tpl.php');
    ?>
