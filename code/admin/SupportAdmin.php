<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 07.08.2016
 * Time: 17:27
 */
class SupportAdmin extends LeftAndMain {

    private static $menu_title = "Support";



    private static $url_segment = "support";



//    private static $menu_icon = "dashboard/images/dashboard.png";
//
//
//
//    private static $tree_class = 'DashboardPanel';
//
//
//
//    private static $url_handlers = array (
//
//        'panel/$ID' => 'handlePanel',
//        '$Action!' => '$Action',
//        '' => 'index'
//    );

    protected $query = null;


    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $r = $this->getRequest();
        if( $filter = $r->requestVar('query') ) {
            if( $filter == "ALL" ) {
                $this->query = null;
            } else {
                $this->query = Convert::raw2sql($r->requestVar('query'));
            }

        } else {
            $this->query = 'status:open';
        }
        Requirements::css('codebasehq/css/SupportAdmin.css');
    }

    public function getEditForm($id = null, $fields = null) {
        // List all reports


        $api = SupportAPI::getInstance();

        $project_info = $api->project($api->config()->project);

        $List = $this->getList();
        $fields = new FieldList();

        $fields->push( LiteralField::create('l_links', "
        <p>
        <a class='action' href='". HTTP::setGetVar('query', '', $this->Link()) ."'>offene Tickets ({$project_info['open-tickets']})</a> |&nbsp;
        <a class='action' href='". HTTP::setGetVar('query', 'status:closed', $this->Link()) ."'>geschlossene Tickets ({$project_info['closed-tickets']})</a> |&nbsp;
        <a class='action' href='". HTTP::setGetVar('query', 'ALL', $this->Link()) ."'>alleTickets ({$project_info['total-tickets']})</a> 
        <em><small style='font-size: 87%; margin-left: 20px; color: #666'>(im Moment die aktuellsten 20 Einträge)</small></em>
        </p>
        ") );


        $gridFieldConfig = GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldFooter()
        );
        $gridField = new GridField('Tickets',false, $List, $gridFieldConfig);
        $columns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns');
        $columns->setDisplayFields(array(
            'ticket-id' => 'ID',
            'summary' => 'Title',
            'created-at' => 'erstellt am',
            'updated-at' => 'update am',
            'PriorityName' => 'Prio',
            'TypeName' => 'Type',
            'StatusName' => 'Status',
            'assignee' => 'Zuständig',
            'total-time-spent' => 'Zeit (h:m)',
        ));
        $columns->setFieldFormatting(array(
            'total-time-spent' => function($value, &$item) {
                $hours = floor($value / 60);
                $minutes = ($value % 60);
                return sprintf('%02d:%02d', $hours, $minutes);
            },
            'StatusName' => function($value, &$item) {
                return sprintf('<span class="supportadmin-status status-%s">%s</span>', $item->StatusColor, $item->StatusName);
            },
            'summary' => function($value, &$item) {
                return sprintf('<a href ="%s" target="codebasehq">%s</a>', $item->Link(), $item->summary);
            },
            'created-at' => function($value, &$item) {
                $date = new DateTime($value);
                return $date->format('d.m.y H:i');
            },
            'updated-at' => function($value, &$item) {
                $date = new DateTime($value);
                return $date->format('d.m.y H:i');
            },

        ));
        $fields->push($gridField);

        $actions = new FieldList();
        $form = CMSForm::create(
            $this, "EditForm", $fields, $actions
        )->setHTMLID('Form_EditForm');
        $form->setResponseNegotiator($this->getResponseNegotiator());
        $form->addExtraClass('cms-edit-form cms-panel-padded center ' . $this->BaseCSSClasses());
        $form->loadDataFrom($this->request->getVars());

        $this->extend('updateEditForm', $form);

        return $form;
    }

    public function getList() {
        $tickets = SupportAPI::getInstance()->tickets($this->query?$this->query:null);
        return SupportAPI::toArrayList($tickets);
    }

    public function LinkCreateTicket() {
        return implode("/", array(
            Config::inst()->get('SupportAPI', 'web_endpoint'),
            "tickets",
            'new'
        ));
    }

}