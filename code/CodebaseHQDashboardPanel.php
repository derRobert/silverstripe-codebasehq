<?php
/**
 * User: robert
 * Date: 12.08.2016
 */

if( class_exists('Dashboard') ) {
    class CodebaseHQDashboardPanel extends DashboardPanel
    {
        private static $db = array (
        );

        private static $priority = 10;


        /**
         * Gets the label for this panel
         *
         * @return string
         */
        public function getLabel() {
            return "Offene Tickets";
        }

        /**
         * Gets the title for this panel
         *
         * @return string
         */
        public function getDescription() {
            return 'Zeigt offene Tickets an';
        }

        public function getConfiguration() {
            $fields = parent::getConfiguration();
            return $fields;
        }


        public function Tickets() {
            $tickets = SupportAPI::getInstance()->tickets('status:open');
            $list = SupportAPI::toArrayList($tickets);
            return $list;
        }

    }
}
