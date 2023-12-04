<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;


class adnVerificationRestHandler
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
    public function verify(Request $request, Response $response, array $arguments) : Response
    {
        $this->logger->dump($arguments);
        $handler = new adnCardVerificationHandler(
            $arguments['TAC'] ?? '',
            $arguments['TAG_ID'] ?? '',
            $arguments['CERTIFICATE_ID'] ?? ''
        );

        $html = $handler->handleRequest();

        $body = new stdClass();
        $body->validation_result = $html;

        return $response
            ->withHeader('ContentType', 'application/json')
            ->withStatus(200, '')
            ->withJson($body);
    }
}