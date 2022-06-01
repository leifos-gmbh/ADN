<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
use ILIAS\DI\LoggingServices;
/**
 * Adapter class for for rpc calls
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnRpcAdapter.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ingroup ServicesADN
 */
class adnRpcAdapter
{
    protected ilLogger $log;
    /**
     * Contructor
     */
    public function __construct()
    {
        global $DIC;
        $this->log = $DIC->logger()->lfadn();
    }
    
    /**
     * Fill Pdf template
     * @param string absolute path to pdf input file
     * @param string absolute path to pdf output file
     * @param array associative array with key value pairs
     * @return bool
     *
     * @throws adnReportException
     */
    public function fillPdfTemplate($a_infile, $a_outfile, $a_keyvalues)
    {
        
        try {
            include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
            $res = ilRpcClientFactory::factory('RPCTransformationHandler')->fillPdfTemplate(
                $a_infile,
                $a_outfile,
                $a_keyvalues
            );
            return true;
        } catch (XML_RPC2_FaultException $e) {
            $this->log->write(__METHOD__ . ': ' . $e->getMessage());
            throw new adnReportException($e->getMessage());
        } catch (Exception $e) {
            $this->log->write(__METHOD__ . ': ' . $e->getMessage());
            throw new adnReportException($e->getMessage());
        }
        return false;
    }
    
    /**
     * Perform multiple tasks in one step
     * @param string $a_xmldef
     * @return
     */
    public function transformationTaskScheduler($a_xmldef)
    {
        
        try {
            include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
            $res = ilRpcClientFactory::factory('RPCTransformationHandler')->transformationTaskScheduler(
                $a_xmldef
            );
            return true;
        } catch (XML_RPC2_FaultException $e) {
            $this->log->write(__METHOD__ . ': ' . $e->getMessage());
            throw new adnReportException($e->getMessage());
        } catch (Exception $e) {
            $this->log->write(__METHOD__ . ': ' . $e->getMessage());
            throw new adnReportException($e->getMessage());
        }
        return false;
    }
    
    /**
     * Write answer sheet
     * @param string $a_infile xml description
     * @param string $a_outfile generated pdf
     * @return
     */
    public function writeQuestionSheet($a_infile, $a_outfile)
    {
        
        try {
            include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
            $res = ilRpcClientFactory::factory('RPCTransformationHandler')->writeQuestionSheet(
                $a_infile,
                $a_outfile
            );
            return true;
        } catch (XML_RPC2_FaultException $e) {
            $this->log->write(__METHOD__ . ': ' . $e->getMessage());
            throw new adnReportException($e->getMessage());
        } catch (Exception $e) {
            $this->log->write(__METHOD__ . ': ' . $e->getMessage());
            throw new adnReportException($e->getMessage());
        }
        return false;
    }
}
