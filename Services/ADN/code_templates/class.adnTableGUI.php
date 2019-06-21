/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN ${txt_class_name} table GUI class
 *
 * @author ${author} <${email}>
 * @version $$Id$$
 *
 * @ingroup ServicesADN
 */
class adn${ClassName}TableGUI extends ilTable2GUI
{
	
	/**
	 * Constructor
	 *
	 * @param	object	$$a_parent_obj	parent gui object
	 * @param	string	$$a_parent_cmd	parent default command
	 */
	function __construct($$a_parent_obj, $$a_parent_cmd)
	{
		global $$ilCtrl, $$lng;

		parent::__construct($$a_parent_obj, $$a_parent_cmd);
		include_once("./Services/ADN/TA/classes/class.adn${ClassName}.php");
		$$this->setData(adn${ClassName}::getAll${ClassName}s());
		$$this->setTitle($$lng->txt("adn_${class_name}s"));
		
		$$this->addColumn("", "", "1");
		$$this->addColumn($$this->lng->txt("adn_${property_var}"), "${property_var}");
		$$this->addColumn($$this->lng->txt("actions"));

		$$this->setDefaultOrderField("${property_var}");
		$$this->setDefaultOrderDirection("asc");
		
		$$this->setFormAction($$ilCtrl->getFormAction($$a_parent_obj));
		$$this->setRowTemplate("tpl.${class_name}s_row.html", "Services/ADN/TA");
		
		$$this->addMultiCommand("confirm${ClassName}Deletion", $$lng->txt("delete"));
	}
	
	/**
	 * Fill table row
	 *
	 * @param	array	$$a_set	data array
	 */
	protected function fillRow($$a_set)
	{
		global $$lng, $$ilCtrl;

		// actions...
		$$ilCtrl->setParameter($$this->parent_obj, "${save_par}", $$a_set["id"]);

		// ...edit
		$$this->tpl->setCurrentBlock("action");
		$$this->tpl->setVariable("TXT_CMD",
			$$lng->txt("adn_edit_${class_name}"));
		$$this->tpl->setVariable("HREF_CMD",
			$$ilCtrl->getLinkTarget($$this->parent_obj, "edit${ClassName}"));
		$$this->tpl->parseCurrentBlock();

		$$ilCtrl->setParameter($$this->parent_obj, "${save_par}", "");

		// properties
		$$this->tpl->setVariable("VAL_ID", $$a_set["id"]);
		$$this->tpl->setVariable("VAL_NAME", $$a_set["${property_var}"]);
	}
	
}