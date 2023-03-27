<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/ADN/Card/classes/class.adnCardVerificationHandler.php';
$verification = new adnCardVerificationHandler();
$verification->initRequest();
$verification->handleRequest();
