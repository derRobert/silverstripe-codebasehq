<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 07.08.2016
 * Time: 18:21
 */

class ViewableTicket extends ViewableData {

    public function __construct($array)
    {
        parent::__construct();
        foreach( $array as $k=>$v ) {
            if( !is_array($v) ) {
                $prop = str_replace('-', '_', $k);
                $this->$prop=$v;
            }
        }
        $this->TypeName = isset($array['type']['name'])?$array['type']['name']:false;
        $this->StatusName = isset($array['status']['name'])?$array['status']['name']:false;
        $this->StatusColor = isset($array['status']['colour'])?$array['status']['colour']:false;
        $this->PriorityName = isset($array['priority']['name'])?$array['priority']['name']:false;
    }

    public function Link() {
        return implode("/", array(
            Config::inst()->get('SupportAPI', 'web_endpoint'),
            "tickets",
            $this->{"ticket_id"}
        ));
    }

}