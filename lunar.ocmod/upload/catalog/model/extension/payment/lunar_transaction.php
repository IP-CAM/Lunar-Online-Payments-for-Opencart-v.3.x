<?php

require_once(DIR_SYSTEM . 'library/Lunar/helper/LunarHelper.php');

/**
 * 
 */
class ModelExtensionPaymentLunarTransaction extends Model
{
    /**
     * 
     */
    public function getLastTransaction($orderId)
    {
        $lastTransaction = $this->db->query("SELECT *
                                    FROM `" . LunarHelper::LUNAR_DB_TABLE . "`
                                    WHERE order_id = '" . $orderId . "'
                                    ORDER BY lunar_transaction_id
                                    DESC
                                    LIMIT 1"
                                );

        return $lastTransaction->row;
    }

    /**
     * 
     */
    public function addTransaction($data)
    {
        $this->db->query("UPDATE `" . LunarHelper::LUNAR_DB_TABLE . "`
                            SET history = 1
                            WHERE history = '0'
                            AND transaction_id = '" . $data['transaction_id'] . "'"
                        );

        $this->db->query("INSERT INTO `" . LunarHelper::LUNAR_DB_TABLE . "`
                            SET order_id = '" . $data['order_id'] . "',
                                transaction_id = '" . $data['transaction_id'] . "',
                                transaction_type = '" . $data['transaction_type'] . "',
                                transaction_currency = '" . $data['transaction_currency'] . "',
                                order_amount = '" . $data['order_amount'] . "',
                                transaction_amount = '" . $data['transaction_amount'] . "',
                                history = '" . $data['history'] . "',
                                date_added = NOW()"
                        );
    }


    /**
     * 
     */
    public function updateOrder($data, $new_order_status_id)
    {
        if (!($new_order_status_id > 0)) {
            return;
        }

        /** Update the order status & date_modified. */
        $this->db->query("UPDATE `" . DB_PREFIX . "order`
                            SET order_status_id = '" . $new_order_status_id . "',
                                date_modified = NOW()
                            WHERE order_id = '" . $data['order_id'] . "'"
                        );

        /** Update the last order history that was inserted just a moment before. */
        $this->updateOrderHistory($data, $new_order_status_id);
    }


    /**
     * 
     */
    public function updateOrderHistory($data, $new_order_status_id)
    {
        $comment = 'Transaction ref: ' . $data['transaction_id'] . "\r\n" 
                    . 'Type: ' . $data['transaction_type']
                    . ' - Amount: ' . $data['formatted_amount'];

        $this->db->query("UPDATE `" . DB_PREFIX . "order_history`
                            SET notify = '1',
                                comment = '" . $this->db->escape($comment) . "',
                                date_added = NOW()
                            WHERE order_id = '" . $data['order_id'] . "'
                            AND order_status_id = '" . $new_order_status_id . "'
                            ORDER BY order_history_id DESC
                            LIMIT 1"
                        );
    }

    /**
     * @return mixed
     */
    public function savePaymentIntentOnTransaction($data)
    {
        // prevent inserting init transaction multiple times
        $this->db->query("DELETE FROM `" . LunarHelper::LUNAR_DB_TABLE . "`
                            WHERE order_id = '" . $data['order_id'] . "'
                            AND transaction_type = 'INIT'");

        $this->db->query("INSERT INTO `" . LunarHelper::LUNAR_DB_TABLE . "`
                        SET order_id = '" . $data['order_id'] . "',
                            transaction_id = '" . $data['transaction_id'] . "',
                            transaction_type = 'INIT',
                            transaction_currency = '" . $data['transaction_currency'] . "',
                            order_amount = '" . $data['order_amount'] . "',
                            transaction_amount = '0',
                            history = '0',
                            date_added = NOW()"
                    );
    }

    /**
     * 
     */
    public function updateInitTransaction($data)
    {
        $this->db->query("UPDATE `" . LunarHelper::LUNAR_DB_TABLE . "`
                            SET order_id = '" . $data['order_id'] . "',
                                transaction_id = '" . $data['transaction_id'] . "',
                                transaction_type = '" . $data['transaction_type'] . "',
                                transaction_currency = '" . $data['transaction_currency'] . "',
                                order_amount = '" . $data['order_amount'] . "',
                                transaction_amount = '" . $data['transaction_amount'] . "',
                                history = '0',
                                date_added = NOW()
                            WHERE order_id = '" . $data['order_id'] . "'
                            AND transaction_type = 'INIT'"
                        );
    }
}
