<?php
require_once "UserClass.php";
require_once "AdminDetails.php";

class ShippingKitProvider {

    public static $lengthName = "length";
    public static $widthName = "width";
    public static $heightName = "height";
    public static $ValueName = "value";

    public static $carrierCode = "stamps_com";
    public static $serviceCode = "usps_first_class_mail";
    public static $packageCode = "package";

    public static $weight = array(
        "value" => 1,
        "units" => "ounces"
    );
    public static $confirmation = "delivery";
    public static $dimensions = array(
        "length" => 18,
        "width" => 14,
        "height" => 6,
        "units" => "inches"
    );

}


class ShipStation
{
    //minimal request options
    public $carrierCode;
    public $serviceCode;
    public $packageCode;
    public $shipDate;
    public $weight;
    public $confirmation;

    //

    public $shipFromName;
    public $shipFromCompany;
    public $shipFromStreet1;
    public $shipFromStreet2;
    public $shipFromStreet3;
    public $shipFromCity;
    public $shipFromState;
    public $shipFromPostalCode;
    public $shipFromCountry;
    public $shipFromPhone;
    public $shipFromResidential;

    //

    public $shipToName;
    public $shipToCompany;
    public $shipToStreet1;
    public $shipToStreet2;
    public $shipToStreet3;
    public $shipToCity;
    public $shipToState;
    public $shipToPostalCode;
    public $shipToCountry;
    public $shipToPhone;
    public $shipToResidential;

    //

    public $testLabel = false;

    //response
    public $shipmentId;
    public $orderId;
    public $userId;
    public $customerEmail;
    public $orderNumber;
    public $createDate;
    public $shipmentCost;
    public $insuranceCost;
    public $trackingNumber;
    public $isReturnLabel;
    public $batchNumber;
    public $warehouseId;
    public $voided;
    public $voidDate;
    public $marketplaceNotified;
    public $notifyErrorMessage;
    public $dimensions;
    public $insuranceOptions;
    public $advancedOptions;
    public $shipmentItems;
    public $labelData;
    public $formData;

    private $Url = "https://ssapi.shipstation.com/shipments/createlabel";
    private $UrlAllServices = "https://ssapi.shipstation.com/carriers/getcarrier?carrierCode=";
    private $UrlServices = "https://ssapi.shipstation.com/carriers/listservices?carrierCode=";
    private $AuthKey = "";
    private $AuthSecret = "";
    private $timeout = 15;

    function ShipStation() {

    }

