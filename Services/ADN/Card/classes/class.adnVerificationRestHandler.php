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
    public function verify(Request $request, Response $response)
    {
        $handler = new adnCardVerificationHandler(
            $request->getQueryParams()['tac'] ?? '',
            $request->getQueryParams()['tagID'] ?? '',
            $request->getQueryParams()['certificateID'] ?? ''
        );
        $html = $handler->handleRequest();

        $body = $response
            ->withHeader('ContentType', 'text/html')
            ->withStatus(200)
            ->getBody();
        $body->write($html);
    }
}