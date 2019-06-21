<?php
 
/**
 * Auto Generate Username configuration class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilNewAccountNotificationConfig
{
	/**
	 * @var ilSetting
	 */
	protected $setting;

	protected $recipients = array();
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->setting = new ilSetting("xnan");
		$this->read();
	}
	
	public function getRecipients()
	{
		return $this->recipients;
	}
	
	/**
	 * Set recipients
	 * @param array
	 */
	public function setRecipients($a_rcp)
	{
		$this->recipients = $a_rcp;
	}

	/**
	 * Get recipient
	 */
	public function read()
	{
		$this->setting->read();
		
		$rcps = $this->setting->get('recipients', null);
		if($rcps)
		{
			$this->recipients = explode(',', $rcps);
		}
		else
		{
			$this->recipients = array();
		}
	}

	/**
	 * Update settings
	 */
	public function update()
	{
		$this->setting->set('recipients', implode(',', $this->getRecipients()));
	}

}
?>