    function GetLabelShipStation() {

        $shipTo = array(
            "name" => $this->shipToName,
            "company" => $this->shipToCompany,
            "street1" => $this->shipToStreet1,
            "street2" => $this->shipToStreet2,
            "street3" => $this->shipToStreet3,
            "city" => $this->shipToCity,
            "state" => $this->shipToState,
            "postalCode" => $this->shipToPostalCode,
            "country" => $this->shipToCountry,
            "phone" => $this->shipToPhone,
            "residential" => $this->shipToResidential
        );

        $shipFrom = array(
            "name" => $this->shipFromName,
            "company" => $this->shipFromCompany,
            "street1" => $this->shipFromStreet1,
            "street2" => $this->shipFromStreet2,
            "street3" => $this->shipFromStreet3,
            "city" => $this->shipFromCity,
            "state" => $this->shipFromState,
            "postalCode" => $this->shipFromPostalCode,
            "country" => $this->shipFromCountry,
            "phone" => $this->shipFromPhone,
            "residential" => $this->shipFromResidential
        );

        $params = array(
            "carrierCode" => $this->carrierCode,
            "serviceCode" => $this->serviceCode,
            "packageCode" => $this->packageCode,
            "shipDate" => $this->shipDate,
            "weight" => $this->weight,
            "dimensions" => $this->dimensions,
            "shipFrom" => $shipFrom,
            "shipTo" => $shipTo,
            "testLabel" => $this->testLabel,
            "confirmation" => $this->confirmation
        );
        $json = json_encode($params);

        $post = http_build_query($json);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $this->Url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,

            CURLOPT_HTTPHEADER => array(
                "Host:ssapi.shipstation.com",
                "Authorization: Basic ".base64_encode($this->AuthKey.":".$this->AuthSecret),
                "Content-Type:application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $testedLabel = json_decode($response);
        if($this->testLabel) {
            $testedLabel->trackingNumber = rand(101,333).rand(333,666).rand(666,999).rand(1000,9999).rand(101,333).rand(333,666).rand(666,999).rand(1000,9999);
        }
        if($testedLabel->ExceptionMessage == '') {
            $name = $root = $testedLabel->trackingNumber.".pdf";
            $result = file_put_contents($name, base64_decode($testedLabel->labelData));
        }

        return $testedLabel;
    }

    function GetLabelShipStationWithData(UserClass $user, ShippingKit $kit) {

        $adminDetails = new AdminDetails();
        $adminDetails->GetAdminDetails();

        $shipTo = array(
            "name" => $user->FirstName." ".$user->LastName,
            "company" => "",
            "street1" => $user->Address1,
            "street2" => $user->Address2,
            "street3" => "",
            "city" => $user->City,
            "state" => $user->State,
            "postalCode" => $user->Zipcode,
            "country" => "US",
            "phone" => $user->PhoneNumber,
            "residential" => true
        );

        $shipFrom = array(
            "name" => "BuyBackWorld Shipping Kits",
            "company" => "BuyBackWorld",
            "street1" => $adminDetails->Address,
            "street2" => "",
            "street3" => "",
            "city" => $adminDetails->City,
            "state" => $adminDetails->State,
            "postalCode" => $adminDetails->Zipcode,
            "country" => $adminDetails->Country,
            "phone" => "1234567890",
            "residential" => false
        );

        $dimentions = explode("x", $kit->KitDimenstions);

        $size = array(
            "length" => $dimentions[0],
            "width" => $dimentions[1],
            "height" => $dimentions[2],
            "units" => "inches"
        );

        $weight = array(
            "value" => $kit->KitWeight,
            "units" => "ounces"
        );

        //

        $params = array(
            "orderId" => $this->orderId,
            "carrierCode" => $kit->CarrirCode,
            "serviceCode" => $kit->ServiceCode,
            "packageCode" => ShippingKitProvider::$packageCode,
            "shipDate" => date("Y-m-d", time()),
            "weight" => $weight,
            "dimensions" => $size,
            "shipFrom" => $shipFrom,
            "shipTo" => $shipTo,
            "testLabel" => $this->testLabel,
            "confirmation" => ShippingKitProvider::$confirmation
        );
        $json = json_encode($params);

        //$post = http_build_query($json);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $this->Url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,

            CURLOPT_HTTPHEADER => array(
                "Host:ssapi.shipstation.com",
                "Authorization: Basic ".base64_encode($this->AuthKey.":".$this->AuthSecret),
                "Content-Type:application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $testedLabel = json_decode($response);
        if($this->testLabel) {
            $testedLabel->trackingNumber = rand(101,333).rand(333,666).rand(666,999).rand(1000,9999).rand(101,333).rand(333,666).rand(666,999).rand(1000,9999);
        }
        if($testedLabel->ExceptionMessage == '') {
            $name = $root = $_SERVER['DOCUMENT_ROOT']."/srbox/".$testedLabel->trackingNumber.".pdf";
            $result = file_put_contents($name, base64_decode($testedLabel->labelData));
        }
        //->trackingNumber

        return $testedLabel;
    }

    function GetListServices($service) {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $this->UrlServices.$service,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => array(
                "Host:ssapi.shipstation.com",
                "Authorization: Basic ".base64_encode($this->AuthKey.":".$this->AuthSecret),
                "Content-Type:application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;

    }

    function GetCarrierByCode($code) {
        //"https://ssapi.shipstation.com/carriers/getcarrier?carrierCode=%7B".$code."%7D"

        //$code = "%7B".$code."%7D";
        $url = $this->UrlAllServices.$code;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => array(
                "Host: ssapi.shipstation.com",
                "Authorization: Basic ".base64_encode($this->AuthKey.":".$this->AuthSecret)
            ),
        ));

        $response = curl_exec($curl);
        if(curl_exec($curl) === false) {
            $error = 'Curl error: ' . curl_error($curl);
        } else {
            //echo 'Operation completed without any errors, you have the response';
        }
        curl_close($curl);


        return $response;
    }

    function GetAllCarriers()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ssapi.shipstation.com/carriers",
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => array(
                "Host: ssapi.shipstation.com",
                "Authorization: Basic ".base64_encode($this->AuthKey.":".$this->AuthSecret)
            ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            $error = 'Curl error: ' . curl_error($curl);
        } else {
            //echo 'Operation completed without any errors, you have the response';
        }
        curl_close($curl);

        return $response;
    }



}