<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(__DIR__ . "/../sites/default/sqlconf.php");
// print_r($sqlconf);die;
global $conn;
$conn = new mysqli($sqlconf['host'], $sqlconf['login'], $sqlconf['pass'], $sqlconf['dbase']);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if($_POST['section'] == 'demographics'){
	$delete_sql = "DELETE FROM demographics WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO demographics SET pid='".$_POST['pid']."',gender='".$_POST['gender']."',pref_pronoun='".$_POST['pronous']."',race='".$_POST['race']."',ethnicity='".$_POST['hispanic']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
	//$patient_data = sqlQuery("SELECT * FROM patient_data");
	//print_r($patient_data);die;
}
else if($_POST['section'] == 'personal_cancer_history_1'){
    $delete_sql = "DELETE FROM cahx WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO cahx SET pid='".$_POST['pid']."',PersCaHx='".$_POST['cancer']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}
else if($_POST['section'] == 'personal_cancer_history_2'){
	$sql = "UPDATE cahx SET pid='".$_POST['pid']."',ca_num='".$_POST['different_cancers']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}
else if($_POST['section'] == 'personal_cancer_history_3'){
    // print_r($_POST);die;
	$arr = $_POST['selectedArr'];
	$breast = '0';
	if(in_array('breast', $arr)){
		$breast = '1';
	}
	$testicular = '0';
	if(in_array('testicular', $arr)){
		$testicular = '1';
	}
	$liver = '0';
	if(in_array('liver', $arr)){
		$liver = '1';
	}
	$bladder = '0';
	if(in_array('bladder', $arr)){
		$bladder = '1';
	}
	$h_n = '0';
	if(in_array('h_n', $arr)){
		$h_n = '1';
	}
	$cervical = '0';
	if(in_array('cervical', $arr)){
		$cervical = '1';
	}
	$lung = '0';
	if(in_array('lung', $arr)){
		$lung = '1';
	}
	$panccholangio = '0';
	if(in_array('panccholangio', $arr)){
		$panccholangio = '1';
	}
	$melanoma = '0';
	if(in_array('melanoma', $arr)){
		$melanoma = '1';
	}
	
	$leukemia = '0';
	if(in_array('leukemia', $arr)){
		$leukemia = '1';
	}
	$uterine = '0';
	if(in_array('uterus', $arr)){
		$uterine = '1';
	}
	$colorectal = '0';
	if(in_array('colorectal', $arr)){
		$colorectal = '1';
	}
	$thyroid = '0';
	if(in_array('thyroid', $arr)){
		$thyroid = '1';
	}
	$mcsc = '0';
	if(in_array('non_melanoma', $arr)){
		$mcsc = '1';
	}
	$lymphoma = '0';
	if(in_array('lymphoma', $arr)){
		$lymphoma = '1';
	}
	$ovarian = '0';
	if(in_array('ovarian', $arr)){
		$ovarian = '1';
	}
	$esophageal = '0';
	if(in_array('esophageal', $arr)){
		$esophageal = '1';
	}
	$adrenal = '0';
	if(in_array('adrenal', $arr)){
		$adrenal = '1';
	}
	$sarcoma = '0';
	if(in_array('sarcoma', $arr)){
		$sarcoma = '1';
	}
	$pancreatic = '0';
	if(in_array('pancreatic', $arr)){
		$pancreatic = '1';
	}
	$prostate = '0';
	if(in_array('prostate', $arr)){
		$prostate = '1';
	}
	$stomach = '0';
	if(in_array('stomach', $arr)){
		$stomach = '1';
	}
	$kidney = '0';
	if(in_array('kidney', $arr)){
		$kidney = '1';
	}
	$brain = '0';
	if(in_array('brain', $arr)){
		$brain = '1';
	}
	$gbduct = '0';
	if(in_array('gbduct', $arr)){
		$gbduct = '1';
	}
	$other = '0';
	if(in_array('other', $arr)){
		$other = '1';
	}
	
	//$delete_sql = "DELETE FROM cahx WHERE pid='".$_POST['pid']."' ";
	//$conn->query($delete_sql);

	$sql = "UPDATE cahx SET pid='".$_POST['pid']."',no_cahx='".@$_POST['cancer']."',adrenal='".$adrenal."',bladder='".$bladder."',brain='".$brain."',breast='".$breast."',cervical='".$cervical."',crc='".$colorectal."',esophageal='".$esophageal."',gbduct='".$gbduct."',hn='".$h_n."',renal='0',leukemia='".$leukemia."',lymphoma='".$lymphoma."',hcc='0',lung='".$lung."',melanoma='".$melanoma."',nmsc='".$mcsc."',ovarian='".$ovarian."',pancreatic='".$pancreatic."',prostate='".$prostate."',sarcoma='".$sarcoma."',gastric='0',testicular='".$testicular."',thyroid='".$thyroid."',uterine='".$uterine."',oth_ca='".@$_POST['other_text']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
	//$patient_data = sqlQuery("SELECT * FROM patient_data");
	//print_r($patient_data);die;
}
else if($_POST['section'] == 'personal_cancer_history_4'){
    $delete_sql = "DELETE FROM ca_detail WHERE pid='".$_POST['pid']."' ";
    $conn->query($delete_sql);
    $sql = "INSERT INTO ca_detail SET pid='".$_POST['pid']."',her2status='".$_POST['hr2_status']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}
