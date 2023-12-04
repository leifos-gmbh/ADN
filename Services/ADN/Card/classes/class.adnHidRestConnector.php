<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;

/**
 * NFC tag verification using "Trusted Tag Services" by HID
 * Dummy test rest server
 *
 * @author       Stefan Meyer <meyer@leifos.de>
 * @ingroup      ServicesADN
 */
class adnHidRestConnector
{
    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->adn();
    }


    /**
     * Verify token
     *
     * @param Request  $request
     * @param Response $response
     */
    public function verify(Request $request, Response $response)
    {
        $json_response = new stdClass();
        $json_response->response = 'true';
        $json_response->code = '0000';
        $json_response->description = 'TAC was authenticated';
        $json_request = json_decode($request->getBody());
        $this->logger->dump($json_request);
        $user = $json_request->systemUserName;
        if ($user !== 'adn') {
            $json_response->code = '0003';
        }
        elseif ($json_request->systemPassword !== 'adn') {
            $json_response->code = '0002';
        }
        elseif (strlen($json_request->tac) !== 3) {
            $json_response->code = '0006';
        }
        elseif ($json_request->tac !== 'adn') {
            $json_response->code = '0001';
        }
        elseif ($json_request->tagID !== 'adn') {
            $json_response->code = '0005';
        }
        return $response
            ->withHeader('ContentType', 'application/json')
            ->withJson($json_response);
    }

    public function verifyOrder(Request $request, Response $response)
    {
        $json_request = json_decode($request->getBody());
        $this->logger->dump('Incoming data');
        $this->logger->dump($request->getContentType());
        $this->logger->dump($json_request);

        return $response
            ->withHeader('ContentType', 'text/plain')
            ->withStatus(200, 'Ok');
    }

}