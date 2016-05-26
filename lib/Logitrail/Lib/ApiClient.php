<?php

namespace Logitrail\Lib;

class ApiClient {
    private $merchantId;
    private $secretKey;

    private $orderId;

    private $firstName;
    private $lastName;
    private $address;
    private $postalCode;
    private $city;

    private $products = array();

    private $checkoutUrl = 'http://checkout.test.logitrail.com/go';
    private $apiUrl = 'http://api-1.test.logitrail.com/2015-01-01/';

    public function addProduct($id, $name, $amount, $weight, $price, $taxPct) {
        $this->products[] = array('id' => $id, 'name' => $name, 'amount' => $amount, 'weight' => $weight, 'price' => $price, 'taxPct' => $taxPct);
    }

    public function setMerchantId($merchantId) {
        $this->merchantId = $merchantId;
    }

    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;
    }

    public function setOrderId($orderId) {
        $this->orderId = $orderId;
    }

    public function setCustomerInfo($firstname, $lastname, $address, $postalCode, $city) {
        $this->firstName = $firstname;
        $this->lastName = $lastname;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->city = $city;
    }

    public function getForm() {

        // TODO: Check that all mandatory values are set
        $post = array();

        $post['merchant'] = $this->merchantId;
        $post['request'] = 'new_order';
        $post['order_id'] = $this->orderId; // Merchant's own ID for the order.
        $post['customer_fn'] = $this->firstName;
        $post['customer_ln'] = $this->lastName;
        $post['customer_addr'] = $this->address;
        $post['customer_pc'] = $this->postalCode;
        $post['customer_city'] = $this->city;

        // add products to post data
        foreach($this->products as $id => $product) {
            $post['products_'.$id.'_id'] = $product['id'];
            $post['products_'.$id.'_name'] = $product['name'];
            $post['products_'.$id.'_amount'] = $product['amount'];
            $post['products_'.$id.'_weight'] = $product['weight'];
            $post['products_'.$id.'_price'] = $product['price'];
            $post['products_'.$id.'_tax'] = $product['taxPct'];
        }

        $mac = $this->calculateMac($post, $this->secretKey);
        $post['mac'] = $mac;

        $form = '<form id="form" method="post" action="' . $this->checkoutUrl . '">';

        foreach($post as $name => $value) {
            $form .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
        }

        $form .= '</form>';
        $form .= "<script>document.getElementById('form').submit();</script>";
        return $form;
    }

    public function updateOrder($logitrailOrderId, $data) {
        return $this->doPost($this->apiUrl . 'orders/' . $logitrailOrderId, $data);
    }

    public function confirmOrder($logitrailOrderId) {
        return $this->doPost($this->apiUrl . 'orders/' . $logitrailOrderId . '/_confirm');
    }

    private function doPost($url, $data = false) {
        // TODO: Check that merchId and secret are set
        $auth = 'M-' . $this->merchantId . ':' . $this->secretKey;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $auth);

        if(is_array($data)) {
            $postData = json_encode($data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData))
            );

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    private function calculateMac($requestValues, $secretKey) {
        ksort($requestValues);

        $macValues = [];
        foreach ($requestValues as $key => $value) {
            if ($key === 'mac') {
                continue;
            }
            $macValues[] = $value;
        }

        $macValues[] = $secretKey;

        $macSource = join('|', $macValues);

        $correctMac = base64_encode(hash('sha512', $macSource, true));
        return $correctMac;
    }
}
