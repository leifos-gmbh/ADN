<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php

	$ilDB->modifyTableColumn('adn_md_wmo', 'cert_nr',
		array("type" => "text", "length" => 10, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#4>
<?php

	$ilDB->modifyTableColumn('adn_md_wmo', 'duplicate_nr',
		array("type" => "text", "length" => 10, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#5>
<?php

	$ilDB->modifyTableColumn('adn_md_wmo', 'ext_nr',
		array("type" => "text", "length" => 10, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#6>
<?php

	$ilDB->modifyTableColumn('adn_md_wmo', 'exam_nr',
		array("type" => "text", "length" => 10, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#7>
<?php
if (!$ilDB->tableColumnExists("adn_md_wmo", "subtitle"))
{
	$ilDB->addTableColumn("adn_md_wmo", "subtitle", array(
		"notnull" => false,
		"length" => 64,
		"type" => "text",
		"default" => '',
		'fixed' => true
		)
	);
}
?>
<#8>
<?php

	$ilDB->modifyTableColumn('adn_md_wmo', 'bank',
		array("type" => "text", "length" => 64, "notnull" => false,
			"default" => "", "fixed" => false));
?>
<#9>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#10>
<?php

$query = 'update adn_ep_assignment set score_case = score_case * 10';
$res = $ilDB->manipulate($query);
$query = 'update adn_ep_assignment set score_mc = score_mc * 10';
$res = $ilDB->manipulate($query);

?>
<#11>
<?php
if (!$ilDB->tableColumnExists("adn_cp_professional", "foreign_cert_handed_in"))
{
	$ilDB->addTableColumn("adn_cp_professional", "foreign_cert_handed_in", array(
		"notnull" => false,
		"length" => 1,
		"type" => "integer",
		"unsigned" => false,
		"default" => 0));
}
?>
<#12>
<?php
if (!$ilDB->tableColumnExists("adn_md_wmo", "notification_email"))
{
	$ilDB->addTableColumn("adn_md_wmo", "notification_email", array(
		"notnull" => false,
		"length" => 128,
		"type" => "text",
		"default" => '',
		'fixed' => true
	));
}
?>
<#13>
<?php
if (!$ilDB->tableColumnExists("adn_md_wmo", "exam_gas_chem_nr"))
{
    $ilDB->addTableColumn("adn_md_wmo", "exam_gas_chem_nr", array(
        "notnull" => false
        ,"length" => 10
        ,"default" => ""
        ,"fixed" => false
        ,"type" => "text"
    ));
}

if (!$ilDB->tableColumnExists("adn_md_wmo", "exam_gas_chem_description"))
{
    $ilDB->addTableColumn("adn_md_wmo", "exam_gas_chem_description", array(
        "notnull" => false
        ,"length" => 1000
        ,"default" => ""
        ,"fixed" => false
        ,"type" => "text"
    ));
}

if (!$ilDB->tableColumnExists("adn_md_wmo", "exam_gas_chem_cost"))
{
    $ilDB->addTableColumn("adn_md_wmo", "exam_gas_chem_cost", array(
        "notnull" => true
        ,"length" => 4
        ,"unsigned" => false
        ,"default" => 0
        ,"type" => "integer"
    ));
}
?>
<#14>
<?php

// ensure that minimal character set is available
$chars = array("°", "Σ", "ρ", "²", "³", "₀", "₁", "₂",
               "₃", "₄", "₅", "₆", "₇", "₈", "₉");

foreach($chars as $char)
{
    $set = $ilDB->queryF(
        "SELECT * FROM adn_ad_character " .
        " WHERE charact = %s ",
        ["text"],
        [$char]
    );
    $found = false;
    while ($rec = $ilDB->fetchAssoc($set)) {
        if ($rec["charact"] === $char) {
            $found = true;
        }
    }
    if (!$found) {
        $fields = array("id" => array("integer", $ilDB->nextId("adn_ad_character")),
                        "charact" => array("text", $char));
        $ilDB->insert("adn_ad_character", $fields);
    }
}
?>
<#15>
<?php

try {
    $ilDB->dropForeignKey('adn_ep_exam_event', 'fk1');
    $ilDB->dropForeignKey('adn_ep_exam_event', 'fk2');
    $ilDB->dropForeignKey('adn_ep_exam_event', 'fk3');
    $ilDB->dropForeignKey('adn_ep_exam_event', 'fk4');
} catch (ilDatabaseException $e) {
}
?>
<#16>
<?php
$query = 'SELECT login, usr_id from usr_data WHERE ' . $ilDB->like('login', ilDBConstants::T_TEXT, 'pruefkand_%');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    [$prefix, $candidate_id] = explode('_', $row->login);
    $update = 'update usr_data set login = ' . $ilDB->quote('Kandidat' . $candidate_id, ilDBConstants::T_TEXT) .
        'WHERE login = ' . $ilDB->quote($row->login, ilDBConstants::T_TEXT);
    $ilDB->manipulate($update);
}
?>
<#17>
<?php
if (!$ilDB->tableColumnExists('adn_es_certificate', 'uuid'))
{
    $ilDB->addTableColumn('adn_es_certificate', 'uuid', array(
        "notnull" => false
        ,"length" => 50
        ,"default" => ""
        ,"fixed" => false
        ,"type" => "text"
    ));
}
?>
<#18>
<?php
if (!$ilDB->tableColumnExists('adn_es_certificate', 'card_status'))
{
    $ilDB->addTableColumn('adn_es_certificate', 'card_status', array(
        "notnull" => false
        ,"length" => 2
        ,"default" => "0"
        ,"type" => "integer"
    ));
}
?>
