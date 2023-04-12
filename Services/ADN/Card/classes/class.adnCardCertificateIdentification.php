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

use ILIAS\Data\UUID\Factory as Factory;

/**
 * @classDescription creates unique certificate uuids
 * @author Stefan Meyer <meyer@leifos.de>
 *
 */
class adnCardCertificateIdentification
{
    private ILIAS\Data\UUID\Factory $uuid_factory;

    public function __construct()
    {
        $this->uuid_factory = new Factory();
    }

    public function identificator() : string
    {
        $uuid = '';
        try {
            $uuid = $this->uuid_factory->uuid4AsString();
        } catch (\Exception $e) {
            throw new LogicException('Creating certificate identificator failed with message: ' . $e->getMessage());
        }
        return $uuid;

    }
}