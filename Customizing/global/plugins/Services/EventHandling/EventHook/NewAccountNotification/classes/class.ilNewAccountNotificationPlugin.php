<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/EventHandling/classes/class.ilEventHookPlugin.php';
require_once 'Services/Component/classes/class.ilPluginAdmin.php';
include_once 'Services/Utilities/classes/class.ilStr.php';

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilNewAccountNotificationPlugin extends ilEventHookPlugin
{

	/**
	 * @return string
	 */
	final public function getPluginName()
	{
		return "NewAccountNotification";
	}

	/**
	 * @param string $a_component
	 * @param string $a_event
	 * @param array $a_params
	 * @return bool
	 * @throws ilUserException
	 */
	public function handleEvent($a_component, $a_event, $a_params)
	{
		switch($a_component)
		{
			case 'Services/User':
				switch($a_event)
				{
					case 'afterCreate':
						if(!$a_params['user_obj'] instanceof ilObjUser)
						{
							return false;
						}
						
						$auth_mode = $a_params['user_obj']->getAuthMode();
						if(substr($auth_mode,0,4) !== 'ldap')
						{
							#return false;
						}
						ilLoggerFactory::getLogger('xnan')->debug('New user creation event: ' . $a_params['user_obj']->getAuthMode());
						$this->includeClass('class.ilNewAccountNotification.php');
						$this->includeClass('class.ilNewAccountNotificationConfig.php');
						$noti = new ilNewAccountNotification(
							new ilNewAccountNotificationConfig(),
							$this
						);
						$noti->setAdditionalInformation(array('usr' => $a_params['user_obj']));
						$noti->send();
						break;
				}
			break;
		}

		return true;
	}

}
