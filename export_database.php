<?php
gc_enable();
define("SCRIPT_CLIENT", "main");
define("EXPORT_FILE", "db_export.xml");
$mlimit = (int)(ini_get('memory_limit') * 1024 * 1024);
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

echo("Start export client ". SCRIPT_CLIENT . " to ". EXPORT_FILE ."\n");
echo("Memory Limit: " . ($mlimit/1024/1024) . "MB\n");

$xml = new XMLWriter();
$xml->openURI(EXPORT_FILE);
$xml->startDocument("1.0","UTF-8");

$xml->startElement("tables");

foreach ($ilDB->listTables() as $table)
{
	echo("Process " . $table. "                                  \r");
	$xml->startElement("table");
	$xml->startAttribute("name");
	$xml->text($table);
	$xml->endAttribute();
	$qc = 0;
	do{
		$i = 0;
		$ilDB->setLimit(100, $qc*100);
		$res = $ilDB->query("SELECT * FROM ".$table);

		$ex = false;

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
				$xml->endElement();
			}
			$xml->endElement();

			if( memory_get_usage() > $mlimit ) {
				$ex = true;
				$xml->flush();
				gc_collect_cycles();
			}
			$i++;
		}
		if($qc > 1 && $qc%10 == 1)
		{
			echo("Process " . $table. " Querys: ". ($qc*100) . " Memory usage: " . memory_get_usage() . "/" . $mlimit . " MB " .(memory_get_usage() > $mlimit*0.66 ? " (critical)": "") . "\r");
		}
		$qc++;
	}while($i >= 100);

	$xml->endElement();
	$xml->flush();
	gc_collect_cycles();
}
$xml->endElement();
$xml->endDocument();
$xml->flush();
echo("Stop                                                                                        \n");