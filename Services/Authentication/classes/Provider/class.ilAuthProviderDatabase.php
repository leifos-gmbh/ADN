<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthProviderDatabase extends ilAuthProvider implements ilAuthProviderInterface
{

	
	/**
	 * Do authentication
	 * @return bool
	 */
	public function doAuthentication(ilAuthStatus $status)
	{
		include_once './Services/User/classes/class.ilUserPasswordManager.php';

		/**
		 * @var $user ilObjUser
		 */
		$user = ilObjectFactory::getInstanceByObjId(ilObjUser::_loginExists($this->getCredentials()->getUsername()),false);

		$this->getLogger()->debug('Trying to authenticate user: '. $this->getCredentials()->getUsername());
		if($user instanceof ilObjUser)
		{
			// adn-patch start
			// Check online test access code
			include_once("./Services/ADN/EP/classes/class.adnAssignment.php");

			if (adnAssignment::isValidAccessCode(
				ilUtil::stripSlashes($_POST['username']),
				ilUtil::stripSlashes($_POST['password'])))
			{
				$_SESSION["adn_online_test"] = true;
				$_SESSION["adn_test_user"] = ilUtil::stripSlashes($_POST['username']);
				$_SESSION["adn_access_code"] = ilUtil::stripSlashes($_POST['password']);
				$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
				$status->setAuthenticatedUserId($user->getId());
				return true;
			}
			// adn-patch end


			if(!ilAuthUtils::isLocalPasswordEnabledForAuthMode($user->getAuthMode(true)))
			{
				$this->getLogger()->debug('DB authentication failed: current user auth mode does not allow local validation.');
				$this->getLogger()->debug('User auth mode: ' . $user->getAuthMode(true));
				$this->handleAuthenticationFail($status, 'err_wrong_login');
				return false;
			}
			if(ilUserPasswordManager::getInstance()->verifyPassword($user, $this->getCredentials()->getPassword()))
			{
				$this->getLogger()->debug('Successfully authenticated user: ' . $this->getCredentials()->getUsername());
				$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
				$status->setAuthenticatedUserId($user->getId());
				return true;
				
			}
		}
		$this->handleAuthenticationFail($status, 'err_wrong_login');
		return false;
	}
}
?>