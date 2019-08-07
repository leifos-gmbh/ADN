<?php

define("SCRIPT_CLIENT", "main");
define("EXPORT_FILE", "db_export.xml");

require_once("Services/Init/classes/class.ilInitialisation.php");
class CustomInitialisation extends ilInitialisation{
	public static function customInit($client_id)
	{
		$_COOKIE["ilClientId"] = $client_id;
		self::initCore();
		self::initClient();
	}
}

CustomInitialisation::customInit(SCRIPT_CLIENT);

/**
 * @var $ilDB ilDB
 */
global $ilDB;

echo("Start\n");

$xml = new XMLWriter();
$xml->openMemory();
$xml->startDocument("1.0","UTF-8");

$xml->startElement("tables");

foreach ($ilDB->listTables() as $table)
{
	echo("Process " . $table. "\n");
	$xml->startElement("table");
	$xml->startAttribute("name");
	$xml->text($table);
	$xml->endAttribute();

	$res = $ilDB->query("SELECT * FROM ".$table);

	while($row = $ilDB->fetchAssoc($res))
	{
		$xml->startElement("row");
		foreach($row as $column => $value)
		{
			$xml->startElement("column");
			$xml->startAttribute("name");
			$xml->text($column);
			$xml->endAttribute();
			$xml->writeCdata($value);
			//$xml->text($value);
			$xml->endElement();
		}
		$xml->endElement();
	}
	$xml->endElement();

}
$xml->endElement();
$xml->endDocument();

file_put_contents(EXPORT_FILE, $xml->outputMemory());
echo("Stop\n");