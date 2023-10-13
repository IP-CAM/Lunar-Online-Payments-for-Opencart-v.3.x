<?php

/**
 * 
 */
abstract class AbstractLunarFrontModel extends \Model
{
    protected string $paymentMethodCode = '';
    
    /**
     * 
     */
    public function getMethod($address, $total)
    {
        $status = $this->getConfigValue('status') == 1;

        $query = $this->db->query("SELECT * 
                                    FROM " . DB_PREFIX . "zone_to_geo_zone 
                                    WHERE geo_zone_id = '" . (int) $this->getConfigValue('geo_zone') . "'
                                    AND country_id = '" . (int) $address['country_id'] . "'
                                    AND (zone_id = '" . (int) $address['zone_id'] . "'
                                    OR zone_id = '0')"
                                );

        $minimumTotal = $this->getConfigValue('minimum_total');
		if ($minimumTotal > 0 && $minimumTotal > $total) {
			$status = false;
		} elseif (!$this->getConfigValue('geo_zone')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
        
        if (!$status) {
            return [];
        }

        $logos = $this->getConfigValue('checkout_cc_logo');
        $logos_string = '';
        if (is_array($logos)) {
            foreach ($logos as $logo) {
                $logos_string .= '<img src="' . HTTPS_SERVER
                                . 'catalog/view/theme/default/image/lunar/'
                                . $logo . '" style="display-inline;height:25px;margin-left:5px;" />';
            }
        }

        if ('mobilePay' == $this->paymentMethodCode) {
            $logos_string = '<img src="' . HTTPS_SERVER
                                . 'catalog/view/theme/default/image/lunar/mobilepay-logo.png" 
                                    style="display-inline;height:25px;margin-left:5px;" />';
        }

        return [
            'code'       => 'lunar_' . $this->paymentMethodCode,
            'title'      => $this->getConfigValue('method_title'),
            'terms'      => $logos_string,
            'sort_order' => $this->getConfigValue('sort_order')
        ];
    }

    /**
     * @return mixed
     */
    protected function getConfigValue($configKey)
    {
        return $this->config->get('payment_lunar_' . $this->paymentMethodCode  . '_' . $configKey);
    }
}
