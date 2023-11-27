<?php

declare(strict_types=1);

// Generell funktions
require_once __DIR__ . '/../libs/_traits.php';

// CLASS PresenceDetector
class TwinklyDevice extends IPSModule
{
    // Helper traits
    use ColorHelper;
    use DebugHelper;
    use FormatHelper;
    use ProfileHelper;
    use TwinklyHelper;
    use VariableHelper;

    // Token constant
    private const TOKEN_LIFETIME = 14400;

    // Echo maps
    private const TWINKLY_MAP_GESTALT = [
        ['product_name', 'Product name', 3],
        ['hardware_version', 'Hardware Version', 3],
        ['bytes_per_led', 'Bytes per LED', 2],
        ['hw_id', 'Hardware ID', 3],
        ['flash_size', 'Flash Size', 2],
        ['led_type', 'LED Type', 2],
        ['product_code', 'Product Code', 3],
        ['fw_family', 'Firmware Family', 3],
        ['device_name', 'Device Name', 3],
        ['uptime', 'Uptime', 2, ' ms'],
        ['mac', 'MAC:', 3],
        ['uuid', 'UUID', 3],
        ['max_supported_led', 'Max supported LED', 2],
        ['number_of_led', 'Number of LED', 2],
        ['led_profile', 'LED Profile', 3],
        ['measured_frame_rate', 'Frame Rate', 2],
        ['frame_rate', 'Measured Frame Rate', 2],
        ['movie_capacity', 'Movie Capacity', 2],
        ['copyright', 'Copyright', 3],
    ];

    // Profil array
    private $assoMODE = [
        [0, 'Color', '', 0xFFFF00, 'color'],
        [1, 'Effect', '', 0x00FF00, 'effect'],
        [2, 'Movie', '', 0xFF0000, 'movie'],
        [3, 'Demo', '', 0x00FFFF, 'demo'],
    ];

    private $assoMODEEX = [
        [0, 'Color', '', 0xFFFF00, 'color'],
        [1, 'Effect', '', 0x00FF00, 'effect'],
        [2, 'Movie', '', 0xFF0000, 'movie'],
        [3, 'Demo', '', 0x00FFFF, 'demo'],
        [4, 'Musicreactive', '', 0xFF00FF, 'musicreactive'],
        [5, 'Playlist', '', 0x0000FF, 'playlist'],
        [6, 'RealTime', '', 0xFF7D00, 'rt'],
    ];

    private $assoSWITCH = [
        [0, 'Off', 'Light-0', -1],
        [1, 'On', 'Light-100', 0x00FF00],
    ];

