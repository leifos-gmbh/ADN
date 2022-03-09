<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Adn report exception
 * Wrappes underlying exceptions and failures.
 * E.g. socket timeouts to rpc server or failures when trying to create PDFs
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesADN
 */
class adnReportException extends ilException
{
    /**
     * Constructor
     * @param string $message
     * @param int	$a_code
     */
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
