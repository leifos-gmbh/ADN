<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 *y Auto generate username configuration GUI class
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @version $Id$
 *
 */
class ilNewAccountNotificationConfigGUI extends ilPluginConfigGUI
{
	/**
	 * @var ilNewAccountNotificationConfig
	 */
	protected $config;

	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{

		switch ($cmd)
		{
			case "configure":
			case "save":
				$this->$cmd();
				break;
		}
	}

	/**
	 * Configure screen
	 */
	public function configure(ilPropertyFormGUI $form = null)
	{
		global $tpl;

		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initConfigurationForm();
		}
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init configuration form.
	 *
	 * @return ilPropertyFormGUI form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl, $ilUser;
		
		$this->initConfig();
		$pl = $this->getPluginObject();

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));
		$form->setTitle($pl->txt('new_account_rcps_tbl'));
		
		
		$form->addCommandButton("save", $lng->txt("save"));
		
		$rcps = new ilTextInputGUI($pl->txt('new_account_rcps'), 'recipients');
		$rcps->setInfo($pl->txt('new_account_rcps_info'));
		
		$rcps->setMulti(true);
		$rcps->setValue($this->config->getRecipients());
		$rcps->setSize(32);
		$rcps->setMaxLength(100);
		
		$form->addItem($rcps);
		return $form;
	}
	
	/**
	 * Save form input (currently does not save anything to db)
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;

		$this->initConfig();
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput())
		{
			ilLoggerFactory::getLogger('xnan')->dump($form->getInput('recipients'));
			
			$this->config->setRecipients($form->getInput('recipients'));
			$this->config->update();

			ilUtil::sendSuccess($lng->txt("saved_successfully"), true);
			$ilCtrl->redirect($this, "configure");
		}
		else
		{
			$form->setValuesByPost();
			$this->configure($form);
		}
	}

	/**
	 * Init config
	 */
	public function initConfig()
	{
		$this->getPluginObject()->includeClass("class.ilNewAccountNotificationConfig.php");

		$this->config = new ilNewAccountNotificationConfig();
	}
}
?>
