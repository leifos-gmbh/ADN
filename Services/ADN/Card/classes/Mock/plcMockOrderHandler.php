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

declare(strict_types=1);

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;

class plcMockOrderHandler
{
    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->adn();
    }

    public function order(Request $request, Response $response) : Response
    {
        $this->logger->dump('Incoming data...', ilLogLevel::INFO);
        $this->logger->dump($request->getHeaders());
        $this->logger->dump($request->getContentType());
        $this->logger->dump($request->getBody());

        // write to stream
        $response->getBody()->write((rand(0, 10) >= 5) ? 'OK' : '');
        return $response
            ->withHeader('ContentType', 'text/plain')
            ->withStatus(200, 'Ok');
    }

}