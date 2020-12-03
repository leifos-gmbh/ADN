<#1>
<?php
//
// adn_ad_character
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"charact" => array (
		"notnull" => false
		,"length" => 1
		,"default" => ""
		,"fixed" => true
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ad_character", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ad_character", $pk_fields);

$ilDB->createSequence("adn_ad_character", 1);

?>
<#2>
<?php

//
// adn_ad_user
//
$fields = array (
	"il_user_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"sign" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"phone" => array (
		"notnull" => false
		,"length" => 30
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"fax" => array (
		"notnull" => false
		,"length" => 30
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"email" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ad_user", $fields, false, false, true);

?>
<#3>
<?php

//
// adn_cp_invoice
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"cp_professional_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ep_exam_event_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"es_certificate_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"code" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"due_on" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "date"
	)
);
$ilDB->createTable("adn_cp_invoice", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_cp_invoice", $pk_fields);

$ilDB->createSequence('adn_cp_invoice');

?>
<#4>
<?php

//
// adn_cp_professional
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"salutation" => array (
		"notnull" => false
		,"length" => 1
		,"default" => ""
		,"fixed" => true
		,"type" => "text"
	)
	,"last_name" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"first_name" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"birthdate" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "date"
	)
	,"citizenship" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"subject_area" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"registered_for_exam" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"foreign_certificate" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"pa_country" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"pa_postal_code" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_city" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_street" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_street_no" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"sa_salutation" => array (
		"notnull" => false
		,"length" => 1
		,"fixed" => true
		,"type" => "text"
	)
	,"sa_last_name" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"sa_first_name" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"sa_country" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"sa_postal_code" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"sa_city" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"sa_street" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"sa_street_no" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"sa_active" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"phone" => array (
		"notnull" => false
		,"length" => 30
		,"fixed" => false
		,"type" => "text"
	)
	,"email" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"ucomment" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"registered_by_wmo_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"blocked_until" => array (
		"notnull" => false
		,"type" => "date"
	)
	,"blocked_by_wmo_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"last_ta_event_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_cp_professional", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_cp_professional", $pk_fields);

?>
<#5>
<?php

//
// adn_ec_given_answer
//
$fields = array (
	"ep_cand_sheet_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_question_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"answer" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ec_given_answer", $fields, false, false, true);

$pk_fields = array("ep_cand_sheet_id","ed_question_id");
$ilDB->addPrimaryKey("adn_ec_given_answer", $pk_fields);


?>
<#6>
<?php

//
// adn_ed_case
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"subject_area" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"butan" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"text" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_case", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_case", $pk_fields);

$ilDB->createSequence("adn_ed_case", 1);

?>
<#7>
<?php

//
// adn_ed_case_answ_good
//
$fields = array (
	"ed_good_related_answer_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_good_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => "0"
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_case_answ_good", $fields, false, false, true);

$pk_fields = array("ed_good_related_answer_id","ed_good_id");
$ilDB->addPrimaryKey("adn_ed_case_answ_good", $pk_fields);


?>
<#8>
<?php

//
// adn_ed_good
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_good_category_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"un_nr" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 200
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"class" => array (
		"notnull" => false
		,"length" => 5
		,"fixed" => false
		,"type" => "text"
	)
	,"class_code" => array (
		"notnull" => false
		,"length" => 5
		,"fixed" => false
		,"type" => "text"
	)
	,"packing_group" => array (
		"notnull" => false
		,"length" => 5
		,"fixed" => false
		,"type" => "text"
	)
	,"material_file" => array (
		"notnull" => false
		,"length" => 500
		,"fixed" => false
		,"type" => "text"
	)
	,"upload_date" => array (
		"notnull" => false
		,"type" => "timestamp"
	)
	,"type" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_good", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_good", $pk_fields);

$ilDB->createSequence("adn_ed_good", 1);

?>
<#9>
<?php

//
// adn_ed_good_category
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 200
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"type" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_good_category", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_good_category", $pk_fields);

$ilDB->createSequence("adn_ed_good_category", 1);

?>
<#10>
<?php

//
// adn_ed_good_rel_answer
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_question_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"butan_or_empty" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"answer" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_good_rel_answer", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_good_rel_answer", $pk_fields);

$ilDB->createSequence("adn_ed_good_rel_answer", 1);

?>
<#11>
<?php

