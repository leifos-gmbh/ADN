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

/**
 * Library to access/query multiple certificates
 */
class adnCertificates
{
    private ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * @return adnCertificate[]
     */
    public function getCertificatesByCardOrderStatus(int $card_status): array
    {
        $query = 'select id from adn_es_certificate ' .
            'where uuid IS NOT NULL ' .
            'and card_status = ' . $this->db->quote($card_status, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $certificates = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $certificates[] = new adnCertificate($row->id);
        }
        return $certificates;
    }

    public function updateProductionState(string $a_certificate_id, int $a_production_state): void
    {
        $query = 'update adn_es_certificate ' .
            'set card_status = ' . $this->db->quote($a_production_state, ilDBConstants::T_INTEGER) . ' ' .
            'where uuid = ' . $this->db->quote($a_certificate_id, ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }
}