else if($_POST['section'] == 'personal_cancer_history_5'){
	$sql = "UPDATE ca_detail SET pid='".$_POST['pid']."',hr_status='".$_POST['hr_status']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}
else if($_POST['section'] == 'personal_cancer_history_6'){
    //print_r($_POST);die;
    $sql = "SELECT * FROM ca_detail WHERE pid='".$_POST['pid']."' ";
    $check = $conn->query($sql);
    if($check->num_rows){
        $cancer_type = implode(",",$_POST['cancer_type']);
        $age_diagnosed = implode(",",$_POST['age_diagnosed']);
        $finished_treatment = implode(",",$_POST['finished_treatment']);
        $sql = "UPDATE ca_detail SET pid='".$_POST['pid']."',ca_type='".$cancer_type."',age_ca_dx='".$age_diagnosed."',end_ca_rx='".$finished_treatment."' ";
        // echo $sql;die;
    	if ($conn->query($sql) === TRUE) {
    		echo 'added'; die;
    	}
    }
// 	$sql = "UPDATE ca_detail SET pid='".$_POST['pid']."',hr_status='".$_POST['hr_status']."' ";
// 	if ($conn->query($sql) === TRUE) {
// 		echo 'added'; die;
// 	}
}
// else if($_POST['section'] == 'personal_cancer_history2'){
// 	$delete_sql = "DELETE FROM ca_detail WHERE pid='".$_POST['pid']."' ";
// 	$conn->query($delete_sql);

// 	$sql = "INSERT INTO ca_detail SET pid='".$_POST['pid']."',her2status='".$_POST['hr2_status']."',hr_status='".$_POST['hr_status']."',age_ca_dx='".$_POST['age_dx']."',end_ca_rx='".$_POST['finished_cancer']."' ";
// 	if ($conn->query($sql) === TRUE) {
// 		echo 'added'; die;
// 	}
// }

