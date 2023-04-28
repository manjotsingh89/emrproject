<?php

/**
 * Patient Portal Home
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Shiqiang Tao <StrongTSQ@gmail.com>
 * @author    Ben Marte <benmarte@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2021 Ben Marte <benmarte@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('verify_session.php');
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once('lib/portal_mail.inc');
require_once(__DIR__ . '/../library/appointments.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Events\PatientPortal\RenderEvent;
use OpenEMR\Events\PatientPortal\AppointmentFilterEvent;

if (isset($_SESSION['register']) && $_SESSION['register'] === true) {
    require_once(__DIR__ . '/../src/Common/Session/SessionUtil.php');
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

if (!isset($_SESSION['portal_init'])) {
    $_SESSION['portal_init'] = true;
}

// Get language definitions for js
$language = $_SESSION['language_choice'] ?? '1'; // defaults english
$sql = "SELECT c.constant_name, d.definition FROM lang_definitions as d
        JOIN lang_constants AS c ON d.cons_id = c.cons_id
        WHERE d.lang_id = ?";
$tarns = sqlStatement($sql, $language);
$language_defs = array();
while ($row = SqlFetchArray($tarns)) {
    $language_defs[$row['constant_name']] = $row['definition'];
}

$whereto = $_SESSION['whereto'] ?? null;

$user = $_SESSION['sessionUser'] ?? 'portal user';
$result = getPatientData($pid);
// echo '<pre>';print_r($result);die;

$msgs = getPortalPatientNotes($_SESSION['portal_username']);
$msgcnt = count($msgs);
$newcnt = 0;
foreach ($msgs as $i) {
    if ($i['message_status'] == 'New') {
        $newcnt += 1;
    }
}
if ($newcnt > 0 && $_SESSION['portal_init']) {
    $whereto = $_SESSION['whereto'] = '#secure-msgs-card';
}
$messagesURL = $GLOBALS['web_root'] . '' . '/portal/messaging/messages.php';

$isEasyPro = $GLOBALS['easipro_enable'] && !empty($GLOBALS['easipro_server']) && !empty($GLOBALS['easipro_name']);

$current_date2 = date('Y-m-d');
$apptLimit = 30;
$appts = fetchNextXAppts($current_date2, $pid, $apptLimit);

$appointments = array();

if ($appts) {
    $stringCM = '(' . xl('Comments field entry present') . ')';
    $stringR = '(' . xl('Recurring appointment') . ')';
    $count = 0;
    foreach ($appts as $row) {
        $status_title = getListItemTitle('apptstat', $row['pc_apptstatus']);
        $count++;
        $dayname = xl(date('l', strtotime($row['pc_eventDate'])));
        $dispampm = 'am';
        $disphour = (int)substr($row['pc_startTime'], 0, 2);
        $dispmin = substr($row['pc_startTime'], 3, 2);
        if ($disphour >= 12) {
            $dispampm = 'pm';
            if ($disphour > 12) {
                $disphour -= 12;
            }
        }

        if ($row['pc_hometext'] != '') {
            $etitle = xl('Comments') . ': ' . $row['pc_hometext'] . "\r\n";
        } else {
            $etitle = '';
        }

        $formattedRecord = [
            'appointmentDate' => $dayname . ', ' . $row['pc_eventDate'] . ' ' . $disphour . ':' . $dispmin . ' ' . $dispampm,
            'appointmentType' => xl('Type') . ': ' . $row['pc_catname'],
            'provider' => xl('Provider') . ': ' . $row['ufname'] . ' ' . $row['ulname'],
            'status' => xl('Status') . ': ' . $status_title,
            'mode' => (int)$row['pc_recurrtype'] > 0 ? 'recurring' : $row['pc_recurrtype'],
            'icon_type' => (int)$row['pc_recurrtype'] > 0,
            'etitle' => $etitle,
            'pc_eid' => $row['pc_eid'],
        ];
        $filteredEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch(new AppointmentFilterEvent($row, $formattedRecord), AppointmentFilterEvent::EVENT_NAME);
        $appointments[] = $filteredEvent->getAppointment() ?? $formattedRecord;
    }
}

function buildNav($newcnt, $pid, $result)
{
    $navItems = [
        [
            'url' => '#',
            'label' => $result['fname'] . ' ' . $result['lname'],
            'icon' => 'fa-user',
            'dropdownID' => 'account',
            'messageCount' => $newcnt ?? 0,
            'children' => [
                [
                    'url' => '#profilecard',
                    'label' => xl('My Profile'),
                    'icon' => 'fa-user',
                    'dataToggle' => 'collapse',
                ],

                [
                    'url' => '#secure-msgs-card',
                    'label' => xl('My Messages'),
                    'icon' => 'fa-envelope',
                    'dataToggle' => 'collapse',
                    'messageCount' => $newcnt ?? 0,
                ],
                [
                    'url' => '#documentscard',
                    'label' => xl('My Documents'),
                    'icon' => 'fa-file-medical',
                    'dataToggle' => 'collapse'
                ],
                [
                    'url' => '#lists',
                    'label' => xl('My Dashboard'),
                    'icon' => 'fa-list',
                    'dataToggle' => 'collapse'
                ],
                [
                    'url' => '#openSignModal',
                    'label' => xl('My Signature'),
                    'icon' => 'fa-file-signature',
                    'dataToggle' => 'modal',
                    'dataType' => 'patient-signature'
                ]
            ],
        ],
        [
            'url' => '#',
            'label' => xl('Reports'),
            'icon' => 'fa-book-medical',
            'dropdownID' => 'reports',
            'children' => [
                [
                    'url' => $GLOBALS['web_root'] . '' . '/ccdaservice/ccda_gateway.php?action=view&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken()),
                    'label' => xl('View CCD'),
                    'icon' => 'fa-eye',
                    'target_blank' => 'true',
                ],
                [
                    'url' => $GLOBALS['web_root'] . '' . '/ccdaservice/ccda_gateway.php?action=dl&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken()),
                    'label' => xl('Download CCD'),
                    'icon' => 'fa-download',
                ]
            ]
        ]
    ];
    if (($GLOBALS['portal_two_ledger'] || $GLOBALS['portal_two_payments'])) {
        if (!empty($GLOBALS['portal_two_ledger'])) {
            $navItems[] = [
                'url' => '#',
                'label' => xl('Accountings'),
                'icon' => 'fa-file-invoice-dollar',
                'dropdownID' => 'accounting',
                'children' => [
                    [
                        'url' => '#ledgercard',
                        'label' => xl('Ledger'),
                        'icon' => 'fa-folder-open',
                        'dataToggle' => 'collapse'
                    ]
                ]
            ];
        }
    }

    // Build sub nav items

    if (!empty($GLOBALS['allow_portal_chat'])) {
        $navItems[] = [
            'url' => '#messagescard',
            'label' => xl('Chat'),
            'icon' => 'fa-comment-medical',
            'dataToggle' => 'collapse',
            'dataType' => 'cardgroup'
        ];
    }

    for ($i = 0, $iMax = count($navItems); $i < $iMax; $i++) {
        if ($GLOBALS['allow_portal_appointments'] && $navItems[$i]['label'] === ($result['fname'] . ' ' . $result['lname'])) {
            $navItems[$i]['children'][] = [
                'url' => '#appointmentcard',
                'label' => xl('My Appointments'),
                'icon' => 'fa-calendar-check',
                'dataToggle' => 'collapse'
            ];
        }

        if ($navItems[$i]['label'] === ($result['fname'] . ' ' . $result['lname'])) {
            array_push(
                $navItems[$i]['children'],
                [
                    'url' => 'javascript:changeCredentials(event)',
                    'label' => xl('Change Credentials'),
                    'icon' => 'fa-cog fa-fw',
                ],
                [
                    'url' => 'logout.php',
                    'label' => xl('Logout'),
                    'icon' => 'fa-ban fa-fw',
                ]
            );
        }

        if (!empty($GLOBALS['portal_onsite_document_download']) && $navItems[$i]['label'] === xl('Reports')) {
            array_push(
                $navItems[$i]['children'],
                [
                    'url' => '#reportcard',
                    'label' => xl('Report Content'),
                    'icon' => 'fa-folder-open',
                    'dataToggle' => 'collapse'
                ],
                [
                    'url' => '#downloadcard',
                    'label' => xl('Download Charted Documents'),
                    'icon' => 'fa-download',
                    'dataToggle' => 'collapse'
                ]
            );
        }
        if (!empty($GLOBALS['portal_two_payments']) && $navItems[$i]['label'] === xl('Accountings')) {
            $navItems[$i]['children'][] = [
                'url' => '#paymentcard',
                'label' => xl('Make Payment'),
                'icon' => 'fa-credit-card',
                'dataToggle' => 'collapse'
            ];
        }
    }

    return $navItems;
}

$navMenu = buildNav($newcnt, $pid, $result);

$twig = (new TwigContainer('', $GLOBALS['kernel']))->getTwig();
echo $twig->render('portal/home.html.twig', [
    'user' => $user,
    'whereto' => $_SESSION['whereto'] ?? null ?: ($whereto ?? '#documentscard'),
    'result' => $result,
    'msgs' => $msgs,
    'msgcnt' => $msgcnt,
    'newcnt' => $newcnt,
    'allow_portal_appointments' => $GLOBALS['allow_portal_appointments'],
    'web_root' => $GLOBALS['web_root'],
    'payment_gateway' => $GLOBALS['payment_gateway'],
    'gateway_mode_production' => $GLOBALS['gateway_mode_production'],
    'portal_two_payments' => $GLOBALS['portal_two_payments'],
    'allow_portal_chat' => $GLOBALS['allow_portal_chat'],
    'portal_onsite_document_download' => $GLOBALS['portal_onsite_document_download'],
    'portal_two_ledger' => $GLOBALS['portal_two_ledger'],
    'images_static_relative' => $GLOBALS['images_static_relative'],
    'youHave' => xl('You have'),
    'navMenu' => $navMenu,
    'pagetitle' => xl('Home') . ' | ' . xl('OpenEMR Portal'),
    'messagesURL' => $messagesURL,
    'patientID' => $pid,
    'patientName' => $_SESSION['ptName'] ?? null,
    'csrfUtils' => CsrfUtils::collectCsrfToken(),
    'isEasyPro' => $isEasyPro,
    'appointments' => $appointments,
    'appts' => $appts,
    'appointmentLimit' => $apptLimit,
    'appointmentCount' => $count ?? null,
    'displayLimitLabel' => xl('Display limit reached'),
    'site_id' => $_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'), // one way or another, we will have a site_id.
    'portal_timeout' => $GLOBALS['portal_timeout'] ?? 1800, // timeout is in seconds
    'language_defs' => $language_defs,
    'eventNames' => [
        'sectionRenderPost' => RenderEvent::EVENT_SECTION_RENDER_POST,
        'scriptsRenderPre' => RenderEvent::EVENT_SCRIPTS_RENDER_PRE
    ]
]);

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$webroot = $GLOBALS['web_root'];
$link = $actual_link.$webroot.'/portal/patient/onsitedocuments?pid=#PID#&questionnaire=1';
?>
<script>
    $(document).ready(function(){
        $('.nav-item .dropdown-menu li a.dropdown-item').click(function(){ 
            var html = $(this).html();
            var patient_id = $('#patient_id').val();
            var link = '<?=@$link;?>';
            if (html.indexOf('Questionnaire') > -1)
            {
                window.location.href = link.replace("#PID#", patient_id);
            }
        });
        
    });
</script>

<!-- <style>
    /* your CSS goes here*/
     body {
        background: #139595 !important;
    }

    #regForm {
        background-color: #ffffff;
        margin: 0px auto;
        font-family: Raleway;
        padding: 40px;
        border-radius: 10px
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
        height: 15px;
        width: 15px;
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
        margin-top: 30px;
        margin-bottom: 30px
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

    .container input[type="radio"] {
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
        border-radius: 50%;
    }


    /* On mouse-over, add a grey background color */

    .container:hover input~.checkmark {
        background-color: #ccc;
    }


    /* When the radio button is checked, add a blue background */

    .container input:checked~.checkmark {
        background-color: #2196F3;
    }


    /* Create the indicator (the dot/circle - hidden when not checked) */

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }


    /* Show the indicator (dot/circle) when checked */

    .container input:checked~.checkmark:after {
        display: block;
    }


    /* Style the indicator (dot/circle) */

    .container .checkmark:after {
        top: 9px;
        left: 9px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
    }

