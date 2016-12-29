/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN ${txt_class_name} GUI class
 *
 * @author ${author} <${email}>
 * @version $$Id$$
 *
 * @ingroup ServicesADN
 */
class adn${ClassName}GUI
{
	// current ${txt_class_name} object
	protected $$${class_instance_var} = null;

	// current form object
	protected $$form = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $$ilCtrl;

		// save ${txt_class_name} ID through requests
		$$ilCtrl->saveParameter($$this, array("${save_par}"));

		$$this->read${ClassName}();
	}

	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		global $$ilCtrl;

		$$next_class = $$ilCtrl->getNextClass();

		// forward command to next gui class in control flow
		switch ($$next_class)
		{
			case "...":
				break;

			// no next class:
			// this class is responsible to process the command
			default:
				$$cmd = $$ilCtrl->getCmd("list${ClassName}s");

				switch ($$cmd)
				{
					// commands that need read permission
					case "list${ClassName}s":

						// @todo: check read permission

						$$this->$$cmd();

						break;

					// commands that need write permission
					case "add${ClassName}":
					case "save${ClassName}":
					case "edit${ClassName}":
					case "update${ClassName}":
					case "confirm${ClassName}Deletion":
					case "delete${ClassName}":

						// @todo: check write permission

						$$this->$$cmd();

						break;

				}
				break;
		}
	}

	/**
	 * Read ${txt_class_name}
	 */
	protected function read${ClassName}()
	{
		if ((int) $$_GET["${save_par}"] > 0)
		{
			include_once("./Services/ADN/TA/classes/class.adn${ClassName}.php");
			$$this->${class_instance_var} = new adn${ClassName}((int) $$_GET["${save_par}"]);
		}
	}

	/**
	 * List all ${txt_class_name}s
	 */
	protected function list${ClassName}s()
	{
		global $$tpl, $$ilToolbar, $$ilCtrl, $$lng;


		// @todo: check write permission for button
		// button: "Add Training Organizer"
		$$ilToolbar->addButton($$lng->txt("adn_add_${class_name}"),
			$$ilCtrl->getLinkTarget($$this, "add${ClassName}"));

		// table of ${txt_class_name}s
		include_once("./Services/ADN/TA/classes/class.adn${ClassName}TableGUI.php");
		$$table = new adn${ClassName}TableGUI($$this, "list${ClassName}s");

		// output table
		$$tpl->setContent($$table->getHTML());
	}

	/**
	 * Add new ${txt_class_name}
	 */
	protected function add${ClassName}()
	{
		global $$tpl;

		$$this->init${ClassName}Form("create");
		$$tpl->setContent($$this->form->getHTML());
	}

	/**
	 * Edit ${txt_class_name}
	 */
	protected function edit${ClassName}()
	{
		global $$tpl;

		$$this->init${ClassName}Form("edit");
		$$this->get${ClassName}Values();
		$$tpl->setContent($$this->form->getHTML());
	}

	/**
	 * Init ${txt_class_name} form.
	 *
	 * @param	string	$$a_mode		form mode ("create" | "edit")
	 */
	protected function init${ClassName}Form($$a_mode = "edit")
	{
		global $$lng, $$ilCtrl;

		// get form object and add input fields
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$$this->form = new ilPropertyFormGUI();

		// ${txt_property_name}
		$$ti = new ilTextInputGUI($$lng->txt("adn_${property_var}"), "${property_var}");
		$$ti->setMaxLength(200);
		$$ti->setRequired(true);
		$$this->form->addItem($$ti);

		if ($$a_mode == "create")
		{
			// creation: save/cancel buttons and title
			$$this->form->addCommandButton("save${ClassName}", $$lng->txt("save"));
			$$this->form->addCommandButton("list${ClassName}s", $$lng->txt("cancel"));
			$$this->form->setTitle($$lng->txt("adn_add_${class_name}"));
		}
		else
		{
			// editing: update/cancel buttons and title
			$$this->form->addCommandButton("update${ClassName}", $$lng->txt("save"));
			$$this->form->addCommandButton("list${ClassName}s", $$lng->txt("cancel"));
			$$this->form->setTitle($$lng->txt("adn_edit_${class_name}"));
		}

		$$this->form->setFormAction($$ilCtrl->getFormAction($$this));
	}

	/**
	 * Get current values for ${txt_class_name} form
	 */
	protected function get${ClassName}Values()
	{
		$$values = array();

		$$values["${property_var}"] = $$this->${class_instance_var}->get${Property}();

		$$this->form->setValuesByArray($$values);
	}

	/**
	 * Save ${txt_class_name} form
	 */
	protected function save${ClassName}()
	{
		global $$tpl, $$lng, $$ilCtrl;

		$$this->init${ClassName}Form("create");

		// check input
		if ($$this->form->checkInput())
		{
			// input ok: create new ${txt_class_name}
			include_once("./Services/ADN/TA/classes/class.adn${ClassName}.php");
			$$${class_instance_var} = new adn${ClassName}();
			$$${class_instance_var}->set${Property}($$_POST["${property_var}"]);
			$$${class_instance_var}->create();

			// show success message and return to list
			ilUtil::sendSuccess($$lng->txt("adn_${class_name}_created"), true);
			$$ilCtrl->redirect($$this, "list${ClassName}s");
		}

		// input not valid: show form again
		$$this->form->setValuesByPost();
		$$tpl->setContent($$this->form->getHtml());
	}

	/**
	 * Update ${txt_class_name}
	 */
	protected function update${ClassName}()
	{
		global $$lng, $$ilCtrl, $$tpl;

		$$this->init${ClassName}Form("edit");

		// check input
		if ($$this->form->checkInput())
		{
			// perform update
			$$this->${class_instance_var}->set${Property}($$_POST["${property_var}"]);
			$$this->${class_instance_var}->update();

			// show success message and return to list
			ilUtil::sendSuccess($$lng->txt("adn_${class_name}_updated"), true);
			$$ilCtrl->redirect($$this, "list${ClassName}s");
		}

		// input not valid: show form again
		$$this->form->setValuesByPost();
		$$tpl->setContent($$this->form->getHtml());
	}

	/**
	 * Confirm ${txt_class_name} deletion
	 */
	protected function confirm${ClassName}Deletion()
	{
		global $$ilCtrl, $$tpl, $$lng;

		// check whether at least one item has been seleced
		if (!is_array($$_POST["${class_instance_var}_id"]) || count($$_POST["${class_instance_var}_id"]) == 0)
		{
			ilUtil::sendFailure($$lng->txt("no_checkbox"), true);
			$$ilCtrl->redirect($$this, "list${ClassName}s");
		}
		else
		{
			// display confirmation message
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$$cgui = new ilConfirmationGUI();
			$$cgui->setFormAction($$ilCtrl->getFormAction($$this));
			$$cgui->setHeaderText($$lng->txt("adn_sure_delete_${class_name}"));
			$$cgui->setCancel($$lng->txt("cancel"), "list${ClassName}s");
			$$cgui->setConfirm($$lng->txt("delete"), "delete${ClassName}");

			// list objects that should be deleted
			foreach ($$_POST["${class_instance_var}_id"] as $$i)
			{
				include_once("./Services/ADN/TA/classes/class.adn${ClassName}.php");
				$$cgui->addItem("${class_instance_var}_id[]", $$i, adn${ClassName}::lookupName($$i));
			}

			$$tpl->setContent($$cgui->getHTML());
		}
	}

	/**
	 * Delete ${txt_class_name}
	 */
	protected function delete${ClassName}()
	{
		global $$ilCtrl, $$lng;

		include_once("./Services/ADN/TA/classes/class.adn${ClassName}.php");

		if (is_array($$_POST["${class_instance_var}_id"]))
		{
			foreach ($$_POST["${class_instance_var}_id"] as $$i)
			{
				$$${class_instance_var} = new adn${ClassName}($$i);
				$$${class_instance_var}->delete();
			}
		}
		ilUtil::sendSuccess($$lng->txt("adn_${class_name}_deleted"), true);
		$$ilCtrl->redirect($$this, "list${ClassName}s");
	}

}