else if($_POST['section'] == 'past_medical_history_pmh'){
	$delete_sql = "DELETE FROM pmh WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO pmh SET pid='".$_POST['pid']."',htn='".$_POST['HTN']."',dyslipidemia='".$_POST['Dyslipidemia']."',mi='".$_POST['MI']."',ChestPain='".$_POST['ChestPain']."',chf='".$_POST['CHF']."',arrhythmia='".$_POST['Arrhythmia']."',claudication='".$_POST['Claudication']."',othcvd='".$_POST['OthCVD']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh2'){
	$sql = "UPDATE pmh SET cva='".$_POST['cva']."',tia='".$_POST['tia']."',hearing='".$_POST['hearing']."',vision='".$_POST['vision']."',oth_neuro='".$_POST['oth_neuro']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_headache'){
	$sql = "UPDATE pmh SET Migraine='".$_POST['migraine']."',TensionHA='".$_POST['tension']."',hanos='".$_POST['hanos']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh3'){
	$sql = "UPDATE pmh SET asthma='".$_POST['asthma']."',othpulm='".$_POST['OthPulm']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pulmonary'){
	$sql = "UPDATE pmh SET Emphysema='".$_POST['emphysema']."',ChrBronch='".$_POST['ChrBronch']."',COPDNOS='".$_POST['COPDNOS']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh4'){
	$sql = "UPDATE pmh SET gerd='".$_POST['gerd']."',barretts='".$_POST['barretts']."',ibs='".$_POST['ibs']."',nash='".$_POST['nash']."',cirrhosis='".$_POST['cirrhosis']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
	// $sql = "UPDATE pmh SET gerd='".$_POST['gerd']."',barretts='".$_POST['barretts']."',ibd='".$_POST['ibd']."',ibs='".$_POST['ibs']."',nash='".$_POST['nash']."',vhep='".$_POST['vhep']."',cirrhosis='".$_POST['cirrhosis']."',pancreatitis='".$_POST['pancreatitis']."',hpylori='".$_POST['hpylori']."' WHERE pid='".$_POST['pid']."' ";
}else if($_POST['section'] == 'past_medical_history_ibd_inflammatory'){
	$sql = "UPDATE pmh SET ibd='".$_POST['ibd']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_hepatitis'){
	$sql = "UPDATE pmh SET ibd='".$_POST['ibd']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pancreatitis'){
	$sql = "UPDATE pmh SET pancreatitis='".$_POST['pancreatitis']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pylori'){
	$sql = "UPDATE pmh SET hpylori='".$_POST['hpylori']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh5'){
	$sql = "UPDATE pmh SET cri='".$_POST['cri']."',nocturia='".$_POST['nocturia']."',freq_uti='".$_POST['freq_uti']."',oth_gu='".$_POST['OthGU']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_required'){
	$sql = "UPDATE pmh SET cri='".$_POST['cri']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh6'){
	$sql = "UPDATE pmh SET psoriasis='".$_POST['psoriasis']."',eczema='".$_POST['eczema']."',oth_derm='".$_POST['OthDerm']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh7'){
	$sql = "UPDATE pmh SET gout='".$_POST['gout']."',lupus='".$_POST['lupus']."',ra='".$_POST['ra']."',psor_arth='".$_POST['PsorArth']."',oa='".$_POST['OA']."',fibromyalgia='".$_POST['Fibromyalgia']."',oth_rheum='".$_POST['OthRheum']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh8'){
	$sql = "UPDATE pmh SET diabetes='".$_POST['Diabetes']."',pre_diabetes='".$_POST['pre_diabetes']."',oth_endo='".$_POST['OthEndo']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_thyroid_disease'){
	$sql = "UPDATE pmh SET thyrD='".$_POST['hyperthyroid']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_adrenal_conditions'){
	$sql = "UPDATE pmh SET adrena_id='".$_POST['adrena_id']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh9'){
	$sql = "UPDATE pmh SET menorrhagia='".$_POST['Menorrhagia']."',metrorrhagia='".$_POST['Metrorrhagia']."',dysmeno='".$_POST['Dysmeno']."',dub='".$_POST['DUB']."',hot_flash='".$_POST['HotFlash']."',nite_sweats='".$_POST['NiteSweats']."',hp_vinfect='".$_POST['HPVinfect']."',hi_br_dens='".$_POST['HiBrDens']."',oth_gyn='".$_POST['OthGyn']."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh10'){
    //print_r($_POST);die;
    $age_diagnosed = array();
    $i = 0;
	foreach($_POST['condition_types'] AS $type){
	    $age_diagnosed[$type] = $_POST['age_diagnosed'][$i];
	    $i++;
	}
	
	$finished_treatment = array();
    $j = 0;
	foreach($_POST['condition_types'] AS $type){
	    $finished_treatment[$type] = $_POST['finished_treatment'][$j];
	    $j++;
	}
// 	print_r($finished_treatment);die;
	$sql = "UPDATE pmh SET age_dx='".json_encode($age_diagnosed)."',age_stop='".json_encode($finished_treatment)."' WHERE pid='".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_pmh11'){
// 	$sql = "UPDATE pmh SET age_stop='".$_POST['age_stop']."' WHERE pid='".$_POST['pid']."' ";
// 	if ($conn->query($sql) === TRUE) {
// 		echo 'added'; die;
// 	}
}else if($_POST['section'] == 'past_medical_history_abnormal'){
	$set = " abnl_mammo='".$_POST['AbnlMammo']."',abnl_pap='".$_POST['AbnlPap']."',abnl_hpv='".$_POST['AbnlHPV']."',abnl_psa='".$_POST['AbnlPSA']."',abnl_colo='".$_POST['AbnlColo']."',abnl_oth_scope='".$_POST['AbnlOthScope']."',abnl_mole='".$_POST['AbnlMole']."' ";
	$sql1 = "SELECT * FROM abnl_test WHERE pid='".$_POST['pid']."' ";
    $check = $conn->query($sql1);
    if($check->num_rows){
        $sql = "UPDATE abnl_test SET $set WHERE pid='".$_POST['pid']."' ";
    }else{
        $sql = "INSERT INTO abnl_test SET $set ";
    }
// 	echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'past_medical_history_htwt'){
	$delete_sql = "DELETE FROM htwt WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	$curr_wt_lb = '';
	$curr_wt_kg = '';
	$wt18lb = '';
	$wt18kg = '';
	if($_POST['weight'] == 'lb'){
		$curr_wt_lb = $_POST['weight_text'];
		$wt18lb = $_POST['weight_at_age_18'];
	}else{
		$curr_wt_kg = $_POST['weight_text'];
		$wt18kg = $_POST['weight_at_age_18'];
	}

	$sql = "INSERT INTO htwt SET pid='".$_POST['pid']."',curr_ht_in='".$_POST['height_inch']."',curr_ht_cm='".$_POST['cm']."',curr_wt_lb='".$curr_wt_lb."',curr_wt_kg='".$curr_wt_kg."',wt18lb='".$wt18lb."',wt18kg='".$wt18kg."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'genetic_testing_history_gt'){
	$delete_sql = "DELETE FROM gt WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO gt SET pid='".$_POST['pid']."',prior_gt='".$_POST['genetic_testing']."',guide_status='".$_POST['clearly_meets']."',guide_version='".$_POST['v_no']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'genetic_testing_history_gtresult'){
	$delete_sql = "DELETE FROM gt_result WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	if(isset($_POST['arr'])){
		$arr = $_POST['arr'];
		$lab = implode(",",$arr);
	}else{
		$lab = '';
	}
	
	if(isset($_POST['arr_results'])){
		$arr_results = $_POST['arr_results'];
		$result = implode(",",$arr_results);
	}else{
		$result = '';
	}
	if(isset($_POST['selectedGenesArr'])){
		$arr_results = $_POST['selectedGenesArr'];
		$genes = implode(",",$arr_results);
	}else{
		$genes = '';
	}

	$sql = "INSERT INTO gt_result SET pid='".$_POST['pid']."',lab='".$lab."',result='".$result."',genes='".$genes."',recvd_gc='".$_POST['genetic_risk']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'genetic_testing_history_psh'){
	$delete_sql = "DELETE FROM psh WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO psh SET pid='".$_POST['pid']."',bxyn='".$_POST['biopsy']."',surgery_yn='".$_POST['surgery_forany']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'genetic_testing_history_biopsy'){
	$delete_sql = "DELETE FROM biopsy WHERE pid='".$_POST['pid']."' ";
	$arr = $_POST['arr'];
	$type = implode(",",$arr);
	$conn->query($delete_sql);

	$sql = "INSERT INTO biopsy SET pid='".$_POST['pid']."',bx_type='".$type."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'genetic_testing_history_surgery'){
	$delete_sql = "DELETE FROM surgery WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO surgery SET pid='".$_POST['pid']."',surg_yr='".$_POST['surgery_year']."',laterality='".$_POST['what_side_performed']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'medication_supplement'){
	$delete_sql = "DELETE FROM meds WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	if(isset($_POST['arr'])){
		$arr = $_POST['arr'];
		$type = implode(",",$arr);
	}else{
		$type = '';
	}
	$sql = "INSERT INTO meds SET pid='".$_POST['pid']."',meds='".$_POST['medications']."',med_name='".$_POST['list_medications_text']."',route='".$type."',units='".$_POST['select_unit']."',dose='".$_POST['time_text']."',med_start='".$_POST['start_this_medication']."',med_end='".$_POST['stop_this_medication']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'medication_supplement_supp'){
	$delete_sql = "DELETE FROM supp WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	if(isset($_POST['arr'])){
		$arr = $_POST['arr'];
		$type = implode(",",$arr);
	}else{
		$type = '';
	}
	$sql = "INSERT INTO supp SET pid='".$_POST['pid']."',supp='".$_POST['supplements']."',supp_name='".$_POST['supp_name']."',route='".$type."',units='".$_POST['select_unit_supp']."',dose='".$_POST['time_text_supp']."',supp_start='".$_POST['start_this_supplement']."',supp_end='".$_POST['stop_this_supplement']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'allergy'){
	$delete_sql = "DELETE FROM allergies WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	if(isset($_POST['arr'])){
		$arr = $_POST['arr'];
		$type = implode(",",$arr);
	}else{
		$type = '';
	}
	$sql = "INSERT INTO allergies SET pid='".$_POST['pid']."',med_allergy='".$_POST['list_medications']."',oth_allergy='".$_POST['othallergy']."',allergic_rxn='".$type."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'reproductive_history'){
	$delete_sql = "DELETE FROM repro_hx WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO repro_hx SET pid='".$_POST['pid']."',children='".$_POST['children']."',adoptees='".$_POST['adopted']."',gravida='".$_POST['pregnancies']."',parity='".$_POST['live_birth']."',sab='".$_POST['miscarried']."',tab='".$_POST['voluntarily']."',boys='".$_POST['boys']."',girls='".$_POST['girls']."',menarche='".$_POST['first_menstrual_period']."',lmp='".$_POST['household_income']."',meno_age='".$_POST['MenoAge']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'family_history'){
	$delete_sql = "DELETE FROM fam_struct WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);

	$sql = "INSERT INTO fam_struct SET proband_adopt_status='".$_POST['adopted']."',proband_adoptIn_out='".$_POST['biologic']."',father_adopt_status='".$_POST['biologic_father']."',father_vital_stat='".$_POST['biologic_father_alive']."',father_age_death='".$_POST['died']."',father_curr_age='".$_POST['old']."',father_name='".$_POST['father_first_name']."',mother_adopt_status='".$_POST['biologic_mother']."',mother_vital_stat='".$_POST['biologic_mother_alive']."',mother_age_death='".$_POST['she_died']."',mother_curr_age='".$_POST['she_old_now']."',mother_name='".$_POST['mother_first_name']."',sub_num='".$_POST['siblings']."',mgm_vital_stat='".$_POST['grandmother_alive']."',mgm_age_death='".$_POST['mom_old_died']."',mgm_curr_age='".$_POST['mom_old_now']."',mgm_heritage='".$_POST['mom_descend']."',mgm_ethnicity='".$_POST['family_jewish']."',mgf_vital_stat='".$_POST['grandfather_alive']."',mgf_age_death='".$_POST['grandfather_old_died']."',mgf_curr_age='".$_POST['grandfather_old_now']."',mgf_heritage='".$_POST['country_grandfather_descend']."',mgf_ethnicity='".$_POST['paternal_family_jewish']."',pat_unc='".$_POST['paternal_uncles']."',pat_aunt='".$_POST['paternal_aunts']."',pid='".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'people_with_cancer'){
	$delete_sql = "DELETE FROM fam_cahx WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	if(isset($_POST['which_family_memeber_div'])){
		$arr = $_POST['which_family_memeber_div'];
		$CancerRel = implode(",",$arr);
	}else{
		$CancerRel = '';
	}

	if(isset($_POST['which_specific_cancer_div'])){
		$arr = $_POST['which_specific_cancer_div'];
		$CancerTime = implode(",",$arr);
	}else{
		$CancerTime = '';
	}
	$_POST['adequate'] = str_replace("'","`",$_POST['adequate']);
	$sql = "INSERT INTO fam_cahx SET CancerFamHx='".$_POST['adequate']."',CancerRel='".$CancerRel."',CancerTime='".$CancerTime."',AgeDx='".html_entity_decode($_POST['old_initially_diagnosed'])."',pid='".$_POST['pid']."' ";
	//echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}
