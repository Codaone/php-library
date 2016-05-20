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

    public function doCheckout() {
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

        $cred = ''; // sprintf("Authorization: Basic %s\r\n", base64_encode('M-'.$this->merchantId.':'.$this->secretKey) );

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n".$cred,
                'method'  => 'POST',
                'content' => http_build_query($post)
            )
        );

        $context  = stream_context_create($options);
//        $result = file_get_contents($this->domain, false, $context);
        //if ($result === FALSE) { /* Handle error */ }


        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        //var_dump('res', $result);
        echo $response;

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
