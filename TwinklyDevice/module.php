<?php

declare(strict_types=1);

/** Generell funktions */
require_once __DIR__ . '/../libs/_traits.php';

/** Namespaced traits */
use Wilkware\Twinkly\ColorHelper;
use Wilkware\Twinkly\DebugHelper;
use Wilkware\Twinkly\FormatHelper;
use Wilkware\Twinkly\TwinklyHelper;
use Wilkware\Twinkly\VariableHelper;

/**
 * CLASS PresenceDetector
 */
class TwinklyDevice extends IPSModuleStrict
{
    // -------------------------------------------------------------------------
    // Traits
    // -------------------------------------------------------------------------

    use ColorHelper;
    use DebugHelper;
    use FormatHelper;
    use TwinklyHelper;
    use VariableHelper;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    /** @var array<int,mixed> Twinkly modes */
    private const TWINKLY_MODES = [
        [0, 'color'],
        [1, 'effect'],
        [2, 'movie'],
        [3, 'demo'],
        [4, 'musicreactive'],
        [5, 'playlist'],
        [6, 'rt'],
    ];

    /** @var array<int,mixed> Twinkly movie */
    private const TWINKLY_MOVIE = [
        [-1, 'No movies available!'],
    ];

    // -------------------------------------------------------------------------
    // Echo Maps
    // -------------------------------------------------------------------------

    private const TWINKLY_MAP_GESTALT = [
        ['product_name', 'Product name', 3, null],
        ['hardware_version', 'Hardware Version', 3, null],
        ['bytes_per_led', 'Bytes per LED', 2, null],
        ['hw_id', 'Hardware ID', 3, null],
        ['flash_size', 'Flash Size', 2, null],
        ['led_type', 'LED Type', 2, null],
        ['product_code', 'Product Code', 3, null],
        ['fw_family', 'Firmware Family', 3, null],
        ['device_name', 'Device Name', 3, null],
        ['uptime', 'Uptime', 2, ' ms', null],
        ['mac', 'MAC:', 3, null],
        ['uuid', 'UUID', 3, null],
        ['max_supported_led', 'Max supported LED', 2, null],
        ['number_of_led', 'Number of LED', 2, null],
        ['led_profile', 'LED Profile', 3, null],
        ['measured_frame_rate', 'Frame Rate', 2, null],
        ['frame_rate', 'Measured Frame Rate', 2, null],
        ['movie_capacity', 'Movie Capacity', 2, null],
        ['copyright', 'Copyright', 3, null],
    ];

    // -------------------------------------------------------------------------
    // Presentations
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> Switch Presentation (Switch) */
    private const TWINKLY_PRESENTATION_SWITCH = [
        'PRESENTATION'   => VARIABLE_PRESENTATION_SWITCH,
        'USE_ICON_FALSE' => true,
        'USAGE_TYPE'     => 0,
        'ICON_TRUE'      => 'lightbulb',
        'ICON_FALSE'     => 'lightbulb-on',
        'GLOW_INTENSITY' => 50,
        'GLOW_COLOR'     => 16771899,
    ];

    /**
     * @var array<string,mixed> Mode Presentation (Enumeration)
     */
    private const TWINKLY_PRESENTATION_MODE = [
        'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
        'OPTIONS'      => '[{"Caption":"Color","Color":16776960,"IconActive":false,"IconValue":"","Value":0},{"Caption":"Effect","Color":65280,"IconActive":false,"IconValue":"","Value":1},{"Caption":"Movie","Color":16711680,"IconActive":false,"IconValue":"","Value":2},{"Caption":"Demo","Color":65535,"IconActive":false,"IconValue":"","Value":3}]',
        'LAYOUT'       => 0,
        'ICON'         => 'Remote',
        'DISPLAY'      => 0,
    ];

    /**
     * @var array<string,mixed> Mode Extended Presentation (Enumeration)
     */
    private const TWINKLY_PRESENTATION_MODE_EX = [
        'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
        'OPTIONS'      => '[{"Caption":"Color","Color":16776960,"IconActive":false,"IconValue":"","Value":0},{"Caption":"Effect","Color":65280,"IconActive":false,"IconValue":"","Value":1},{"Caption":"Movie","Color":16711680,"IconActive":false,"IconValue":"","Value":2},{"Caption":"Demo","Color":65535,"IconActive":false,"IconValue":"","Value":3},{"Caption":"Musicreactive","Color":16711935,"IconActive":false,"IconValue":"","Value":4},{"Caption":"Playlist","Color":255,"IconActive":false,"IconValue":"","Value":5},{"Caption":"RealTime","Color":16743680,"IconActive":false,"IconValue":"","Value":6}]',
        'LAYOUT'       => 0,
        'ICON'         => 'Remote',
        'DISPLAY'      => 0,
    ];

