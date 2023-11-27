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
        return $this->doAPI($ip, $token, 'gestalt');
    }

    /**
     * doVersion - Get firmware version.
     *
     * HTTP request
     * GET /xled/v1/fw/version
     *
     * The response will be an object:
     *  version             - (string) firmware_version
     *  code                - Application return code.
     */
    private function doVersion($ip)
    {
        return $this->doAPI($ip, null, 'fw/version');
    }

    /**
     * doNetwork - Gets network mode operation..
     *
     * HTTP request
     * GET /xled/v1/network/status
     *
     * The response will be an object:
     *  mode                - (enum) 1 or 2
     *  station             - (object)
     *  ap                  - (object)
     *  code                - Application return code.
     *
     *  Contents of object station for firmware family “G” since firmware version 2.4.21 and “F” since 2.2.1:
     *    ssid              - (string), SSID of a WiFi network to connect to. If empty string is passed it defaults to prefix ESP_ instead of Twinkly_.
     *    ip                - (string), IP address of the device
     *    gw                - (string), IP address of the gateway
     *    mask              - (string), subnet mask
     *  Contents of object ap:
     *    ssid              - (string), SSID of the device
     *    channel           - (integer), channel number
     *    ip                - (string), IP address
     *    enc               - (enum), 0 for no encryption, 2 for WPA1, 3 for WPA2, 4 for WPA1+WPA2
     *    ssid_hidden       - (integer), default 0. Since firmware version 2.4.25.
     */
    private function doNetwork($ip, $token)
    {
        return $this->doAPI($ip, $token, 'network/status');
    }

    /**
     * doName - Get/Set the device name.
     *
     * HTTP request
     * GET /xled/v1/device_name
     *
     * The response will be an object:
     *  name    - (string) Device name.
     *  code    - Application return code.
     *
     * HTTP request
     * POST /xled/v1/device_name
     *
     * Parameters as JSON object:
     *  name    - (string) Desired device name. At most 32 characters.
     *
     * The response will be an object:
     *  code    - Application return code. 1103 if too long.
     */
    private function doName($ip, $token, $body = null)
    {
        return $this->doAPI($ip, $token, 'device_name', $body);
    }

    /**
     * doEffect - Get/Set current LED effect
     *
     * HTTP request
     * GET /xled/v1/led/effects/current
     *
     * The response will be an object:
     * code         - (integer), application return code.
     * unique_id    - (string), UUID. Since firmware version 2.5.6.
     * effect_id    - (integer), e.g. 0
     *
     * HTTP request
     * POST /xled/v1/led/effects/current
     *
     * Parameters as JSON object:
     *  effect_id   - (int), id of effect, e.g. 0.
     *
     * The response will be an object:
     *  code        - Application return code.
     */
    private function doEffect($ip, $token, $body = null)
    {
        return $this->doAPI($ip, $token, 'led/effects/current', $body);
    }

    /**
     * doEffects - Get number of LED effects
     *
     * HTTP request
     * GET /xled/v1/led/effects
     *
     * The response will be an object:
     *  effects_number  - (integer), e.g. 5
     *  unique_ids      - (array) of guid's. e.g. 00000000-0000-0000-0000-000000000001
     *  code            - Application return code.
     */
    private function doEffects($ip, $token)
    {
        return $this->doAPI($ip, $token, 'led/effects', null);
    }

    /**
     * doMovie - Gets/Sets the id of the movie shown when in movie mode.
     *
     * HTTP request
     * GET /xled/v1/led/movies/current
     *
     * The response will be an object.
     *  code            - (integer), application return code.
     *  id              - (integer), numeric id of movie, in range 0 .. 15
     *  unique_id       - (string), UUID of movie.
     *  name            - (string), name of movie.
     *
     * HTTP request
     * POST /xled/v1/led/movies/current
     *
     * Parameters as JSON object.
     *  id              - (int), id of movie, in range 0 .. 15.
     *
     * The response will be an object.
     *  code            - (integer), application return code.
     *
     */
    private function doMovie($ip, $token, $body = null)
    {
        return $this->doAPI($ip, $token, 'movies/current', $body);
    }

    /**
     * doMovies - Get list of movies
     *
     * HTTP request
     * GET /xled/v1/movies
     *
     * The response will be an object.
     *  code            - Application return code.
     *  movies          - Array of objects
     *  available_frames- (integer), e.g. 992
     *  max_capacity    - (integer), e.g. 992
     *
     * Where each item of movies is an object.
     *  id              - (integer), e.g. 0
     *  name            - (string)
     *  unique_id       - (string) UUID
     *  descriptor_type - (string), e.g “rgbw_raw” for firmware family “G” or “rgb_raw” for firmware family “F”
     *  leds_per_frame  - (integer), e.g. 210
     *  frames_number   - (integer), e.g. 4
     *  fps             - (integer), e.g. 0
     */
    private function doMovies($ip, $token)
    {
        return $this->doAPI($ip, $token, 'movies', null);
    }

    /**
     * doMode - Get/Set LED operation mode.
     *
     * HTTP request
     * GET /xled/v1/led/mode
     *
     * The response will be an object:
     *   code        - (integer), application return code.
     *   mode        - (string) mode of operation.
     *   shop_mode   - (integer), by default 0. Since firmware version 2.4.21.
     *
     * HTTP request
     * POST /xled/v1/led/mode
     *
     * Parameters as JSON object:
     *  mode    - (string) mode of operation.
     *            Mode can be one of:
     *              off - turns off lights
     *              color - lights show a static color
     *              demo - starts predefined sequence of effects that are changed after few seconds
     *              effect - plays a predefined effect
     *              movie - plays predefined or uploaded effect
     *              playlist - cycles through playlist of uploaded movies
     *              rt - receive effect in real time
     *
     * The response will be an object:
     *  code    - Application return code.
     */
    private function doMode($ip, $token, $body = null)
    {
        return $this->doAPI($ip, $token, 'led/mode', $body);
    }

    /**
     * doColor - Get/Set the color shown when in color mode.
     *
     * HTTP request
     * GET /xled/v1/led/color
     *
     * The response will be an object.
     *  hue         - (integer), hue component of HSV, in range 0..359
     *  saturation  - (integer), saturation component of HSV, in range 0..255
     *  value       - (integer), value component of HSV, in range 0..255
     *  red         - (integer), red component of RGB, in range 0..255
     *  green       - (integer), green component of RGB, in range 0..255
     *  blue        - (integer), blue component of RGB, in range 0..255
     *  code        - (integer), application return code.
     *
     * HTTP request
     * POST /xled/v1/led/color
     *
     * Parameters as JSON object:
     *
     * Either the three HSV components:
     *  hue         - (integer), hue component of HSV, in range 0..359
     *  saturation  - (integer), saturation component of HSV, in range 0..255
     *  value       - (integer), value component of HSV, in range 0..255
     *
     * Or the three RGB components:
     *
     *  red         - (integer), red component of RGB, in range 0..255
     *  green       - (integer), green component of RGB, in range 0..255
     *  blue        - (integer), blue component of RGB, in range 0..255
     *
     * The response will be an object:
     *  code    - Application return code.
     */
    private function doColor($ip, $token, $body = null)
    {
        return $this->doAPI($ip, $token, 'led/color', $body);
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
        return $this->doAPI($ip, $token, 'led/out/brightness', $body);
    }

    /**
     * doSaturation - Get/Set tthe current saturation level.
     *
     * HTTP request
     * GET /xled/v1/led/out/saturation
     *
     * The response will be an object:
     *  mode    - (string) one of “enabled”, “disabled”.
     *  value   - (integer) saturation level in range of 0..100
     *  code    - Application return code.
     *
     * HTTP request
     * POST /xled/v1/led/out/saturation
     *
     * Parameters as JSON object:
     *  mode    - (string) one of “enabled”, “disabled”.
     *  type    - (string) either “A” for Absolute value or “R” for Relative value
     *  value   - (signed integer) saturation level in range of 0..100 if type is “A”, or change of level in range -100..100 if type is “R”
     *
     * When mode is “disabled” no desaturation is applied and the led works at full color. It is not necessary to submit all the parameters,
     * basically it would work if only value or mode is supplied. type parameter can be omitted (“A” is the default).
     * The saturation level value is in percent so 0 is completely black-and-white and maximum meaningful value is 100.
     * Greater values are possible but don’t seem to have any effect.
     *
     * The response will be an object:
     *  code    - Application return code.
     */
    private function doSaturation($ip, $token, $body = null)
    {
        return $this->doAPI($ip, $token, 'led/out/saturation', $body);
    }

    /**
     * doTimer - Get/Set time when lights should be turned on and time to turn them off.
     *
     * HTTP request
     * GET /xled/v1/led/timer
     *
     * The response will be an object:
     *  time_now - (integer) current time in seconds after midnight.
     *  time_on  - (number) time when to turn lights on in seconds after midnight. -1 if not set.
     *  time_off - (number) time when to turn lights off in seconds after midnight. -1 if not set.
     *  code     - Application return code.
     *
     * HTTP request
     * POST /xled/v1/led/timer
     *
     * Parameters as JSON object:
     *  time_now - (integer) current time in seconds after midnight.
     *  time_on  - (number) time when to turn lights on in seconds after midnight. -1 if not set.
     *  time_off - (number) time when to turn lights off in seconds after midnight. -1 if not set.
     *
     * The response will be an object:
     *  code    - Application return code.
     */
    private function doTimer($ip, $token, $body = null)
    {
        return $this->doAPI($ip, $token, 'timer', $body);
    }

    /**
     * doAPI - Get/Set device data.
     *
     * $ip      - IP of the device
     * $tocken  - valid authenticaed tocken
     * $path    - API url path
     * body     - request parameter to post
     *
     * returns JSON data, otherwise false.
     */
    private function doAPI($ip, $token, $path, $body = null)
    {
        $url = "http://$ip/xled/v1/" . $path;
        $this->SendDebug(__FUNCTION__, $url, 0);
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
        // prepeare header
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . (($request == null) ? 0 : strlen($request)),
        ];
        // prepeare token
        if ($token != null) {
            $headers[] = 'X-Auth-Token: ' . $token;
        }
        // prepeare curl call
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
        // evaluate response
        if ($response != null) {
            $ret = true;
            $json = json_decode($response, true);
            if (isset($json['code'])) {
                if ($json['code'] != 1000) {
                    $error = sprintf('Request failed: (%d) - URL: %s - Request: %s', $json['code'], $url, $request);
                    $this->SendDebug(__FUNCTION__, $error, 0);
                    $ret = false;
                }
            } else {
                $error = sprintf('Request failed for URL: %s - Response: %s', $url, $response);
                $this->SendDebug(__FUNCTION__, $error, 0);
                $ret = false;
            }
        }
        // return result
        return $ret;
    }
}
