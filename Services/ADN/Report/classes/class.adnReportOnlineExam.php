<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/ADN/Report/classes/class.adnReport.php';

/**
 * Generation of online exam reports.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportOnlineExam.php 35419 2012-07-06 12:36:06Z jluetzen $
 *
 * @ingroup ServicesADN
 */

class adnReportOnlineExam extends adnReport
{
    private $event_id = 0;
    private $event = null;

    private ilLanguage $lng;

    /**
     * Contructor
     * @return
     */
    public function __construct($event_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct();
        $this->event_id = $event_id;
    }
    
    /**
     * Get relative data dir
     * @return
     */
    public function getRelativeDataDir()
    {
        return 'oex';
    }

    /**
     * Create report
     * @return
     *
     * @throws adnReportException
     */
    public function create()
    {
        global $ilUser,$lng;
        
        include_once './Services/ADN/Report/classes/class.adnReportFileUtils.php';
        $form = adnReportFileUtils::getTemplatePathByType(
            adnReportFileUtils::TPL_REPORT_ONLINE_EXAM_LIST
        );
        
        // Write on task (fillPdfTemplate for every candidate) and finally merge them in one PDF.
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
        
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignments = adnAssignment::getAllAssignments(
            array(
                "event_id" => $this->event_id),
            array(
                "first_name",
                "last_name",
                "birthdate",
                "blocked_until",
                "ilias_user_id"
            )
        );

        // Collection of all outfile
        $all_outfiles = array();
        foreach ($assignments as $idx => $item) {
            $writer->xmlStartTag(
                'action',
                array(
                    'method' => 'fillPdfTemplate'
                )
            );
            
            $outfile = $this->initOutfile();
            $all_outfiles[] = $outfile;
            
            $writer->addParameter('string', $form);
            $writer->addParameter('string', $outfile);
                
            $map = array();
            $map['name'] = $item['last_name'];
            $map['first'] = $item['first_name'];
            $date = new ilDate($item['birthdate'], IL_CAL_DATE);
            $map['born'] = $date->get(IL_CAL_FKT_DATE, 'd.m.Y');
            $map['login'] = ilObjUser::_lookupLogin($item['ilias_user_id']);
            $map['pass'] = $item['access_code'];

            $writer->addParameter('map', $map);
            $writer->xmlEndTag('action');
        }
        
        // Merge all single pdf's to one pdf
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'mergePdf'
            )
        );
        $writer->addParameter('vector', $all_outfiles);
        $writer->addParameter('string', $this->initOutfile());
        $writer->xmlEndTag('action');
        $writer->xmlEndTag('tasks');
        
        
        try {
            // Start tranformation
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
     * Create answer sheets
     * @return void
     * @throws adnReportException
     */
    public function createSheets()
    {
        global $lng;

        $lng->loadLanguageModule('assessment');
        $lng->loadLanguageModule('crs');
        
        $this->initEvent();

        // Write on task (merge them in one PDF)
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');


        // Read questions
        include_once './Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php';
        $sheets = adnAnswerSheetAssignment::getEventSheets($this->getEvent()->getId());
        $questions = array();
        foreach ((array) $sheets as $sheet) {
            include_once './Services/ADN/EP/classes/class.adnAnswerSheet.php';
            $sheet = new adnAnswerSheet($sheet);

            if ($sheet->getType() == adnAnswerSheet::TYPE_MC) {
                foreach ($sheet->getQuestions() as $qst_id) {
                    include_once './Services/ADN/ED/classes/class.adnMCQuestion.php';
                    $qst = new adnMCQuestion($qst_id);

                    $questions[$sheet->getId()][] = array(
                        'id' => $qst->getId(),
                        'title' => $qst->getName(),
                        'number' => $qst->buildADNNumber(),
                        'question' => $qst->getTranslatedQuestion($sheet),
                        'answer_1' => $qst->getAnswerA(),
                        'answer_2' => $qst->getAnswerB(),
                        'answer_3' => $qst->getAnswerC(),
                        'answer_4' => $qst->getAnswerD(),
                        'solution' => $qst->getCorrectAnswer()
                    );
                }
            }
        }
        if (!$sheet instanceof adnAnswerSheet) {
            throw new adnReportException(
                "Dieser Prüfung sind keine Antwortbögen zugeordnet."
            );
        }

        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignments = adnAssignment::getAllAssignments(
            array(
                "event_id" => $this->event_id),
            array()
        );
        if (!count($assignments)) {
            throw new adnReportException(
                "Dieser Prüfung sind keine Kandidaten zugeordnet."
            );
        }

        $all_outfiles = array();
        foreach ($assignments as $ass) {
            $counter = 0;

            include_once './Services/ADN/ES/classes/class.adnCertifiedProfessional.php';
            $pro = new adnCertifiedProfessional($ass['cp_professional_id']);

            include_once './Services/ADN/EC/classes/class.adnTest.php';
            $res = adnTest::lookupResults($this->getEvent()->getId(), $pro->getId());

            include_once './Services/Xml/classes/class.ilXmlWriter.php';
            $xml = new ilXmlWriter();
            $xml->xmlStartTag('page');

            $header = $pro->getFirstName() . ' ' . $pro->getLastName() . ', ';
            $header .= $pro->getPostalStreet() . ' ' . $pro->getPostalStreetNumber() . ', ';
            $header .= $pro->getPostalCode() . ' ' . $pro->getPostalCity();
            $xml->xmlElement('pageHeader', array(), $header);

            $this->addExamInfoToAssignment($xml, $pro, $ass);
            
            // get sheet(s) for current candidate
            $cand_sheets = adnAnswerSheetAssignment::getAllSheets(
                $pro->getId(),
                $this->event_id
            );
            if (!count($cand_sheets)) {
                throw new adnReportException(
                    "Einem Kandidaten (" . $header . ") sind keine Prüfungsbögen zugeordnet."
                );
            }
            
            foreach ($cand_sheets as $cand_sheet) {
                if (
                    !isset($questions[$cand_sheet['ep_answer_sheet_id']]) ||
                    !is_array($questions[$cand_sheet['ep_answer_sheet_id']])
                ) {
                    throw new adnReportException(
                        "Es fehlen Fragen zu einen Prüfungsbogen."
                    );
                }
                // add questions for current sheet
                foreach ($questions[$cand_sheet['ep_answer_sheet_id']] as $qst) {
                    $qstid = $qst['id'];
                    
                    $xml->xmlElement(
                        'paragraph',
                        array('type' => 'bold'),
                        $lng->txt('adn_question') . ' ' . ++$counter . ': ' . $qst['number']
                    );

                    $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
                    $this->parseFormatting($xml, $qst['question']);
                    $xml->xmlEndTag('paragraph');

                    $prefixes = [
                        '1' => 'A. ',
                        '2' => 'B. ',
                        '3' => 'C. ',
                        '4' => 'D. '
                    ];
                    foreach (['1','2','3','4'] as $answer) {
                        $xml->xmlStartTag('paragraph', ['type' => 'phrase']);
                        $this->parseFormatting($xml, $prefixes[$answer] . $qst['answer_' . $answer]['text']);
                        $xml->xmlEndTag('paragraph');
                    }

                    $xml->xmlStartTag('paragraph', ['type' => 'phrase']);
                    $this->parseFormatting($xml, 'Richtige Antwort: ' . strtoupper($qst['solution']));
                    $xml->xmlEndTag('paragraph');

                    if (isset($res[$qstid]) and isset($qst['answer_' . $res[$qstid]])) {
                        $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
                        $this->parseFormatting($xml, 'Gegebene Antwort: ' . $prefixes[$res[$qstid]]);
                        $xml->xmlEndTag('paragraph');
                    } else {
                        $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
                        $this->parseFormatting($xml, 'Gegebene Antwort: Nicht beantwortet');
                        $xml->xmlEndTag('paragraph');
                    }
                    $xml->xmlElement(
                        'paragraph',
                        array('type' => 'header'),
                        ' '
                    );
                }
            }

            // Signature
            $xml->xmlElement(
                'paragraph',
                array('type' => 'bold'),
                ilDatePresentation::formatDate(new ilDate(time(), IL_CAL_UNIX))
            );
            $xml->xmlElement(
                'paragraph',
                array('type' => 'header'),
                'Unterschrift _____________________________________________'
            );
            $xml->xmlEndTag('page');

            $outfile_xml = $this->getDataDir() . '/' . $pro->getId() . '_' . $sheet->getId() . '_sheet.xml';
            $outfile_pdf = $this->getDataDir() . '/' . $pro->getId() . '_' . $sheet->getId() . '_sheet.pdf';

            $xml->xmlDumpFile($outfile_xml, false);

            // Task definition
            $writer->xmlStartTag(
                'action',
                array(
                        'method' => 'adnPdfFromXml'
                    )
            );
            $writer->addParameter('string', $outfile_xml);
            $writer->addParameter('string', $outfile_pdf);
            $writer->xmlEndTag('action');

            $all_outfiles[] = $outfile_pdf;
        }
        // Merge pdfs only if there is more than one
        if (count($all_outfiles) > 1) {
            $this->initOutfile();
            $writer->xmlStartTag(
                'action',
                array(
                        'method' => 'mergePdf'
                    )
            );
            $writer->addParameter('vector', (array) $all_outfiles);
            $writer->addParameter('string', $this->getOutfile());
            $writer->xmlEndTag('action');
        } else {
            $all_outfiles = (array) $all_outfiles;
            $this->setOutfile(end($all_outfiles));
        }

        $writer->xmlEndTag('tasks');

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

    protected function addExamInfoToAssignment(ilXmlWriter $writer, adnCertifiedProfessional $pro, array $assignment) : void
    {
        $writer->xmlStartTag('pageInfoTable', ['columns' => 2]);
        $writer->xmlElement('column', ['type' => 'bold'], $this->lng->txt('adn_oex_examination_info'));
        $writer->xmlElement(
            'column',
            ['type' => 'phrase'],
            ilDatePresentation::formatPeriod(
                $this->getEvent()->getDateFrom(),
                $this->getEvent()->getDateTo()
            ) . ', ' . $this->lng->txt('adn_train_type_' . $this->getEvent()->getType())
        );
        $writer->xmlElement('column', ['type' => 'bold'], $this->lng->txt('adn_oex_professional_info'));

        $professional_info =
            ilObjUser::_lookupLogin($pro->getIliasUserId()) . ', ' .
            $assignment['access_code'];
        $writer->xmlElement('column', ['type' => 'phrase'], $professional_info);
        $writer->xmlEndTag('pageInfoTable');

    }

    /**
     * Create elearning answer sheet.
     * @param array $a_qsts
     * @param array $a_answers
     */
    public function createELearningSheet($a_qsts, $a_answers)
    {
        global $lng;

        $map = array(1 => "A", 2 => "B", 3 => "C", 4 => "D");

        $this->initOutfile();
        $lng->loadLanguageModule('assessment');

        include_once("./Services/ADN/ED/classes/class.adnMCQuestion.php");
        include_once './Services/Xml/classes/class.ilXmlWriter.php';
        $xml = new ilXmlWriter();
        $xml->xmlStartTag('page');

        $counter = 0;
        foreach ((array) $a_qsts as $qst_id) {
            ++$counter;
            $qst = new adnMCQuestion($qst_id);

            // Question
            #$xml->xmlElement('paragraph',array('type' => 'bold'),$counter.'.');

            $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
            $this->parseFormatting($xml, $counter . '. ' . $lng->txt('ass_question') . ': ' . $qst->getQuestion());
            $xml->xmlEndTag('paragraph');

            // "Your  answer"
            if (isset($a_answers[$qst_id]) and $a_answers[$qst_id]) {
                $method = 'getAnswer' . $map[$a_answers[$qst_id]];
                $answer = $qst->$method();

                $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
                $this->parseFormatting($xml, $lng->txt('adn_your_answer') . ': ' . $answer['text']);
                $xml->xmlEndTag('paragraph');
            } else {
                $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
                $this->parseFormatting($xml, $lng->txt('adn_your_answer') . ': Nicht beantwortet');
                $xml->xmlEndTag('paragraph');
            }

            // Correct answer
            
            $method = 'getAnswer' . strtoupper($qst->getCorrectAnswer());
            $answer = $qst->$method();

            $xml->xmlStartTag('paragraph', array('type' => 'phrase'));
            $this->parseFormatting(
                $xml,
                $lng->txt('adn_correct_answer') . ': ' . $answer['text']
            );
            $xml->xmlEndTag('paragraph');

            $xml->xmlElement(
                'paragraph',
                array('type' => 'header'),
                ' '
            );
        }

        $xml->xmlEndTag('page');

        $outfile_xml = $this->getDataDir() . '/elearning.xml';
        $xml->xmlDumpFile($outfile_xml, false);

        // Write on task (merge them in one PDF)
        include_once './Services/ADN/Report/classes/class.adnTaskScheduleWriter.php';
        $writer = new adnTaskScheduleWriter();
        $writer->xmlStartTag('tasks');
        $writer->xmlStartTag(
            'action',
            array(
                'method' => 'adnPdfFromXml'
            )
        );
        $writer->addParameter('string', $outfile_xml);
        $writer->addParameter('string', $this->getOutfile());
        $writer->xmlEndTag('action');
        $writer->xmlEndTag('tasks');

        try {
            include_once './Services/ADN/Base/classes/class.adnRpcAdapter.php';
            $adapter = new adnRpcAdapter();
            $adapter->transformationTaskScheduler($writer->xmlDumpMem());
        } catch (adnReportException $e) {
            throw $e;
        }
        return;
    }
    
    /**
     * Write
     * @return
     */
    protected function writeSheet(adnAnswerSheet $sheet)
    {
        global $lng;
        
        include_once './Services/Xml/classes/class.ilXmlWriter.php';
        $xml = new ilXmlWriter();
        $xml->xmlStartTag('questions');
        
        $lng->loadLanguageModule('assessment');

        foreach ($sheet->getQuestions() as $qst_id) {
            include_once './Services/ADN/ED/classes/class.adnMCQuestion.php';
            $qst = new adnMCQuestion($qst_id);
            $xml->xmlStartTag(
                'question',
                array(
                    'name' => $lng->txt('ass_question') . ': '
                )
            );
            $this->parseFormatting($xml, $qst->getTranslatedQuestion($sheet));
            $xml->xmlEndTag('question');
        }
        $xml->xmlEndTag('questions');
        
        $GLOBALS['ilLog']->write(__METHOD__ . ': write to ' . $this->getDataDir() . '/' .
            $sheet->getId() . '_sheet.xml');
        
        $xml->xmlDumpFile(
            $this->getDataDir() . '/' . $sheet->getId() . '_sheet.xml'
        );
    }
    
    /**
     * Init event object
     * @return
     */
    protected function initEvent()
    {
        include_once "Services/ADN/EP/classes/class.adnExaminationEvent.php";
        $this->event = new adnExaminationEvent($this->event_id);
    }
    
    /**
     * Get event
     * @return adnExaminationEvent
     */
    public function getEvent()
    {
        return $this->event;
    }
}
