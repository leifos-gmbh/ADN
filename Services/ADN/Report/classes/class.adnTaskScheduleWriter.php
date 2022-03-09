<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once './Services/Xml/classes/class.ilXmlWriter.php';

/**
 * Task schedule writer for writing a xml collection of tasks send to the java server.
 * Using this scheduler increases the performance, since multiple RPC calls are avoided.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.adnTaskScheduleWriter.php 27907 2011-03-01 16:34:18Z smeyer $
 *
 * @ingroup ServicesADN
 */
class adnTaskScheduleWriter extends ilXmlWriter
{
    /**
     * Constructor
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    /**
     * Add parameter type and value
     * @param string $a_type
     * @param string $a_value
     * @return bool
     */
    public function addParameter($a_type, $a_value)
    {
        switch ($a_type) {
            case 'string':
                $this->xmlStartTag('param', array('type' => 'string'));
                $this->xmlElement('string', array(), $a_value);
                $this->xmlEndTag('param');
                break;
                
            case 'map':
                $this->xmlStartTag('param', array('type' => 'map'));
                foreach ($a_value as $key => $val) {
                    $this->xmlElement('map', array('name' => $key,'value' => $val));
                }
                $this->xmlEndTag('param');
                break;
            
            case 'vector':
                $this->xmlStartTag('param', array('type' => 'vector'));
                foreach ($a_value as $val) {
                    $this->xmlElement('vector', array(), $val);
                }
                $this->xmlEndTag('param');
                break;
                                
        }
        return true;
    }
}
