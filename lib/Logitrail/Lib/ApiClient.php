<?php

namespace Logitrail\Lib;

class ApiClient { // implements ApiClientInterface {

    private $merchantId;
    private $secretKey;

    private $orderId;

    private $firstName;
    private $lastName;
    private $address;
    private $postalCode;
    private $city;

    private $products = array();

    private $url = 'http://checkout.test.logitrail.com/go';


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

        $form = '<form id="form" method="post" action="' . $this->url . '">';

        foreach($post as $name => $value) {
            $form .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
        }

        $form .= '</form>';
        $form .= "<script>document.getElementById('form').submit();</script>";
        return $form;
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