//
// adn_ed_license
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"title" => array (
		"notnull" => false
		,"length" => 200
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"lfile" => array (
		"notnull" => false
		,"length" => 500
		,"fixed" => false
		,"type" => "text"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_license", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_license", $pk_fields);

$ilDB->createSequence("adn_ed_license", 1);

?>
<#12>
<?php

//
// adn_ed_license_good
//
$fields = array (
	"ed_license_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_good_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_license_good", $fields, false, false, true);

$pk_fields = array("ed_license_id","ed_good_id");
$ilDB->addPrimaryKey("adn_ed_license_good", $pk_fields);


?>
<#13>
<?php

//
// adn_ed_objective
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"catalog_area" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"nr" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"title" => array (
		"notnull" => false
		,"length" => 100
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"topic" => array (
		"notnull" => false
		,"length" => 200
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ed_objective", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_objective", $pk_fields);

$ilDB->createSequence("adn_ed_objective", 1);

?>
<#14>
<?php

//
// adn_ed_question
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"backup_of" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"ed_objective_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_subobjective_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"nr" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"title" => array (
		"notnull" => false
		,"length" => 200
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"question" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"status" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"status_date" => array (
		"notnull" => false
		,"type" => "timestamp"
	)
	,"qfile" => array (
		"notnull" => false
		,"length" => 200
		,"fixed" => false
		,"type" => "text"
	)
	,"last_change_comment" => array (
		"notnull" => false
		,"type" => "clob"
	)
);
$ilDB->createTable("adn_ed_question", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_question", $pk_fields);

$ilDB->createSequence("adn_ed_question", 1);

?>
<#15>
<?php

//
// adn_ed_question_case
//
$fields = array (
	"ed_question_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"default_answer" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"good_specific_question" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_question_case", $fields, false, false, true);

$pk_fields = array("ed_question_id");
$ilDB->addPrimaryKey("adn_ed_question_case", $pk_fields);

?>
<#16>
<?php

//
// adn_ed_quest_case_good
//
$fields = array (
	"ed_question_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_good_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => "0"
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_quest_case_good", $fields, false, false, true);

$pk_fields = array("ed_question_id","ed_good_id");
$ilDB->addPrimaryKey("adn_ed_quest_case_good", $pk_fields);


?>
<#17>
<?php

//
// adn_ed_question_mc
//
$fields = array (
	"ed_question_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"correct_answer" => array (
		"notnull" => false
		,"length" => 1
		,"default" => ""
		,"fixed" => true
		,"type" => "text"
	)
	,"answer_1" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"answer_1_file" => array (
		"notnull" => false
		,"length" => 200
		,"fixed" => false
		,"type" => "text"
	)
	,"answer_2" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"answer_2_file" => array (
		"notnull" => false
		,"length" => 200
		,"fixed" => false
		,"type" => "text"
	)
	,"answer_3" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"answer_3_file" => array (
		"notnull" => false
		,"length" => 200
		,"fixed" => false
		,"type" => "text"
	)
	,"answer_4" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"answer_4_file" => array (
		"notnull" => false
		,"length" => 200
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ed_question_mc", $fields, false, false, true);

$pk_fields = array("ed_question_id");
$ilDB->addPrimaryKey("adn_ed_question_mc", $pk_fields);

?>
<#18>
<?php

//
// adn_ed_quest_target_nr
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"subject_area" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"mc_case" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"nr_of_questions" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"max_one_per_objective" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_quest_target_nr", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_quest_target_nr", $pk_fields);

$ilDB->createSequence("adn_ed_quest_target_nr", 1);

?>
<#19>
<?php

//
// adn_ed_question_total
//
$fields = array (
	"subject_area" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"mc_case" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => "0"
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"total" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_question_total", $fields, false, false, true);

$pk_fields = array("subject_area","mc_case");
$ilDB->addPrimaryKey("adn_ed_question_total", $pk_fields);

?>
<#20>
<?php

//
// adn_ed_subobjective
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_objective_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"nr" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"title" => array (
		"notnull" => false
		,"length" => 100
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"topic" => array (
		"notnull" => false
		,"length" => 200
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ed_subobjective", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ed_subobjective", $pk_fields);

$ilDB->createSequence("adn_ed_subobjective", 1);

?>
<#21>
<?php

//
// adn_ed_target_nr_obj
//
$fields = array (
	"ed_question_target_nr_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_objective_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"ed_subobjective_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ed_target_nr_obj", $fields, false, false, true);

?>
<#22>
<?php


//
// adn_ep_answer_sheet
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ep_exam_event_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"nr" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"butan" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"ed_license_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"prev_ed_good_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"new_ed_good_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"generated_on" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
);
$ilDB->createTable("adn_ep_answer_sheet", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ep_answer_sheet", $pk_fields);

$ilDB->createSequence('adn_ep_answer_sheet');

?>
<#23>
<?php

//
// adn_ep_assignment
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ep_exam_event_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"cp_professional_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"invited_on" => array (
		"notnull" => false
		,"type" => "timestamp"
	)
	,"has_participated" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"score_mc" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"score_case" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"result_mc" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"result_case" => array (
		"notnull" => false
		,"length" => 1
		,"unsigned" => false
		,"type" => "integer"
	)
	,"notified_on" => array (
		"notnull" => false
		,"type" => "timestamp"
	)
	,"access_code" => array (
		"notnull" => false
		,"length" => 20
		,"fixed" => false
		,"type" => "text"
	)
	,"score_notification_letter_file" => array (
		"notnull" => false
		,"length" => 500
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ep_assignment", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ep_assignment", $pk_fields);

$ilDB->createSequence('adn_ep_assignment');

?>
<#24>
<?php

//
// adn_ep_cand_sheet
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"cp_professional_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ep_answer_sheet_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"generated_on" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"answer_sheet_file" => array (
		"notnull" => false
		,"length" => 500
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"scoring_sheet_file" => array (
		"notnull" => false
		,"length" => 500
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ep_cand_sheet", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ep_cand_sheet", $pk_fields);

$ilDB->createSequence('edn_ep_cand_sheet');

?>
<#25>
<?php

//
// adn_ep_exam_event
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"subject_area" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"date_from" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"date_to" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"md_exam_facility_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"chairman_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"co_chair_1_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"co_chair_2_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"additional_costs" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"participant_list_file" => array (
		"notnull" => false
		,"length" => 500
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"participant_list_create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ep_exam_event", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ep_exam_event", $pk_fields);

$ilDB->createSequence("adn_ep_exam_event", 1);

?>
<#26>
<?php

//
// adn_ep_exam_invitation
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ep_exam_event_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"cp_professional_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ifile" => array (
		"notnull" => false
		,"length" => 500
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ep_exam_invitation", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ep_exam_invitation", $pk_fields);

$ilDB->createSequence('adn_ep_exam_invitation');

?>
<#27>
<?php

//
// adn_ep_information
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ifile" => array (
		"notnull" => false
		,"length" => 500
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ep_information", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ep_information", $pk_fields);

$ilDB->createSequence("adn_ep_information", 1);

?>
<#28>
<?php

//
// adn_ep_sheet_question
//
$fields = array (
	"ep_answer_sheet_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ed_question_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ep_sheet_question", $fields, false, false, true);

$pk_fields = array("ep_answer_sheet_id","ed_question_id");
$ilDB->addPrimaryKey("adn_ep_sheet_question", $pk_fields);


?>
<#29>
<?php

//
// adn_es_certificate
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"cp_professional_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ep_exam_id" => array (
		"notnull" => false
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"nr" => array (
		"notnull" => false
		,"length" => 14
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"type_dry" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type_tank" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type_gas" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type_chem" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"valid_until" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "date"
	)
	,"signed_by" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"issued_by_wmo" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"issued_on" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"status" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"proof_of_experience" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"is_extension" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"nr_of_duplicates" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"cfile" => array (
		"notnull" => false
		,"length" => 500
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_es_certificate", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_es_certificate", $pk_fields);

$ilDB->createSequence('adn_es_certificate');

?>
<#30>
<?php

//
// adn_md_cochair
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"md_wmo_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"salutation" => array (
		"notnull" => false
		,"length" => 1
		,"default" => ""
		,"fixed" => true
		,"type" => "text"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_md_cochair", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_md_cochair", $pk_fields);

$ilDB->createSequence("adn_md_cochair", 1);

?>
<#31>
<?php

//
// adn_md_country
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"code" => array (
		"notnull" => false
		,"length" => 2
		,"default" => ""
		,"fixed" => true
		,"type" => "text"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 100
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_md_country", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_md_country", $pk_fields);

$ilDB->createSequence("adn_md_country", 1);

?>
<#32>
<?php

//
// adn_md_exam_facility
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"md_wmo_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"street" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"street_no" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"postal_code" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"city" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_md_exam_facility", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_md_exam_facility", $pk_fields);

$ilDB->createSequence("adn_md_exam_facility", 1);

?>
<#33>
<?php

//
// adn_md_wmo
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 100
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"code_nr" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"street" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"street_no" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"postal_code" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"city" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"visit_street" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"visit_street_no" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"visit_postal_code" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"visit_city" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"bank" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"bank_id" => array (
		"notnull" => false
		,"length" => 20
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"account_id" => array (
		"notnull" => false
		,"length" => 20
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"iban" => array (
		"notnull" => false
		,"length" => 20
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"bic" => array (
		"notnull" => false
		,"length" => 20
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"phone" => array (
		"notnull" => false
		,"length" => 30
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"fax" => array (
		"notnull" => false
		,"length" => 30
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"email" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"internet" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"cert_nr" => array (
		"notnull" => false
		,"length" => 4
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"cert_description" => array (
		"notnull" => false
		,"length" => 1000
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"cert_cost" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"duplicate_nr" => array (
		"notnull" => false
		,"length" => 4
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"duplicate_description" => array (
		"notnull" => false
		,"length" => 1000
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"duplicate_cost" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ext_nr" => array (
		"notnull" => false
		,"length" => 4
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"ext_description" => array (
		"notnull" => false
		,"length" => 1000
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"ext_cost" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"exam_nr" => array (
		"notnull" => false
		,"length" => 4
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"exam_description" => array (
		"notnull" => false
		,"length" => 1000
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"exam_cost" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_md_wmo", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_md_wmo", $pk_fields);

$ilDB->createSequence("adn_md_wmo", 1);

?>
<#34>
<?php

//
// adn_ta_event
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ta_provider_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"type" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"date_from" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"date_to" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"ta_facility_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ta_event", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ta_event", $pk_fields);

$ilDB->createSequence("adn_ta_event", 1);

?>
<#35>
<?php

//
// adn_ta_expertise
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"title" => array (
		"notnull" => false
		,"length" => 200
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ta_expertise", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ta_expertise", $pk_fields);

$ilDB->createSequence("adn_ta_expertise", 1);

?>
<#36>
<?php

//
// adn_ta_facility
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ta_provider_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"name" => array (
		"notnull" => false
		,"type" => "clob"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ta_facility", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ta_facility", $pk_fields);

$ilDB->createSequence("adn_ta_facility", 1);

?>
<#37>
<?php

//
// adn_ta_information
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 200
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"ifile" => array (
		"notnull" => false
		,"length" => 500
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ta_information", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ta_information", $pk_fields);

$ilDB->createSequence("adn_ta_information", 1);

?>
<#38>
<?php

//
// adn_ta_instructor
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ta_provider_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_name" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"first_name" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ta_instructor", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ta_instructor", $pk_fields);

$ilDB->createSequence("adn_ta_instructor", 1);

?>
<#39>
<?php

//
// adn_ta_instructor_exp
//
$fields = array (
	"ta_instructor_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"ta_expertise_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
);
$ilDB->createTable("adn_ta_instructor_exp", $fields, false, false, true);

$pk_fields = array("ta_instructor_id","ta_expertise_id");
$ilDB->addPrimaryKey("adn_ta_instructor_exp", $pk_fields);

?>
<#40>
<?php

//
// adn_ta_instr_ttype
//
$fields = array (
	"ta_instructor_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"training_type" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ta_instr_ttype", $fields, false, false, true);

$pk_fields = array("ta_instructor_id","training_type");
$ilDB->addPrimaryKey("adn_ta_instr_ttype", $pk_fields);

?>
<#41>
<?php

//
// adn_ta_provider
//
$fields = array (
	"id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"create_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"create_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"last_update" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
	,"last_update_user" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"archived" => array (
		"notnull" => true
		,"length" => 1
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"name" => array (
		"notnull" => false
		,"length" => 100
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"contact" => array (
		"notnull" => false
		,"length" => 100
		,"fixed" => false
		,"type" => "text"
	)
	,"postal_code" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"city" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"street" => array (
		"notnull" => false
		,"length" => 100
		,"fixed" => false
		,"type" => "text"
	)
	,"street_no" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"po_box" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_postal_code" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_city" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_street" => array (
		"notnull" => false
		,"length" => 100
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_street_no" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"pa_po_box" => array (
		"notnull" => false
		,"length" => 10
		,"fixed" => false
		,"type" => "text"
	)
	,"phone" => array (
		"notnull" => false
		,"length" => 50
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"fax" => array (
		"notnull" => false
		,"length" => 50
		,"fixed" => false
		,"type" => "text"
	)
	,"email" => array (
		"notnull" => false
		,"length" => 100
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
);
$ilDB->createTable("adn_ta_provider", $fields, false, false, true);

$pk_fields = array("id");
$ilDB->addPrimaryKey("adn_ta_provider", $pk_fields);

$ilDB->createSequence("adn_ta_provider", 1);

?>
<#42>
<?php

//
// adn_ta_provider_ttype
//
$fields = array (
	"ta_provider_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"default" => ""
		,"type" => "integer"
	)
	,"training_type" => array (
		"notnull" => false
		,"length" => 10
		,"default" => ""
		,"fixed" => false
		,"type" => "text"
	)
	,"approval_date" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
);
$ilDB->createTable("adn_ta_provider_ttype", $fields, false, false, true);

$pk_fields = array("ta_provider_id","training_type");
$ilDB->addPrimaryKey("adn_ta_provider_ttype", $pk_fields);


?>
<#43>
<?php

$fields = array("cp_professional_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_cp_invoice", $fields, "adn_cp_professional", $ref_fields);

?>
<#44>
<?php

$fields = array("ep_exam_event_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_cp_invoice", $fields, "adn_ep_exam_event", $ref_fields);

?>
<#45>
<?php

$fields = array("es_certificate_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk3","adn_cp_invoice", $fields, "adn_es_certificate", $ref_fields);

?>
<#46>
<?php

$fields = array("registered_by_wmo_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_cp_professional", $fields, "adn_md_wmo", $ref_fields);

?>
<#47>
<?php

$fields = array("blocked_by_wmo_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_cp_professional", $fields, "adn_md_wmo", $ref_fields);

?>
<#48>
<?php

$fields = array("citizenship");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk3","adn_cp_professional", $fields, "adn_md_country", $ref_fields);

?>
<#49>
<?php

$fields = array("pa_country");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk4","adn_cp_professional", $fields, "adn_md_country", $ref_fields);

?>
<#50>
<?php

$fields = array("sa_country");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk5","adn_cp_professional", $fields, "adn_md_country", $ref_fields);

?>
<#51>
<?php

$fields = array("last_ta_event_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk6","adn_cp_professional", $fields, "adn_ta_event", $ref_fields);

?>
<#52>
<?php

$fields = array("ep_cand_sheet_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ec_given_answer", $fields, "adn_ep_cand_sheet", $ref_fields);

?>
<#53>
<?php

$fields = array("ed_question_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ec_given_answer", $fields, "adn_ed_question", $ref_fields);

?>
<#54>
<?php

$fields = array("ed_good_related_answer_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ed_case_answ_good", $fields, "adn_ed_good_rel_answer", $ref_fields);

?>
<#55>
<?php

$fields = array("ed_good_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ed_case_answ_good", $fields, "adn_ed_good", $ref_fields);

?>
<#56>
<?php

$fields = array("ed_good_category_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ed_good", $fields, "adn_ed_good_category", $ref_fields);

?>
<#57>
<?php

$fields = array("ed_question_id");
$ref_fields = array("ed_question_id");
#$ilDB->addForeignKey("fk1","adn_ed_good_rel_answer", $fields, "adn_ed_question_case", $ref_fields);

?>
<#58>
<?php

$fields = array("ed_license_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ed_license_good", $fields, "adn_ed_license", $ref_fields);

?>
<#59>
<?php

$fields = array("ed_good_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ed_license_good", $fields, "adn_ed_good", $ref_fields);

?>
<#60>
<?php

$fields = array("ed_objective_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ed_question", $fields, "adn_ed_objective", $ref_fields);

?>
<#61>
<?php

$fields = array("ed_subobjective_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ed_question", $fields, "adn_ed_subobjective", $ref_fields);

?>
<#62>
<?php

$fields = array("ed_question_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ed_question_case", $fields, "adn_ed_question", $ref_fields);

?>
<#63>
<?php

$fields = array("ed_question_id");
$ref_fields = array("ed_question_id");
#$ilDB->addForeignKey("fk1","adn_ed_quest_case_good", $fields, "adn_ed_question_case", $ref_fields);

?>
<#64>
<?php

$fields = array("ed_good_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ed_quest_case_good", $fields, "adn_ed_good", $ref_fields);

?>
<#65>
<?php

$fields = array("ed_question_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ed_question_mc", $fields, "adn_ed_question", $ref_fields);

?>
<#66>
<?php

$fields = array("ed_objective_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ed_subobjective", $fields, "adn_ed_objective", $ref_fields);

?>
<#67>
<?php

$fields = array("ed_objective_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ed_target_nr_obj", $fields, "adn_ed_objective", $ref_fields);

?>
<#68>
<?php

$fields = array("ed_subobjective_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ed_target_nr_obj", $fields, "adn_ed_subobjective", $ref_fields);

?>
<#69>
<?php

$fields = array("ep_exam_event_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ep_answer_sheet", $fields, "adn_ep_exam_event", $ref_fields);

?>
<#70>
<?php

$fields = array("ed_license_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ep_answer_sheet", $fields, "adn_ed_license", $ref_fields);

?>
<#71>
<?php

$fields = array("prev_ed_good_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk3","adn_ep_answer_sheet", $fields, "adn_ed_good", $ref_fields);

?>
<#72>
<?php

$fields = array("new_ed_good_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk4","adn_ep_answer_sheet", $fields, "adn_ed_good", $ref_fields);

?>
<#73>
<?php

$fields = array("ep_exam_event_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ep_assignment", $fields, "adn_ep_exam_event", $ref_fields);

?>
<#74>
<?php

$fields = array("cp_professional_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ep_assignment", $fields, "adn_cp_professional", $ref_fields);

?>
<#75>
<?php

$fields = array("cp_professional_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ep_cand_sheet", $fields, "adn_cp_professional", $ref_fields);

?>
<#76>
<?php

$fields = array("ep_answer_sheet_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ep_cand_sheet", $fields, "adn_ep_answer_sheet", $ref_fields);

?>
<#77>
<?php

$fields = array("md_exam_facility_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ep_exam_event", $fields, "adn_md_exam_facility", $ref_fields);

?>
<#78>
<?php

$fields = array("chairman_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ep_exam_event", $fields, "adn_md_cochair", $ref_fields);

?>
<#79>
<?php

$fields = array("co_chair_1_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk3","adn_ep_exam_event", $fields, "adn_md_cochair", $ref_fields);

?>
<#80>
<?php

$fields = array("co_chair_2_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk4","adn_ep_exam_event", $fields, "adn_md_cochair", $ref_fields);

?>
<#81>
<?php

$fields = array("cp_professional_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ep_exam_invitation", $fields, "adn_cp_professional", $ref_fields);

?>
<#82>
<?php

$fields = array("ep_exam_event_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ep_exam_invitation", $fields, "adn_ep_exam_event", $ref_fields);

?>
<#83>
<?php

$fields = array("ep_answer_sheet_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ep_sheet_question", $fields, "adn_ep_answer_sheet", $ref_fields);

?>
<#84>
<?php

$fields = array("ed_question_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ep_sheet_question", $fields, "adn_ed_question", $ref_fields);

?>
<#85>
<?php

$fields = array("cp_professional_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_es_certificate", $fields, "adn_cp_professional", $ref_fields);

?>
<#86>
<?php

$fields = array("ep_exam_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_es_certificate", $fields, "adn_ep_exam_event", $ref_fields);

?>
<#87>
<?php

$fields = array("issued_by_wmo");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk3","adn_es_certificate", $fields, "adn_md_wmo", $ref_fields);

?>
<#88>
<?php

$fields = array("md_wmo_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_md_cochair", $fields, "adn_md_wmo", $ref_fields);

?>
<#89>
<?php

$fields = array("md_wmo_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_md_exam_facility", $fields, "adn_md_wmo", $ref_fields);

?>
<#90>
<?php

$fields = array("ta_provider_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ta_event", $fields, "adn_ta_provider", $ref_fields);

?>
<#91>
<?php

$fields = array("ta_facility_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ta_event", $fields, "adn_ta_facility", $ref_fields);

?>
<#92>
<?php

$fields = array("ta_provider_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ta_facility", $fields, "adn_ta_provider", $ref_fields);

?>
<#93>
<?php

$fields = array("ta_provider_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ta_instructor", $fields, "adn_ta_provider", $ref_fields);

?>
<#94>
<?php

$fields = array("ta_instructor_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ta_instructor_exp", $fields, "adn_ta_instructor", $ref_fields);

?>
<#95>
<?php

$fields = array("ta_expertise_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk2","adn_ta_instructor_exp", $fields, "adn_ta_expertise", $ref_fields);

?>
<#96>
<?php

$fields = array("ta_instructor_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ta_instr_ttype", $fields, "adn_ta_instructor", $ref_fields);

?>
<#97>
<?php

$fields = array("ta_provider_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk1","adn_ta_provider_ttype", $fields, "adn_ta_provider", $ref_fields);

?>
<#98>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#99>
<?php
	$ilDB->modifyTableColumn('adn_cp_professional', 'subject_area',
		array("type" => "text", "length" => 5, "notnull" => false, "default" => ""));
?>
<#100>
<?php
	$ilDB->modifyTableColumn('adn_md_wmo', 'visit_street',
		array("type" => "text", "length" => 50, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#101>
<?php
	$ilDB->modifyTableColumn('adn_md_wmo', 'visit_street_no',
		array("type" => "text", "length" => 10, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#102>
<?php
	$ilDB->modifyTableColumn('adn_md_wmo', 'visit_postal_code',
		array("type" => "text", "length" => 10, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#103>
<?php
	$ilDB->modifyTableColumn('adn_md_wmo', 'visit_city',
		array("type" => "text", "length" => 50, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#104>
<?php
if (!$ilDB->tableColumnExists("adn_ep_assignment", "archived"))
{
	$ilDB->addTableColumn("adn_ep_assignment", "archived", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 1));
}
?>
<#105>
<?php
	$ilDB->modifyTableColumn('adn_ta_provider', 'pa_postal_code',
		array("type" => "text", "length" => 10, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#106>
<?php
	$ilDB->modifyTableColumn('adn_ta_provider', 'pa_city',
		array("type" => "text", "length" => 50, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#107>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#108>
<?php
	global $rbacadmin;
	include_once './Services/AccessControl/classes/class.ilObjRole.php';

	$roles = array(
		"ZSUK" => "y",
		"WSD" => "y",
		"BMVBS" => "y",
		"Kandidat" => "y");

	foreach ($roles as $r => $p)
	{
		// create object record
		$id = $ilDB->nextId("object_data");
		$q = "INSERT INTO object_data ".
			 "(obj_id,type,title,description,owner,create_date,last_update,import_id) ".
			 "VALUES ".
			 "(".
			 $ilDB->quote($id, "integer").",".
			 $ilDB->quote("role", "text").",".
			 $ilDB->quote($r, "text").",".
			 $ilDB->quote("", "text").",".
			 $ilDB->quote(0, "integer").",".
			 $ilDB->now().",".
			 $ilDB->now().",".
			 $ilDB->quote("", "text").")";
		$ilDB->manipulate($q);

		// create role record
		$query = "INSERT INTO role_data ".
			"(role_id,allow_register,assign_users,disk_quota) ".
			"VALUES ".
			"(".$ilDB->quote($id, 'integer').",".
			$ilDB->quote(false, 'integer').",".
			$ilDB->quote(false, 'integer').",".
			$ilDB->quote(0, 'integer').")"
			;
		$ilDB->manipulate($query);

		// assign role to global role folder
		$query = sprintf('INSERT INTO rbac_fa (rol_id, parent, assign, protected) '.
			'VALUES (%s,%s,%s,%s)',
			$ilDB->quote($id, 'integer'),
			$ilDB->quote(8, 'integer'),
			$ilDB->quote('y', 'text'),
			$ilDB->quote($p, 'text'));
		$res = $ilDB->manipulate($query);
	}
?>
<#109>
<?php
$types = array("xata", "xaed", "xaep", "xaec", "xaes", "xacp", "xast", "xamd", "xaad");
foreach ($types as $t)
{
	// register new object type
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF(
	"INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
			"VALUES (%s, %s, %s, %s, %s, %s, %s)",
			array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
			array($id, "typ", $t, "", -1, ilUtil::now(), ilUtil::now()));
	$typ_id = $id;

	// create object data entry
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF(
	"INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
			"VALUES (%s, %s, %s, %s, %s, %s, %s)",
			array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
			array($id, $t, $t, "", -1, ilUtil::now(), ilUtil::now()));

	// create object reference entry
	$ref_id = $ilDB->nextId('object_reference');
	$res = $ilDB->manipulateF("INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($ref_id, $id));

	// put in tree
	$tree = new ilTree(ROOT_FOLDER_ID);
	$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

	// add rbac operations
	// 1: edit_permissions, 2: visible, 3: read, 4:write
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 1));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 2));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 3));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 4));
}
?>
<#110>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#111>
<?php
if (!$ilDB->tableColumnExists("adn_ep_answer_sheet", "archived"))
{
	$ilDB->addTableColumn("adn_ep_answer_sheet", "archived", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 1));
}
?>
<#112>
<?php
	$ilDB->createSequence("adn_ep_cand_sheet");
?>
<#113>
<?php
if (!$ilDB->tableColumnExists("adn_ep_cand_sheet", "archived"))
{
	$ilDB->addTableColumn("adn_ep_cand_sheet", "archived", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 1));
}
?>
<#114>
<?php
if (!$ilDB->tableColumnExists("adn_ed_objective", "sheet_subjected"))
{
	$ilDB->addTableColumn("adn_ed_objective", "sheet_subjected", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 1));
}
?>
<#115>
<?php

	$ilDB->dropTableColumn("adn_es_certificate", "proof_of_experience");

	$fields = array("proof_train_dry", "proof_train_tank", "proof_train_combined",
		"proof_train_gas", "proof_train_chemicals", "proof_exp_gas",
		"proof_exp_chemicals");

	foreach ($fields as $f)
	{
		$ilDB->addTableColumn("adn_es_certificate", $f, array(
			"notnull" => true,
			"length" => 1,
			"default" => "0",
			"type" => "integer"));
	}
?>

<#116>
<?php
	$ilDB->dropTableColumn("adn_es_certificate", "type_dry");
	$ilDB->addTableColumn("adn_es_certificate", "type_dm", array(
		"notnull" => true,
		"length" => 1,
		"default" => "0",
		"type" => "integer"));

	$ilDB->dropTableColumn("adn_es_certificate", "proof_train_dry");
	$ilDB->addTableColumn("adn_es_certificate", "proof_train_dm", array(
		"notnull" => true,
		"length" => 1,
		"default" => "0",
		"type" => "integer"));

?>

<#117>
<?php
	$ilDB->dropTableColumn("adn_es_certificate", "nr");
	$ilDB->addTableColumn("adn_es_certificate", "nr", array(
		"notnull" => true,
		"length" => 4,
		"default" => "0",
		"type" => "integer"));
?>
<#118>
<?php

	$ilDB->dropTableColumn("adn_es_certificate", "proof_train_chemicals");
	$ilDB->addTableColumn("adn_es_certificate", "proof_train_chem", array(
		"notnull" => true,
		"length" => 1,
		"default" => "0",
		"type" => "integer"));
?>
<#119>
<?php

	$ilDB->dropTableColumn("adn_es_certificate", "proof_exp_chemicals");
	$ilDB->addTableColumn("adn_es_certificate", "proof_exp_chem", array(
		"notnull" => true,
		"length" => 1,
		"default" => "0",
		"type" => "integer"));
?>
<#120>
<?php

	$ilDB->dropTableColumn("adn_es_certificate", "issued_on");
	$ilDB->addTableColumn("adn_es_certificate", "issued_on", array(
		"notnull" => true,
		"type" => "date"));
?>
<#121>
<?php
	$ilDB->modifyTableColumn('adn_ed_objective', 'nr',
		array("type" => "text", "length" => 50, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#122>
<?php
	$ilDB->modifyTableColumn('adn_ed_subobjective', 'nr',
		array("type" => "text", "length" => 50, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#123>
<?php
	$ilDB->dropTableColumn("adn_ed_question_case", "default_answer");
	$ilDB->addTableColumn("adn_ed_question_case", "default_answer", array(
		"notnull" => false,
		"type" => "clob"));
?>
<#124>
<?php
	$ilDB->dropTableColumn("adn_ta_facility", "name");
	$ilDB->addTableColumn("adn_ta_facility", "name",
		array("type" => "text", "length" => 4000, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#125>
<?php

$raw = '"AC";"Ascension"
"AD";"Andorra"
"AE";"Vereinigte Arabische Emirate"
"AF";"Afghanistan"
"AG";"Antigua und Barbuda"
"AI";"Anguilla"
"AL";"Albanien"
"AM";"Armenien"
"AN";"Niederländische Antillen"
"AO";"Angola"
"AQ";"Antarktis"
"AR";"Argentinien"
"AS";"Amerikanisch-Samoa"
"AT";"Österreich"
"AU";"Australien"
"AW";"Aruba"
"AX";"Aland"
"AZ";"Aserbaidschan"
"BA";"Bosnien und Herzegowina"
"BB";"Barbados"
"BD";"Bangladesch"
"BE";"Belgien"
"BF";"Burkina Faso"
"BG";"Bulgarien"
"BH";"Bahrain"
"BI";"Burundi"
"BJ";"Benin"
"BM";"Bermuda"
"BN";"Brunei"
"BO";"Bolivien"
"BR";"Brasilien"
"BS";"Bahamas"
"BT";"Bhutan"
"BV";"Bouvetinsel"
"BW";"Botswana"
"BY";"Weißrussland"
"BZ";"Belize"
"CA";"Kanada"
"CC";"Kokosinseln"
"CD";"Kongo, Demokratische Republik"
"CF";"Zentralafrikanische Republik"
"CG";"Kongo, Republik"
"CH";"Schweiz"
"CI";"Cote d\'Ivoire"
"CK";"Cookinseln"
"CL";"Chile"
"CM";"Kamerun"
"CN";"China, Volksrepublik"
"CO";"Kolumbien"
"CR";"Costa Rica"
"CS";"Serbien und Montenegro"
"CU";"Kuba"
"CV";"Kap Verde, Republik"
"CX";"Weihnachtsinsel"
"CY";"Zypern, Republik"
"CZ";"Tschechische Republik"
"DE";"Deutschland"
"DG";"Diego Garcia"
"DJ";"Dschibuti"
"DK";"Dänemark"
"DM";"Dominica"
"DO";"Dominikanische Republik"
"DZ";"Algerien"
"EC";"Ecuador"
"EE";"Estland"
"EG";"Ägypten"
"EH";"Westsahara"
"ER";"Eritrea"
"ES";"Spanien"
"ET";"Äthiopien"
"EU";"Europäische Union"
"FI";"Finnland"
"FJ";"Fidschi"
"FK";"Falklandinseln"
"FM";"Mikronesien, Föderierte Staaten von"
"FO";"Färöer"
"FR";"Frankreich"
"GA";"Gabun"
"GB";"Vereinigtes Königreich von Großbritannien und Nordirland"
"GD";"Grenada"
"GE";"Georgien"
"GF";"Französisch-Guayana"
"GG";"Guernsey, Vogtei"
"GH";"Ghana, Republik"
"GI";"Gibraltar"
"GL";"Grönland"
"GM";"Gambia"
"GN";"Guinea, Republik"
"GP";"Guadeloupe"
"GQ";"Äquatorialguinea, Republik"
"GR";"Griechenland"
"GS";"Südgeorgien und die Südlichen Sandwichinseln"
"GT";"Guatemala"
"GU";"Guam"
"GW";"Guinea-Bissau, Republik"
"GY";"Guyana"
"HK";"Hongkong"
"HM";"Heard und McDonaldinseln"
"HN";"Honduras"
"HR";"Kroatien"
"HT";"Haiti"
"HU";"Ungarn"
"IC";"Kanarische Inseln"
"ID";"Indonesien"
"IE";"Irland, Republik"
"IL";"Israel"
"IM";"Insel Man"
"IN";"Indien"
"IO";"Britisches Territorium im Indischen Ozean"
"IQ";"Irak"
"IR";"Iran"
"IS";"Island"
"IT";"Italien"
"JE";"Jersey"
"JM";"Jamaika"
"JO";"Jordanien"
"JP";"Japan"
"KE";"Kenia"
"KG";"Kirgisistan"
"KH";"Kambodscha"
"KI";"Kiribati"
"KM";"Komoren"
"KN";"St. Kitts und Nevis"
"KP";"Korea, Demokratische Volkrepublik"
"KR";"Korea, Republik"
"KW";"Kuwait"
"KY";"Kaimaninseln"
"KZ";"Kasachstan"
"LA";"Laos"
"LB";"Libanon"
"LC";"St. Lucia"
"LI";"Liechtenstein, Fürstentum"
"LK";"Sri Lanka"
"LR";"Liberia, Republik"
"LS";"Lesotho"
"LT";"Litauen"
"LU";"Luxemburg"
"LV";"Lettland"
"LY";"Libyen"
"MA";"Marokko"
"MC";"Monaco"
"MD";"Moldawien"
"ME";"Montenegro"
"MG";"Madagaskar, Republik"
"MH";"Marshallinseln"
"MK";"Mazedonien"
"ML";"Mali, Republik"
"MM";"Myanmar"
"MN";"Mongolei"
"MO";"Macao"
"MP";"Nördliche Marianen"
"MQ";"Martinique"
"MR";"Mauretanien"
"MS";"Montserrat"
"MT";"Malta"
"MU";"Mauritius, Republik"
"MV";"Malediven"
"MW";"Malawi, Republik"
"MX";"Mexiko"
"MY";"Malaysia"
"MZ";"Mosambik"
"NA";"Namibia, Republik"
"NC";"Neukaledonien"
"NE";"Niger"
"NF";"Norfolkinsel"
"NG";"Nigeria"
"NI";"Nicaragua"
"NL";"Niederlande"
"NO";"Norwegen"
"NP";"Nepal"
"NR";"Nauru"
"NT";"Neutrale Zone"
"NU";"Niue"
"NZ";"Neuseeland"
"OM";"Oman"
"PA";"Panama"
"PE";"Peru"
"PF";"Französisch-Polynesien"
"PG";"Papua-Neuguinea"
"PH";"Philippinen"
"PK";"Pakistan"
"PL";"Polen"
"PM";"St. Pierre und Miquelon"
"PN";"Pitcairninseln"
"PR";"Puerto Rico"
"PS";"Palästinensische Autonomiegebiete"
"PT";"Portugal"
"PW";"Palau"
"PY";"Paraguay"
"QA";"Katar"
"RE";"Réunion"
"RO";"Rumänien"
"RU";"Russische Föderation"
"RW";"Ruanda, Republik"
"SA";"Saudi-Arabien, Königreich"
"SB";"Salomonen"
"SC";"Seychellen, Republik der"
"SD";"Sudan"
"SE";"Schweden"
"SG";"Singapur"
"SH";"Die Kronkolonie St. Helena und Nebengebiete"
"SI";"Slowenien"
"SJ";"Svalbard und Jan Mayen"
"SK";"Slowakei"
"SL";"Sierra Leone, Republik"
"SM";"San Marino"
"SN";"Senegal"
"SO";"Somalia, Demokratische Republik"
"SR";"Suriname"
"ST";"São Tomé und Príncipe"
"SU";"Union der Sozialistischen Sowjetrepubliken"
"SV";"El Salvador"
"SY";"Syrien"
"SZ";"Swasiland"
"TA";"Tristan da Cunha"
"TC";"Turks- und Caicosinseln"
"TD";"Tschad, Republik"
"TF";"Französische Süd- und Antarktisgebiete"
"TG";"Togo, Republik"
"TH";"Thailand"
"TJ";"Tadschikistan"
"TK";"Tokelau"
"TL";"Timor-Leste, Demokratische Republik"
"TM";"Turkmenistan"
"TN";"Tunesien"
"TO";"Tonga"
"TR";"Türkei"
"TT";"Trinidad und Tobago"
"TV";"Tuvalu"
"TW";"Taiwan"
"TZ";"Tansania, Vereinigte Republik"
"UA";"Ukraine"
"UG";"Uganda, Republik"
"US";"Vereinigte Staaten von Amerika"
"UY";"Uruguay"
"UZ";"Usbekistan"
"VA";"Vatikanstadt"
"VC";"St. Vincent und die Grenadinen (GB)"
"VE";"Venezuela"
"VG";"Britische Jungferninseln"
"VI";"Amerikanische Jungferninseln"
"VN";"Vietnam"
"VU";"Vanuatu"
"WF";"Wallis und Futuna"
"WS";"Samoa"
"YE";"Jemen"
"YT";"Mayotte"
"ZA";"Südafrika, Republik"
"ZM";"Sambia, Republik"
"ZW";"Simbabwe, Republik"';

$countries = array();
foreach(explode("\n", $raw) as $item)
{
	$item = explode("\";\"", $item);
	$countries[substr($item[0], 1)] = substr($item[1], 0, -1);
}

$old = array();
$set = $ilDB->query("SELECT id FROM adn_md_country ORDER BY id");
while($row = $ilDB->fetchAssoc($set))
{
	$old[] = $row["id"];
}

foreach($countries as $code => $name)
{
	$fields = array(
		"code" => array("text", $code),
		"name" => array("text", $name)
		);

	$id = null;
	if(sizeof($old))
	{
		$id = array_shift($old);
		$ilDB->update("adn_md_country", $fields,  array("id"=>array("integer", $id)));
	}
	else
	{
		$fields["id"] = array("integer", $ilDB->nextId("adn_md_country"));
		$fields["create_date"] = $fields["last_update"] = array("timestamp", date("Y-m-d H:i:s"));
		$fields["create_user"] = $fields["last_update_user"] = array("integer", 1);
		$ilDB->insert("adn_md_country", $fields);
	}
}

?>
<#126>
<?php
if (!$ilDB->tableColumnExists("adn_ep_sheet_question", "ed_objective_id"))
{
	$ilDB->addTableColumn("adn_ep_sheet_question", "ed_objective_id", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 4));
}
?>
<#127>
<?php

$fields = array("ed_objective_id");
$ref_fields = array("id");
#$ilDB->addForeignKey("fk3","adn_ep_sheet_question", $fields, "adn_ed_objective", $ref_fields);
?>
<#128>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#129>
<?php
	$ilDB->createSequence('adn_cp_professional',1);
?>
<#130>
<?php
	$ilDB->dropTableColumn("adn_es_certificate", "nr_of_duplicates");
?>
<#131>
<?php
$fields = array (
	"es_certificate_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"duplicate_issued_on" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
);
$ilDB->createTable("add_es_duplicate", $fields, false, false, true);

?>
<#132>
<?php
$ilDB->dropTable("add_es_duplicate");
?>
<#133>
<?php
$fields = array (
	"es_certificate_id" => array (
		"notnull" => true
		,"length" => 4
		,"unsigned" => false
		,"type" => "integer"
	)
	,"duplicate_issued_on" => array (
		"notnull" => true
		,"default" => ""
		,"type" => "timestamp"
	)
);
$ilDB->createTable("adn_es_duplicate", $fields, false, false, true);

?>
<#134>
<?php
	$ilDB->dropTableColumn("adn_es_duplicate", "duplicate_issued_on");
?>
<#135>
<?php
	$ilDB->addTableColumn("adn_es_duplicate", "duplicate_issued_on", array(
		"notnull" => true
		,"default" => ""
		,"type" => "date"));
?>
<#136>
<?php

	// set code number of existing (test) wmos to 99
	$ilDB->manipulate("UPDATE adn_md_wmo SET ".
	" code_nr = ".$ilDB->quote("99", "integer"));

	$wmos = array("1" => "WSD Nord", "2" => "WSD Nordwest", "3" => "WSD Mitte", "4" => "WSD West",
		"5" => "WSD Südwest", "6" => "WSD Süd", "7" => "WSD Ost");
	foreach ($wmos as $code => $name)
	{
		$id = $ilDB->nextId("adn_md_wmo");

		$fields = array(
			"code_nr" => array("text", $code),
			"name" => array("text", $name),
			"street" => array("text", "."),
			"street_no" => array("text", "."),
			"postal_code" => array("text", "."),
			"city" => array("text", "."),
			"visit_street" => array("text", null),
			"visit_street_no" => array("text", null),
			"visit_postal_code" => array("text", null),
			"visit_city" => array("text", null),
			"bank" => array("text", "."),
			"bank_id" => array("integer", 0),
			"account_id" => array("integer", 0),
			"bic" => array("text", "."),
			"iban" => array("text", "."),
			"phone" => array("text", "."),
			"fax" => array("text", "."),
			"email" => array("text", "."),
			"internet" => array("text", "."),
			"create_date" => array("timestamp", ilUtil::now()),
			"create_user" => array("integer", 6),
			"last_update" => array("timestamp", ilUtil::now()),
			"last_update_user" => array("integer", 6),
			"archived" => array("integer", 0)
			);

		$fields["id"] = array("integer", $id);

		$costs = array("cert", "duplicate", "ext", "exam");
		foreach($costs as $id)
		{
			$fields[$id."_nr"] = array("integer", 1);
			$fields[$id."_description"] = array("text", ".");
			$fields[$id."_cost"] = array("integer", 0);
		}

		$ilDB->insert("adn_md_wmo", $fields);
	}
?>
<#137>
<?php
	$adn_fields = array("wmo_code", "sign");

	foreach ($adn_fields as $f)
	{
		// Add definition entry
		$next_id = $ilDB->nextId('udf_definition');

		$values = array(
			'field_id'				=> array('integer', $next_id),
			'field_name'			=> array('text', $f),
			'field_type'			=> array('integer', 1),
			'field_values'			=> array('clob', null),
			'visible'				=> array('integer', 1),
			'changeable'			=> array('integer', 0),
			'required'				=> array('integer', 0),
			'searchable'			=> array('integer', 0),
			'export'				=> array('integer', 0),
			'course_export'			=> array('integer', 0),
			'registration_visible'	=> array('integer', 0),
			'visible_lua'			=> array('integer', 0),
			'changeable_lua'		=> array('integer', 0),
			'group_export'			=> array('integer', 0)
		);

		$ilDB->insert('udf_definition',$values);
	}
?>
<#138>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#139>
<?php

$ilDB->manipulate("DELETE FROM adn_ad_character");

$chars = array("°", "Σ", "ρ", "²", "³", "₀", "₁", "₂",
	"₃", "₄", "₅", "₆", "₇", "₈", "₉");

foreach($chars as $char)
{
	$fields = array("id" => array("integer", $ilDB->nextId("adn_ad_character")),
		"charact" => array("text", $char));

	$ilDB->insert("adn_ad_character", $fields);
}

?>
<#140>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#141>
<?php

$client_ini = CLIENT_WEB_DIR."/client.ini.php";
$ini = new ilIniFile($client_ini);
$ini->GROUPS = parse_ini_file($client_ini, true);
$ini->setVariable("language","default", "de");
$ini->write();

$ilDB->update("settings", array(
		"value" => array("clob", "de")
	), array(
		"module" => array("text", "common"),
		"keyword" => array("text", "language")
	));

?>

<#142>
<?php

$ilDB->update("settings", array(
		"value" => array("clob", "0")
	), array(
		"module" => array("text", "common"),
		"keyword" => array("text", "require_email")
	));

?>

<#143>
<?php
if (!$ilDB->tableColumnExists("adn_cp_professional", "ilias_user_id"))
{
	$ilDB->addTableColumn("adn_cp_professional", "ilias_user_id", array(
		"notnull" => false
		,"length" => 4
		,"default" => null
		,"type" => "integer"));
}
?>

<#144>
<?php
	include_once './Services/AccessControl/classes/class.ilObjRole.php';
	$set = $ilDB->query("SELECT * FROM object_data WHERE ".
		" type = ".$ilDB->quote("role", "text")." AND ".
		" title = ".$ilDB->quote("Kandidat", "text")
		);
	$rec = $ilDB->fetchAssoc($set);

	$client_ini = CLIENT_WEB_DIR."/client.ini.php";
	$ini = new ilIniFile($client_ini);
	$ini->GROUPS = parse_ini_file($client_ini, true);
	$ini->setVariable("system","ONLINE_TEST_ROLE", $rec["obj_id"]);
	$ini->write();

?>

<#145>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#146>
<?php
if($ilDB->tableExists("adn_ad_user"))
{
	$ilDB->dropTable("adn_ad_user");
}
?>

<#147>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#148>
<?php

if (!$ilDB->tableColumnExists("adn_ed_question", "padded_nr"))
{
	$ilDB->addTableColumn("adn_ed_question", "padded_nr", array(
			"notnull" => false
			,"length" => 10
			,"default" => ""
			,"fixed" => false
			,"type" => "text"));
}

$set = $ilDB->query("SELECT id,nr FROM adn_ed_question");
while($row = $ilDB->fetchAssoc($set))
{
	$qnr = $padded_nr = $row["nr"];

	// chop first digit from rest, normalize and re-combine
	if(preg_match("/^([0-9]+)/", $padded_nr, $qnr_clean))
	{
		$digit = str_pad($qnr_clean[1], 2, "0", STR_PAD_LEFT);
		$padded_nr = $digit.substr($padded_nr, strlen($qnr_clean[1]));
	}

	$ilDB->manipulate("UPDATE adn_ed_question".
		" SET padded_nr = ".$ilDB->quote($padded_nr, "text").
		" WHERE id = ".$ilDB->quote($row["id"], "integer"));
}

?>

<#149>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#150>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#151>
<?php
	$ilDB->modifyTableColumn('adn_cp_professional', 'subject_area',
		array("type" => "text", "length" => 5, "notnull" => false,
			"default" => "", "fixed" => false));
?>

<#152>
<?php
if (!$ilDB->tableColumnExists("adn_ep_assignment", "scoring_update"))
{
	$ilDB->addTableColumn("adn_ep_assignment ", "scoring_update", array(
		"notnull" => false,
		"default" => "",
		"type" => "timestamp"));
}
?>

<#153>
<?php
if (!$ilDB->tableColumnExists("adn_ep_assignment", "scoring_update_user"))
{
	$ilDB->addTableColumn("adn_ep_assignment ", "scoring_update_user", array(
		"notnull" => false,
		"length" => 4,
		"type" => "integer"));
}
?>

<#154>
<?php

	$ilDB->modifyTableColumn("adn_md_wmo", "iban",
		array("type" => "text", "length" => 34, "notnull" => false,
			"default" => "", "fixed" => false));

?>

<#155>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#156>
<?php
	$ilCtrlStructureReader->getStructure();
?>
