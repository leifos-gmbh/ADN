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
