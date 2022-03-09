<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Statistics GUI base class
 *
 * Module controller, handles table GUIs directly (just simple calls)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.adnStatisticsGUI.php 30175 2011-08-07 13:56:30Z smeyer $
 *
 * @ingroup ServicesADN
 *
 * @ilCtrl_Calls adnStatisticsGUI:
 */
class adnStatisticsGUI
{
    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $lng, $tpl;

        // set page title
        $tpl->setTitle($lng->txt("adn_st"));

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd();

        switch ($cmd) {	// menu item triggered
            case "processMenuItem":
                // determine cmd and cmdClass from menu item
                include_once("./Services/ADN/UI/classes/class.adnMainMenuGUI.php");
                switch ($_GET["menu_item"]) {
                    // extensions, refreshers
                    case adnMainMenuGUI::ST_ERS:
                        $cmd = "showExtensionsRefresher";
                        break;

                    // extensions, experience
                    case adnMainMenuGUI::ST_EES:
                        $cmd = "showExtensionsExperience";
                        break;

                    // certificates, other applications
                    case adnMainMenuGUI::ST_COS:
                        $cmd = "showCertificatesOther";
                        break;

                    // certificates, total
                    case adnMainMenuGUI::ST_TNS:
                        $cmd = "showCertificatesTotal";
                        break;

                    // certificates, gas/chem
                    case adnMainMenuGUI::ST_TGC:
                        $cmd = "showCertificatesGasChem";
                        break;

                    case adnMainMenuGUI::ST_EXS:
                    default:
                        // exams
                        $cmd = "showExams";
                        break;
                }
                // fallthrough

                // no break
            case "showExams":
            case "showExtensionsRefresher":
            case "showExtensionsExperience":
            case "showCertificatesOther":
            case "showCertificatesTotal":
            case "showCertificatesGasChem":
            case "applyExamsFilter":
            case "resetExamsFilter":
            case "applyExtensionsRefresherFilter":
            case "resetExtensionsRefresherFilter":
            case "applyExtensionsExperienceFilter":
            case "resetExtensionsExperienceFilter":
            case "applyCertificatesOtherFilter":
            case "resetCertificatesOtherFilter":
            case "applyCertificatesTotalFilter":
            case "resetCertificatesTotalFilter":
            case "applyCertificatesGasChemFilter":
            case "resetCertificatesGasChemFilter":
            case "downloadExams":
            case "downloadExtensionsRefresher":
            case "downloadExtensionsExperience":
            case "downloadCertificatesOther":
            case "downloadCertificatesTotal":
            case "downloadCertificatesGasChem":
                $this->$cmd();
                break;
        }

