<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/classes/class.adnReport.php';
include_once './Services/ADN/MD/classes/class.adnWMO.php';

/**
 * Generation of answer sheets.
 * See menu "Exam preparation -> Answer Sheets".
 * Use setType() for different types (Dry materials, Combined, Gas, Chemicals) of answer sheet
 * reports.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportAnswerSheet.php 45509 2013-10-16 10:16:00Z smeyer $
 *
 * @ingroup ServicesADN
 */

class adnReportAnswerSheet extends adnReport
{
    const TYPE_DRY = 1;
    const TYPE_TANK = 2;
    const TYPE_COMB = 3;
    const TYPE_GAS = 4;
    const TYPE_CHEM = 5;
    
    private $event = null;
    private $wmo = null;
    private $type = null;
    
    private $candidates = array();

    private $has_case_part = false;

    /**
     * Contructor
     */
    public function __construct(adnExaminationEvent $event)
    {
        parent::__construct();
        $this->event = $event;

        include_once './Services/ADN/MD/classes/class.adnExamFacility.php';
        $fac = new adnExamFacility($this->getEvent()->getFacility());
        $this->wmo = new adnWMO($fac->getWMO());

        include_once './Services/ADN/ED/classes/class.adnSubjectArea.php';
        $this->has_case_part = adnSubjectArea::hasCasePart($this->getEvent()->getType());
    }
    
    /**
     * Lookup file mtime
     * @param int $a_candidate_id
     * @param int $a_sheet_id
     * @return
     * @access static
     */
    public static function lookupCandidateSheetGenerated($a_candidate_id, $a_sheet_id)
    {
        include_once './Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php';

        $sheet = new adnAnswerSheetAssignment(
            adnAnswerSheetAssignment::find($a_candidate_id, $a_sheet_id)
        );
        $dt = $sheet->getGeneratedOn()->get(IL_CAL_UNIX);
        return ($dt < mktime(0, 0, 0, 1, 1, 2000)) ? 0 : $dt;
    }

    /**
     * Delete candidate sheet
     * @param int $candidate_id
     * @param int $sheet_id
     * @access static
     */
    public static function deleteSheet($a_candidate_id, $a_sheet_id)
    {
        $path = ilUtil::getDataDir() . '/adn/report/ash/';
        $file = $path . $a_sheet_id . '_' . $a_candidate_id . '_cover_mc.pdf';

        if (@file_exists($file)) {
            return @unlink($file);
        }

        $file = $path . $a_sheet_id . '_' . $a_candidate_id . '_cover_case.pdf';
        if (@file_exists($file)) {
            return @unlink($file);
        }

        $file = $path . $a_sheet_id . '_' . $a_candidate_id . '_cover_base.pdf';
        if (@file_exists($file)) {
            return @unlink($file);
        }

        return 0;
    }


    /**
     * Check if event has case part
     * @return bool
     */
    public function hasCasePart()
    {
        return $this->has_case_part;
    }
    
    /**
     * Get relative data dir
     * @return string
     */
    public function getRelativeDataDir()
    {
        return 'ash';
    }

