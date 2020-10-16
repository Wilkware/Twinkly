<?php

/**
 * TwinklyHelper.php
 *
 * PHP Wrapper for Twinkly API Calls
 * https://xled-docs.readthedocs.io/en/latest/index.html
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2020 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * ------------------------------ API Overview ----------------------------------
 *
 *   POST /xled/v1/login
 *       => Request access token.
 *   POST /xled/v1/verify
 *       => Verify the token retrieved by Login.
 *   GET /xled/v1/gestalt
 *       => Gets information detailed information about the device.
 *   GET /xled/v1/device_name
 *   POST /xled/v1/device_name
 *       => Gets and sets the device name
 *   POST /xled/v1/logout
 *       => Probably invalidate access token. Doesn’t work.
 *   POST /xled/v1/network/status
 *       => Sets network mode operation.
 *   GET /xled/v1/timer
 *   POST /xled/v1/timer
 *       => Sets time when lights should be turned on and time to turn them off.
 *   POST /xled/v1/led/mode
 *       => Changes LED operation mode.
 *   POST /xled/v1/led/movie/full
 *       => Effect is received in body of the request with Content-Type application/octet-stream.
 *          If mode is movie it starts playing this effect.
 *   POST /xled/v1/led/movie/config
 *       => Set LED movie config
 *   GET /xled/v1/fw/version
 *       => Get firmware version. Note: no authentication needed.
 *   POST /xled/v1/fw/update
 *       => Probably initiates firmware update.
 *
 * ---------------------------- Daten Strukturen --------------------------------
 *
 * => Request:
 *       GET /xled/v1/device_name HTTP/1.1
 *       Host: 192.168.4.1
 *       X-Auth-Token: 5jPe+ONhwUY=
 *
 * => Response:
 *       HTTP/1.1 200 Ok
 *       Connection: close
 *       Content-Length: 37
 *       Content-Type: application/json
 *       {"name":"Twinkly_33AAFF","code":1000}
 *
 */

declare(strict_types=1);

/**
 * Helper class for the debug output.
 */
trait TwinklyHelper
{
    /**
     * doLogin - Request access token.
     *
     * HTTP request
     * POST /xled/v1/login
     *
     * Parameters as JSON object:
     *  challenge   -   Random 32 byte string encoded with base64.
     *
     * The response will be an object:
     *  authentication_token            - Access token in format: 8 byte string base64 encoded.
     *                                    First authenticated API with this token must be Verify.
     *  challenge-response              - 41 byte string ([0-9a-h])
     *  code                            - Application return code.
     *  authentication_token_expires_in - integer. All the time 14400
     */
    private function doLogin($ip)
    {
        $url = "http://$ip/xled/v1/login";

        $rand = random_bytes(32);
        $base64 = base64_encode($rand);
        $base64 = utf8_decode($base64);

        $login = ['challenge' => $base64];
        $request = json_encode($login);
        $response = json_encode([]);

        $err = $this->doRequest($url, null, $request, $response);

        if ($err) {
            $json = json_decode($response, true);
        }

        return $err ? $json : $err;
    }

    /**
     * doVerify - Verify the token retrieved by Login.
     *
     * HTTP request
     * POST /xled/v1/verify
     *
     * Parameters as JSON object:
     *  challenge-response              - (optional) value returned by login request.
     *
     * The response will be an object:
     *  code                            -  Application return code.
     */
    private function doVerify($ip, $token, $challange)
    {
        $url = "http://$ip/xled/v1/verify";

        $verify = ['challenge-response' => $challange];
        $request = json_encode($verify);
        $response = json_encode([]);

        $err = $this->doRequest($url, $token, $request, $response);

        if ($err) {
            $json = json_decode($response, true);
        }

        return $err ? $json : $err;
    }

