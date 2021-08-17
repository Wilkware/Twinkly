<?php

declare(strict_types=1);

// Generell funktions
require_once __DIR__ . '/../libs/_traits.php';

// CLASS PresenceDetector
class TwinklyDevice extends IPSModule
{
    // Helper traits
    use DebugHelper;
    use ProfileHelper;
    use TwinklyHelper;

    // Token constant
    const TOKEN_LIFETIME = 14400;

    // Profil array
    private $assoMODE = [
        [0, 'Aus', '', 0xFF0000, 'off'],
        [1, 'An', '', 0x00FF00, 'movie'],
        [2, 'Demo', '', 0xFF8000, 'demo'],
        [3, 'Echtzeit', '', 0x00FFFF, 'rt'],
    ];
    private $assoSWITCH = [
        [0, 'Aus', 'Light-0', -1, 'off'],
        [1, 'An', 'Light-100', 0x00FF00, 'movie'],
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Host', '127.0.0.1');
        $this->RegisterPropertyBoolean('Switch', false);
        $this->RegisterPropertyBoolean('TimerCheck', false);
        $this->RegisterPropertyString('TimerOn', '{"hour": 15,"minute": 0,"second": 0}');
        $this->RegisterPropertyString('TimerOff', '{"hour": 23,"minute": 0,"second": 0}');

        // Attributes for Login
        $this->RegisterAttributeString('Token', '');
        $this->RegisterAttributeInteger('Validate', 0);

        // Variablen Profile einrichten
        $this->RegisterProfile(VARIABLETYPE_INTEGER, 'Twinkly.Mode', 'Remote', '', '', 0, 0, 0, 0, $this->assoMODE);
        $this->RegisterProfile(VARIABLETYPE_INTEGER, 'Twinkly.Switch', 'Light', '', '', 0, 0, 0, 0, $this->assoSWITCH);

        // Variablen erzeugen
        $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), 'Twinkly.Mode', 0);
        $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), '~Intensity.100', 0);
        // Actions
        $this->EnableAction('Mode');
        $this->EnableAction('Brightness');
    }

    /**
     * Configuration Form.
     *
     * @return JSON configuration string.
     */
    public function GetConfigurationForm()
    {
        $alias = $this->GetDeviceName();
        $timer = $this->GetTimer();
        // Debug output
        $this->SendDebug(__FUNCTION__, 'Load device name: ' . $alias, 0);
        $this->SendDebug(__FUNCTION__, $timer, 0);
        // Get Form
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        // Timer
        if(!empty($timer)) {
            $form['elements'][3]['items'][0]['items'][2]['value'] = $timer['on'];
            $form['elements'][3]['items'][0]['items'][4]['value'] = $timer['off'];
        } else {
            $form['elements'][3]['items'][0]['items'][0]['value'] = false;
            $form['elements'][3]['items'][0]['items'][1]['enabled'] = false;
            $form['elements'][3]['items'][0]['items'][2]['enabled'] = false;
            $form['elements'][3]['items'][0]['items'][3]['enabled'] = false;
            $form['elements'][3]['items'][0]['items'][4]['enabled'] = false;
        }
        // Device Name (alias)
        $form['actions'][5]['items'][0]['value'] = $alias;
        // Debug output
        //$this->SendDebug(__FUNCTION__, $form);
        return json_encode($form);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        // IP Check
        $host = $this->ReadPropertyString('Host');
        $switch = $this->ReadPropertyBoolean('Switch');
        // IP Check
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $this->SetStatus(102);
        } else {
            $this->SetStatus(201);
        }
        // Timer 
        $this->SetTimer();
        // Aditionally Switch
        $this->MaintainVariable('Switch', $this->Translate('Switch'), VARIABLETYPE_INTEGER, 'Twinkly.Switch', 0, $switch);
        if ($switch) {
            $this->EnableAction('Switch');
        }
        // Debug message
        $this->SendDebug(__FUNCTION__, 'IP=' . $host, 0);
    }

    /**
     * RequestAction - SDK function.
     *
     * @param string $ident Variable identifier
     * @param int $value New value
     */
    public function RequestAction($ident, $value)
    {
        $this->SendDebug(__FUNCTION__, 'Ident: ' . $ident . ' Value: ' . $value, 0);
        switch ($ident) {
            case 'TimingCheck':
                $this->SendDebug(__FUNCTION__, 'New mode selected: ' . $value, 0);
                $this->OnTimingCheck($value);
                break;
            case 'TimingNow':
                $this->SendDebug(__FUNCTION__, 'New mode selected: ' . $value, 0);
                $this->OnTimingNow($value);
                break;
            case 'Switch':
            case 'Mode':
                $this->SendDebug(__FUNCTION__, 'New mode selected: ' . $value, 0);
                $this->SetMode($value);
                $this->SetValueInteger($ident, $value);
                break;
            case 'Brightness':
                $this->SendDebug(__FUNCTION__, 'Brightness changed: ' . $value, 0);
                $this->SetBrightness($value);
                $this->SetValueInteger($ident, $value);
                break;
            default:
            throw new Exception('Invalid Ident');
        }
    }

    /**
     * Sets the device mode.
     * 
     * @param int $value Mode value.
     */
    private function SetMode(int $value)
    {
        // Safty check
        if ($value < 0 || $value > 3) {
            // out of range
            $this->SendDebug(__FUNCTION__, 'Out of range' . $value, 0);
            return;
        }
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!', 0);
            return;
        }
        // Extract assoziated mode string
        $mode = $this->assoMODE[$value][4];
        $this->SendDebug(__FUNCTION__, 'Selected mode: ' . $mode, 0);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Mode
        $set = ['mode' => $mode];
        // Request
        $this->doMode($host, $token, $set);
    }

    /**
     * Sets the brightness level.
     *
     * @param int $value Brightness value.
     */
    private function SetBrightness(int $value)
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!', 0);
            return;
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Set brightness to: ' . $value, 0);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Brightness
        $body = [
            'mode'   => 'enabled',
            'value'  => $value,
        ];
        // Request
        $this->doBrightness($host, $token, $body);
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Brightness($id);
     */
    public function Brightness()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain device brightness.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doBrightness($host, $token);
        // Sync brightness
        if ($json !== false) {
            SetValue($this->GetIDForIdent('Brightness'), $json['value']);
            // Display value
            return $this->Translate('Brightness: ') . $json['value'] . '%';
        }
        return $this->Translate('Error occurred!');
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Gestalt();
     */
    public function Gestalt()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain device information.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doGestalt($host, $token);
        $this->SendDebug(__FUNCTION__, $json);

        return sprintf(
            "Product name: %s\nHardware Version: %s\nBytes per LED: %d\nHardware ID: %s\nFlash Size: %d\nLED Type: %d\nProduct Code: %s\nFirmware Family: %s\nDevice Name: %s\nUptime: %s ms\nMAC: %s\nUUID: %s\nMax supported LED: %d\nNumber of LED: %d\nLED Profile: %s\nFrame Rate: %.2f\nMeasured Frame Rate: %.2f\nMovie Capacity: %d\nCopyright: %s",
            $json['product_name'],
            $json['hardware_version'],
            $json['bytes_per_led'],
            $json['hw_id'],
            $json['flash_size'],
            $json['led_type'],
            $json['product_code'],
            $json['fw_family'],
            $json['device_name'],
            $json['uptime'],
            $json['mac'],
            $json['uuid'],
            $json['max_supported_led'],
            $json['number_of_led'],
            $json['led_profile'],
            $json['measured_frame_rate'],
            $json['frame_rate'],
            $json['movie_capacity'],
            $json['copyright']
        );
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Version();
     */
    public function Version()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain firmware version.', 0);
        // only Host
        $host = $this->ReadPropertyString('Host');
        // Request
        $json = $this->doVersion($host);

        return $this->Translate('Firmware: ') . $json['version'];
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Network();
     */
    public function Network()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain device information.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doNetwork($host, $token);
        $this->SendDebug(__FUNCTION__, $json);

        $enc = [0 => "NONE", 2 => 'WPA1', 3 => 'WPA2', 4 => 'WPA1+WPA2'];

        return sprintf(
            "Network mode: %s\nStation:\n\tSSID: %s\n\tIP: %s\n\tGateway: %s\n\tMask: %s\n\tRSSI: %d db\nAccess Point:\n\tSSID: %s\n\tIP: %s\n\tChannel: %s\n\tEncryption: %s\n\tSSID Hidden: %s\n\tMax connections: %d",
            $json['mode'] == 1?"1 (Station)":"2 (Access Point)",
            $json['station']['ssid'],
            $json['station']['ip'],
            $json['station']['gw'],
            $json['station']['mask'],
            $json['station']['rssi'],
            $json['ap']['ssid'],
            $json['ap']['ip'],
            $json['ap']['channel'],
            $enc[$json['ap']['enc']],
            $json['ap']['ssid_hidden']?"true":"false",
            $json['ap']['max_connections']
        );
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_DeviceName();
     */
    public function DeviceName(string $value)
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Set device name to: ' . $value, 0);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Brightness
        $body = [
            'name'  => $value,
        ];
        // Request
        $json = $this->doName($host, $token, $body);
        if($json === false) {
            return $this->Translate("Name could not be changed!");
        }
        else {
            return $this->Translate("Name was changed successfully!");
        }
    }

    /**
     * Validate the token and login to renew it.
     * 
     * @return bool true if successful, otherwise false.
     */
    private function CheckLogin()
    {
        // $last =  $this->ReadAttributeInteger('Validate');
        $now = time();
        //if($now - $livetime > $last) {
        if (true) {
            // Timestamp
            $this->WriteAttributeInteger('Validate', $now);
            // Host
            $host = $this->ReadPropertyString('Host');
            // Debug
            $this->SendDebug(__FUNCTION__, 'Login to host: ' . $host, 0);
            // Login
            $challange = $this->doLogin($host);
            // Check
            if ($challange === false) {
                $this->SendDebug(__FUNCTION__, 'Login failed!', 0);
                return false;
            }
            // Validate
            $token = $challange['authentication_token'];
            $response = $challange['challenge-response'];
            // Check
            if ($this->doVerify($host, $token, $response) === false) {
                $this->SendDebug(__FUNCTION__, 'Verify failed!', 0);
                return false;
            }
            // Token
            $this->WriteAttributeString('Token', $token);
        }
        return true;
    }

    /**
     * Gets device name.
     *
     * @return string Current device name.
     */
    private function GetDeviceName()
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!', 0);
            return '';
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doName($host, $token);
        if($json === false) {
            return '';
        }
        return $json['name'];
    }

    /**
     * Gets timer information.
     *
     * @return string Timer settings.
     */
    private function GetTimer()
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!', 0);
            return '';
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doTimer($host, $token);
        if ($json === false) {
            return [];
        }
        if ($json['time_on'] == -1 || $json['time_off'] == -1) {
            return [];
        }
        $hours = floor($json['time_on'] / 3600);
        $mins  = floor($json['time_on'] / 60 % 60);
        $secs  = floor($json['time_on'] % 60);
        $on = sprintf('{"hour": %d,"minute": %d,"second": %d}', $hours, $mins, $secs);
        $hours = floor($json['time_off'] / 3600);
        $mins  = floor($json['time_off'] / 60 % 60);
        $secs  = floor($json['time_off'] % 60);
        $off = sprintf('{"hour": %d,"minute": %d,"second": %d}', $hours, $mins, $secs);
        return ['on' => $on, 'off' => $off];
    }

    /**
     * Sets timer information.
     *
     * @return string Timer settings.
     */
    private function SetTimer()
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!', 0);
            return '';
        }
        // Timer
        $timer = $this->ReadPropertyBoolean('TimerCheck');
        $on = -1;
        $off = -1;
        if($timer) {
            $time = $this->ReadPropertyString('TimerOn');
            $json = json_decode($time, true);
            $on = ($json['hour'] * 3600) + ($json['minute'] * 60) + ($json['second']);
            $this->SendDebug(__FUNCTION__, $on, 0);
            $time = $this->ReadPropertyString('TimerOff');
            $json = json_decode($time, true);
            $off = ($json['hour'] * 3600) + ($json['minute'] * 60) + ($json['second']);
            $this->SendDebug(__FUNCTION__, $off, 0);
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Timer
        $body = [
            'time_now'  => time() - strtotime("today"),
            'time_on'  => $on,
            'time_off'  => $off,
        ];
       // Request
        $json = $this->doTimer($host, $token, $body);
    }

     /**
     * User has switch timing check box.
     *
     * @param bool $value check value.
     */
    private function OnTimingCheck(bool $value)
    {
        $this->SendDebug(__FUNCTION__, 'Value: ' . $value);
        $this->UpdateFormField('TimerOn', 'enabled', $value);
        $this->UpdateFormField('TimerOff', 'enabled', $value);
        $this->UpdateFormField('TimerNowOn', 'enabled', $value);
        $this->UpdateFormField('TimerNowOff', 'enabled', $value);
    }   

     /**
     * User has click on NOW button.
     *
     * @param string $value ON or OFF.
     */
    private function OnTimingNow(string $value)
    {
        $this->SendDebug(__FUNCTION__, 'Value: ' . $value);
        $ts = time();
        $h = intval(date('H', $ts));
        $m = intval(date('i', $ts));
        $s = intval(date('s', $ts));
       $this->UpdateFormField('Timer' . $value, 'value', '{"hour":' . $h . ',"minute":' .$m .',"second":' . $s . '}');
    }   

    /**
     * Update a boolean value.
     *
     * @param string $ident Ident of the boolean variable
     * @param bool   $value Value of the boolean variable
     */
    private function SetValueBoolean(string $ident, bool $value)
    {
        $id = $this->GetIDForIdent($ident);
        SetValueBoolean($id, $value);
    }

    /**
     * Update a string value.
     *
     * @param string $ident Ident of the string variable
     * @param string $value Value of the string variable
     */
    private function SetValueString(string $ident, string $value)
    {
        $id = $this->GetIDForIdent($ident);
        SetValueString($id, $value);
    }

    /**
     * Update a integer value.
     *
     * @param string $ident Ident of the integer variable
     * @param int    $value Value of the integer variable
     */
    private function SetValueInteger(string $ident, int $value)
    {
        $id = $this->GetIDForIdent($ident);
        SetValueInteger($id, $value);
    }
}
