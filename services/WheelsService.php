<?php
/**
 * Services class 
 * defines functions that affect on controller
 *
 * PHP verion 7.2
 *
 * @category   Services
 * @author     praveen <praveen@travelinsert.com>
 *
 * @version    0.1
 */
namespace Praveen;

//use App\Services\WheelsService;
/**
 * IndexController Class
 *
 * This class is used for send and receive the XML from Wheesys API.
 *
 * @filesource Services.class.php
 * @api Wheelsys API
 * @since 1.0
 */
 
class WheelsService
{
    /**
     * cURL request and response
     *
     * This function is used for send and receive the XML format.
     * * We will convert the XML into Json formats using the simplexml_load_string function
     * * We Remove the @ character from the json response
     *
     * @param string $post_string is used for collect the XML request
     * @filesource Services.class.php
     * @api Wheelsys API
     * @since 1.0
     * @return void
     */

    public function services($post_string)
    {
        $curl_url = 'http://develop.invensys.gr/reservations/link/';
        $url = $curl_url.$post_string;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT,        60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
        $xml = new \SimpleXMLElement($response);
        $json = str_replace('@attributes', 'attributes', json_encode($xml));
        $error = $xml->errors->error['code'];
        $status = $xml->reservation['status'][0];
        if ($status == 'ERR/0') {
            echo 'There is a stop sale for that Group/Period/Station';
        } elseif ($status == 'ERR/100') {
            echo 'Required Parameter is Missing';
        } elseif ($status == 'ERR/101') {
            echo 'Vehicle Group Not Found';
        } elseif ($status == 'ERR/102') {
            echo 'Pickup/Return Station Not Found';
        } elseif ($status == 'ERR/103') {
            echo 'Additional Option not Found';
        } elseif ($status == 'ERR/104') {
            echo 'Reservation Already Cancelled';
        } elseif ($status == 'ERR/105') {
            echo 'Car Already Delivered';
        } elseif ($status == 'ERR/106') {
            echo 'Invalid date/time combination';
        } elseif ($status == 'ERR/107') {
            echo 'Reservation Number not valid';
        } elseif ($status == 'ERR/108') {
            echo 'Reference/voucher Number already used';
        } elseif ($status == 'ERR/110') {
            echo 'Price Quote Not Found';
        } elseif ($status == 'ERR/111') {
            echo 'Price Quote Has Expired';
        } elseif ($status == 'ERR/112') {
            echo 'Price Quote not valid for this reservation request';
        } elseif ($status == 'ERR/114') {
            echo 'Group not Supported at specified location';
        } elseif ($status == 'ERR/999') {
            echo 'System is down for maintenance';
        } elseif ($status == 'RES') {
            echo 'Reservation is active';
        } elseif ($status == 'CNC') {
            echo 'Reservation is cancelled';
        } elseif ($status == 'REQ') {
            echo 'Reservation is still on request status';
        } elseif ($status == 'RNT') {
            echo 'Reservation was executed';
        } elseif ($error == 'ERR/100') {
            echo 'Required Parameter is Missing';
        } elseif ($error == 'ERR/102') {
            echo 'Pickup/Return Station Not Found';
        } elseif ($error == 'ERR/106') {
            echo 'Invalid date/time combination';
        } elseif ($error == 'ERR/107') {
            echo 'Reservation Number not valid';
        } elseif ($error == 'ERR/999') {
            echo 'System is down for maintenance';
        } else {
            print_r($json);
        }
        curl_close($ch);
        exit();
    }

    /**
     * GetStation List
     *
     * This function is used to get all station list from the Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param NULL
     *
     * @return json string
     **/

    public function station()
    {
        $post_string = "stations_AUF72F23.html?agent=INV123456";
        //$station = $this->services($post_string);
        $station = WheelsService::services($post_string);
    }

