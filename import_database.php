<?php

define("SCRIPT_CLIENT", "cp");
define("IMPORT_FILE", "db_export.xml");

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

require_once("Services/Xml/classes/class.ilSaxParser.php");
class ilImportDatabase extends ilSaxParser{
	private $table;
	private $column;
	private $columns;
	private $character;
	private $ignore;

	/**
	 * set event handler
	 * should be overwritten by inherited class
	 * @access	private
	 */
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	 * start the parser
	 */
	function startParsing()
	{
		parent::startParsing();
	}

	/**
	 * handler for begin of element
	 */
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		global $ilDB, $file;
		switch($a_name)
		{
			case "row":
			case "tables":
				break;
			case "table":
				$this->table = $a_attribs["name"];
				$this->ignore = false;
				if(!$ilDB->tableExists($this->table)) {
					echo("Missing Table " . $this->table. "\n");
					$this->ignore = true;
					break;
				}

				echo("Process " . $this->table. "\n");

				if($ilDB->sequenceExists($this->table) && isset($a_attribs["seq"]))
				{
					$ilDB->dropSequence($this->table);
					$ilDB->createSequence($this->table, (int) $a_attribs["seq"]);
					echo(" Set sequence of ". $this->table. "\n");
				}

				$sql = "DELETE FROM ". $this->table;
				$ilDB->manipulate($sql);

				echo(" Deleted content of: ". $this->table . "\n" );
				break;
			case "column":
				$this->column = $a_attribs["name"];
				break;
		}
		$this->character = "";
	}

	/**
	 * handler for end of element
	 */
	function handlerEndTag($a_xml_parser, $a_name)
	{
		global $ilDB, $file;
		switch($a_name)
		{
			case "tables":
				break;
			case "table":
				$this->columns = array();
				echo(" Filled content of: ". $this->table . "\n" );
				break;
			case "column":
				$this->columns[$this->column] = $ilDB->quote( $this->character );
				break;
			case "row":
				if(!$this->ignore) {
					$columns = '(' . implode(array_keys($this->columns), ", ") . ')';
					$values = '(' . implode($this->columns, ", ") . ')';

					$sql = "INSERT INTO " . $this->table .
						" " . $columns .
						" VALUES " . $values . ";";
					$ilDB->manipulate($sql);
				}

				break;
		}
		$this->character = "";
	}

	/**
	 * handler for character data
	 */
	function handlerCharacterData($a_xml_parser, $a_data)
	{
		$this->character .= $a_data;
	}
}

echo("Start\n");
$sax = new ilImportDatabase(IMPORT_FILE);
$sax->startParsing();
echo("Stop\n");
