<?php

namespace webmaster\models\api;

use Yii;
use yii\db\ActiveRecord;

class ApiScript extends ActiveRecord
{
    const ACTIVE_API_SCRIPT_STATUS = 1;
    const INACTIVE_API_SCRIPT_STATUS = 0;

    public static function tableName()
    {
        return '{{wm_api_scripts}}';
    }

    public static function getContextPHPScript($data)
    {
        return '<?php
            $currentUrl = getPageUrl();
            function getPageUrl(){
                if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"){
                    $url = "https://";
                } else {
                    $url = "http://";
                }

                $url.= $_SERVER["HTTP_HOST"];
                $url.= $_SERVER["REQUEST_URI"];

                return $url;
            }

            function getParameterByUrl($currentUrl, $param){
                $parts = parse_url($currentUrl);
                parse_str($parts["query"], $query);

                if(isset($query[$param])){
                    return $query[$param];
                } else {
                    return null;
                }
            }
                
                $url = "http://back.w.crmka.net/api/orders/create-order";
                $user_agent = $_SERVER["HTTP_USER_AGENT"];
                $ch = curl_init();
                $data = array(
                "name" => $_POST["name"],
                "phone" => $_POST["phone"],
                "api-key" => "' . $data["api_key"] . '",
                "flow-key" => "' . $data["flow_key"] . '",
                "geoiso" => "' . $data["geo"] . '",
                "address" => isset($_POST["address"]) ? $_POST["address"] : "",
                "user_agent"    => $user_agent,
                "userIP" => $_SERVER["REMOTE_ADDR"],
                "subid1" => getParameterByUrl($currentUrl, "subid1"),
                "subid2" => getParameterByUrl($currentUrl, "subid2"),
                "subid3" => getParameterByUrl($currentUrl, "subid3"),
                "subid4" => getParameterByUrl($currentUrl, "subid4"),
                "subid5" => getParameterByUrl($currentUrl, "subid5"),
                "url" => $currentUrl,
            );
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $response = array(
                "error" => $curl_error,
                "errno" => $curl_errno,
                "http_code" => $http_code,
                "result" => $result,
            );

            if ($response["http_code"] == 200 && $response["errno"] === 0) {
                header("Location: /thanks");
            } else {
                var_dump($response);
                //header("Location: /");
            }
    ';

        //         session_start();
        //         $_SESSION['data'] = $data;
        //         $_SESSION['lead_id'] = $resultOk['lead_id'];

        //         $trackingParam = '';

        //         if (!is_null($_GET['pixel'])) {
        //             $trackingParam = '?pixel=' . $_GET['pixel'];
        //         } elseif (!is_null($_GET['tiktok'])) {
        //             $trackingParam = '?tiktok=' . $_GET['tiktok'];
        //         }

        //         header('Location: success.php' . $trackingParam);
        //     } else {
        //         if (!empty($response['result'])) {
        //         $result = json_decode($response['result']);
        //         throw new Exception($result->error);
        //         } else {
        //             throw new Exception('HTTP request error. ' . $response['error']);
        //         }
        //     }
        // } catch (Exception $e) {
        //     echo $e->getMessage();
        // }";
    }

    public static function getContextJSScript($data)
    {
        return '

function getLandData() {
    const flow_key = "' . $data["flow_key"] . '"
    const geo_iso = "' . $data["geo"] . '"

    httpRequest(
        "http://back.w.crmka.net/api/api-script/offer-info?flow_key=" + flow_key + "&geo_iso=" + geo_iso,
        "GET",
        resp => {
            initLanding(JSON.parse(resp.response).data)
    });
}

function httpRequest(address, reqType, asyncProc) {
    let req = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    if (asyncProc) {
        req.onreadystatechange = function() {
            if (this.readyState === 4) {
                asyncProc(this);
            }
        };
    } else {
        req.timeout = 4000;  // Reduce default 2mn-like timeout to 4 s if synchronous
    }
    req.open(reqType, address, !(!asyncProc));
    req.send();
    return req;
}

function initLanding(landData) {
    console.log(landData)
    let forms = document.getElementsByClassName("form_block")
    var queryStr = window.location.search
    var currentRequestModify = "api.php"

    for (let form of forms) {
        form.getElementsByClassName("adfh-discount")[0].innerText = landData.discount
        form.getElementsByClassName("adfh-old-price")[0].innerText = landData.old_price
        form.getElementsByClassName("adfh-new-price")[0].innerText = landData.new_price
        let phone_num_count = document.createElement("input")
        phone_num_count.type = "hidden"
        phone_num_count.name = "phone_num_count"
        phone_num_count.value = landData.phone_num_count
        form.getElementsByClassName("orderformcdn")[0].appendChild(phone_num_count)
        form.getElementsByClassName("orderformcdn")[0].action = "api.php" + window.location.search
        for (let currency of form.getElementsByClassName("adfh-currency")){
            currency.innerText = landData.currency
        }
    }
}
        ';
    }

    public function rules()
    {
        return [
            [['api-key', 'wm_id', 'flow_id', 'flow_name', 'geo_iso', 'active'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Script ID'),
            'api_key' => Yii::t('app', 'User Api Key'),
            'wm_id' => Yii::t('app', 'User ID'),
            'flow_id' => Yii::t('app', 'Flow ID'),
            'flow_name' => Yii::t('app', 'Flow name'),
            'geo_iso' => Yii::t('app', 'Geo ISO'),
            'active' => Yii::t('app', 'is Active'),
        ];
    }
}