    /**
     * Set report type
     * @param string $a_type
     * @return
     */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }
    
    /**
     * Get report type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * get certificate object
     * @return adnCertificate
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * Get wmo of event
     * @return
     */
    public function getWMO()
    {
        return $this->wmo;
    }
    
    /**
     * Set candidates
     *
     * @param array candidates
     * @return
     */
    public function setCandidates($a_cand)
    {
        $this->candidates = $a_cand;
    }

    /**
     * Get candidates
     * @return
     */
    public function getCandidates()
    {
        return (array) $this->candidates;
    }
    /**
     * Check if sheet id deprecated
     * @param int $cand
     * @param int $sheet_id
     */
    public function isSheetDeprecated($cand, $sheet_id)
    {
        include_once './Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php';
        $sheet_ass = new adnAnswerSheetAssignment(adnAnswerSheetAssignment::find($cand, $sheet_id));
        return $sheet_ass->isReportDeprecated();
    }

    /**
     * Create a one document for the selected candidates
     * Throws wrapped adnReportException for underlying rcp calls.
     * @param bool $check_deprecated
     * @return void
     */
    public function collectAllDocuments($check_deprecated = true)
    {
        include_once './Services/ADN/MD/classes/class.adnCountry.php';
        include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
        include_once './Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php';
        include_once './Services/ADN/EP/classes/class.adnAnswerSheet.php';
        include_once './Services/ADN/ED/classes/class.adnLicense.php';


        $this->initOutfile();

        $outfiles = array();
        $all_sheets = array();
        
        $cand_out_files = array();
        
        foreach ($this->getCandidates() as $cand) {
            $sheets = adnAnswerSheetAssignment::getAllSheets(
                $cand,
                $this->getEvent()->getId()
            );

            if (count((array) $sheets) < ($this->hasCasePart() ? 2 : 1)) {
                throw new adnReportException(
                    $GLOBALS['lng']->txt('adn_report_err_not_generated')
                );
            }

            // Lookup generated
            foreach ((array) $sheets as $sheet_data) {
                if (!self::lookupCandidateSheetGenerated($cand, $sheet_data['ep_answer_sheet_id'])) {
                    throw new adnReportException(
                        $GLOBALS['lng']->txt('adn_report_err_not_generated')
                    );
                }
            }

            foreach ($sheets as $sheet_data) {
                $sheet_id = $sheet_data['ep_answer_sheet_id'];
                $all_sheets[] = $sheet_id;

                if ($check_deprecated && $this->isSheetDeprecated($cand, $sheet_id)) {
                    throw new adnReportException(
                        $GLOBALS['lng']->txt('adn_report_err_sheet_deprecated')
                    );
                }
                
                $sheet = new adnAnswerSheet($sheet_id);
                if ($this->getEvent()->getType() != 'chem' and $this->getEvent()->getType() != 'gas') {
                    $cand_out_files[$cand][] =
                        $this->getDataDir() . '/' . $sheet_id . '_' . $cand . '_cover_base.pdf';
                } elseif ($sheet->getType() == adnAnswerSheet::TYPE_CASE) {
                    // Cover
                    $cand_out_files[$cand][] =
                        $this->getDataDir() . '/' . $sheet_id . '_' . $cand . '_cover_case.pdf';
                } else {
                    $cand_out_files[$cand][] =
                        $this->getDataDir() . '/' . $sheet_id . '_' . $cand . '_cover_mc.pdf';
                }

                // Add situation description
                if ($sheet->getType() == adnAnswerSheet::TYPE_CASE) {
                    foreach ($this->getSituation($sheet) as $file) {
                        $cand_out_files[$cand][] = $file;
                    }
                }

                // Add question sheet
                $cand_out_files[$cand][] =
                    $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $sheet_id . '_sheet.pdf';
                    
                // Add license
                if ($sheet->getType() == adnAnswerSheet::TYPE_CASE) {
                    foreach ($this->createLicense($sheet) as $lic) {
                        $cand_out_files[$cand][] = $lic;
                    }
                }
                // Add goods
                if ($sheet->getType() == adnAnswerSheet::TYPE_CASE) {
                    foreach ($this->createGoods($sheet) as $good) {
                        $cand_out_files[$cand][] = $good;
                    }
                }
            }
        }
        
        // Write on task (merge them in one PDF)
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');

        foreach ($cand_out_files as $candidate_id => $candoutfiles) {
            $writer->xmlStartTag(
                'action',
                array(
                        'method' => 'mergePdf'
                    )
            );
            $writer->addParameter('vector', (array) $candoutfiles);
            $this->initOutfile();
            $writer->addParameter('string', $this->getOutfile());
            $writer->xmlEndTag('action');
            
            // Number this pdf
            $writer->xmlStartTag(
                'action',
                array(
                        'method' => 'adnNumberPdf'
                    )
            );
            $writer->addParameter('string', $this->getOutfile());
            $this->initOutfile();
            $writer->addParameter('string', $this->getOutfile());
            $writer->xmlEndTag('action');
            
            $outfiles[] = $this->getOutfile();
        }


        // Add solution sheet
        foreach ($all_sheets as $sheet_id) {
            $outfiles[] =
                $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $sheet_id . '_solution.pdf';
        }

        
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'mergePdf'
            )
        );
        $writer->addParameter('vector', $outfiles);
        $this->initOutfile();
        $writer->addParameter('string', $this->getOutfile());
        $writer->xmlEndTag('action');
        
        
        $writer->xmlEndTag('tasks');
        
        //$GLOBALS['ilLog']->write('XML '.$writer->xmlDumpMem(true));
        
        try {
            include_once './Services/ADN/Base/classes/class.adnRpcAdapter.php';
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler(
                $writer->xmlDumpMem()
            );
        } catch (adnReportException $e) {
            throw $e;
        }
    }


    /**
     * Create license
     * @param adnAnswerSheet $sheet
     * @return array
     *
     */
    protected function createLicense($sheet)
    {
        static $licenses = null;
        
        // Gas
        if ($this->getEvent()->getType() == 'gas') {
            if (isset($licenses['gas'])) {
                return (array) $licenses['gas'];
            }
        }
        // Chem
        if (isset($licenses[(int) $sheet->getLicense()])) {
            return (array) $licenses[(int) $sheet->getLicense()];
        }

        include_once './Services/ADN/ED/classes/class.adnLicense.php';

        // Type chem
        if ($sheet->getLicense()) {
            $lic = new adnLicense($sheet->getLicense());

            if (@file_exists(
                $lic->getFilePath() . $lic->getId()
            )) {
                @copy(
                    $lic->getFilePath() . $lic->getId(),
                    $new_path = $this->getDataDir() . '/' . $sheet->getLicense() . '_license.pdf'
                );
                $licenses[$sheet->getLicense()][] = $new_path;
                return $licenses[$sheet->getLicense()];
            }
            return array();
        } else {
            // Type gas
            $lics = adnLicense::getLicensesSelect(adnLicense::TYPE_GAS);
            foreach ((array) $lics as $lic_id => $name) {
                $lic = new adnLicense($lic_id);

                if (@file_exists($lic->getFilePath() . $lic->getId())) {
                    @copy(
                        $lic->getFilePath() . $lic->getId(),
                        $new_path = $this->getDataDir() . '/' . $lic->getId() . '_license.pdf'
                    );
                    $licenses['gas'][] = $new_path;
                } else {
                    $GLOBALS['ilLog']->write("Not found: " . $lic->getFilePath() . $lic->getId());
                }
            }
            return (array) $licenses['gas'];
        }
    }



    /**
     * Create good attachments
     * @param adnAnswerSheet $sheet
     */
    protected function createGoods($sheet)
    {
        static $goods = null;

        include_once './Services/ADN/ED/classes/class.adnGoodInTransit.php';
        $all_materials = array();

        if (isset($goods[$sheet->getPreviousGood()]) and $sheet->getPreviousGood()) {
            $all_materials[] = $goods[$sheet->getPreviousGood()];
        } elseif ($sheet->getPreviousGood()) {
            $good = new adnGoodInTransit($sheet->getPreviousGood());

            if ($good->getFileName()) {
                copy(
                    $good->getFilePath() . $sheet->getPreviousGood(),
                    $new_path = $this->getDataDir() . '/' . $sheet->getPreviousGood() . '_good.pdf'
                );
                $all_materials[] = $new_path;
            }
        }
        if (isset($goods[$sheet->getNewGood()]) and $sheet->getNewGood()) {
            $all_materials[] = $goods[$sheet->getNewGood()];
        } elseif ($sheet->getNewGood()) {
            $good = new adnGoodInTransit($sheet->getNewGood());

            if ($good->getFileName()) {
                copy(
                    $good->getFilePath() . $sheet->getNewGood(),
                    $new_path = $this->getDataDir() . '/' . $sheet->getNewGood() . '_good.pdf'
                );
                $all_materials[] = $new_path;
            }
        }
        return $all_materials ? $all_materials : array();
    }

    /**
     * Get situation description
     * @param adnAnswerSheet $sheet
     */
    protected function getSituation($sheet)
    {
        if ($sheet->getType() != adnAnswerSheet::TYPE_CASE) {
            return false;
        }

        include_once './Services/ADN/ED/classes/class.adnCase.php';
        include_once './Services/ADN/ED/classes/class.adnSubjectArea.php';
        $case = new adnCase(
            adnCase::getIdByArea(
                    $this->getEvent()->getType(),
                    $sheet->getButan()
                )
        );

        if (@file_exists(
            $this->getDataDir() . '/' . $case->getId() . '_situation.pdf'
        )) {
            return array($this->getDataDir() . '/' . $case->getId() . '_situation.pdf');
        }

        return array();
    }

    /**
     * Create pdf for situation
     * @param adnTaskScheduleWriter $writer
     * @param adnAnswerSheet $sheet
     */
    protected function createSituation($writer, $sheet)
    {
        static $st_case = null;

        if ($sheet->getType() != adnAnswerSheet::TYPE_CASE) {
            return false;
        }

        include_once './Services/ADN/ED/classes/class.adnCase.php';
        include_once './Services/ADN/ED/classes/class.adnSubjectArea.php';
        $case = new adnCase(
            adnCase::getIdByArea(
                    $this->getEvent()->getType(),
                    $sheet->getButan()
                )
        );

        if (isset($st_case[$case->getId()])) {
            return true;
        }

        $st_case[$case->getId()] = true;

        $txt = $case->getTranslatedText($sheet, $this->getEvent());

        include_once './Services/Xml/classes/class.ilXmlWriter.php';
        $xml = new ilXmlWriter();
        $xml->xmlStartTag('page');
        $xml->xmlElement('paragraph', array('type' => 'header'), $GLOBALS['lng']->txt('adn_case'));
        $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
        $this->parseFormatting($xml, $txt);
        $xml->xmlEndTag('paragraph');
        $xml->xmlEndTag('page');

        $xml->xmlDumpFile(
            $this->getDataDir() . '/' . $case->getId() . '_situation.xml',
            false
        );

        // Write task for creating Pdf from XML
        $writer->xmlStartTag(
            'action',
            array(
                    'method' => 'adnPdfFromXml'
                )
        );
        $writer->addParameter('string', $this->getDataDir() . '/' . $case->getId() . '_situation.xml');
        $writer->addParameter('string', $this->getDataDir() . '/' . $case->getId() . '_situation.pdf');
        $writer->xmlEndTag('action');
    }

    /**
     * Create pdfs for each assignment
     * @param adnTaskScheduleWriter $writer
     * @return
     */
    protected function createAssignments($writer)
    {
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
        include_once './Services/ADN/MD/classes/class.adnCountry.php';
        include_once "Services/ADN/ES/classes/class.adnCertifiedProfessional.php";
        include_once './Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php';
        include_once './Services/ADN/EP/classes/class.adnAnswerSheet.php';
        
        // Create answer sheets
        $all_sheets = adnAnswerSheetAssignment::getEventSheets($this->getEvent()->getId());
        foreach ($all_sheets as $sheet_id) {
            if (!adnAnswerSheetAssignment::isAssigned($sheet_id)) {
                continue;
            }

            $sheet = new adnAnswerSheet($sheet_id);
            if ($sheet->validate()) {
                $this->createAnswerSheet($writer, $sheet);
            } else {
                throw new adnReportException("Ungültiger Prüfungsbogen: " . $sheet->getNumber());
            }
        }

        // All assignments
        include_once "Services/ADN/EP/classes/class.adnAssignment.php";
        $assignments = adnAssignment::getAllAssignments(
            array(
                "event_id" => $this->getEvent()->getId()
            )
        );
        foreach ($assignments as $assignment) {
            // @todo: sort by MC, CASE
            $sheets = adnAnswerSheetAssignment::getAllSheets(
                $assignment['cp_professional_id'],
                $this->getEvent()->getId()
            );

            $cand = new adnCertifiedProfessional($assignment["cp_professional_id"]);
            foreach ($sheets as $sheet_data) {
                $sheet_id = $sheet_data['ep_answer_sheet_id'];
                
                $sheet = new adnAnswerSheet($sheet_id);
                $this->createSheetCover($writer, $sheet, $cand);
                $this->createSituation($writer, $sheet);

                // Update generated on time
                $sheet_assignment = new adnAnswerSheetAssignment(
                    adnAnswerSheetAssignment::find(
                            $assignment['cp_professional_id'],
                            $sheet_id
                        )
                );
                $sheet_assignment->setGeneratedOn(new ilDateTime(time(), IL_CAL_UNIX));
                $sheet_assignment->update();
            }
        }
    }
    
    /**
     * Create sheet
     * @param ilXmlWriter $writer
     * @param object $sheet
     * @param object $candidate
     * @return
     */
    protected function createSheetCover($writer, $sheet, $candidate)
    {
        global $lng;
        
        // Create Cover
        switch ($sheet->getType()) {
            case adnAnswerSheet::TYPE_CASE:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_COVER_SHEET_CASE
                );
                $outfile =
                    $this->getDataDir() . '/' . $sheet->getId() . '_' . $candidate->getId() . '_cover_case.pdf';
                break;
                
            case adnAnswerSheet::TYPE_MC:
                $form = adnReportFileUtils::getTemplatePathByType(
                    adnReportFileUtils::TPL_REPORT_COVER_SHEET_MC
                );
                $outfile =
                    $this->getDataDir() . '/' . $sheet->getId() . '_' . $candidate->getId() . '_cover_mc.pdf';
                break;
        }
        if ($this->getEvent()->getType() != 'chem' and $this->getEvent()->getType() != 'gas') {
            $form = adnReportFileUtils::getTemplatePathByType(
                adnReportFileUtils::TPL_REPORT_COVER_SHEET_BASE
            );
            $outfile =
                $this->getDataDir() . '/' . $sheet->getId() . '_' . $candidate->getId() . '_cover_base.pdf';
        }

        $map = array();
        $map['wsd_name'] = $this->getWMO()->getName();
        $map['sheet_number'] = $lng->txt('adn_report_exam_sheet') . ': ' . $sheet->getNumber();
        $map['sheet_type'] = $lng->txt('adn_report_exam') . ': ' .
            $lng->txt(
                'adn_subject_area_' . $this->getEvent()->getType()
            );
        $map['c_name'] = $candidate->getLastName();
        $map['c_firstname'] = $candidate->getFirstName();
        $map['c_born'] = $candidate->getBirthdate()->get(IL_CAL_FKT_DATE, 'd.m.Y');
        
        $country = new adnCountry($candidate->getPostalCountry());
        $map['c_country'] = $country->getCode();
        $map['exam_date'] = $this->getEvent()->getDateFrom()->get(IL_CAL_FKT_DATE, 'd.m.Y');

        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'fillPdfTemplate'
            )
        );
        $writer->addParameter('string', $form);
        $writer->addParameter('string', $outfile);
        $writer->addParameter('map', $map);
        $writer->xmlEndTag('action');
    }
    
    /**
     * Create answer sheet
     * Throws wrapped adnREportException in case of underlying rpc faults
     * @return void
     * @throws adnReportException
     */
    protected function createAnswerSheet($writer, $sheet)
    {
        global $lng;
        
        include_once './Services/ADN/ED/classes/class.adnCaseQuestion.php';
        include_once './Services/ADN/ED/classes/class.adnMCQuestion.php';
        include_once 'Services/ADN/ED/classes/class.adnGoodRelatedAnswer.php';
        
        include_once './Services/Xml/classes/class.ilXmlWriter.php';
        $xml = new ilXmlWriter();
        $xml->xmlHeader();
        $xml->xmlStartTag('answerSheet');
        $xml->xmlElement('wsd', array(), $this->getWMO()->getName());
        
        $xml->xmlElement(
            'solutionHeaderA',
            array(),
            $lng->txt('adn_report_solution_sheet') . ' ' . $sheet->getNumber()
        );
        $xml->xmlElement(
            'solutionHeaderB',
            array(),
            $lng->txt('adn_report_exam') . ' ' .
            $lng->txt('adn_train_type_' . $this->getEvent()->getType())
        );
        
        $xml->xmlStartTag(
            'questions',
            array(
                'type' => $sheet->getType()
            )
        );

        if ($sheet->getType() == adnAnswerSheet::TYPE_MC) {
            foreach ($sheet->getQuestionsInObjectiveOrder(true) as $qst_id) {
                $qst = new adnMCQuestion($qst_id);
                $path = $qst->getFilePath();
                
                $xml->xmlStartTag('question', array('filePath' => $path . '' . $qst_id . '_1'));
                $xml->xmlStartTag('title');
                $this->parseFormatting($xml, $qst->getQuestion());
                $xml->xmlEndTag('title');
                $counter = 1;
                foreach (array('A','B','C','D') as $char) {
                    $file = $qst->getFilePath() . $qst_id . "_" . ++$counter;
                    $file_path = '';
                    if (@file_exists($file)) {
                        $file_path = $file;
                    }
                    $method = 'getAnswer' . $char;
                    
                    $answer = $qst->$method($char);
                    $xml->xmlStartTag('answer', array('file' => $answer['file'],'filePath' => $file_path));
                    $this->parseFormatting($xml, $answer['text']);
                    $xml->xmlEndTag('answer');
                }
                // Solution
                $xml->xmlElement('solution', array(), $qst->getCorrectAnswer());
                $xml->xmlElement('fullNumber', array(), $qst->buildADNNumber());
                $xml->xmlEndTag('question');
            }
        } else {
            $all = $sheet->getQuestionsInObjectiveOrder(true);
            foreach ((array) $all as $qid) {
                $qst = new adnCaseQuestion($qid);
                $correct_answer = adnGoodRelatedAnswer::getAnswerForSheet(
                    $sheet,
                    $qst,
                    $this->event
                );
                
                $xml->xmlStartTag('question');

                $xml->xmlStartTag('title');
                $this->parseFormatting($xml, $qst->getTranslatedQuestion($sheet));
                $xml->xmlEndTag('title');
                $xml->xmlStartTag('solution');
                $this->parseFormatting($xml, $correct_answer);
                $xml->xmlEndTag('solution');
                $xml->xmlElement('fullNumber', array(), '(' . $qst->buildADNNumber() . ')');
                $xml->xmlEndTag('question');
            }
        }

        $xml->xmlEndTag('questions');
        $xml->xmlEndTag('answerSheet');
        
        $GLOBALS['ilLog']->write("Dumping XML " . $xml->xmlDumpMem(true));
        
        // Dump to file
        $xml->xmlDumpFile(
            $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $sheet->getId() . '_sheet.xml',
            false
        );
        $GLOBALS['ilLog']->write('Wrote new sheet: ' . $this->getDataDir() . '/' .
        $this->getEvent()->getId() . '_' . $sheet->getId() . '_sheet.xml');
        //
        // Add "writeQuestionSheet" to scheduler
        //
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'writeQuestionSheet'
            )
        );
        // Infile
        $writer->addParameter(
            'string',
            $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $sheet->getId() . '_sheet.xml'
        );
        // Outfile
        $writer->addParameter(
            'string',
            $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $sheet->getId() . '_sheet.pdf'
        );
        $writer->xmlEndTag('action');
        
        //
        // Add new task "writeSolutionSheet" to scheduler
        //
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'writeSolutionSheet'
            )
        );
        // Infile
        $writer->addParameter(
            'string',
            $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $sheet->getId() . '_sheet.xml'
        );
        // Outfile
        $writer->addParameter(
            'string',
            $this->getDataDir() . '/' . $this->getEvent()->getId() . '_' . $sheet->getId() . '_solution.pdf'
        );
        $writer->xmlEndTag('action');
    }
    

    /**
     * Create report
     * Throws wrapped adnREportException in case of underlying rpc faults
     *
     * @return void
     *
     * @throws adnReportException
     */
    public function create()
    {
        global $ilUser,$lng;
        

        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');

        $this->createAssignments($writer);
        
        $writer->xmlEndTag('tasks');
        #$GLOBALS['ilLog']->write($writer->xmlDumpMem(true));
        
        try {
            include_once './Services/ADN/Base/classes/class.adnRpcAdapter.php';
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler(
                $writer->xmlDumpMem()
            );
        } catch (adnReportException $e) {
            throw $e;
        }
    }
}
