<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/Rest/classes/class.ilRestFileStorage.php';

/**
 * Slim rest server
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilRestServer extends Slim\App
{
    /**
     * ilRestServer constructor.
     * @param array $container
     */
    public function __construct($container = [])
    {
        parent::__construct($container);
    }


    /**
     * Init server / add handlers
     */
    public function init()
    {
        $callback_obj = new ilRestFileStorage();
        
        $this->get('/fileStorage', array($callback_obj,'getFile'));
        $this->post('/fileStorage', array($callback_obj,'createFile'));

        // begin-patch adn
        $adn_rest_test = new adnHidRestConnector();
        $this->post('/testHidVerification', [$adn_rest_test, 'verify']);
        $this->post('/services/65/prod/v1', [$adn_rest_test, 'verifyOrder']);

        $adn_rest_verification = new adnVerificationRestHandler();
        $this->get('/v1/verify', [$adn_rest_verification, 'verify']);

        $callback_obj->deleteDeprecated();
    }
}