    /**
     * doGestalt - Gets information detailed information about the device.
     *
     * HTTP request
     * GET /xled/v1/gestalt
     *
     * The response will be an object:
     *  product_name        - (string) Twinkly product_version (numeric string), e.g. “2”
     *  hardware_version    - (numeric string), e.g. “6”
     *  flash_size          - (number), e.g. 16
     *  led_type            - (number), e.g. 6
     *  led_version         - (string) “1”
     *  product_code        - (string), e.g. “TW105SEUP06”
     *  device_name         - (string), by default consists of Twinkly_ prefix and uppercased hw_id (see bellow)
     *  rssi                - (number), Received signal strength indication. Since firmware version: 2.1.0.
     *  uptime              - (string) number as a string, e.g. “60”
     *  hw_id               - (string), right three bytes of mac address encoded as hexadecimal digits prefixed with 00.
     *  mac                 - (string) MAC address as six groups of two hexadecimal digits separated by colons (:).
     *  uuid                - (string) UUID of the device. Since firmware version: 2.0.22-mqtt.
     *  max_supported_led   - (number), e.g. 180
     *  base_leds_number    - (number), e.g. 105
     *  number_of_led       - (number), e.g. 105
     *  led_profile         - (string) “RGB”
     *  frame_rate          - (number), 25
     *  movie_capacity      - (number), e.g. 719
     *  copyright           - (string) “LEDWORKS 2017”
     *  code                - Application return code.
     */
    private function doGestalt($ip, $token)
    {
        $url = "http://$ip/xled/v1/gestalt";

        $response = json_encode([]);

        $err = $this->doRequest($url, $token, null, $response);

        if ($err) {
            $json = json_decode($response, true);
        }

        return $err ? $json : $err;
    }

    /**
     * doVersion - Get firmware version.
     *
     * HTTP request
     * GET /xled/v1/fw/version
     *
     * The response will be an object.
     *
     * The response will be an object:
     *  version             - (string) firmware_version
     *  code                - Application return code.
     */
    private function doVersion($ip)
    {
        $url = "http://$ip/xled/v1/fw/version";

        $response = json_encode([]);

        $err = $this->doRequest($url, null, null, $response);

        if ($err) {
            $json = json_decode($response, true);
        }

        return $err ? $json : $err;
    }

    /**
     * doMode - Changes LED operation mode.
     *
     * HTTP request
     * POST /xled/v1/led/mode
     *
     * Parameters as JSON object:
     *  mode    - (string) mode of operation.
     *            Mode can be one of:
     *              off - turns off lights
     *              demo - starts predefined sequence of effects that are changed after few seconds
     *              movie - plays predefined or uploaded effect
     *              rt - receive effect in real time
     *
     * The response will be an object:
     *  code    - Application return code.
     */
    private function doMode($ip, $token, $mode = null)
    {
        $url = "http://$ip/xled/v1/led/mode";

        $response = json_encode([]);
        if ($mode == null) {
            $err = $this->doRequest($url, $token, null, $response);
        } else {
            $request = json_encode($mode);
            $err = $this->doRequest($url, $token, $request, $response);
        }

        if ($err) {
            $json = json_decode($response, true);
        }

        return $err ? $json : $err;
    }

    /**
     * doBrightness - Get/Set the current brightness level.
     *
     * HTTP request
     * GET /xled/v1/led/out/brightness
     *
     * The response will be an object:
     *  mode    - (string) one of “enabled”, “disabled”.
     *  value   - (integer) brighness level in range of 0..100
     *  code    - Application return code.
     *
     * HTTP request
     * POST /xled/v1/led/out/brightness
     *
     * Parameters as JSON object:
     *  mode    - (string) one of “enabled”, “disabled”.
     *  type    - (string) always “A”
     *  value   - (integer) brighness level in range of 0..255
     *
     * The response will be an object:
     *  code    - Application return code.
     */
    private function doBrightness($ip, $token, $body = null)
    {
        $url = "http://$ip/xled/v1/led/out/brightness";

        $response = json_encode([]);
        if ($body == null) {
            $err = $this->doRequest($url, $token, null, $response);
        } else {
            $request = json_encode($body);
            $err = $this->doRequest($url, $token, $request, $response);
        }

        if ($err) {
            $json = json_decode($response, true);
        }

        return $err ? $json : $err;
    }

    /*
     * doRequest - Sends the request to the device
     *
     * If $request not null, we will send a POST request, else a GET request.
     * Over the $method parameter can we force a POST or GET request!
     */
    private function doRequest($url, $token, $request, &$response, $method = 'GET')
    {
        $ret = false;

        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . (($request == null) ? 0 : strlen($request)),
        ];

        if ($token != null) {
            $headers[] = 'X-Auth-Token: ' . $token;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($request != null) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response != null) {
            $ret = true;
            $json = json_decode($response, true);
            if (isset($json['code']) && $json['code'] != 1000) {
                $error = sprintf('Request failed: (%d) - URL: %s - Request: %s', $json['code'], $url, $request);
                $this->SendDebug('doRequest', $error, 0);
                $ret = false;
            }
        }

        return $ret;
    }
}