    /**
     * Get Price Quote List
     *
     * This function is used to get price quote list for rates and availability of the vehicles from the Wheelsys API
     * @api Wheelsys API
     * @filesource indexcontroller.php
     *
     * @param array $data is used to get from,from_time,to,to_time,pickup_stationm,return_station,pickup_point,
     * dropoff_point
     *
     * @return json string
     **/

    public function priceQuote($data)
    {
        $from = $data['from']; //14/12/2018
        $from_time = $data['from_time']; //1200
        $to = $data['to']; //18/12/2018
        $to_time = $data['to_time']; //2200
        $pickup_station = $data['pickup_station']; //A9K
        $return_station = $data['return_station']; //A9K //optional variable
        $pickup_point = $data['pickup_point']; //optional variable
        $dropoff_point = $data['dropoff_point'];
        $agents = 'price-quote_AUF72F23.html?agent=INV123456';
        $post_string = "&date_from=".$from."&time_from=".$from_time."&date_to=".$to."&time_to=".$to_time.
            "&pickup_station=".$pickup_station."&return_station=".$return_station."&pickup_point=".$pickup_point.
            "&dropoff_point=".$dropoff_point."";
        $priceqoute = WheelsService::services($agents.$post_string);
    }

    /**
     * Get Groups List
     *
     * This function is used to get the vehicle's model and group from the Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param NULL
     *
     * @return json string
     **/

    public function groups()
    {
        $post_string = "groups_AUF72F23.html?agent=INV123456";
        $groups = WheelsService::services($post_string);
        return $groups;
    }

    /**
     * Get Reservation
     *
     * This function is used to make the reservation of the vehicles from the Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param array $data is used to get from ,from_time,to,to_time,pickup_station,return_station,customer_name,
     * voucherno,pickup_info,return_info,customer_email,quoteref_id,pickup_point,remarks,option_code
     *
     * @return json string
     **/

    public function reservation($data)
    {
        $from = $data['from']; //14/12/2018
        $from_time = $data['from_time']; //1200
        $to = $data['to']; //18/12/2018
        $to_time = $data['to_time']; //2200
        $pickup_station = $data['pickup_station']; //A9K
        $return_station = $data['return_station']; //A9K //optional variable
        $customer_name = $data['customer_name'];
        $group = $data['group'];
        $voucherno = $data['voucherno'];
        $pickup_info = $data['pickup_info']; //optional variable  /* If outside the office or Flight No */
        $return_info = $data['return_info']; //optional variable  /* If outside the office or any specific area */
        $customer_email = $data['customer_email']; //optional variable
        $customer_phone = $data['customer_phone']; //optional variable
        $quoteref_id = $data['quoteref_id']; //optional variable /* Your Price Quote ID */
        $pickup_point = $data['pickup_point']; //optional variable
        $dropoff_point = $data['dropoff_point']; //optional variable
        $remarks = $data['remarks']; //optional variable
        $option_code = $data['option_code']; //optional variable /* options for the vehicles */
        $post_string = "new-res_AUF72F23.html?agent=INV123456&date_from=".$from."&time_from=".$from_time."&date_to="
            .$to."&time_to=".$to_time."&pickup_station=".$pickup_station."&return_station=".$return_station."&group="
            .$group."&customer_name=".$customer_name."&voucherno=".$voucherno."&pickup_info=".$pickup_info.
            "&return_info=".$return_info."&customer_email=".$customer_email."&customer_phone=".$customer_phone.
            "&quoteref_id=".$quoteref_id."&pickup_point=".$pickup_point."&dropoff_point=".$dropoff_point."&remarks="
            .$remarks."&option_code=".$option_code."";
        $reservation = WheelsService::services($post_string);
        return $reservation;
    }

    /**
     * Get expresscheckout details
     *
     * This function is used to get check to provide advance customer details such as license number & identification 
     * to speed up car delivery.
     * Express information can be set even if the reservation is at on-request statusf from Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param array $data is used to get refno,irn
     *
     * @return json sting
     **/