</style>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" type="text/css" rel="stylesheet" />

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="questionnaire-html container mt-5" style="display: block;">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-md-10">
                    <form id="regForm">
                        <h1 id="register">Questionnaire</h1>
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
                             
                        </div>
                        <div class="tab">
                            <h3>Donation Type:</h3>
                            <label class="container">One time
                                    <input type="radio" checked="checked" name="radio">
                                    <span class="checkmark"></span>
                            </label>
                            <label class="container">Recurring
                                    <input type="radio" name="radio">
                                    <span class="checkmark"></span>
                            </label>
                            <p><input type="text" placeholder="Amount" oninput="this.className = ''" name="amount"></p>

                        </div>
                        <div class="tab">
                            <p><input placeholder="First Name" oninput="this.className = ''" name="first"></p>
                            <p><input placeholder="Last Name" oninput="this.className = ''" name="last"></p>
                            <p><input placeholder="Email" oninput="this.className = ''" name="email"></p>
                            <p><input placeholder="Phone" oninput="this.className = ''" name="phone"></p>
                            <p><input placeholder="Street Address" oninput="this.className = ''" name="address"></p>
                            <p><input placeholder="City" oninput="this.className = ''" name="city"></p>
                            <p><input placeholder="State" oninput="this.className = ''" name="state"></p>
                            <p><input placeholder="Country" oninput="this.className = ''" name="country"></p>

                        </div>
                        <div class="tab">
                            <p><input placeholder="Credit Card #" oninput="this.className = ''" name="email"></p>
                            <p>Exp Month
                                <select id="month">
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            </p>
                            <p>Exp Year
                                <select id="year">
                                    <option value="2021">2021</option>
                                    <option value="2022">2022</option>
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                </select>
                            </p>

                            <p><input placeholder="CVV" oninput="this.className = ''" name="phone"></p>
                        </div>

                        <div class="thanks-message text-center" id="text-message"> <img src="https://i.imgur.com/O18mJ1K.png" width="100" class="mb-4">
                            <h3>Thanks for your Donation!</h3> <span>Your donation has been entered! We will contact you shortly!</span>
                        </div>
                        <div style="overflow:auto;" id="nextprevious">
                            <div style="float:right;"> <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button> <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button> </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>

<script>
    //your javascript goes here
    var currentTab = 0;
    document.addEventListener("DOMContentLoaded", function(event) {


        showTab(currentTab);

    });

    function showTab(n) {
        var x = document.getElementsByClassName("tab");
        x[n].style.display = "block";
        if (n == 0) {
            document.getElementById("prevBtn").style.display = "none";
        } else {
            document.getElementById("prevBtn").style.display = "inline";
        }
        if (n == (x.length - 1)) {
            document.getElementById("nextBtn").innerHTML = "Submit";
        } else {
            document.getElementById("nextBtn").innerHTML = "Next";
        }
        fixStepIndicator(n)
    }

    function nextPrev(n) {
        var x = document.getElementsByClassName("tab");
        if (n == 1 && !validateForm()) return false;
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

</script> -->
