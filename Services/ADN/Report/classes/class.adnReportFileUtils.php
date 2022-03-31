<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Factory for files access.
 * Stores the mapping of report types to filessystem filenames of PDF templates
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnReportFileUtils.php 31050 2011-10-09 10:38:06Z smeyer $
 */
class adnReportFileUtils
{
    const PDF_PATH = '/Services/ADN/Report/templates/pdf';
    const TPL_REPORT_CERTIFICATE = 'adn_report_certificate.pdf';
    // CR008: no text "Ersatzausfertigung" so REPORT and REPORT_CERTIFICATE are identic.
    const TPL_REPORT_CERTIFICATE_DUPLICATE = 'adn_report_certificate.pdf';
    
    const TPL_REPORT_INVITATION_GAS = 'adn_report_invitation_gas.pdf';
    const TPL_REPORT_INVITATION_CHEM = 'adn_report_invitation_chem.pdf';
    const TPL_REPORT_INVITATION_DRY = 'adn_report_invitation_dry.pdf';
    const TPL_REPORT_INVITATION_TANK = 'adn_report_invitation_tank.pdf';
    const TPL_REPORT_INVITATION_COMB = 'adn_report_invitation_comb.pdf';
    
    const TPL_REPORT_COVER_SHEET_MC = 'adn_report_answer_cover_mc.pdf';
    const TPL_REPORT_COVER_SHEET_CASE = 'adn_report_answer_cover_case.pdf';
    const TPL_REPORT_COVER_SHEET_BASE = 'adn_report_answer_cover_base.pdf';
    
    const TPL_REPORT_ATTENDANCE_LIST = 'adn_report_attendance_list.pdf';
    const TPL_REPORT_ATTENDANCE_LIST_G = 'adn_report_attendance_list_g.pdf';
    const TPL_REPORT_ATTENDANCE_LIST_C = 'adn_report_attendance_list_c.pdf';
    const TPL_REPORT_ATTENDANCE_LIST_ONE_CHAIR = 'adn_report_attendance_list_one_chair.pdf';
    const TPL_REPORT_ATTENDANCE_LIST_G_ONE_CHAIR = 'adn_report_attendance_list_g_one_chair.pdf';
    const TPL_REPORT_ATTENDANCE_LIST_C_ONE_CHAIR = 'adn_report_attendance_list_c_one_chair.pdf';

    const TPL_REPORT_SCORE_NOTIFICATION_SUCCESS_BASE = 'adn_report_score_base.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_SUCCESS_GAS = 'adn_report_score_gas.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_SUCCESS_CHEM = 'adn_report_score_chem.pdf';
    
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_BOTH = 'adn_report_failed_gas_both.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_MC = 'adn_report_failed_gas_mc.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_CASE = 'adn_report_failed_gas_case.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_GAS_LIMIT = 'adn_report_failed_gas_limit.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_BOTH = 'adn_report_failed_chem_both.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_MC = 'adn_report_failed_chem_mc.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_CASE = 'adn_report_failed_chem_case.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_CHEM_LIMIT = 'adn_report_failed_chem_limit.pdf';
    
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_DM = 'adn_report_failed_dm.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_TANK = 'adn_report_failed_tank.pdf';
    const TPL_REPORT_SCORE_NOTIFICATION_FAILED_COMB = 'adn_report_failed_comb.pdf';

    const TPL_REPORT_INVOICE = 'adn_report_invoice.pdf';
    
    const TPL_REPORT_STAT_EXAM = 'adn_report_stat_exam.pdf';
    const TPL_REPORT_STAT_EXTENSION_EXP = 'adn_report_stat_extension_exp.pdf';
    const TPL_REPORT_STAT_EXTENSION_REF = 'adn_report_stat_extension_ref.pdf';
    const TPL_REPORT_STAT_CERTIFICATES_OTHER = 'adn_report_stat_certificates_other.pdf';
    const TPL_REPORT_STAT_CERTIFICATES_GC = 'adn_report_stat_certificates_gc.pdf';
    const TPL_REPORT_STAT_CERTIFICATES_SUM = 'adn_report_stat_certificates_sum.pdf';
    
    const TPL_REPORT_ONLINE_EXAM_LIST = 'adn_report_online_exam_list.pdf';
    
    const TPL_REPORT_DIRECTORY = 'adn_report_directory.pdf';
    
    
    
    /**
     * Get path to template
     * @param string $a_type
     * @return string
     */
    public static function getTemplatePathByType($a_type)
    {
        return ILIAS_ABSOLUTE_PATH . self::PDF_PATH . '/' . $a_type;
    }
}
