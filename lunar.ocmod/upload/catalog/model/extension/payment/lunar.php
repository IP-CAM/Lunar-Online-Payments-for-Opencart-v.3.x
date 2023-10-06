<?php

class ModelExtensionPaymentLunar extends Model
{

    public function getMethod($address, $total)
    {
        $status = $this->config->get('payment_lunar_status');

        $query = $this->db->query("SELECT * 
                                    FROM " . DB_PREFIX . "zone_to_geo_zone 
                                    WHERE geo_zone_id = '" . (int) $this->config->get('payment_lunar_geo_zone') . "'
                                    AND country_id = '" . (int) $address['country_id'] . "'
                                    AND (zone_id = '" . (int) $address['zone_id'] . "'
                                    OR zone_id = '0')"
                                );

        $minimumTotal = $this->config->get('payment_lunar_minimum_total');
		if ($minimumTotal > 0 && $minimumTotal > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_lunar_geo_zone')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
        
        if (!$status) {
            return [];
        }

        $logos = $this->config->get('payment_lunar_checkout_cc_logo');
        $logos_string = '';
        if (is_array($logos)) {
            foreach ($logos as $logo) {
                $logos_string .= '<img src="' . HTTPS_SERVER
                                . 'catalog/view/theme/default/image/lunar/'
                                . $logo . '" style="display-inline;height:25px;margin-left:5px;" />';
            }
        }

        return [
            'code'       => 'lunar',
            'title'      => $this->config->get('payment_lunar_method_title'),
            'terms'      => $logos_string,
            'sort_order' => $this->config->get('payment_lunar_sort_order')
        ];
    }
}
