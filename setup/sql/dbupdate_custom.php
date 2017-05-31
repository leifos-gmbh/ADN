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

