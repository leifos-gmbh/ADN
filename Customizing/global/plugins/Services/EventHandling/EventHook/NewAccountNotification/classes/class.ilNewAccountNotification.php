<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 */
class ilNewAccountNotification extends ilMailNotification
{
	/**
	 * ilNewAccountConfig
	 */
	private $config = null;
	
	/**
	 * ilPlugin
	 */
	private $plugin = null;
	
	
	
	/**
	 * Default constructor
	 * @return 
	 */
	public function __construct(ilNewAccountNotificationConfig $config, ilPlugin $plugin)
	{
		parent::__construct();
		
		$this->config = $config;
		$this->plugin = $plugin;
	}
	
	/**
	 * @param string $a_keyword
	 * @return string
	 */
	protected function getLanguageText($a_keyword)
	{
		return str_replace('\n', "\n", $this->plugin->txt($a_keyword));
	}
	
	
	/**
	 * Parse and send mail
	 * @return 
	 */
	public function send()
	{
		foreach($this->config->getRecipients() as $rcp)
		{
			ilLoggerFactory::getLogger('xnan')->debug('Sending mail to ' . $rcp);
			
			$this->initLanguage($rcp);
			$this->initMail();
			$this->setSubject(
				$this->getLanguageText('mail_new_user')
			);
			$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
			$this->appendBody("\n\n");
				
			$this->appendBody($this->getLanguageText('mail_new_user_body'));
			$this->appendBody("\n\n");
			$this->appendBody($this->getLanguageText('mail_body_profile'));
			
			$info = $this->getAdditionalInformation();
			
			$this->appendBody("\n\n");
			$this->appendBody($info['usr']->getProfileAsString($this->getLanguage()));


            /** @var ilMailMimeSenderFactory $senderFactory */
            $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

            $mime = new ilMimeMail();
            $mime->From($senderFactory->system());
			$mime->To($rcp);
			$mime->Subject($this->getSubject());
			$mime->Body($this->getBody());
			$mime->Send();
        }
	}
	
	/**
	 * Add language module registration
	 * @param object $a_usr_id
	 * @return 
	 */
	protected function initLanguage($a_usr_id)
	{
		parent::initLanguage($a_usr_id);
		$this->getLanguage()->loadLanguageModule('registration');
	}
	
}