    /**
     * @var array<string,mixed> Movie Presentation (Enumeration)
     */
    private const TWINKLY_PRESENTATION_MOVIE = [
        'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
        'OPTIONS'      => '[{"Caption":"No movies available!","Color":16711680,"IconActive":false,"IconValue":"","Value":0}]',
        'LAYOUT'       => 0,
        'ICON'         => 'Favorite',
        'DISPLAY'      => 0,
    ];

    /**
     * @var array<string,mixed> Intensity Presentation (Slider)
     */
    private const TWINKLY_PRESENTATION_SLIDER = [
        'PRESENTATION'        => VARIABLE_PRESENTATION_SLIDER,
        'USAGE_TYPE'          => 2,
        'THOUSANDS_SEPARATOR' => '',
        'DECIMAL_SEPARATOR'   => 'Client',
        'PERCENTAGE'          => true,
        'DIGITS'              => 0,
        'INTERVALS'           => '[]',
        'ICON'                => 'signal',
        'INTERVALS_ACTIVE'    => false,
        'MAX'                 => 100,
        'GRADIENT_TYPE'       => 0,
        'MIN'                 => 0,
        'CUSTOM_GRADIENT'     => '[]',
        'PREFIX'              => '',
        'STEP_SIZE'           => 1.0,
        'SUFFIX'              => ' %',
    ];

    /**
     * @var array<string,mixed> Effects Presentation (Slider)
     */
    private const TWINKLY_PRESENTATION_EFFECTS = [
        'PRESENTATION'        => VARIABLE_PRESENTATION_SLIDER,
        'USAGE_TYPE'          => 5,
        'THOUSANDS_SEPARATOR' => '',
        'DECIMAL_SEPARATOR'   => 'Client',
        'PERCENTAGE'          => false,
        'DIGITS'              => 0,
        'INTERVALS'           => '[]',
        'ICON'                => 'stars',
        'INTERVALS_ACTIVE'    => false,
        'MAX'                 => 5,
        'GRADIENT_TYPE'       => 0,
        'MIN'                 => 1,
        'CUSTOM_GRADIENT'     => '[]',
        'PREFIX'              => '',
        'STEP_SIZE'           => 1.0,
        'SUFFIX'              => '',
    ];

    /**
     * @var array<string,mixed> Presentation (type)
     */
    private const TWINKLY_PRESENTATION_COLOR = [
        'PRESENTATION'  => VARIABLE_PRESENTATION_COLOR,
        'SELECTION'     => 0,
        'PRESET_VALUES' => '[{"Color":16007990},{"Color":16761095},{"Color":10233776},{"Color":48340},{"Color":2201331},{"Color":15277667}]',
        'ENCODING'      => 0,
        'COLOR_SPACE'   => 1,
        'COLOR_CURVE'   => 0,
    ];

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * In contrast to Construct, this function is called only once when creating the instance and starting IP-Symcon.
     * Therefore, status variables and module properties which the module requires permanently should be created here.
     *
     * @return void
     */
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        // Properties
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
        $exists = IPS_VariableExists(@$this->GetIDForIdent('Movie'));