        adnBaseGUI::setHelpButton($ilCtrl->getCmdClass());
    }

    /**
     * Show list of exams
     */
    protected function showExams()
    {
        global $tpl, $lng, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $lng->txt("adn_download_statistics"),
            $ilCtrl->getLinkTarget($this, "downloadExams")
        );
        
        $tpl->setTitle($lng->txt("adn_st") . " - " . $lng->txt("adn_st_exs"));

        include_once "Services/ADN/ST/classes/class.adnStatisticsExamsTableGUI.php";
        $table = new adnStatisticsExamsTableGUI($this, "showExams");

        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply table filter (from table gui)
     */
    protected function applyExamsFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsExamsTableGUI.php";
        $table = new adnStatisticsExamsTableGUI($this, "showExams");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showExams();
    }

    /**
     * Reset table filter (from table gui)
     */
    protected function resetExamsFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsExamsTableGUI.php";
        $table = new adnStatisticsExamsTableGUI($this, "showExams");
        $table->resetOffset();
        $table->resetFilter();

        $this->showExams();
    }

    /**
     * Show list of refresher training types
     */
    protected function showExtensionsRefresher()
    {
        global $tpl, $lng, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $lng->txt("adn_download_statistics"),
            $ilCtrl->getLinkTarget($this, "downloadExtensionsRefresher")
        );

        $tpl->setTitle($lng->txt("adn_st") . " - " . $lng->txt("adn_st_ers"));

        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsRefresherTableGUI.php";
        $table = new adnStatisticsExtensionsRefresherTableGUI($this, "showExtensionsRefresher");

        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply table filter (from table gui)
     */
    protected function applyExtensionsRefresherFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsRefresherTableGUI.php";
        $table = new adnStatisticsExtensionsRefresherTableGUI($this, "showExtensionsRefresher");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showExtensionsRefresher();
    }

    /**
     * Reset table filter (from table gui)
     */
    protected function resetExtensionsRefresherFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsRefresherTableGUI.php";
        $table = new adnStatisticsExtensionsRefresherTableGUI($this, "showExtensionsRefresher");
        $table->resetOffset();
        $table->resetFilter();

        $this->showExtensionsRefresher();
    }

    /**
     * Show list of experiences
     */
    protected function showExtensionsExperience()
    {
        global $tpl, $lng, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $lng->txt("adn_download_statistics"),
            $ilCtrl->getLinkTarget($this, "downloadExtensionsExperience")
        );

        $tpl->setTitle($lng->txt("adn_st") . " - " . $lng->txt("adn_st_ees"));

        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsExperienceTableGUI.php";
        $table = new adnStatisticsExtensionsExperienceTableGUI($this, "showExtensionsExperience");

        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply table filter (from table gui)
     */
    protected function applyExtensionsExperienceFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsExperienceTableGUI.php";
        $table = new adnStatisticsExtensionsExperienceTableGUI($this, "showExtensionsExperience");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showExtensionsExperience();
    }

    /**
     * Reset table filter (from table gui)
     */
    protected function resetExtensionsExperienceFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsExperienceTableGUI.php";
        $table = new adnStatisticsExtensionsExperienceTableGUI($this, "showExtensionsExperience");
        $table->resetOffset();
        $table->resetFilter();

        $this->showExtensionsExperience();
    }

    /**
     * Show certificates for other applications
     */
    protected function showCertificatesOther()
    {
        global $tpl, $lng, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $lng->txt("adn_download_statistics"),
            $ilCtrl->getLinkTarget($this, "downloadCertificatesOther")
        );

        $tpl->setTitle($lng->txt("adn_st") . " - " . $lng->txt("adn_st_cos"));

        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesOtherTableGUI.php";
        $table = new adnStatisticsCertificatesOtherTableGUI($this, "showCertificatesOther");

        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply table filter (from table gui)
     */
    protected function applyCertificatesOtherFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesOtherTableGUI.php";
        $table = new adnStatisticsCertificatesOtherTableGUI($this, "showCertificatesOther");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showCertificatesOther();
    }

    /**
     * Reset table filter (from table gui)
     */
    protected function resetCertificatesOtherFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesOtherTableGUI.php";
        $table = new adnStatisticsCertificatesOtherTableGUI($this, "showCertificatesOther");
        $table->resetOffset();
        $table->resetFilter();

        $this->showCertificatesOther();
    }

    /**
     * Show list of certificates
     *
     */
    protected function showCertificatesTotal()
    {
        global $tpl, $lng, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $lng->txt("adn_download_statistics"),
            $ilCtrl->getLinkTarget($this, "downloadCertificatesTotal")
        );

        $tpl->setTitle($lng->txt("adn_st") . " - " . $lng->txt("adn_st_tns"));

        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesTotalTableGUI.php";
        $table = new adnStatisticsCertificatesTotalTableGUI($this, "showCertificatesTotal");

        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply table filter (from table gui)
     */
    protected function applyCertificatesTotalFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesTotalTableGUI.php";
        $table = new adnStatisticsCertificatesTotalTableGUI($this, "showCertificatesTotal");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showCertificatesTotal();
    }

    /**
     * Reset table filter (from table gui)
     */
    protected function resetCertificatesTotalFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesTotalTableGUI.php";
        $table = new adnStatisticsCertificatesTotalTableGUI($this, "showCertificatesTotal");
        $table->resetOffset();
        $table->resetFilter();

        $this->showCertificatesTotal();
    }

    /**
     * Show list of certificates for gas and chemicals training
     *
     */
    protected function showCertificatesGasChem()
    {
        global $tpl, $lng, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $lng->txt("adn_download_statistics"),
            $ilCtrl->getLinkTarget($this, "downloadCertificatesGasChem")
        );

        $tpl->setTitle($lng->txt("adn_st") . " - " . $lng->txt("adn_st_tgc"));

        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesGasChemTableGUI.php";
        $table = new adnStatisticsCertificatesGasChemTableGUI($this, "showCertificatesGasChem");

        $tpl->setContent($table->getHTML());
    }

    /**
     * Apply table filter (from table gui)
     */
    protected function applyCertificatesGasChemFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesGasChemTableGUI.php";
        $table = new adnStatisticsCertificatesGasChemTableGUI($this, "showCertificatesGasChem");
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showCertificatesGasChem();
    }

    /**
     * Reset table filter (from table gui)
     */
    protected function resetCertificatesGasChemFilter()
    {
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesGasChemTableGUI.php";
        $table = new adnStatisticsCertificatesGasChemTableGUI($this, "showCertificatesGasChem");
        $table->resetOffset();
        $table->resetFilter();

        $this->showCertificatesGasChem();
    }
    
    /**
     * Create and deliver report statistic
     */
    protected function downloadExams()
    {
        global $ilCtrl,$lng;
        
        include_once "Services/ADN/ST/classes/class.adnStatisticsExamsTableGUI.php";
        $table = new adnStatisticsExamsTableGUI($this, "showExtensionsRefresher");
        $table->getHTML();

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/Report/classes/class.adnReportStatistics.php';
            $stat = new adnReportStatistics();
            $stat->setType(adnReportStatistics::TYPE_EXAM);
            $stat->setData($table->getData());
            $stat->setWmo(
                $table->getFilterItemByPostVar('wmo')->getValue()
            );
            $stat->setDuration(
                $table->getFilterItemByPostVar('date')->getCombinationItem('from')->getDate(),
                $table->getFilterItemByPostVar('date')->getCombinationItem('to')->getDate()
            );
            
            $stat->create();
            
            ilUtil::deliverFile(
                $stat->getOutfile(),
                'Statistik.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'showExams');
        }
    }
    
    /**
     * Create and deliver report statistic (pdf)
     */
    protected function downloadExtensionsExperience()
    {
        global $ilCtrl,$lng;
        
        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsExperienceTableGUI.php";
        $table = new adnStatisticsExtensionsExperienceTableGUI($this, "showExtensionsExperience");
        $table->getHTML();

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/Report/classes/class.adnReportStatistics.php';
            $stat = new adnReportStatistics();
            $stat->setWmo(
                $table->getFilterItemByPostVar('wmo')->getValue()
            );
            $stat->setType(adnReportStatistics::TYPE_EXTENSION_EXP);
            $stat->setData($table->getData());
            $stat->setWmo(
                $table->getFilterItemByPostVar('wmo')->getValue()
            );
            $stat->setDuration(
                $table->getFilterItemByPostVar('date')->getCombinationItem('from')->getDate(),
                $table->getFilterItemByPostVar('date')->getCombinationItem('to')->getDate()
            );
            
            $stat->create();
            
            ilUtil::deliverFile(
                $stat->getOutfile(),
                'Statistik.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'showExams');
        }
    }

    /**
     * Create and deliver report statistic (pdf)
     */
    protected function downloadExtensionsRefresher()
    {
        global $ilCtrl,$lng;
        
        include_once "Services/ADN/ST/classes/class.adnStatisticsExtensionsRefresherTableGUI.php";
        $table = new adnStatisticsExtensionsRefresherTableGUI($this, "showExtensionsRefresher");
        $table->getHTML();

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/Report/classes/class.adnReportStatistics.php';
            $stat = new adnReportStatistics();
            $stat->setType(adnReportStatistics::TYPE_EXTENSION_REF);
            $stat->setData($table->getData());
            $stat->setWmo(
                $table->getFilterItemByPostVar('wmo')->getValue()
            );
            $stat->setDuration(
                $table->getFilterItemByPostVar('date')->getCombinationItem('from')->getDate(),
                $table->getFilterItemByPostVar('date')->getCombinationItem('to')->getDate()
            );
            
            $stat->create();
            
            ilUtil::deliverFile(
                $stat->getOutfile(),
                'Statistik.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'showExams');
        }
    }
    
    /**
     * Create and deliver report statistic (pdf)
     */
    protected function downloadCertificatesOther()
    {
        global $ilCtrl,$lng;
        
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesOtherTableGUI.php";
        $table = new adnStatisticsCertificatesOtherTableGUI($this, "showCertificatesOther");
        $table->getHTML();

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/Report/classes/class.adnReportStatistics.php';
            $stat = new adnReportStatistics();
            $stat->setType(adnReportStatistics::TYPE_CERTIFICATES_OTHER);
            $stat->setWmo(
                $table->getFilterItemByPostVar('wmo')->getValue()
            );
            $stat->setData($table->getData());
            $stat->setDuration(
                $table->getFilterItemByPostVar('date')->getCombinationItem('from')->getDate(),
                $table->getFilterItemByPostVar('date')->getCombinationItem('to')->getDate()
            );
            
            $stat->create();
            
            ilUtil::deliverFile(
                $stat->getOutfile(),
                'Statistik.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'showExams');
        }
    }
    
    /**
     * Create and deliver report statistic (pdf)
     */
    protected function downloadCertificatesGasChem()
    {
        global $ilCtrl,$lng;
        
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesGasChemTableGUI.php";
        $table = new adnStatisticsCertificatesGasChemTableGUI($this, "showCertificatesGasChem");
        $table->getHTML();

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/Report/classes/class.adnReportStatistics.php';
            $stat = new adnReportStatistics();
            $stat->setType(adnReportStatistics::TYPE_CERTIFICATES_GC);
            $stat->setWmo(
                $table->getFilterItemByPostVar('wmo')->getValue()
            );
            $stat->setData($table->getData());
            $stat->setDuration(
                $table->getFilterItemByPostVar('date')->getCombinationItem('from')->getDate(),
                $table->getFilterItemByPostVar('date')->getCombinationItem('to')->getDate()
            );
            
            $stat->create();
            
            ilUtil::deliverFile(
                $stat->getOutfile(),
                'Statistik.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'showExams');
        }
    }

    /**
     * Create and deliver report statistic (pdf)
     */
    protected function downloadCertificatesTotal()
    {
        global $ilCtrl,$lng;
        
        include_once "Services/ADN/ST/classes/class.adnStatisticsCertificatesTotalTableGUI.php";
        $table = new adnStatisticsCertificatesTotalTableGUI($this, "showCertificatesTotal");
        $table->getHTML();

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once './Services/ADN/Report/classes/class.adnReportStatistics.php';
            $stat = new adnReportStatistics();
            $stat->setType(adnReportStatistics::TYPE_CERTIFICATES_SUM);
            $stat->setWmo(
                $table->getFilterItemByPostVar('wmo')->getValue()
            );
            $stat->setData($table->getData());
            $stat->setDuration(
                $table->getFilterItemByPostVar('date')->getCombinationItem('from')->getDate(),
                $table->getFilterItemByPostVar('date')->getCombinationItem('to')->getDate()
            );
            
            $stat->create();
            
            ilUtil::deliverFile(
                $stat->getOutfile(),
                'Statistik.pdf',
                'application/pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $ilCtrl->redirect($this, 'showExams');
        }
    }
}
