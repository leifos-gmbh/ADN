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
        // begin-patch adn
        $adn_rest_test = new adnHidRestConnector();
        $this->post('/testHidVerification', [$adn_rest_test, 'verify']);
        $this->post('/services/65/prod/v1', [$adn_rest_test, 'verifyOrder']);

        $plc_mock_connection_handler = new plcMockConnectionHandler();
        $this->get('/services/65/test/v1/heartbeat', [$plc_mock_connection_handler, 'heartbeat']);

        $plc_mock_order_handler = new plcMockOrderHandler();
        $this->post('/services/65/test/v1/order', [$plc_mock_order_handler, 'order']);

        $plc_mock_card_status_handler = new plcMockCardStatusHandler();
        $this->post('/services/65/test/v1/status/card', [$plc_mock_card_status_handler, 'status']);

        $adn_rest_verification = new adnVerificationRestHandler();
        $this->get('/verification/{TAC}/{TAG_ID}/{CERTIFICATE_ID}', [$adn_rest_verification, 'verify']);

        #$callback_obj->deleteDeprecated();
    }
}