        // Presentations
        $mode = $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MODE, 'OPTIONS', 'Caption');
        $movie = $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MOVIE, 'OPTIONS', 'Caption');
        if ($exists) {
            $movie = '';
        }

        // Variablen erzeugen
        $this->RegisterVariableBoolean('Switch', $this->Translate('Switch'), self::TWINKLY_PRESENTATION_SWITCH, 0);
        $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), $mode, 1);
        $this->RegisterVariableInteger('Color', $this->Translate('Color'), self::TWINKLY_PRESENTATION_COLOR, 2);
        $this->RegisterVariableInteger('Effect', $this->Translate('Effect'), self::TWINKLY_PRESENTATION_EFFECTS, 3);
        $this->RegisterVariableInteger('Movie', $this->Translate('Movie'), $movie, 4);
        $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), self::TWINKLY_PRESENTATION_SLIDER, 5);
        $this->RegisterVariableInteger('Saturation', $this->Translate('Saturation'), self::TWINKLY_PRESENTATION_SLIDER, 6);

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
     * This function is called when deleting the instance during operation and when updating via "Module Control".
     * The function is not called when exiting IP-Symcon.
     *
     * @return void
     */
    public function Destroy(): void
    {
        parent::Destroy();
    }

    /**
     * The content can be overwritten in order to transfer a self-created configuration page.
     * This way, content can be generated dynamically.
     * In this case, the "form.json" on the file system is completely ignored.
     *
     * @return string Content of the configuration page.
     */
    public function GetConfigurationForm(): string
    {
        // Get Form
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        $alias = $this->GetDeviceName();
        $timer = $this->GetTimer();
        // Debug output
        $this->LogDebug(__FUNCTION__, 'Load device name: ' . $alias);
        $this->LogDebug(__FUNCTION__, $timer);
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
        //$this->LogDebug(__FUNCTION__, $form);
        return json_encode($form);
    }

    /**
     * Is executed when "Apply" is pressed on the configuration page and immediately after the instance has been created.
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();

        // IP Check
        $host = $this->ReadPropertyString('Host');
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
            $mode = $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MODE_EX, 'OPTIONS', 'Caption');
        } else {
            $mode = $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MODE, 'OPTIONS', 'Caption');
        }
        $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), 'Twinkly.ModeEx', 1);

        // Debug message
        $this->LogDebug(__FUNCTION__, 'IP=' . $host);
    }

    /**
     * Is called when, for example, a button is clicked in the visualization.
     *
     * @param string $ident Ident of the variable
     * @param mixed $value The value to be set
     *
     * @return void
     */
    public function RequestAction(string $ident, mixed $value): void
    {
        $this->LogDebug(__FUNCTION__, 'Ident: ' . $ident . ' Value: ' . $value);
        switch ($ident) {
            case 'TimingCheck':
                $this->OnTimingCheck($value);
                break;
            case 'TimingNow':
                $this->OnTimingNow($value);
                break;
            case 'Switch':
                $this->SetSwitch($value);
                $this->SetValueBoolean($ident, $value);
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
     *
     * @return string Brightness value or error message
     */
    public function Brightness(): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain device brightness.');
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
     *
     * @return string Saturation value or error message
     */
    public function Saturation()
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain device saturation.');
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doSaturation($host, $token);
        $this->LogDebug(__FUNCTION__, $json);
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
     *
     * @return string Color value or error message
     */
    public function Color(): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain color information.');
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doColor($host, $token);
        $this->LogDebug(__FUNCTION__, $json);
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
     *
     * @return string Effect value or error message
     */
    public function Effect(): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain effect id.');
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doEffect($host, $token);
        $this->LogDebug(__FUNCTION__, $json);
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
     *
     * @return string Movie value or error message
     */
    public function Movie(): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain movie id.');
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Movie list
        $json = $this->doMovies($host, $token);
        $this->LogDebug(__FUNCTION__, $json);
        if ($json !== false) {
            $movies = [];
            if (count($json['movies']) > 0) {
                foreach ($json['movies'] as $movie) {
                    $movies[] = [$movie['id'], $movie['name']];
                }
            } else {
                $movies = self::TWINKLY_MOVIE;
            }
            // Delete VariableProfile
            if (IPS_VariableProfileExists('Twinkly.Movie')) {
                IPS_DeleteVariableProfile('Twinkly.Movie'); // migration to presentation
            }

            $presentation = self::TWINKLY_PRESENTATION_MOVIE;
            $options = array_map(function ($movie)
            {
                return [
                    'Caption'     => $this->Translate($movie[1]),
                    'Color'       => -1,
                    'IconActive'  => false,
                    'IconValue'   => '',
                    'Value'       => $movie[0],
                ];
            }, $movies);
            $encode = json_encode($options);
            $presentation['OPTIONS'] = $encode;

            $mid = @$this->GetIDForIdent('Movie');
            if (IPS_VariableExists($mid)) {
                IPS_SetVariableCustomPresentation($mid, $presentation);
            }

            // Sync movie
            $this->SetValueInteger('Movie', $json['id']);
            // Display value
            return $this->Translate('Movie: ') . ($json['id']) . ' (' . $json['name'] . ')';

        } else {
            $this->SetValueInteger('Movie', -1);
            return $this->Translate('No films uploaded!');
            //return $this->Translate('Error occurred!');
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Gestalt();
     *
     * @return string Device information value or error message
     */
    public function Gestalt(): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain device information.');
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doGestalt($host, $token);
        $this->LogDebug(__FUNCTION__, $json);

        return $this->PrettyPrint(self::TWINKLY_MAP_GESTALT, json_encode($json));
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWICKLY_Version();
     *
     * @return string Firmware version value or error message
     */
    public function Version(): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain firmware version.');
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
     *
     * @return string Network information value or error message
     */
    public function Network(): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Obtain device information.');
        // Host & Token
        $host = $this->ReadPropertyString('Host');
        $token = $this->ReadAttributeString('Token');
        // Request
        $json = $this->doNetwork($host, $token);
        $this->LogDebug(__FUNCTION__, $json);

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
     *
     * @return string Device name or error message
     */
    public function DeviceName(string $value): string
    {
        if ($this->CheckLogin() === false) {
            return $this->Translate('Login error!');
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Set device name to: ' . $value);
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
     * User has switch timing check box.
     *
     * @param bool $value check value.
     *
     * @return void
     */
    protected function OnTimingCheck(bool $value): void
    {
        $this->LogDebug(__FUNCTION__, 'Value: ' . $value);
        $this->UpdateFormField('TimerOn', 'enabled', $value);
        $this->UpdateFormField('TimerOff', 'enabled', $value);
        $this->UpdateFormField('TimerNowOn', 'enabled', $value);
        $this->UpdateFormField('TimerNowOff', 'enabled', $value);
    }

    /**
     * User has click on NOW button.
     *
     * @param string $value ON or OFF.
     *
     * @return void
     */
    protected function OnTimingNow(string $value): void
    {
        $this->LogDebug(__FUNCTION__, 'Value: ' . $value);
        $ts = time();
        $h = intval(date('H', $ts));
        $m = intval(date('i', $ts));
        $s = intval(date('s', $ts));
        $this->UpdateFormField('Timer' . $value, 'value', '{"hour":' . $h . ',"minute":' . $m . ',"second":' . $s . '}');
    }

    /**
     * Switch the Stripe on/off.
     *
     * @param bool $value State value.
     *
     * @return void
     */
    private function SetSwitch(bool $value): void
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Mode
        $mode = 'off'; // Default
        if ($value) {  // 1 == 'On' (true)
            $mode = self::TWINKLY_MODES[$this->GetValue('Mode')][1];
        }
        $this->LogDebug(__FUNCTION__, 'Switch mode: ' . $mode);
        // Body
        $body = ['mode' => $mode];
        // Request
        $this->doMode($host, $token, $body);
    }

    /**
     * Sets the device mode.
     *
     * @param int $value Mode value.
     *
     * @return void
     */
    private function SetMode(int $value): void
    {
        if ($this->GetValue('Switch') == false) { // 0 == 'Off' (false)
            return;
        }
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Mode
        $mode = self::TWINKLY_MODES[$value][1];
        $this->LogDebug(__FUNCTION__, 'Selected mode: ' . $mode);
        // Body
        $body = ['mode' => $mode];
        // Request
        $this->doMode($host, $token, $body);
    }

    /**
     * Sets the color value.
     *
     * @param int $value Color value.
     *
     * @return void
     */
    private function SetColor(int $value): void
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Set color to: ' . $value);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // RGB
        $rgb = $this->int2rgb($value);
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
     *
     * @return void
     */
    private function SetEffect(int $value): void
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Set effectId to: ' . $value);
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
     *
     * @return void
     */
    private function SetMovie(int $value): void
    {
        if ($value < 0) {
            $this->LogDebug(__FUNCTION__, 'No movie to set!');
            return;
        }

        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Set movieId to: ' . $value);
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
     *
     * @return void
     */
    private function SetBrightness(int $value): void
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Set brightness to: ' . $value);
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
     *
     * @return void
     */
    private function SetSaturation(int $value): void
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Set saturation to: ' . $value);
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
    private function CheckLogin(): bool
    {
        // $last =  $this->ReadAttributeInteger('Validate');
        $now = time();
        // Timestamp
        $this->WriteAttributeInteger('Validate', $now);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Debug
        $this->LogDebug(__FUNCTION__, 'Login to host: ' . $host);
        // Login
        $challange = $this->doLogin($host);
        // Check
        if ($challange === false) {
            $this->LogDebug(__FUNCTION__, 'Login failed!');
            return false;
        }
        // Validate
        $token = $challange['authentication_token'];
        $response = $challange['challenge-response'];
        // Check
        if ($this->doVerify($host, $token, $response) === false) {
            $this->LogDebug(__FUNCTION__, 'Verify failed!');
            return false;
        }
        // Token
        $this->WriteAttributeString('Token', $token);
        return true;
    }

    /**
     * Gets device name.
     *
     * @return string Current device name.
     */
    private function GetDeviceName(): string
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
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
     * @return array<string,string> Timer settings.
     */
    private function GetTimer(): array
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return [];
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
     * @return void
     */
    private function SetTimer(): void
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Timer
        $timer = $this->ReadPropertyBoolean('TimerCheck');
        $on = -1;
        $off = -1;
        if ($timer) {
            $time = $this->ReadPropertyString('TimerOn');
            $json = json_decode($time, true);
            $on = ($json['hour'] * 3600) + ($json['minute'] * 60) + ($json['second']);
            $this->LogDebug(__FUNCTION__, $on);
            $time = $this->ReadPropertyString('TimerOff');
            $json = json_decode($time, true);
            $off = ($json['hour'] * 3600) + ($json['minute'] * 60) + ($json['second']);
            $this->LogDebug(__FUNCTION__, $off);
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
        $this->doTimer($host, $token, $body);
    }
}