else if($_POST['section'] == 'socioeconomics'){
    $delete_sql = "DELETE FROM soc_hx WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	$education = '';
	if(isset($_POST['Education'])){
		$education = $_POST['Education'];
		$education = implode(",",$education);
	}

	$sql = "INSERT INTO soc_hx SET occupation='".$_POST['Occupation']."',education='".$education."',income='".$_POST['Income']."',household='".$_POST['Household']."',pid='".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}
else if($_POST['section'] == 'social_history'){
	$delete_sql = "DELETE FROM soc_hx WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	
	$sql = "UPDATE soc_hx SET soc_support='".$_POST['soc_support']."',soc_support_detail='".$_POST['soc_support_detail']."',ca_friends='".$_POST['ca_friends']."',friend_effect='".$_POST['friend_effect']."',perc_risk='".$_POST['PercRisk']."',perc_reasons='".$_POST['PercReasons']."' WHERE pid='".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_tobacco'){
	$delete_sql = "DELETE FROM init_prev WHERE pid='".$_POST['pid']."' ";
	$conn->query($delete_sql);
	$tabocoo_container_arr = '';
	if(isset($_POST['tabocoo_container_arr'])){
		$tabocoo_container_arr = $_POST['tabocoo_container_arr'];
		$tabocoo_container_arr = implode(",",$tabocoo_container_arr);
	}

	$sql = "INSERT INTO init_prev SET pid='".$_POST['pid']."',tob_yn='".$_POST['tobacco_products']."',tob_types='".$tabocoo_container_arr."',cig_num='".$_POST['average_number_cigarettes']."',ppd='".$_POST['ppd']."',cig_yrs='".$_POST['years_smoked']."',pkyr='".$_POST['PkYr']."',PkYrFlag='".$_POST['PkYrFlag']."',secondhand_tobacco_flag='".$_POST['secondhand_tobacco_flag']."',last_tob='".$_POST['last_use']."',smkless_to_dur='".$_POST['how_long_used']."',etsyn='".$_POST['secondhand_tobacco']."',ets_dur='".$_POST['inhale_second_hand_smoke']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_alchohol'){
	$alcohlic_drinks_container_arr = '';
	if(isset($_POST['alcohlic_drinks_container_arr'])){
		$alcohlic_drinks_container_arr = $_POST['alcohlic_drinks_container_arr'];
		$alcohlic_drinks_container_arr = implode(",",$alcohlic_drinks_container_arr);
	}
	$alcohlic_drinks_prefer_container = '';
	if(isset($_POST['alcohlic_drinks_prefer_container'])){
		$alcohlic_drinks_prefer_container = $_POST['alcohlic_drinks_prefer_container'];
		$alcohlic_drinks_prefer_container = implode(",",$alcohlic_drinks_prefer_container);
	}
	$substances_container = '';
	if(isset($_POST['substances_container'])){
		$substances_container = $_POST['substances_container'];
		$substances_container = implode(",",$substances_container);
	}

	$sql = "UPDATE init_prev SET alc_freq='".$alcohlic_drinks_container_arr."',prior_alc_yn='".$_POST['drink_alcohol_in_past']."',alc_quit='".$_POST['last_drink']."',alc_form='".$alcohlic_drinks_prefer_container."',drugs_yn='".$_POST['illicit_drugs']."',drug_use_type='".$substances_container."',last_drug='".$_POST['last_use_alchohol']."' WHERE pid = '".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_environment'){
	$protection_cantainer = '';
	$sql1 = '';
	if(isset($_POST['protection_cantainer'])){
		foreach($_POST['protection_cantainer'] AS $protection_cantainer){
			$sql1 .= $protection_cantainer."='1',";
		}
		//$protection_cantainer = implode(",",$protection_cantainer);
	}
	

	$sql = "UPDATE init_prev SET $sql1 pollution='".$_POST['live_large_city']."',ToxinDur='".$_POST['total_amount_exposed']."',CleanProd='".$_POST['all_natural_home']."',BeautProd='".$_POST['all_natural_beauty']."' WHERE pid = '".$_POST['pid']."' ";
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_radiation'){
	$sql1 = '';
	if(isset($_POST['exposure_history_apply'])){
		foreach($_POST['exposure_history_apply'] AS $exposure_history_apply){
			$sql1 .= $exposure_history_apply."='1',";
		}
		//$protection_cantainer = implode(",",$protection_cantainer);
	}

	$radiated_container = '';
	if(isset($_POST['radiated_container'])){
		$radiated_container = $_POST['radiated_container'];
		$radiated_container = implode(",",$radiated_container);
	}
	

	$sql = "UPDATE init_prev SET $sql1 TanBed='".$_POST['tanning_bed']."',BeautProd='".$_POST['severe_sunburns']."',RadonExp='".$_POST['knowledge_exposed']."',RadonDetect='".$_POST['radon_detector']."',RTYN='".$_POST['received_radiation']."',RTage='".$_POST['started_radiation_therapy']."',RTfields='".$radiated_container."' WHERE pid = '".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_exogenous'){

	$medication_container = '';
	if(isset($_POST['medication_container'])){
		$medication_container = $_POST['medication_container'];
		$medication_container = implode(",",$medication_container);
	}
	

	$sql = "UPDATE init_prev SET OCPYN='".$_POST['oral_contraceptives']."',OCPregimen='".$_POST['what_did_taking']."',OCPyrs='".$_POST['how_many_years']."',HRTYN='".$_POST['postmenopausal_hormones']."',HRTregimen='".$_POST['what_did_taking_postmenopausal']."',HRTyrs='".$_POST['postmenopausal_hormones_total']."',SERM5ARI='".$medication_container."',SERM5ARIdur='".$_POST['how_many_years_medication']."',DESYN='".$_POST['diethylstilbesterol']."' WHERE pid = '".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_immno'){

	$immno_container = '';
	if(isset($_POST['immno_container'])){
		$immno_container = $_POST['immno_container'];
		$immno_container = implode(",",$immno_container);
	}
	

	$sql = "UPDATE init_prev SET ImmuneRx='".$immno_container."',ImmuneDur='".$_POST['how_many_years_suppresssive']."' WHERE pid = '".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_substances'){

	$other_substances_container = '';
	if(isset($_POST['other_substances_container'])){
		$other_substances_container = $_POST['other_substances_container'];
		$other_substances_container = implode(",",$other_substances_container);
	}
	

	$sql = "UPDATE init_prev SET OthProtect='".$other_substances_container."',ExpOth='".$_POST['tell_us_other_substances']."' WHERE pid = '".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'exposure_history_diet'){

	$diet_container = '';
	if(isset($_POST['diet_container'])){
		$diet_container = $_POST['diet_container'];
		$diet_container = implode(",",$diet_container);
	}
	

	$sql = "UPDATE init_prev SET Diet='".$_POST['particular_diet']."',MeatServ='".$_POST['servings_processed_meat']."',GrainServ='".$_POST['servings_whole_grains']."',FrtVegServ='".$_POST['servings_fruit_vegetables']."' WHERE pid = '".$_POST['pid']."' ";
	// echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
}else if($_POST['section'] == 'save_flag'){
	$sql = '';
	if(isset($_POST['flag_tobacco_products'])){
		$sql .= " flag_tobacco_products = '1'";
	}elseif(isset($_POST['flag_within_a_week'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_within_a_week = '1'";
	}elseif(isset($_POST['flag_more_than_a_month'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_more_than_a_month = '1'";
	}elseif(isset($_POST['flag_secondhand_tobacco'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_secondhand_tobacco = '1'";
	}elseif(isset($_POST['flag_4_more_day'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_4_more_day = '1' ";
	}elseif(isset($_POST['flag_substance_saspirin'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_substance_saspirin = '1' ";
	}elseif(isset($_POST['flag_substance_tamoxifen'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_substance_tamoxifen = '1' ";
	}elseif(isset($_POST['flag_substance_raloxifene'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_substance_raloxifene = '1' ";
	}elseif(isset($_POST['flag_substance_finasteride'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_substance_dutasteride = '1' ";
	}elseif(isset($_POST['flag_substance_birth_control'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_substance_birth_control = '1' ";
	}elseif(isset($_POST['flag_substance_menopausal'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_substance_menopausal = '1' ";
	}elseif(isset($_POST['flag_substance_hormones'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_substance_hormones = '1' ";
	}
	elseif(isset($_POST['flag_consume_alchohol'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_consume_alchohol = '1'";
	}elseif(isset($_POST['flag_all_natural_beauty'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_all_natural_beauty = '1' ";
	}elseif(isset($_POST['flag_environment'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_environment = '1' ";
	}elseif(isset($_POST['particular_diet'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " particular_diet = '1' ";
	}elseif(isset($_POST['diet_container'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " diet_container = '1' ";
	}elseif(isset($_POST['servings_processed_meat'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " servings_processed_meat = '1' ";
	}elseif(isset($_POST['servings_whole_grains'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " servings_whole_grains = '1' ";
	}elseif(isset($_POST['servings_fruit_vegetables'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " servings_fruit_vegetables = '1' ";
	}elseif(isset($_POST['how_often_excercise'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " how_often_excercise = '1' ";
	}elseif(isset($_POST['flag_finances'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_finances = '1' ";
	}elseif(isset($_POST['flag_relationships'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_relationships = '1' ";
	}elseif(isset($_POST['flag_work_related'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_work_related = '1' ";
	}elseif(isset($_POST['flag_excercise'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_excercise = '1' ";
	}

	if(!empty($sql)){
		$sql = "UPDATE init_prev SET $sql WHERE pid = '".$_POST['pid']."' ";
		//echo $sql;die;
		if ($conn->query($sql) === TRUE) {
			echo 'added'; die;
		}
	}
}else if($_POST['section'] == 'save_flag_first'){
	$sql = '';
	if(isset($_POST['gender_flag'])){
		$sql .= " gender_flag = '".$_POST['gender_flag']."'";
	}elseif(isset($_POST['flag_pronous'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_pronous = '".$_POST['flag_pronous']."'";
	}elseif(isset($_POST['flagbirth'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagbirth = '".$_POST['flagbirth']."'";
	}elseif(isset($_POST['flagBreast'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagBreast = '".$_POST['flagBreast']."'";
	}elseif(isset($_POST['flagProstate'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagProstate = '".$_POST['flagProstate']."'";
	}elseif(isset($_POST['flagColorectal'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagColorectal = '".$_POST['flagColorectal']."'";
	}elseif(isset($_POST['flagLung'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagLung = '".$_POST['flagLung']."'";
	}elseif(isset($_POST['flagUterine'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagUterine = '".$_POST['flagUterine']."'";
	}elseif(isset($_POST['flagPancreatic'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagPancreatic = '".$_POST['flagPancreatic']."'";
	}elseif(isset($_POST['flagStomach'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagStomach = '".$_POST['flagStomach']."'";
	}elseif(isset($_POST['flagEsophageal'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagEsophageal = '".$_POST['flagEsophageal']."'";
	}elseif(isset($_POST['flagSarcoma'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagSarcoma = '".$_POST['flagSarcoma']."'";
	}
	elseif(isset($_POST['flagH_N'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagH_N = '".$_POST['flagH_N']."'";
	}elseif(isset($_POST['flagBrain'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagBrain = '".$_POST['flagBrain']."'";
	}elseif(isset($_POST['flagAdrenal'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagAdrenal = '".$_POST['flagAdrenal']."'";
	}elseif(isset($_POST['flagKidney'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagKidney = '".$_POST['flagKidney']."'";
	}elseif(isset($_POST['flagThyroid'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flagThyroid = '".$_POST['flagThyroid']."'";
	}elseif(isset($_POST['flag_hr_status'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_hr_status = '".$_POST['flag_hr_status']."'";
	}elseif(isset($_POST['flag_finished_cancer'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_finished_cancer = '".$_POST['flag_finished_cancer']."'";
	}elseif(isset($_POST['EndCaRx'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " EndCaRx = '".$_POST['EndCaRx']."'";
	}elseif(isset($_POST['flag_genetic_testing'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_genetic_testing = '".$_POST['flag_genetic_testing']."'";
	}elseif(isset($_POST['flag_genetic_risk'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_genetic_risk = '".$_POST['flag_genetic_risk']."'";
	}elseif(isset($_POST['fag_bisopy_breast'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " fag_bisopy_breast = '".$_POST['fag_bisopy_breast']."'";
	}elseif(isset($_POST['flag_list_medications'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_list_medications = '".$_POST['flag_list_medications']."'";
	}elseif(isset($_POST['flag_household_income'])){
		if(!empty($sql)){
			$sql .= ",";
		}
		$sql .= " flag_household_income = '".$_POST['flag_household_income']."'";
	}

	if(!empty($sql)){
	    $sql1 = "SELECT * FROM flags WHERE pid='".$_POST['pid']."' ";
        $check = $conn->query($sql1);
        if($check->num_rows){
            $sql2 = "UPDATE flags SET $sql WHERE pid = '".$_POST['pid']."' ";
        }else{
            $sql2 = "INSERT INTO flags SET $sql ";
        }
		
// 		echo $sql2;die;
		if ($conn->query($sql2) === TRUE) {
			echo 'added'; die;
		}
	}
}else if($_POST['section'] == 'cancer_screening'){
	$sql = '';
	if(isset($_POST['low_dose_spiral'])){
		$delete_sql = "DELETE FROM init_screen WHERE pid='".$_POST['pid']."' ";
		$conn->query($delete_sql);

		$sql .= " LDCTYN = '".$_POST['low_dose_spiral']."'";
		$sql = "INSERT INTO init_screen SET $sql , pid = '".$_POST['pid']."' ";
		// echo $sql;die;
		if ($conn->query($sql) === TRUE) {
			echo 'added'; die;
		}
	}elseif(isset($_POST['last_perfomed'])){
		$sql .= " LDCTdate = '".$_POST['last_perfomed']."'";
	}elseif(isset($_POST['mammogram'])){
		$sql .= " MammoYN = '".$_POST['mammogram']."'";
	}elseif(isset($_POST['performing_mammograms'])){
		$sql .= " MamNoReason = '".$_POST['performing_mammograms']."'";
	}elseif(isset($_POST['need_mommography'])){
		$sql .= " MamNoNeedDetail = '".$_POST['need_mommography']."'";
	}elseif(isset($_POST['last_mammogram'])){
		$sql .= " LastMammo = '".$_POST['last_mammogram']."'";
	}elseif(isset($_POST['abnormal_mammogram'])){
		$sql .= " MamAbnYN = '".$_POST['abnormal_mammogram']."'";
	}elseif(isset($_POST['when_was_cancer'])){
		$sql .= " MamAbnDate = '".$_POST['when_was_cancer']."'";
	}elseif(isset($_POST['breasts_mammogram'])){
		$sql .= " DenseAwareYN = '".$_POST['breasts_mammogram']."'";
	}elseif(isset($_POST['breastimaging_container'])){
		$sql .= " BreastOth = '".$_POST['breastimaging_container']."'";
	}elseif(isset($_POST['dateofultrasound'])){
		$sql .= " LastOthBrImage = '".$_POST['dateofultrasound']."'";
	}elseif(isset($_POST['select_breastselfexams'])){
		$sql .= " BSE = '".$_POST['select_breastselfexams']."'";
	}elseif(isset($_POST['performing_bse_container'])){
		$sql .= " BSEreason = '".$_POST['performing_bse_container']."'";
	}elseif(isset($_POST['pelvic_exam'])){
		$sql .= " PelvicYN = '".$_POST['pelvic_exam']."'";
	}elseif(isset($_POST['select_screening_pelvic'])){
		$sql .= " SexYN = '".$_POST['select_screening_pelvic']."'";
	}elseif(isset($_POST['trouble_container'])){
		$sql .= " SexTrouble = '".$_POST['trouble_container']."'";
	}elseif(isset($_POST['dateofpelvicexam'])){
		$sql .= " LastPelvic = '".$_POST['dateofpelvicexam']."'";
	}elseif(isset($_POST['last_pelvic_exam'])){
		$sql .= " PapYN = '".$_POST['last_pelvic_exam']."'";
	}elseif(isset($_POST['dateof_lastpap'])){
		$sql .= " LastPap = '".$_POST['dateof_lastpap']."'";
	}elseif(isset($_POST['abnormal_pap_smear'])){
		$sql .= " PapAbnYN = '".$_POST['abnormal_pap_smear']."'";
	}elseif(isset($_POST['when_was_that'])){
		$sql .= " PapAbnDate = '".$_POST['when_was_that']."'";
	}elseif(isset($_POST['hpv_virus_test'])){
		$sql .= " HPVYN = '".$_POST['hpv_virus_test']."'";
	}elseif(isset($_POST['hpv_subtype_container'])){
		$sql .= " HPVtype = '".$_POST['hpv_subtype_container']."'";
	}elseif(isset($_POST['digital_rectal_exam'])){
		$sql .= " DREYN = '".$_POST['digital_rectal_exam']."'";
	}elseif(isset($_POST['last_dre'])){
		$sql .= " DREdate = '".$_POST['last_dre']."'";
	}elseif(isset($_POST['tabocoo_container'])){
		$sql .= " DREresult = '".$_POST['tabocoo_container']."'";
	}elseif(isset($_POST['psa'])){
		$sql .= " PSAYN = '".$_POST['psa']."'";
	}elseif(isset($_POST['last_psa'])){
		$sql .= " PSAdate = '".$_POST['last_psa']."'";
	}elseif(isset($_POST['last_use'])){
		$sql .= " PSAresult = '".$_POST['last_use']."'";
	}elseif(isset($_POST['colonoscopy_psa'])){
		$sql .= " ColoYN = '".$_POST['colonoscopy_psa']."'";
	}elseif(isset($_POST['last_colonoscopy'])){
		$sql .= " LastColo = '".$_POST['last_colonoscopy']."'";
	}elseif(isset($_POST['polyps'])){
		$sql .= " PolypYN = '".$_POST['polyps']."'";
	}elseif(isset($_POST['polyps_so_far'])){
		$sql .= " PolypNum = '".$_POST['polyps_so_far']."'";
	}elseif(isset($_POST['colorectal_cancer'])){
		$sql .= " AltColoYN = '".$_POST['colorectal_cancer']."'";
	}elseif(isset($_POST['alcohlic_drinks_container'])){
		$sql .= " AltColoTest = '".$_POST['alcohlic_drinks_container']."'";
	}elseif(isset($_POST['home_based_tests'])){
		$sql .= " LastAltColo = '".$_POST['home_based_tests']."'";
	}elseif(isset($_POST['cancer_stomach'])){
		$sql .= " UpperGIYN = '".$_POST['cancer_stomach']."'";
	}elseif(isset($_POST['gastrointestinal_cancer'])){
		$sql .= " UpperGITest = '".$_POST['gastrointestinal_cancer']."'";
	}elseif(isset($_POST['blood_test_container'])){
		$sql .= " LiverTest = '".$_POST['blood_test_container']."'";
	}elseif(isset($_POST['screen_for_cancer'])){
		$sql .= " BodyScanYN = '".$_POST['screen_for_cancer']."'";
	}elseif(isset($_POST['body_image_container'])){
		$sql .= " BodyImageType = '".$_POST['body_image_container']."'";
	}elseif(isset($_POST['blood_test'])){
		$sql .= " BloodTestYN = '".$_POST['blood_test']."'";
	}elseif(isset($_POST['once_had_container'])){
		$sql .= " BloodTestType = '".$_POST['once_had_container']."'";
	}elseif(isset($_POST['multi_cancer_last_performed'])){
		$sql .= " BloodTestDate = '".$_POST['multi_cancer_last_performed']."'";
	}

	if(!empty($sql)){
		$sql = "UPDATE init_screen SET $sql WHERE pid = '".$_POST['pid']."' ";
		// echo $sql;die;
		if ($conn->query($sql) === TRUE) {
			echo 'added'; die;
		}
	}
}
else if($_POST['section'] == 'save_demographics1'){
    //print_r($_POST);die;
    //$_POST['pid'] = '1';
	$sql = " title = '".$_POST['form_title']."', fname = '".$_POST['form_fname']."', mname = '".$_POST['form_mname']."', lname = '".$_POST['form_lname']."', birth_fname = '".$_POST['form_birth_fname']."', birth_mname = '".$_POST['form_birth_mname']."', birth_lname = '".$_POST['form_birth_lname']."' ";
    $sql = "UPDATE patient_data SET $sql WHERE pid = '".$_POST['pid']."' ";
	//echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
		
}else if($_POST['section'] == 'save_demographics2'){
    //print_r($_POST);die;
    //$_POST['pid'] = '1';
	$sql = " DOB = '".$_POST['form_DOB']."', sex = '".$_POST['form_sex']."', gender_identity = '".$_POST['form_gender_identity']."',sexual_orientation = '".$_POST['form_sexual_orientation']."',drivers_license = '".$_POST['form_drivers_license']."',status = '".$_POST['form_status']."' ";
    $sql = "UPDATE patient_data SET $sql WHERE pid = '".$_POST['pid']."' ";
	//echo $sql;die;
	if ($conn->query($sql) === TRUE) {
		echo 'added'; die;
	}
		
}else if($_POST['section'] == 'save_demographics3'){
    //print_r($_POST);die;
    //$_POST['pid'] = '1';
		$sql = " race = '".$_POST['race']."', ethnicity = '".$_POST['hispanic']."',ethnoracial = '".$_POST['pronous']."'  ";
	    $sql = "UPDATE patient_data SET $sql WHERE pid = '".$_POST['pid']."' ";
		//echo $sql;die;
		if ($conn->query($sql) === TRUE) {
			echo 'added'; die;
		}
		
}

?>