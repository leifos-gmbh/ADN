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
 * Read order status from card service
 */
class adnCronCardOrderStatus extends ilCronJob
{
    private const CRON_ORDER_STATUS_ID = 'adn_card_order_status';
    private const CRON_ORDER_STATUS_MINUTES = 15;

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return self::CRON_ORDER_STATUS_ID;
    }

    public function hasAutoActivation()
    {
        return true;
    }

    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
    }

    public function getDefaultScheduleValue()
    {
        return self::CRON_ORDER_STATUS_MINUTES;
    }

    public function run()
    {
        $status = new adnCardCertificateOrderStatusHandler();
        $status->updateStatus();
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}