    public function expressCheckout($data)
    {
        $refno = $data['refno']; // AK912 sample value
        $irn = $data['irn']; // 9400907 sampl vlaue
        $post_string = "express_AUF72F23.html?agent=INV123456&refno=".$refno."&irn=".$irn."";
        $station = WheelsService::services($post_string);
        return $station;
    }

    /**
     * Get Amend Reservation  details
     *
     * This function is used to make the amendment for the reservation of the vehicles from the Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param array $data is used to get from ,from_time,to,to_time,pickup_station,return_station,customer_name,
     * voucherno,irn,refno,customer_name,pickup_info,return_info,customer_email,customer_phone,quoteref_id,pickup_point,
     *
     * @return json string
     **/

    public function amendReservation($data)
    {
        $from = $data['from']; //14/12/2018
        $from_time = $data['from_time']; //1200
        $to = $data['to']; //18/12/2018
        $to_time = $data['to_time']; //2200
        $pickup_station = $data['pickup_station']; //A9K
        $return_station = $data['return_station']; //A9K
        $customer_name = $data['customer_name'];
        $group = $data['group'];
        $voucherno = $data['voucherno'];
        $irn =  $data['irn'];
        $refno = $data['refno'];
        $pickup_info = $data['pickup_info']; //optional variable  /* If outside the office or Flight No */
        $return_info = $data['return_info']; //optional variable  /* If outside the office or any specific area */
        $customer_email = $data['customer_email']; //optional variable
        $customer_phone = $data['customer_phone']; //optional variable
        $quoteref_id = $data['quoteref_id']; //optional variable /* Your Price Quote ID */
        $pickup_point = $data['pickup_point']; //optional variable
        $dropoff_point = $data['dropoff_point']; //optional variable
        $remarks = $data['remarks']; //optional variable
        $option_code = $data['option_code']; //optional variable /* options for the vehicles */
        $post_string = "amend-res_AUF72F23.html?agent=INV123456&refno=".$refno."&irn=".$irn."&date_from=".$from.
            "&time_from=".$from_time."&date_to=".$to."&time_to=".$to_time."&pickup_station=".$pickup_station.
            "&return_station=".$return_station."&group=".$group."&customer_name=".$customer_name."&voucherno="
            .$voucherno."&pickup_info=".$pickup_info."&return_info=".$return_info."&customer_email=".$customer_email.
            "&customer_phone=".$customer_phone."&quoteref_id=".$quoteref_id."&pickup_point=".$pickup_point.
            "&dropoff_point=".$dropoff_point."&remarks=".$remarks."&option_code=".$option_code."";
        $station = WheelsService::services($post_string);
        return $station;
    }

    /**
     * Get cancel Reservation  details
     *
     * This function is used to cancel reservation of the vehicles from the Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param array $data is used to get refno,irn
     *
     * @return json string
     **/

    public function cancelReservation($data)
    {
        $irn =  $data['irn'];
        $refno = $data['refno'];
        $post_string = "cancel-res_AUF72F23.html?agent=INV123456&refno=".$refno."&irn=".$irn."";
        $station = WheelsService::services($post_string);
        return $station;
    }

    /**
     * Get Reservation details
     *
     * This function is used to get reservation  details of reserved vehicle from the Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param array $data is used to get refno,irn
     *
     * @return json string
     **/

    public function readReservation($data)
    {
        $irn =  $data['irn'];
        $refno = $data['refno'];
        $post_string = "read-res_AUF72F23.html?agent=INV123456&refno=".$refno."&irn=".$irn."";
        $reservation = WheelsService::services($post_string);
        return $reservation;
    }

    /**
     * Get vehicle options
     *
     * This function is used to get all optiona which the vehicle has from the Wheelsys API
     * @api Wheelsys API
     * @filesource Services.class.php
     *
     * @param NULL
     *
     * @return void
     **/

    public function option()
    {
        $post_string = "options_AUF72F23.html?agent=INV123456";
        $station = WheelsService::services($post_string);
    }
}
