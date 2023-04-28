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
            background: #e9dfd7 !important;
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
            background-color: #024747 !important;
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
            top: 6px;
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
            background: white;
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
            background-color:#ec5c1c !important;
        }
        .introduction h3,.welcome h3{
            color: #fff !important;
        }

    </style>

</head>

<script>
    $(window).on('load', function() {
        var questionnaire = '<?php echo @$_GET['questionnaire']; ?>';
        if(questionnaire == '1'){
            $('#topmenu .navbar-nav .dropdown-menu a').each(function(){
                if($(this).html() == 'Questionnaire1'){
                    $('body').addClass('questionnaire-body3');
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
            if(class_name == 'tobacco2' || class_name == 'alcohol1' || class_name == 'alcohol6' || class_name == 'radiation3' || class_name == 'radiation6' || class_name == 'exogenous_hormones3'){
                h = h;
            }
            if(class_name == 'immno_supressive_therapy1'){
                h = h + 110;
                $('.'+class_name+' .div_immno_supressive_therapy1 label.container').each(function(){
                    $(this).css('height',h);
                });
            }else{
                $('.'+class_name+' label.container').each(function(){
                    $(this).css('height',h);
                });
            }
            // if(class_name == 'upper_gastrointestinal_cancer2' || class_name == 'multi_cancer2' || class_name == 'multi_cancer4'){
            //     h = h+40;
            // }
            
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
            if(currentTabClass.indexOf('tobacco2') != -1){
                var class_name = 'tobacco2';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('alcohol1') != -1){
                var class_name = 'alcohol1';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('alcohol6') != -1){
                var class_name = 'alcohol6';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('radiation3') != -1){
                var class_name = 'radiation3';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('radiation6') != -1){
                var class_name = 'radiation6';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('exogenous_hormones3') != -1){
                var class_name = 'exogenous_hormones3';
                equalHeight(class_name);
            }
            if(currentTabClass.indexOf('immno_supressive_therapy1') != -1){
                var class_name = 'immno_supressive_therapy1';
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
                $('form#regForm').attr('style','background-color : #fff !important;color:#5c3919;');
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
            //$('#register').html('');
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
            if(n == 1 && next == ''){
                
                console.log('currentStep',currentStep);
                var url = '<?=$actual_link.$webroot.'/portal/save_questionnaire.php';?>';
                var pid = '<?=$pid;?>';
                var flag_tobacco_products = '';


                if(currentStep == 1){
                    //nextPrev(18);
                    var tobacco_products = $("input[name='tobacco_products']:checked").val();
                    if(tobacco_products == 'yes'){
                        flag_tobacco_products = 'check cotinine';
                    }
                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_tobacco_products':flag_tobacco_products,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 2){
                    if($("input[name=cigarettes]").is(":checked")){
                        nextPrev(1,'next');
                        return false;
                    }
                    if($("input[name=other_forms_tobaco]").is(":checked") || $("input[name=smokeless_tobacco]").is(":checked")){
                        nextPrev(4);
                        return false;
                    }
                    if($("input[name=none_of_above_tobaco]").is(":checked")){
                        nextPrev(5);  
                        return false; 
                    }
                }

                if(currentStep == 5){
                    var value = $('#last_use').val();
                    if(value == '24-48' || value == 'within_a_week'){
                        var flag_within_a_week = 'eligible for tobacco cessation program';
                    }
                    if(value == 'more_than_a_month'){
                        var flag_more_than_a_month = 'at risk for tobacco relapse';
                    }

                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_within_a_week':flag_within_a_week,'flag_more_than_a_month':flag_more_than_a_month,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 7){
                    var secondhand_tobacco = $("input[name='secondhand_tobacco']:checked").val();
                    if(secondhand_tobacco == 'no'){
                        //alert('here');
                        //move to next section
                        nextPrev(1,'next');
                    }else{
                        var flag_secondhand_tobacco = 'check cotinine';
                        var section = 'save_flag';
                        $.ajax({
                            type:'POST',
                            data: {'flag_secondhand_tobacco':flag_secondhand_tobacco,'section':section,'pid':pid},
                            url: url,
                            success:function(res){
                                if(res == 'added'){
                                    //showAlert('Saved');
                                }
                            }
                        });
                    }
                }

                if(currentStep == 8){
                    var section = 'exposure_history_tobacco';
                    var tobacco_products = $("input[name='tobacco_products']:checked").parents('label').find('.text').html();
                    tobacco_products = tobacco_products.replace(/ /g, '');
                    var tabocoo_container_arr = [];
                    $(".tabocoo_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            tabocoo_container_arr.push($(this).attr('name'));
                        }
                    });
                    var average_number_cigarettes = $("input[name='average_number_cigarettes']").val();
                    average_number_cigarettes = parseInt(average_number_cigarettes);
                    var ppd = 0;
                    if(average_number_cigarettes > 0){
                        ppd = average_number_cigarettes / 20;
                    }
                    ppd = parseInt(ppd);
                    var years_smoked = $("input[name='years_smoked']").val();
                    var PkYr = ppd * years_smoked;
                    var PkYrFlag = 0;
                    if(PkYr >= 20){
                        PkYrFlag = "significant smoker";
                    }
                    var last_use = $('#last_use :selected').html();

                    var how_long_used = $("input[name='how_long_used']:checked").parents('label').find('.text').html();
                    //how_long_used = how_long_used.replace(/ /g, '');

                    var secondhand_tobacco = $("input[name='secondhand_tobacco']:checked").parents('label').find('.text').html();
                    secondhand_tobacco = secondhand_tobacco.replace(/ /g, '');
                    var secondhand_tobacco_flag = '';
                    if(secondhand_tobacco == 'yes'){
                        secondhand_tobacco_flag = 'check cotinine';
                    }

                    var inhale_second_hand_smoke = $("input[name='inhale_second_hand_smoke']").val();
                    
                    // console.log(arr);
                    // return false;
                    $.ajax({
                        type:'POST',
                        data: {'tobacco_products':tobacco_products,'tabocoo_container_arr':tabocoo_container_arr,'average_number_cigarettes':average_number_cigarettes,'ppd':ppd,'years_smoked':years_smoked,'PkYr':PkYr,'PkYrFlag':PkYrFlag,'secondhand_tobacco_flag':secondhand_tobacco_flag,'last_use':last_use,'how_long_used':how_long_used,'secondhand_tobacco':secondhand_tobacco,'inhale_second_hand_smoke':inhale_second_hand_smoke,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }

                if(currentStep == 9){
                    if(!$("input[name=4_more_day]").is(":checked")){
                        var flag_4_more_day = 'ETOH risk';
                    }
                    if(!$("input[name=consume_alchohol]").is(":checked")){
                        var flag_consume_alchohol = 'ETOH protection';
                    }
                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_4_more_day':flag_4_more_day,'flag_consume_alchohol':flag_consume_alchohol,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                    if(!$("input[name=consume_alchohol]").is(":checked")){
                        nextPrev(1,'next');
                    }
                }   
                if(currentStep == 10){
                    var drink_alcohol_in_past = $("input[name='drink_alcohol_in_past']:checked").val();
                    if(drink_alcohol_in_past == 'no'){
                        nextPrev(2);
                    }
                }
                if(currentStep == 13){
                    var illicit_drugs = $("input[name='illicit_drugs']:checked").val();
                    if(illicit_drugs == 'no'){
                        nextPrev(2);
                    }
                }
                if(currentStep == 14){

                }
                if(currentStep == 15){
                    var section = 'exposure_history_alchohol';
                    var alcohlic_drinks_container_arr = [];
                    $(".alcohlic_drinks_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            alcohlic_drinks_container_arr.push($(this).siblings('.text').html());
                        }
                    });
                    var drink_alcohol_in_past = $("input[name='drink_alcohol_in_past']:checked").parents('label').find('.text').html();
                    drink_alcohol_in_past = drink_alcohol_in_past.replace(/ /g, '');
                    
                    var last_drink = $('#last_drink :selected').html();

                    var alcohlic_drinks_prefer_container = [];
                    $(".alcohlic_drinks_prefer_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            alcohlic_drinks_prefer_container.push($(this).siblings('.text').html());
                        }
                    });

                    var illicit_drugs = $("input[name='illicit_drugs']:checked").parents('label').find('.text').html();
                    illicit_drugs = illicit_drugs.replace(/ /g, '');
                    
                    var substances_container = [];
                    $(".substances_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            substances_container.push($(this).siblings('.text').html());
                        }
                    });
                    
                    var last_use_alchohol = $("#last_use_alchohol :selected").html();

                    $.ajax({
                        type:'POST',
                        data: {'alcohlic_drinks_container_arr':alcohlic_drinks_container_arr,'drink_alcohol_in_past':drink_alcohol_in_past,'last_drink':last_drink,'alcohlic_drinks_prefer_container':alcohlic_drinks_prefer_container,'illicit_drugs':illicit_drugs,'substances_container':substances_container,'last_use_alchohol':last_use_alchohol,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentStep == 17){
                    if($("input[name=none_of_above_knowledge]").is(":checked")){
                        nextPrev(1,'next');
                    }
                }
                if(currentStep == 19){
                    var all_natural_beauty = $("input[name='all_natural_beauty']:checked").val();
                    var flag_all_natural_beauty = '';
                    if(all_natural_beauty == 'no'){
                        flag_all_natural_beauty = 'unnatural';
                    }
                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_all_natural_beauty':flag_all_natural_beauty,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 20){
                    var section = 'exposure_history_environment';
                    var live_large_city = $("input[name='live_large_city']:checked").parents('label').find('.text').html();
                    live_large_city = live_large_city.replace(/ /g, '');

                    var protection_cantainer = [];
                    $(".protection_cantainer input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            protection_cantainer.push($(this).attr('name'));
                        }
                    });
                    var total_amount_exposed = $("input[name='total_amount_exposed']").val();

                    var all_natural_home = $("input[name='all_natural_home']:checked").parents('label').find('.text').html();
                    all_natural_home = all_natural_home.replace(/ /g, '');

                    var all_natural_beauty = $("input[name='all_natural_beauty']:checked").parents('label').find('.text').html();
                    all_natural_beauty = all_natural_beauty.replace(/ /g, '');

                    $.ajax({
                        type:'POST',
                        data: {'live_large_city':live_large_city,'protection_cantainer':protection_cantainer,'total_amount_exposed':total_amount_exposed,'all_natural_home':all_natural_home,'all_natural_beauty':all_natural_beauty,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    var all_natural_beauty = $("input[name='all_natural_beauty']:checked").val();
                    var flag_environment = '';
                    if(all_natural_beauty == 'no'){
                        flag_environment = 'unnatural';
                    }
                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_environment':flag_environment,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 26){
                    var received_radiation = $("input[name='received_radiation']:checked").val();
                    if(received_radiation == 'no'){
                        nextPrev(2);
                    }
                }

                if(currentStep == 27){
                    var section = 'exposure_history_radiation';
                    var tanning_bed = $("input[name='tanning_bed']:checked").parents('label').find('.text').html();
                    tanning_bed = tanning_bed.replace(/ /g, '');
                    var severe_sunburns = $("input[name='severe_sunburns']:checked").parents('label').find('.text').html();
                    severe_sunburns = severe_sunburns.replace(/ /g, '');
                    
                    var exposure_history_apply = [];
                    $(".exposure_history_apply   input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            exposure_history_apply.push($(this).attr('name'));
                        }
                    });
                    
                    var knowledge_exposed = $("input[name='knowledge_exposed']:checked").parents('label').find('.text').html();
                    knowledge_exposed = knowledge_exposed.replace(/ /g, '');

                    var radon_detector = $("input[name='radon_detector']:checked").parents('label').find('.text').html();
                    radon_detector = radon_detector.replace(/ /g, '');

                    var received_radiation = $("input[name='received_radiation']:checked").parents('label').find('.text').html();
                    received_radiation = received_radiation.replace(/ /g, '');

                    var radiated_container = [];
                    $(".radiated_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            radiated_container.push($(this).attr('name'));
                        }
                    });
                    var started_radiation_therapy = $("input[name='started_radiation_therapy']").val();

                    $.ajax({
                        type:'POST',
                        data: {'tanning_bed':tanning_bed,'severe_sunburns':severe_sunburns,'exposure_history_apply':exposure_history_apply,'knowledge_exposed':knowledge_exposed,'radon_detector':radon_detector,'received_radiation':received_radiation,'radiated_container':radiated_container,'started_radiation_therapy':started_radiation_therapy,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 28){
                    var oral_contraceptives = $("input[name='oral_contraceptives']:checked").val();
                    if(oral_contraceptives == 'no'){
                        //nextPrev(1,'next');
                    }
                }
                if(currentStep == 29){
                    var postmenopausal_hormones = $("input[name='postmenopausal_hormones']:checked").val();
                    if(postmenopausal_hormones == 'no'){
                        //nextPrev(1,'next');
                    }
                }

                if(currentStep == 30){
                    var section = 'exposure_history_exogenous';

                    var oral_contraceptives = $("input[name='oral_contraceptives']:checked").val();
                    var what_did_taking = $("input[name='what_did_taking']").val();
                    var how_many_years = $("input[name='how_many_years']").val();
                    var postmenopausal_hormones = $("input[name='postmenopausal_hormones']").val();
                    var what_did_taking_postmenopausal = $("input[name='what_did_taking_postmenopausal']").val();
                    var postmenopausal_hormones_total = $("input[name='postmenopausal_hormones_total']").val();
                    
                    var medication_container = [];
                    $(".medication_container   input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            medication_container.push($(this).attr('name'));
                        }
                    });
                    var how_many_years_medication = $("input[name='how_many_years_medication']").val();
                    
                    var diethylstilbesterol = $("input[name='diethylstilbesterol']:checked").parents('label').find('.text').html();
                    diethylstilbesterol = diethylstilbesterol.replace(/ /g, '');
                    
                    $.ajax({
                        type:'POST',
                        data: {'oral_contraceptives':oral_contraceptives,'what_did_taking':what_did_taking,'how_many_years':how_many_years,'postmenopausal_hormones':postmenopausal_hormones,'what_did_taking_postmenopausal':what_did_taking_postmenopausal,'postmenopausal_hormones_total':postmenopausal_hormones_total,'medication_container':medication_container,'how_many_years_medication':how_many_years_medication,'diethylstilbesterol':diethylstilbesterol,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentStep == 31){
                    var section = 'exposure_history_immno';
                    var immno_container = [];
                    $(".immno_container   input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            immno_container.push($(this).attr('name'));
                        }
                    });
                    var how_many_years_suppresssive = $("input[name='how_many_years_suppresssive']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'immno_container':immno_container,'how_many_years_suppresssive':how_many_years_suppresssive,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                }
                if(currentStep == 32){
                    var section = 'exposure_history_substances';
                    var other_substances_container = [];
                    $(".other_substances_container   input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            other_substances_container.push($(this).attr('name'));
                        }
                    });
                    var tell_us_other_substances = $("input[name='tell_us_other_substances']").val();
                    
                    $.ajax({
                        type:'POST',
                        data: {'other_substances_container':other_substances_container,'tell_us_other_substances':tell_us_other_substances,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    
                    var flag_substance_saspirin = '';
                    var flag_substance_tamoxifen = '';
                    var flag_substance_raloxifene = '';
                    var flag_substance_finasteride = '';
                    var flag_substance_dutasteride = '';
                    var flag_substance_birth_control = '';
                    var flag_substance_menopausal = '';
                    var flag_substance_hormones = '';
                    if($("input[name=substance_saspirin]").is(":checked")){
                        flag_substance_saspirin = 'CRCprotect';
                    }
                    if($("input[name=substance_tamoxifen]").is(":checked")){
                         flag_substance_tamoxifen = 'BCprotect and ECrisk';
                    }
                    if($("input[name=substance_raloxifene]").is(":checked")){
                         flag_substance_raloxifene = 'BCprotect';
                    }
                    if($("input[name=substance_finasteride]").is(":checked")){
                         flag_substance_finasteride = 'PCprotect';
                    }
                    if($("input[name=substance_dutasteride]").is(":checked")){
                         flag_substance_dutasteride = 'PCprotect';
                    }
                    if($("input[name=substance_birth_control]").is(":checked")){
                         flag_substance_birth_control = 'OCprotect';
                    }
                    if($("input[name=substance_menopausal]").is(":checked")){
                         flag_substance_menopausal = 'BCrisk and ECrisk and CRCprotect';
                    }
                    if($("input[name=substance_hormones]").is(":checked")){
                         flag_substance_hormones = 'BCrisk2 and ECrisk2';
                    }

                    var flag_substance_saspirin = '';
                    var flag_substance_tamoxifen = '';
                    var flag_substance_raloxifene = '';
                    var flag_substance_finasteride = '';
                    var flag_substance_dutasteride = '';
                    var flag_substance_birth_control = '';
                    var flag_substance_menopausal = '';
                    var flag_substance_hormones = '';

                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_substance_saspirin':flag_substance_saspirin,'flag_substance_tamoxifen':flag_substance_tamoxifen,'flag_substance_raloxifene':flag_substance_raloxifene,'flag_substance_finasteride':flag_substance_finasteride,'flag_substance_dutasteride':flag_substance_dutasteride,'flag_substance_birth_control':flag_substance_birth_control,'flag_substance_menopausal':flag_substance_menopausal,'flag_substance_hormones':flag_substance_hormones,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 33){
                    var section = 'exposure_history_diet';
                    var particular_diet = $("input[name='particular_diet']:checked").val();
                    var diet_container = [];
                    $(".diet_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            diet_container.push($(this).attr('name'));
                        }
                    });
                    var servings_processed_meat = $("#servings_processed_meat :selected").html();
                    var servings_whole_grains = $("#servings_whole_grains :selected").html();
                    var servings_fruit_vegetables = $("#servings_fruit_vegetables :selected").html();
                    
                    $.ajax({
                        type:'POST',
                        data: {'particular_diet':particular_diet,'diet_container':diet_container,'servings_processed_meat':servings_processed_meat,'servings_whole_grains':servings_whole_grains,'servings_fruit_vegetables':servings_fruit_vegetables,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    
                    var flag1 = '';
                    var value = $("#servings_processed_meat :selected").val();
                    if(value == '3_more_servings'){
                        flag1 = 'Meat risk2';
                    }
                    if(value == '1_2_servings_week' || value == '1_2_servings'){
                        flag1 = 'Meat risk';
                    }
                    if(value == 'dont_eat_processed_meat'){
                        flag1 = 'Meat protection';
                    }

                    var flag2 = '';
                    var value = $("#servings_whole_grains :selected").val();
                    if(value == '3_more_servings_grain'){
                        flag2 = 'Grain protection';
                    }
                    if(value == 'dont_eat_processed_meat_grain'){
                        flag2 = 'grain risk';
                    }

                    var flag3 = '';
                    var value = $("#servings_fruit_vegetables :selected").val();
                    if(value == '5_more_servings_fruit'){
                        flag3 = 'Frt Veg protection';
                    }
                    if(value == 'less_than_1_fruit'){
                        flag3 = 'Frt Veg risk';
                    }
                }
                if(currentStep == 34){
                    var section = 'exposure_history_excercise';
                    var how_often_excercise = $("#how_often_excercise :selected").html();
                    
                    $.ajax({
                        type:'POST',
                        data: {'how_often_excercise':how_often_excercise,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });


                    var flag_excercise = '';
                    var value = $('#how_often_excercise :selected').val();
                    if(value == '3_4_per_month' || value == '5_per_week'){
                        flag_excercise = 'Exercise protection';
                    }

                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_excercise':flag_excercise,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });
                }
                if(currentStep == 35){
                    var section = 'exposure_history_excercise';
                    var typical_stress_late = $("#typical_stress_late :selected").html();
                    var stress_container = [];
                    $(".stress_container input[type='checkbox']").each(function(){
                        if($(this).is(':checked')){
                            stress_container.push($(this).attr('name'));
                        }
                    });
                    
                    $.ajax({
                        type:'POST',
                        data: {'typical_stress_late':typical_stress_late,'stress_container':stress_container,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });



                    var flag_finances = '';
                    var flag_relationships = '';
                    var flag_work_related = '';
                    if($("input[name=finances]").is(":checked")){
                         flag_finances = 'Prosperity Prospect';
                    }
                    if($("input[name=relationships]").is(":checked")){
                         flag_relationships = 'Refer to relationship coach';
                    }
                    if($("input[name=work_related]").is(":checked")){
                         flag_work_related = 'Refer to career coach';
                    }

                    var section = 'save_flag';
                    $.ajax({
                        type:'POST',
                        data: {'flag_finances':flag_finances,'flag_relationships':flag_relationships,'flag_work_related':flag_work_related,'section':section,'pid':pid},
                        url: url,
                        success:function(res){
                            if(res == 'added'){
                                //showAlert('Saved');
                            }
                        }
                    });

                    showAlert(flag);
                    return false; 
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
                format: "mm",
                viewMode: "months", 
                minViewMode: "months"
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
                                    <h3>Hi there, please fill out and submit this form.</h3>
                                </div>
                            </div> 
                            <div class="tab" style="text-align: center;">
                                <div>
                                    <h1 id="register">Intake</h1>
                                    <h3>Visit 3</h3>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: tobacco</h1>
                                    <h3>Section 1</h3>
                                    <h4>7 Questions</h4>
                                </div>
                            </div>

                            <div class="tab tobacco1">
                                <h1 id="register">Exposure History: tobacco</h1>

                                <h3>Do you, or have you ever used any form of tobacco products?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="tobacco_products" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="tobacco_products" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>

                            </div>

                            <div class="tab tabocoo_container personal_cancer_tab tobacco2">
                                <h1 id="register">Exposure History: tobacco</h1>
                                
                                <h3>Which of following forms of tobacco have you used?</h3>
                                <div class="">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Cigarettes
                                                </span><input type="checkbox" id="terms_conditions" name="cigarettes">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Other forms of tobacco smoke (eg. cigar, pipe smoking, e-cigarette or vape pens, hookah)
                                                </span><input type="checkbox" id="other_forms_tobaco" name="other_forms_tobaco">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Smokeless tobacco, such as chewing tobacco or snuff
                                                </span><input type="checkbox" id="terms_conditions" name="smokeless_tobacco">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">None of the above
                                                </span><input type="checkbox" id="terms_conditions" name="none_of_above_tobaco">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab tobacco3">
                                <h1 id="register">Exposure History: tobacco</h1>
                                
                                <h3>What is the average number of cigarettes that you smoke per day? [enter a zero if you do not smoke cigarettes]</h3>
                                <p><input type="text" placeholder=""  name="average_number_cigarettes"></p>
                            </div>
                            <div class="tab tobacco4">
                                <h1 id="register">Exposure History: tobacco</h1>
                                
                                <h3>How many years have you smoked / did you smoke?</h3>
                                <p><input type="text" placeholder=""  name="years_smoked"></p>
                            </div>

                            <div class="tab tobacco4">
                                <h1 id="register">Exposure History: tobacco</h1>
                                
                                <h3>When was your last use?</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="last_use" name="weight" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="24-48" selected>within 24-48 hrs</option>
                                            <option value="within_a_week">within a week</option>
                                            <option value="within_a_month">within a month</option>
                                            <option value="more_than_a_month">more than a month ago but less than 3 months</option>
                                            <option value="3-12">between 3 and 12 months</option>
                                            <option value="more_than_a_year">more than a year ago but less than 15 years ago</option>
                                            <option value="more_than_15_years">more than 15 years ago</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="tab tobacco5">
                                <h1 id="register">Exposure History: tobacco</h1>
                                
                                <h3>How long have you used ____?</h3>

                                <label class="container"><span class="text">less than a month
                                        </span><input type="radio" name="how_long_used" value="less_than_month">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">more than a month but less than 3 months
                                        </span><input type="radio" name="how_long_used" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">between 3 and 12 months
                                        </span><input type="radio" name="how_long_used" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">more than a year ago but less than 5 years
                                        </span><input type="radio" name="how_long_used" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">more than 5 years but less than 10 years
                                        </span><input type="radio" name="how_long_used" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">more than 10 years
                                        </span><input type="radio" name="how_long_used" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                
                            </div>
                            
                            <div class="tab tobacco6">
                                <h1 id="register">Exposure History: tobacco</h1>
                                
                                <h3>Have you ever been exposed to secondhand tobacco smoke?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="secondhand_tobacco" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="secondhand_tobacco" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab tobacco7">
                                <h1 id="register">Exposure History: tobacco</h1>
                                
                                <h3>For how many years did you inhale second hand smoke?</h3>
                                <p><input type="text" placeholder=""  name="inhale_second_hand_smoke"></p>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: alcohol</h1>
                                    <h3>Section 2</h3>
                                    <h4>7 Questions</h4>
                                </div>
                            </div>

                            <div class="tab alcohlic_drinks_container personal_cancer_tab alcohol1">
                                <h1 id="register">Exposure History: alcohol</h1>
                                
                                <h3>On average, how many alcohlic drinks do you consume? (choose the best answer)</h3>
                                <div class="">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">4 or more per day
                                                </span><input type="checkbox" id="terms_conditions" name="4_more_day">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">1-3 per day
                                                </span><input type="checkbox" id="other_forms_tobaco" name="1_3_per_day">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">1-3 per week
                                                </span><input type="checkbox" id="terms_conditions" name="1_3_per_week">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">1-3 per month
                                                </span><input type="checkbox" id="terms_conditions" name="1_3_month">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">1-3 a year, or on special occasions only
                                                </span><input type="checkbox" id="terms_conditions" name="1_3_year">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">I do not consume alcohol
                                                </span><input type="checkbox" id="consume_alchohol" name="consume_alchohol">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab alcohol2">
                                <h1 id="register">Exposure History: alcohol</h1>
                                
                                <h3>Did you drink alcohol in the past?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="drink_alcohol_in_past" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="drink_alcohol_in_past" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab alcohol3">
                                <h1 id="register">Exposure History: alcohol</h1>
                                
                                <h3>When was your last drink?</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="last_drink" name="weight" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="within_a_week" selected>within a week</option>
                                            <option value="within_a_month">within a month</option>
                                            <option value="less_than_3">more than a month ago but less than 3 months</option>
                                            <option value="3_12_month">between 3 and 12 months</option>
                                            <option value="5_year_ago">more than a year ago but less than 5 years ago</option>
                                            <option value="more_than_5_year">more than 5 years ago but less than 10 years ago</option>
                                            <option value="more_than_10_years">more than 10 years ago</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab alcohlic_drinks_prefer_container personal_cancer_tab alcohol4">
                                <h1 id="register">Exposure History: alcohol</h1>
                                
                                <h3>What type of alcoholic drink do you prefer to consume? (check all that apply)</h3>
                                <div class="">
                                    <div class="row">
                                        <div class="col-lg-2" id="p1">
                                            <label class="container"><span class="text">Beer or hard cider
                                                </span><input type="checkbox" id="terms_conditions" name="beer_hard_cider">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-2 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Wine or wine coolers
                                                </span><input type="checkbox" id="other_forms_tobaco" name="wine_coolers">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-2 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Whisky or cocktails
                                                </span><input type="checkbox" id="terms_conditions" name="whisky_cocktails">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab alcohol5">
                                <h1 id="register">Exposure History: alcohol</h1>
                                
                                <h3>Do you or have you ever used illicit drugs or become addicted to prescription drugs?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="illicit_drugs" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="illicit_drugs" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            
                            <div class="tab substances_container personal_cancer_tab alcohol6">
                                <h1 id="register">Exposure History: alcohol</h1>
                                
                                <h3>What is/are your substances of choice?</h3>
                                <div class="">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">crack, cocaine, methamphetamine, other
                                                </span><input type="checkbox" id="terms_conditions" name="beer_hard_cider">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Ativan, Xanax, or other nonalcohol
                                                </span><input type="checkbox" id="other_forms_tobaco" name="wine_coolers">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">mushrooms, PCP, LSD acid, or other hallucinogenic agent
                                                </span><input type="checkbox" id="terms_conditions" name="whisky_cocktails">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">heroin or other intravenously administered drug
                                                </span><input type="checkbox" id="terms_conditions" name="heroin_or">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">methadone, Oxycontin, or other oral form of narcotic
                                                </span><input type="checkbox" id="terms_conditions" name="methadone_oxycontin">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="other_substance">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input type="textbox" placeholder="other, please specify" name="other_substance_text"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="tab alcohol7">
                                <h1 id="register">Exposure History: alcohol</h1>
                                
                                <h3>When was your last use?</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="last_use_alchohol" name="exposure_last_use" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="less_than_month" selected>less than a month</option>
                                            <option value="more_less_6months">more than a month but less than 6 months</option>
                                            <option value="between_6_24">between 6 and 24 months</option>
                                            <option value="more_than_2_year">more than 2 years ago but less than 5 years</option>
                                            <option value="last_more_than_5_year">more than 5 years but less than 10 years</option>
                                            <option value="last_more_than_10">more than 10 years</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: environment</h1>
                                    <h3>Section 3</h3>
                                    <h4>5 Questions</h4>
                                </div>
                            </div>

                            <div class="tab environment1">
                                <h1 id="register">Exposure History: environment</h1>

                                <h3>Have you lived in or near a large city for at least 10 years of your life?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="live_large_city" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="live_large_city" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab personal_cancer_tab environment2">
                                <h1 id="register">Exposure History: environment</h1>
                                
                                <h3>To your knowledge: Have you ever been exposed to any of the following without wearing adequate protection?</h3>
                                <div class="protection_cantainer">
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Aflatoxins
                                                </span><input type="checkbox" id="terms_conditions" name="Aflatox">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Aristolochic Acids
                                                </span><input type="checkbox" id="terms_conditions" name="AristoAcids">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Asbestos
                                                </span><input type="checkbox" id="terms_conditions" name="Asbestos">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">Arsenic
                                                </span><input type="checkbox" id="terms_conditions" name="Arsenic">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset padding-0" id="p5">
                                            <label class="container"><span class="text">Benzene
                                                </span><input type="checkbox" id="terms_conditions" name="Benzene">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Benzidine
                                                </span><input type="checkbox" id="terms_conditions" name="Benzidine">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Beryllium
                                                </span><input type="checkbox" id="terms_conditions" name="Beryllium">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">1,3-Butadiene
                                                </span><input type="checkbox" id="terms_conditions" name="Butadiene">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">Cadmium
                                                </span><input type="checkbox" id="terms_conditions" name="Cadmium">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p5">
                                            <label class="container"><span class="text">Chromium
                                                </span><input type="checkbox" id="terms_conditions" name="Chromium">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Coal Tar
                                                </span><input type="checkbox" id="terms_conditions" name="Coal">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Coke
                                                </span><input type="checkbox" id="terms_conditions" name="Coke">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Erionite
                                                </span><input type="checkbox" id="terms_conditions" name="Erionite">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">Ethylene Oxide
                                                </span><input type="checkbox" id="terms_conditions" name="EthylOx">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p5">
                                            <label class="container"><span class="text">Formaldehyde
                                                </span><input type="checkbox" id="terms_conditions" name="Formald">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Silica
                                                </span><input type="checkbox" id="terms_conditions" name="Silica">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Soot (including that from chimneys or burn pits)
                                                </span><input type="checkbox" id="terms_conditions" name="Soot">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Sulfuric acid mist
                                                </span><input type="checkbox" id="terms_conditions" name="SulfAcid">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">Mustard gas
                                                </span><input type="checkbox" id="terms_conditions" name="Mustard">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p5">
                                            <label class="container"><span class="text">Talcum powder
                                                </span><input type="checkbox" id="terms_conditions" name="Talcum">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Thorium
                                                </span><input type="checkbox" id="terms_conditions" name="Thorium">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Trichloroethylene
                                                </span><input type="checkbox" id="terms_conditions" name="Trichleth">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Vinyl Chloride
                                                </span><input type="checkbox" id="terms_conditions" name="VinylCl">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p4">
                                            <label class="container"><span class="text">Wood Dust
                                                </span><input type="checkbox" id="terms_conditions" name="WoodDust">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset padding-0" id="p5">
                                            <label class="container"><span class="text">NONE OF THE ABOVE
                                                </span><input type="checkbox" id="terms_conditions" name="NoToxExp">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="tab environment3">
                                <h1 id="register">Exposure History: environment</h1>
                                
                                <h3>What's the total amount of time you were exposed to these substances without protective gear?</h3>
                                <p><input type="text" placeholder=""  name="total_amount_exposed"></p>
                            </div>

                            <div class="tab environment4">
                                <h1 id="register">Exposure History: environment</h1>
                                
                                <h3>Do you use only all-natural home cleaning products?</h3>

                                <label class="container"><span class="text">Yes
                                        </span><input type="radio" name="all_natural_home" value="yes" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">No
                                        </span><input type="radio" name="all_natural_home" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                            </div>
                            <div class="tab environment5">
                                <h1 id="register">Exposure History: environment</h1>
                                
                                <h3>Do you use only all-natural beauty products?</h3>

                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="all_natural_beauty" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="all_natural_beauty" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: radiation</h1>
                                    <h3>Section 4</h3>
                                    <h4>7 Questions</h4>
                                </div>
                            </div>

                            <div class="tab radiation1">
                                <h1 id="register">Exposure History: radiation</h1>
                                
                                <h3>Have you ever used a tanning bed or sunlamp?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="tanning_bed" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="tanning_bed" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>

                            </div>

                            <div class="tab radiation2">
                                <h1 id="register">Exposure History: radiation</h1>
                                
                                <h3>Did you have severe, repeated sunburns as a child?</h3>
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="severe_sunburns" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="severe_sunburns" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab exposure_history_apply radiation3">
                                <h1 id="register">Exposure History: radiation</h1>
                                
                                <h3>Which of the following apply to you?</h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have fair skin
                                            </span><input type="checkbox" id="terms_conditions" name="FairSkin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2">
                                        <label class="container"><span class="text">I have blue, green, or hazel eyes
                                            </span><input type="checkbox" id="other_forms_tobaco" name="BlueEyes">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">I have naturally blonde or red hair
                                            </span><input type="checkbox" id="terms_conditions" name="RedHair">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab radiation4">
                                <h1 id="register">Exposure History: radiation</h1>
                                
                                <h3>To your knowledge, have you ever been exposed to radon gas?</h3>
                                
                                <div class="label-container yes_no_container">
                                    <label class="container col-lg-5 yes_no_container_checked"><span class="text">Yes
                                            </span><input type="radio" name="knowledge_exposed" value="yes" checked>
                                            <span class="checkmark_radio"></span>
                                    </label>
                                    <label class="container col-lg-5"><span class="text">No
                                            </span><input type="radio" name="knowledge_exposed" value="no">
                                            <span class="checkmark_radio"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="tab radiation5">
                                <h1 id="register">Exposure History: radiation</h1>
                                
                                <h3>Do you have a radon detector in your home?</h3>
                                <label class="container"><span class="text">Yes
                                        </span><input type="radio" name="radon_detector" value="yes" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">No
                                        </span><input type="radio" name="radon_detector" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">I don't know
                                        </span><input type="radio" name="radon_detector" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                            </div>

                            <div class="tab radiation6">
                                <h1 id="register">Exposure History: radiation</h1>
                                
                                <h3>Have you ever received radiation therapy?</h3>
                                <label class="container"><span class="text">No,  never
                                        </span><input type="radio" name="received_radiation" value="no_never" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Yes, I was radiated in the following areas:
                                        </span><input type="radio" name="received_radiation" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>

                                
                                <h3>What areas of your body were radiated?</h3>
                                    <div class="row radiated_container">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">Brain or Head/neck
                                                </span><input type="checkbox" id="terms_conditions" name="brain_head_neck">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">Chest/thorax
                                                </span><input type="checkbox" id="other_forms_tobaco" name="chest_thorax">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Abdomen
                                                </span><input type="checkbox" id="terms_conditions" name="abdomen">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">Pelvis
                                                </span><input type="checkbox" id="terms_conditions" name="pelvis">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                            <label class="container"><span class="text">
                                                </span><input type="checkbox" id="terms_conditions" name="extremity">
                                                <span class="checkmark"></span>
                                            </label>
                                            <p class="other_p"><input type="textbox" placeholder="Extremity, please specify" name="extremity_text"></p>
                                        </div>

                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">right hand/arm/shoulder
                                                </span><input type="checkbox" id="terms_conditions" name="right_hand_arm">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">left hand/arm/shoulder
                                                </span><input type="checkbox" id="terms_conditions" name="left_hand_arm">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">right foot/leg/hip
                                                </span><input type="checkbox" id="terms_conditions" name="right_foot_leg">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">left foot/leg/hip
                                                </span><input type="checkbox" id="terms_conditions" name="left_foot_leg">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                            </div>

                            <div class="tab radiation7">
                                <h1 id="register">Exposure History: radiation</h1>
                                
                                <h3>How old were you when you started radiation therapy?</h3>
                                <p><input type="text" placeholder=""  name="started_radiation_therapy"></p>
                            </div>
                            
                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: exogenous hormones</h1>
                                    <h3>Section 5</h3>
                                    <h4>3 Questions</h4>
                                </div>
                            </div>

                            <div class="tab exogenous_hormones1">
                                <h1 id="register">Exposure History: exogenous hormones</h1>
                                
                                <h3>Have you ever taken oral contraceptives (birth control pills)?</h3>
                                <label class="container"><span class="text">No,  never
                                        </span><input type="radio" name="oral_contraceptives" value="no" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Yes, I take / have taken the following:
                                        </span><input type="radio" name="oral_contraceptives" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <br>
                                <h3>What did/are you taking? (If you are still taking, please make sure to list them in the medication/supplement section)</h3>
                                <p><input type="text" placeholder=""  name="what_did_taking"></p>

                                <h3>For how many years have you used oral contraceptives in total?</h3>
                                <p><input type="text" placeholder=""  name="how_many_years"></p>
                            </div>
                            <div class="tab exogenous_hormones2">
                                <h1 id="register">Exposure History: exogenous hormones</h1>

                                <h3>Have you ever taken postmenopausal hormones?</h3>
                                <label class="container"><span class="text">No,  never
                                        </span><input type="radio" name="postmenopausal_hormones" value="no" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Yes, I take / have taken the following:
                                        </span><input type="radio" name="postmenopausal_hormones" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <br>
                                
                                <h3>What did/are you taking? (If you are still taking, please make sure to list them in the medication/supplement section)</h3>
                                <p><input type="text" placeholder=""  name="what_did_taking_postmenopausal"></p>
                                <br>

                                <h3>For how many years have you used postmenopausal hormones in total?</h3>
                                <p><input type="text" placeholder=""  name="postmenopausal_hormones_total"></p>
                                <br>
                            </div>
                            <div class="tab exogenous_hormones3">
                                <h1 id="register">Exposure History: exogenous hormones</h1>

                                <h3>Have you ever taken any of the following? (If you are still taking any of these, please make sure to list them in the medication/supplement section)</h3>
                                <div class="row medication_container">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">tamoxifen
                                            </span><input type="checkbox" id="terms_conditions" name="tamoxifen">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p2">
                                        <label class="container"><span class="text">raloxifene (Evista)
                                            </span><input type="checkbox" id="other_forms_tobaco" name="raloxifene">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">finasteride (Proscar)
                                            </span><input type="checkbox" id="terms_conditions" name="finasteride">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">dutasteride (Avodart)
                                            </span><input type="checkbox" id="terms_conditions" name="dutasteride">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                        <label class="container"><span class="text">none of the above
                                            </span><input type="checkbox" id="terms_conditions" name="none_of_above_medication">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <br>

                                <h3>For how many years did you take/have you taken ___?</h3>
                                <p><input type="text" placeholder=""  name="how_many_years_medication"></p>
                                <br>

                                <h3>Did your mother take diethylstilbesterol while she was pregnant with you?</h3>
                                <label class="container"><span class="text">Yes
                                        </span><input type="radio" name="diethylstilbesterol" value="yes" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">No
                                        </span><input type="radio" name="diethylstilbesterol" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">I don't know
                                        </span><input type="radio" name="diethylstilbesterol" value="no">
                                        <span class="checkmark_radio"></span>
                                </label>

                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: Immno Supressive Therapy</h1>
                                    <h3>Section 6</h3>
                                    <h4>1 Question</h4>
                                </div>
                            </div>

                            <div class="tab immno_container immno_supressive_therapy1">
                                <h1 id="register">Exposure History: Immno Supressive Therapy</h1>

                                <h3>What types of immunosuppressive therapy have you received? (If you are still taking any of these, please make sure to list them in the medication/supplement section)</h3>
                                <div class="div_immno_supressive_therapy1">
                                    <div class="row ">
                                        <div class="col-lg-3" id="p1">
                                            <label class="container"><span class="text">corticosteroids, such as prednisone (Deltasone, Orasone), budesonide (Entocort EC), or prednisolone (Millipred)
                                                </span><input type="checkbox" id="terms_conditions" name="corticosteroids">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset" id="p2">
                                            <label class="container"><span class="text">cyclosporine (Neoral, Sandimmune, SangCya) and/or tacrolimus (Astagraf XL, Envarsus XR, Prograf)
                                                </span><input type="checkbox" id="other_forms_tobaco" name="cyclosporine">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">IMDH inhibitors such as azathioprine (Azasan, Imuran), leflunomide (Arava), or mycophenolate (CellCept, Myfortic)
                                                </span><input type="checkbox" id="terms_conditions" name="inhibitors">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-lg-3 col-half-offset padding-0" id="p3">
                                            <label class="container"><span class="text">mTOR inhibitors such as sirolimus (Rapamune) or everolimus (Afinitor, Zortress)
                                                </span><input type="checkbox" id="terms_conditions" name="sirolimus">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <h3>Any of the following biologic agents: </h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">abatacept (Orencia)
                                            </span><input type="checkbox" id="terms_conditions" name="abatacept">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">adalimumab (Humira)
                                            </span><input type="checkbox" id="terms_conditions" name="adalimumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">anakinra (Kineret)
                                            </span><input type="checkbox" id="terms_conditions" name="anakinra">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">certolizumab (Cimzia)
                                            </span><input type="checkbox" id="terms_conditions" name="certolizumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>

                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">etanercept (Enbrel)
                                            </span><input type="checkbox" id="terms_conditions" name="etanercept">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">golimumab (Simponi)
                                            </span><input type="checkbox" id="terms_conditions" name="golimumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">infliximab (Remicade)
                                            </span><input type="checkbox" id="terms_conditions" name="infliximab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">ixekizumab (Taltz)
                                            </span><input type="checkbox" id="terms_conditions" name="ixekizumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">natalizumab (Tysabri)
                                            </span><input type="checkbox" id="terms_conditions" name="natalizumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">rituximab (Rituxan)
                                            </span><input type="checkbox" id="terms_conditions" name="rituximab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">secukinumab (Cosentyx)
                                            </span><input type="checkbox" id="terms_conditions" name="secukinumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">tocilizumab (Actemra)
                                            </span><input type="checkbox" id="terms_conditions" name="tocilizumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">ustekinumab (Stelara)
                                            </span><input type="checkbox" id="terms_conditions" name="ustekinumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">vedolizumab (Entyvio)
                                            </span><input type="checkbox" id="terms_conditions" name="vedolizumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">basiliximab (Simulect)
                                            </span><input type="checkbox" id="terms_conditions" name="basiliximab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">daclizumab (Zinbryta)
                                            </span><input type="checkbox" id="terms_conditions" name="daclizumab">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">tofacitinib (Xeljanz)
                                            </span><input type="checkbox" id="terms_conditions" name="tofacitinib">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">none of the above
                                            </span><input type="checkbox" id="terms_conditions" name="none_of_above_agents">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <br>
                                
                                <h3>For how many years did you take/have you taken ___?</h3>
                                <p><input type="text" placeholder=""  name="how_many_years_suppresssive"></p>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History:  Other Substances</h1>
                                    <h3>Section 8</h3>
                                    <h4>1 Question</h4>
                                </div>
                            </div>

                            <div class="tab other_substances_container other_substances1">
                                <h1 id="register">Exposure History:  Other Substances</h1>

                                <h3>Do any of the following apply to you? </h3>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken aspirin for 6 years or more
                                            </span><input type="checkbox" id="terms_conditions" name="substance_saspirin">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken tamoxifen for 5 years or more
                                            </span><input type="checkbox" id="terms_conditions" name="substance_tamoxifen">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken raloxifene for 5 years or more 
                                            </span><input type="checkbox" id="terms_conditions" name="substance_raloxifene">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken finasteride for 5 years or more 
                                            </span><input type="checkbox" id="terms_conditions" name="substance_finasteride">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken dutasteride for 5 years or more
                                            </span><input type="checkbox" id="terms_conditions" name="substance_dutasteride">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken birth control pills for 5 years or more
                                            </span><input type="checkbox" id="terms_conditions" name="substance_birth_control">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken menopausal hormones for 5 to 9 years  
                                            </span><input type="checkbox" id="terms_conditions" name="substance_menopausal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">I have taken menopausal hormones for 10 years or more 
                                            </span><input type="checkbox" id="terms_conditions" name="substance_hormones">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <br>
                                
                                <h3>Tell us about any other substances that you have been exposed to that you are worried about.</h3>
                                <p><input type="text" placeholder=""  name="tell_us_other_substances"></p>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: diet</h1>
                                    <h3>Section 9</h3>
                                    <h4>1 Question</h4>
                                </div>
                            </div>

                            <div class="tab diet_container diet1">
                                <h1 id="register">Exposure History: diet</h1>
                                <h3>Do you follow a particular diet?</h3>
                                <label class="container"><span class="text">No, I don't restrict my eating
                                        </span><input type="radio" name="particular_diet" value="no" checked>
                                        <span class="checkmark_radio"></span>
                                </label>
                                <label class="container"><span class="text">Yes, I follow the following diet(s):
                                        </span><input type="radio" name="particular_diet" value="yes">
                                        <span class="checkmark_radio"></span>
                                </label>

                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Vegan
                                            </span><input type="checkbox" id="terms_conditions" name="vegan">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Vegetarian (strictly plant-based, but not vegan)
                                            </span><input type="checkbox" id="terms_conditions" name="vegetarian_vegan">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Lacto-ovo (fruits & vegetables plus eggs and/or dairy) 
                                            </span><input type="checkbox" id="terms_conditions" name="lacto_ovo">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Pescetarian (fruits & vegetables plus fish and/or eggs/dairy)
                                            </span><input type="checkbox" id="terms_conditions" name="pescetarian">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Gluten-free
                                            </span><input type="checkbox" id="terms_conditions" name="gluten_free">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Low carb
                                            </span><input type="checkbox" id="terms_conditions" name="low_carb">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Low fat  
                                            </span><input type="checkbox" id="terms_conditions" name="low_fat">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Kosher 
                                            </span><input type="checkbox" id="terms_conditions" name="kosher">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Halal
                                            </span><input type="checkbox" id="terms_conditions" name="halal">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Mediterranean
                                            </span><input type="checkbox" id="terms_conditions" name="mediterranean">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Keto
                                            </span><input type="checkbox" id="terms_conditions" name="keto">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Paleo 
                                            </span><input type="checkbox" id="terms_conditions" name="paleo">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="paleo">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other, please specify" name="other_diet"></p>
                                    </div>
                                </div>
                                
                                <br>
                                
                                <h3>On average, how many servings of processed meat do you consume per week? (Processed meats include foods like: ham, hot dogs, bacon, and sausage)</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="servings_processed_meat" name="servings_processed_meat" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="3_more_servings" selected>3 or more servings per week</option>
                                            <option value="1_2_servings_week">1 to 2 servings per week</option>
                                            <option value="1_2_servings">1 to 2 servings every 2 to 3 weeks</option>
                                            <option value="dont_eat_processed_meat">I don’t eat processed meat</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <h3>On average, how many of servings of whole grains do you consume per day?(A serving is one slice of whole wheat bread, 1 ounce of whole grain or bran cereal, 1 ounce of popcorn, or 1 cup of cooked oatmeal, whole grain pasta, rice, or quinoa)</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="servings_whole_grains" name="servings_whole_grains" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="3_more_servings_grain" selected>3 or more servings per day</option>
                                            <option value="1_2_servings_week_grain">1 to 2 servings per week</option>
                                            <option value="1_2_servings_grain">1 to 2 servings every 2 to 3 weeks</option>
                                            <option value="dont_eat_processed_meat_grain">I don’t eat processed meat</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <h3>On average, how many servings of fruit and vegetables do you consume per day?(A serving is one medium apple, banana, or orange; 1 cup of raw leafy vegetable (like spinach or lettuce); ½ cup of cooked beans or peas; ½ cup of chopped, cooked, or canned fruit/vegetable; or ¾ cup of fruit/vegetable juice.)</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="servings_fruit_vegetables" name="servings_fruit_vegetables" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="5_more_servings_fruit" selected>5 or more servings per day</option>
                                            <option value="3_4_servings_week_fruit">3 to 4 servings per week</option>
                                            <option value="1_2_servings_fruit">1 to 2 servings per day</option>
                                            <option value="less_than_1_fruit">Less than 1 serving per day</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: exercise</h1>
                                    <h3>Section 10</h3>
                                    <h4>1 Question</h4>
                                </div>
                            </div>

                            <div class="tab exercise1">
                                <h1 id="register">Exposure History: exercise</h1>

                                <h3>How often do you exercise?</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="how_often_excercise" name="how_often_excercise" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="i_dont_really" selected>I don’t really </option>
                                            <option value="30_60_per_month">30-60 minutes 1-2 times per month</option>
                                            <option value="30_60_per_week">30-60 minutes 1-2 times per week</option>
                                            <option value="3_4_per_month">30-60 minutes 3-4 times per week</option>
                                            <option value="5_per_week">30-60 minutes 5+ times per week</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="tab introduction" style="text-align: center;">
                                <div>
                                    <h1 id="register">Exposure History: stress</h1>
                                    <h3>Section 11</h3>
                                    <h4>1 Question</h4>
                                </div>
                            </div>

                            <div class="tab stress1">
                                <h1 id="register">Exposure History: stress</h1>

                                <h3>Please indicate your typical stress level of late.</h3>
                                <div class="row">
                                    <div class="col-lg-6" id="p1">
                                        <select class="form-select" id="typical_stress_late" name="typical_stress_late" style="height: 48px;">
                                            <option value="">Select</option>
                                            <option value="zen" selected>I am always zen! </option>
                                            <option value="usually_relaxed">I am usually relaxed</option>
                                            <option value="stress_levels">My stress levels varies widely</option>
                                            <option value="stressed_easily">I get stressed easily</option>
                                            <option value="always_stressed_out">I am always stressed out</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <h3>What is/are the primary source(s) of your stress at this time? (check all that apply)</h3>
                                <div class="row stress_container">
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Finances
                                            </span><input type="checkbox" id="terms_conditions" name="finances">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Relationships
                                            </span><input type="checkbox" id="terms_conditions" name="relationships">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3" id="p1">
                                        <label class="container"><span class="text">Work-related 
                                            </span><input type="checkbox" id="terms_conditions" name="work_related">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="col-lg-3 col-half-offset" id="p4" style="position: relative;">
                                        <label class="container"><span class="text">
                                            </span><input type="checkbox" id="terms_conditions" name="other_primary_source">
                                            <span class="checkmark"></span>
                                        </label>
                                        <p class="other_p"><input type="textbox" placeholder="Other (please specify)" name="other_primary_source_text"></p>
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
