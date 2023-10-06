<?php

class ModelExtensionPaymentLunarTransaction extends Model
{
    const LUNAR_DB_TABLE = DB_PREFIX . 'lunar_transaction';

    public function getTransactions($data = array())
    {
        $sql = "SELECT * FROM `" . self::LUNAR_DB_TABLE . "` WHERE history = '0'";

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
        } else {
            $sql .= " AND order_id > 0";
        }

        if (!empty($data['filter_transaction_id'])) {
            $sql .= " AND transaction_id = '" . $data['transaction_id'] . "'";
        }

        if (!empty($data['filter_transaction_type'])) {
            $sql .= " AND transaction_type = '" . $data['filter_transaction_type'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        $sort_data = array(
            'order_id',
            'transaction_id',
            'transaction_type',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'ASC')) {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }


    /**
     * GET last transaction by query data parameters
     */
    public function getLastModuleTransaction($orderId)
    {
        $moduleTransaction = $this->db->query("SELECT *
                                    FROM `" . self::LUNAR_DB_TABLE . "`
                                    WHERE order_id = '" . $orderId . "'
                                    ORDER BY lunar_transaction_id
                                    DESC
                                    LIMIT 1"
                                );

        return $moduleTransaction->row;
    }

    /**
     * ADD transaction in {vendor}_transaction table
     */
    public function addTransaction($data)
    {
        $this->db->query("UPDATE `" . self::LUNAR_DB_TABLE . "`
                            SET history = 1
                            WHERE history = '0'
                            AND transaction_id = '" . $data['transaction_id'] . "'"
                        );

        $this->db->query("INSERT INTO `" . self::LUNAR_DB_TABLE . "`
                            SET order_id = '" . $data['order_id'] . "',
                                transaction_id = '" . $data['transaction_id'] . "',
                                transaction_type = '" . $data['transaction_type'] . "',
                                transaction_currency = '" . $data['transaction_currency'] . "',
                                order_amount = '" . $data['order_amount'] . "',
                                transaction_amount = '" . $data['transaction_amount'] . "',
                                total_amount = '" . $data['total_amount'] . "',
                                history = '" . $data['history'] . "',
                                date_added = " . $data['date_added'] // date_added is a SQL function
                        );
    }


    /**
     * UPDATE order
     */
    public function updateOrder($data, $new_order_status_id)
    {
        if ($new_order_status_id > 0) {
            /** Update the order status & date_modified. */
            $this->db->query("UPDATE `" . DB_PREFIX . "order`
                                SET order_status_id = '" . $new_order_status_id . "',
                                    date_modified = NOW()
                                WHERE order_id = '" . $data['order_id'] . "'"
                            );

            $comment = 'Lunar transaction: ref:' . $data['transaction_id'];
            $comment .= "\r\n" . 'Type: ' . $data['transaction_type'] . ', Amount: ' . $data['transaction_amount'] . ' (' . strtoupper($data['transaction_currency'] . ')');

            /** Update the last order history because it was inserted just a moment before. */
            $this->db->query("UPDATE `" . DB_PREFIX . "order_history`
                                SET notify = '0',
                                    comment = '" . $this->db->escape($comment) . "',
                                    date_added = NOW()
                                WHERE order_status_id = '" . $new_order_status_id . "'
                                AND order_id = '" . $data['order_id'] . "'
                                ORDER BY order_history_id DESC
                                LIMIT 1"
                            );
        }
    }
}
