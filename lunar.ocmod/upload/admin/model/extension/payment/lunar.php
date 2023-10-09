<?php

class ModelExtensionPaymentLunar extends Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `lunar_transaction` (
         `lunar_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
         `order_id`             int(11) NOT NULL,
         `transaction_id`       char(50) NOT NULL,
         `transaction_type`     char(10) NOT NULL,
         `transaction_currency` char(5) NOT NULL,
         `order_amount`         decimal(15,4) NOT NULL,
         `transaction_amount`   decimal(15,4) NOT NULL,
         `history`              tinyint(1) NOT NULL,
         `date_added`           datetime NOT NULL,
         PRIMARY KEY (`lunar_transaction_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->addEvents();
    }

    public function uninstall()
    {
        // $this->db->query("DROP TABLE IF EXISTS `lunar_transaction`");

        $this->deleteEvents();
    }

    private function addEvents()
    {
        $this->load->model('setting/event');

        /** Check if event is in database 'event' table (the result of getEventByCode is an array). */
        if(empty($this->model_setting_event->getEventByCode('lunar_do_transaction_on_order_status_change'))) {
            /** Make sure that the event is introduce only once in DB. */
            /** addEvent($code, $trigger, $action, $status = 1, $sort_order = 0); */
            $this->model_setting_event->addEvent(
                'lunar_do_transaction_on_order_status_change',
                'catalog/controller/api/order/history/after',
                'extension/payment/lunar_transaction/doTransactionOnOrderStatusChange'
            );
        }
    }

    private function deleteEvents()
    {
        $this->load->model('setting/event');
        /** deleteEventByCode($code); */
        $this->model_setting_event->deleteEventByCode('lunar_do_transaction_on_order_status_change');
    }

    public function getCcLogos()
    {
        return array(
            array ( 'name' => 'Mastercard', 'logo' => 'mastercard.png' ),
            array ( 'name' => 'Mastercard Maestro', 'logo' => 'maestro.png' ),
            array ( 'name' => 'Visa', 'logo' => 'visa.png' ),
            array ( 'name' => 'Visa Electron', 'logo' => 'visaelectron.png' ),
        );
    }
}
