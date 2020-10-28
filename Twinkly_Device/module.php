<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/traits.php';  // General helper functions

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

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        // IP Check
        $host = $this->ReadPropertyString('Host');
        $switch = $this->ReadPropertyBoolean('Switch');
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $this->SetStatus(102);
        } else {
            $this->SetStatus(201);
        }
        // Aditionally Switch
        $this->MaintainVariable('Switch', $this->Translate('Switch'), VARIABLETYPE_INTEGER, 'Twinkly.Switch', 0, $switch);
        if ($switch) {
            $this->EnableAction('Switch');
        }
        // Debug message
        $this->SendDebug('ApplyChanges', 'IP=' . $host, 0);
    }

    /**
     * RequestAction - SDK function.
     *
     * @param string $ident Variable identifier
     * @param int $value New value
     */
    public function RequestAction($ident, $value)
    {
        $this->SendDebug('RequestAction', 'Ident: ' . $ident . ' Value: ' . $value, 0);
        switch ($ident) {
            case 'Switch':
            case 'Mode':
                $this->SendDebug('RequestAction', 'Neuer Modus gewählt: ' . $value, 0);
                $this->SetMode($value);
                SetValue($this->GetIDForIdent($ident), $value);
                break;
            case 'Brightness':
                $this->SendDebug('RequestAction', 'Helligkeit geändert: ' . $value, 0);
                $this->SetBrightness($value);
                SetValue($this->GetIDForIdent($ident), $value);
                break;
            default:
            throw new Exception('Invalid Ident');
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_SetMode($id, $value);
     *
     * @param int $value Mode value.
     */
    public function SetMode(int $value)
    {
        // Safty check
        if ($value < 0 || $value > 3) {
            // out of range
            $this->SendDebug('SetMode', 'Out of range' . $value, 0);
            return;
        }
        if ($this->CheckLogin() === false) {
            $this->SendDebug('SetMode', 'Login error!', 0);
            return;
        }
        // Extract assoziated mode string
        $mode = $this->assoMODE[$value][4];
        $this->SendDebug('SetMode', 'Gewählter Modus: ' . $mode, 0);
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
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_SetBrightness($id, $value);
     *
     * @param int $value Brightness value.
     */
    public function SetBrightness(int $value)
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug('SetBrightness', 'Login error!', 0);
            return;
        }
        // Debug
        $this->SendDebug('SetBrightness', 'Set brightness to: ' . $value, 0);
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
        $this->SendDebug('Brightness', 'Obtain device brightness.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doBrightness($host, $token);
        // Sync brightness
        SetValue($this->GetIDForIdent('Brightness'), $json['value']);

        return $this->Translate('Brightness: ') . $json['value'] . '%';
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
        $this->SendDebug('Gestalt', 'Obtain device information.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doGestalt($host, $token);

        return sprintf(
            "Product name: %s\nHardware Version: %s\nBytes per LED: %d\nHardware ID: %s\nFlash Size: %d\nLED Type: %d\nProduct Code: %s\nFirmware Family: %s\nDevice Name: %s\nUptime: %s\nMAC: %s\nUUID: %s\nMax supported LED: %d\nNumber of LED: %d\nLED Profile: %s\nFrame Rate: %f\nMovie Capacity: %d\nCopyright: %s",
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
        $this->SendDebug('Version', 'Obtain firmware version.', 0);
        // only Host
        $host = $this->ReadPropertyString('Host');
        // Request
        $json = $this->doVersion($host);

        return $this->Translate('Firmware: ') . $json['version'];
    }

    /**
     * Validate the token and login to renew it.
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
            $this->SendDebug('CheckLogin', 'Login to host: ' . $host, 0);
            // Login
            $challange = $this->doLogin($host);
            // Check
            if ($challange === false) {
                return false;
            }
            // Validate
            $token = $challange['authentication_token'];
            $response = $challange['challenge-response'];
            // Check
            if ($this->doVerify($host, $token, $response) === false) {
                return false;
            }
            // Token
            $this->WriteAttributeString('Token', $token);
        }

        return true;
    }
}