    private $assoMOVIE = [
        [-1, 'No movies available!', '', 0xFF0000],
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Host', '127.0.0.1');
        $this->RegisterPropertyBoolean('AdvancedMode', false);
        $this->RegisterPropertyBoolean('TimerCheck', false);
        $this->RegisterPropertyString('TimerOn', '{"hour": 15,"minute": 0,"second": 0}');
        $this->RegisterPropertyString('TimerOff', '{"hour": 23,"minute": 0,"second": 0}');

        // Attributes for Login
        $this->RegisterAttributeString('Token', '');
        $this->RegisterAttributeInteger('Validate', 0);

        // Variablen Profile einrichten
        if (IPS_VariableProfileExists('Twinkly.Mode')) {
            IPS_DeleteVariableProfile('Twinkly.Mode'); // v2 => v3 migration check
        }

        // Statusvariablen (Movie)
        $exists = @$this->GetIDForIdent('Movie');

        // Profile anlegen
        $this->RegisterProfileInteger('Twinkly.Switch', 'Light', '', '', 0, 0, 0, $this->assoSWITCH);
        $this->RegisterProfileInteger('Twinkly.Effect', 'Stars', '', '', 1, 5, 1);
        $this->RegisterProfileInteger('Twinkly.Mode', 'Remote', '', '', 0, 0, 0, $this->assoMODE);
        $this->RegisterProfileInteger('Twinkly.ModeEx', 'Remote', '', '', 0, 0, 0, $this->assoMODEEX);
        if (!IPS_VariableProfileExists('Twinkly.Movie')) {
            $this->RegisterProfileInteger('Twinkly.Movie', 'Favorite', '', '', 0, 0, 0, $this->assoMOVIE);
        }

        // Variablen erzeugen
        $this->RegisterVariableInteger('Switch', $this->Translate('Switch'), 'Twinkly.Switch', 0);
        $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), 'Twinkly.Mode', 1);
        $this->RegisterVariableInteger('Color', $this->Translate('Color'), '~HexColor', 2);
        $this->RegisterVariableInteger('Effect', $this->Translate('Effect'), 'Twinkly.Effect', 3);
        $this->RegisterVariableInteger('Movie', $this->Translate('Movie'), 'Twinkly.Movie', 4);
        $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), '~Intensity.100', 5);
        $this->RegisterVariableInteger('Saturation', $this->Translate('Saturation'), '~Intensity.100', 6);

        // Initialwert setzen
        if ($exists === false) {
            $this->SetValueInteger('Movie', -1);
        }

        // Actions
        $this->EnableAction('Switch');
        $this->EnableAction('Mode');
        $this->EnableAction('Color');
        $this->EnableAction('Effect');
        $this->EnableAction('Movie');
        $this->EnableAction('Brightness');
        $this->EnableAction('Saturation');
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
        if (!empty($timer)) {
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
        // IP Check
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $this->SetStatus(102);
        } else {
            $this->SetStatus(201);
        }
        // Timer
        $this->SetTimer();
        // Aditionally Switch
        $advanced = $this->ReadPropertyBoolean('AdvancedMode');
        if ($advanced) {
            $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), 'Twinkly.ModeEx', 1);
        } else {
            $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), 'Twinkly.Mode', 1);
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
                $this->OnTimingCheck($value);
                break;
            case 'TimingNow':
                $this->OnTimingNow($value);
                break;
            case 'Switch':
                $this->SetSwitch($value);
                $this->SetValueInteger($ident, $value);
                break;
            case 'Mode':
                $this->SetMode($value);
                $this->SetValueInteger($ident, $value);
                break;
            case 'Color':
                $this->SetColor($value);
                $this->SetValueInteger($ident, $value);
                break;
            case 'Effect':
                $this->SetEffect($value);
                $this->SetValueInteger($ident, $value);
                break;
            case 'Movie':
                $this->SetMovie($value);
                $this->SetValueInteger($ident, $value);
                break;
            case 'Brightness':
                $this->SetBrightness($value);
                $this->SetValueInteger($ident, $value);
                break;
            case 'Saturation':
                $this->SetSaturation($value);
                $this->SetValueInteger($ident, $value);
                break;
            default:
                throw new Exception('Invalid Ident');
        }
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
            $this->SetValueInteger('Brightness', $json['value']);
            // Display value
            return $this->Translate('Brightness: ') . $json['value'] . '%';
        }
        return $this->Translate('Error occurred!');
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Saturation($id);
     */
    public function Saturation()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain device saturation.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doSaturation($host, $token);
        $this->SendDebug(__FUNCTION__, $json);
        // Sync brightness
        if ($json !== false) {
            $this->SetValueInteger('Saturation', $json['value']);
            // Display value
            return $this->Translate('Saturation: ') . $json['value'] . '%';
        }
        return $this->Translate('Error occurred!');
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Color($id);
     */
    public function Color()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain color information.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doColor($host, $token);
        $this->SendDebug(__FUNCTION__, $json);
        // Sync brightness
        if ($json !== false) {
            $rgb = [$json['red'], $json['green'], $json['blue']];
            $value = $this->RGB2Int($rgb);
            $this->SetValueInteger('Color', $value);
            // Display value
            return $this->Translate('Color: ') . sprintf('0x%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]) . ' (' . $value . ')';
        }
        return $this->Translate('Error occurred!');
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Effect($id);
     */
    public function Effect()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain effect id.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doEffect($host, $token);
        $this->SendDebug(__FUNCTION__, $json);
        // Sync brightness
        if ($json !== false) {
            $this->SetValueInteger('Effect', $json['preset_id'] + 1);
            // Display value
            return $this->Translate('Effect: ') . ($json['preset_id'] + 1) . ' (' . $json['unique_id'] . ')';
        }
        return $this->Translate('Error occurred!');
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Movie($id);
     */
    public function Movie()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Obtain movie id.', 0);
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Movie list
        $json = $this->doMovies($host, $token);
        if ($json !== false) {
            $movies = [];
            if (count($json['movies']) > 0) {
                foreach ($json['movies'] as $movie) {
                    $movies[] = [$movie['id'], $movie['name'], '', 0xFF0000];
                }
            } else {
                $movies = $this->assoMOVIE;
            }
            // Delete VariableProfileAssociation
            $old = IPS_GetVariableProfile('Twinkly.Movie')['Associations'];
            $values = array_column($old, 'Value');
            foreach ($movies as $movie) {
                IPS_SetVariableProfileAssociation('Twinkly.Movie', $movie[0], $this->Translate($movie[1]), $movie[2], $movie[3]);
                $key = array_search($movie[0], $values);
                if (!($key === false)) {
                    unset($values[$key]);
                }
            }
            foreach ($values as $key => $value) {
                IPS_SetVariableProfileAssociation('Twinkly.Movie', $value, '', '', 0);
            }
        } else {
            return $this->Translate('Error occurred!');
        }
        // Request
        $json = $this->doMovie($host, $token);
        $this->SendDebug(__FUNCTION__, $json);
        // Sync brightness
        if ($json !== false) {
            $this->SetValueInteger('Movie', $json['id']);
            // Display value
            return $this->Translate('Movie: ') . ($json['id']) . ' (' . $json['name'] . ')';
        }
        $this->SetValueInteger('Movie', -1);
        return $this->Translate('No films uploaded!');
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

        return $this->PrettyPrint(self::TWINKLY_MAP_GESTALT, $json);
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

        $enc = [0 => 'NONE', 2 => 'WPA1', 3 => 'WPA2', 4 => 'WPA1+WPA2'];
        return sprintf(
            "Network mode: %s\nStation:\n\tSSID: %s\n\tIP: %s\n\tGateway: %s\n\tMask: %s\n\tRSSI: %d db\nAccess Point:\n\tSSID: %s\n\tIP: %s\n\tChannel: %s\n\tEncryption: %s\n\tSSID Hidden: %s\n\tMax connections: %d",
            $json['mode'] == 1 ? '1 (Station)' : '2 (Access Point)',
            $json['station']['ssid'],
            $json['station']['ip'],
            $json['station']['gw'],
            $json['station']['mask'],
            $json['station']['rssi'],
            $json['ap']['ssid'],
            $json['ap']['ip'],
            $json['ap']['channel'],
            $enc[$json['ap']['enc']],
            $json['ap']['ssid_hidden'] ? 'true' : 'false',
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
        if ($json === false) {
            return $this->Translate('Name could not be changed!');
        } else {
            return $this->Translate('Name was changed successfully!');
        }
    }

    /**
     * Switch the Stripe on/off.
     *
     * @param bool $value State value.
     */
    private function SetSwitch(int $value)
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Mode
        $mode = 'off'; // Default
        if ($value == 1) {  // 1 == 'On'
            $mode = $this->assoMODEEX[$this->GetValue('Mode')][4];
        }
        $this->SendDebug(__FUNCTION__, 'Switch mode: ' . $mode, 0);
        // Body
        $body = ['mode' => $mode];
        // Request
        $this->doMode($host, $token, $body);
    }

    /**
     * Sets the device mode.
     *
     * @param int $value Mode value.
     */
    private function SetMode(int $value)
    {
        if ($this->GetValue('Switch') == 0) { // 0 == 'Off'
            return;
        }
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!', 0);
            return;
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Mode
        $mode = $this->assoMODEEX[$value][4];
        $this->SendDebug(__FUNCTION__, 'Selected mode: ' . $mode, 0);
        // Body
        $body = ['mode' => $mode];
        // Request
        $this->doMode($host, $token, $body);
    }

    /**
     * Sets the color value.
     *
     * @param int $value Color value.
     */
    private function SetColor(int $value)
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Set color to: ' . $value);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // RGB
        $rgb = $this->Int2RGB($value);
        $body = [
            'red'    => $rgb[0],
            'green'  => $rgb[1],
            'blue'   => $rgb[2],
        ];
        // Request
        $this->doColor($host, $token, $body);
    }

    /**
     * Sets the effect id.
     *
     * @param int $value Effect id.
     */
    private function SetEffect(int $value)
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Set effectId to: ' . $value);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Effect ID
        $value = $value - 1; // zero based
        if ($value < 0) {
            $value = 0;
        }
        $body = [
            'effect_id'  => $value,
        ];
        // Request
        $this->doEffect($host, $token, $body);
    }

    /**
     * Sets the movie id.
     *
     * @param int $value Movie id.
     */
    private function SetMovie(int $value)
    {
        if ($value < 0) {
            $this->SendDebug(__FUNCTION__, 'No movie to set!');
            return;
        }

        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Set movieId to: ' . $value);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Movie ID
        $body = [
            'id'  => $value,
        ];
        // Request
        $this->doMovie($host, $token, $body);
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
     * Sets the saturation level.
     *
     * @param int $value Saturation value.
     */
    private function SetSaturation(int $value)
    {
        if ($this->CheckLogin() === false) {
            $this->SendDebug(__FUNCTION__, 'Login error!', 0);
            return;
        }
        // Debug
        $this->SendDebug(__FUNCTION__, 'Set saturation to: ' . $value, 0);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Saturation
        $body = [
            'mode'   => 'enabled',
            'value'  => $value,
        ];
        // Request
        $this->doSaturation($host, $token, $body);
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
        if ($json === false) {
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
        $mins = floor($json['time_on'] / 60 % 60);
        $secs = floor($json['time_on'] % 60);
        $on = sprintf('{"hour": %d,"minute": %d,"second": %d}', $hours, $mins, $secs);
        $hours = floor($json['time_off'] / 3600);
        $mins = floor($json['time_off'] / 60 % 60);
        $secs = floor($json['time_off'] % 60);
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
        if ($timer) {
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
            'time_now'  => time() - strtotime('today'),
            'time_on'   => $on,
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
        $this->UpdateFormField('Timer' . $value, 'value', '{"hour":' . $h . ',"minute":' . $m . ',"second":' . $s . '}');
    }
}
