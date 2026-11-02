<?php

declare(strict_types=1);

/** General functions */
require_once __DIR__ . '/../libs/_traits.php';

/** Namespaced traits */
use Wilkware\Twinkly\ColorHelper;
use Wilkware\Twinkly\DebugHelper;
use Wilkware\Twinkly\FormatHelper;
use Wilkware\Twinkly\FormHelper;
use Wilkware\Twinkly\TwinklyHelper;
use Wilkware\Twinkly\VariableHelper;

/**
 * CLASS TwinklyDevice
 */
class TwinklyDevice extends IPSModuleStrict
{
    // -------------------------------------------------------------------------
    // Traits
    // -------------------------------------------------------------------------

    use ColorHelper;
    use DebugHelper;
    use FormatHelper;
    use FormHelper;
    use TwinklyHelper;
    use VariableHelper;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    /** @var int Token lifetime in seconds (device issues tokens valid for 14400 seconds) */
    private const TOKEN_LIFETIME = 14000;

    /** @var int Check interval in seconds while the synchronisation is paused */
    private const PAUSE_INTERVAL = 3600;

    /** @var int Status: synchronisation paused (IS_STANDBY) */
    private const STATUS_PAUSED = 106;

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
        ['uptime', 'Uptime', 2, null],
        ['mac', 'MAC', 3, null],
        ['uuid', 'UUID', 3, null],
        ['max_supported_led', 'Max supported LED', 2, null],
        ['number_of_led', 'Number of LED', 2, null],
        ['led_profile', 'LED Profile', 3, null],
        ['frame_rate', 'Frame Rate', 2, null],
        ['measured_frame_rate', 'Measured Frame Rate', 2, null],
        ['movie_capacity', 'Movie Capacity', 2, null],
        ['copyright', 'Copyright', 3, null],
    ];

    // -------------------------------------------------------------------------
    // Presentations
    // -------------------------------------------------------------------------

    /**
     * @var array<string,mixed> Switch Presentation (Switch)
     */
    private const TWINKLY_PRESENTATION_SWITCH = [
        'GLOW_COLOR'     => 16771899,
        'GLOW_INTENSITY' => 50,
        'ICON_FALSE'     => 'lightbulb',
        'ICON_TRUE'      => 'lightbulb-on',
        'PRESENTATION'   => VARIABLE_PRESENTATION_SWITCH,
        'USAGE_TYPE'     => 0,
        'USE_ICON_FALSE' => true,
    ];

    /**
     * @var array<string,mixed> Mode Presentation (Enumeration)
     */
    private const TWINKLY_PRESENTATION_MODE = [
        'ICON'         => 'sliders',
        'OPTIONS'      => '[{"Caption":"Color","Color":16776960,"IconActive":false,"IconValue":"","Value":0},{"Caption":"Effect","Color":65280,"IconActive":false,"IconValue":"","Value":1},{"Caption":"Movie","Color":16711680,"IconActive":false,"IconValue":"","Value":2},{"Caption":"Demo","Color":65535,"IconActive":false,"IconValue":"","Value":3}]',
        'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
    ];

    /**
     * @var array<string,mixed> Mode Extended Presentation (Enumeration)
     */
    private const TWINKLY_PRESENTATION_MODE_EX = [
        'ICON'         => 'sliders',
        'OPTIONS'      => '[{"Caption":"Color","Color":16776960,"IconActive":false,"IconValue":"","Value":0},{"Caption":"Effect","Color":65280,"IconActive":false,"IconValue":"","Value":1},{"Caption":"Movie","Color":16711680,"IconActive":false,"IconValue":"","Value":2},{"Caption":"Demo","Color":65535,"IconActive":false,"IconValue":"","Value":3},{"Caption":"Music Reactive","Color":16711935,"IconActive":false,"IconValue":"","Value":4},{"Caption":"Playlist","Color":255,"IconActive":false,"IconValue":"","Value":5},{"Caption":"Realtime","Color":16743680,"IconActive":false,"IconValue":"","Value":6}]',
        'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
    ];

    /**
     * @var array<string,mixed> Color Presentation (Color)
     */
    private const TWINKLY_PRESENTATION_COLOR = [
        'COLOR_CURVE'   => 0,
        'COLOR_SPACE'   => 1,
        'ENCODING'      => 0,
        'PRESENTATION'  => VARIABLE_PRESENTATION_COLOR,
        'PRESET_VALUES' => '[{"Color":16007990},{"Color":16761095},{"Color":10233776},{"Color":48340},{"Color":2201331},{"Color":15277667}]',
        'SELECTION'     => 0,
    ];

    /**
     * @var array<string,mixed> Effect Presentation (Enumeration), options are built from the number of device effects
     */
    private const TWINKLY_PRESENTATION_EFFECT = [
        'ICON'         => 'stars',
        'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
    ];

    /**
     * @var array<string,mixed> Movie Presentation (Enumeration)
     */
    private const TWINKLY_PRESENTATION_MOVIE = [
        'ICON'         => 'film',
        'OPTIONS'      => '[{"Caption":"No movies","Color":16711680,"IconActive":false,"IconValue":"","Value":""}]',
        'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
    ];

    /**
     * @var array<string,mixed> Brightness Presentation (Slider)
     */
    private const TWINKLY_PRESENTATION_BRIGHTNESS = [
        'ICON'         => 'sun',
        'MAX'          => 100,
        'MIN'          => 0,
        'PERCENTAGE'   => true,
        'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER,
        'STEP_SIZE'    => 1.0,
        'SUFFIX'       => ' %',
        'USAGE_TYPE'   => 2,
    ];

    /**
     * @var array<string,mixed> Saturation Presentation (Slider)
     */
    private const TWINKLY_PRESENTATION_SATURATION = [
        'ICON'         => 'droplet',
        'MAX'          => 100,
        'MIN'          => 0,
        'PERCENTAGE'   => true,
        'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER,
        'STEP_SIZE'    => 1.0,
        'SUFFIX'       => ' %',
        'USAGE_TYPE'   => 5,
    ];

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * In contrast to Construct, this function is called only once when creating the instance and starting Symcon.
     * Therefore, status variables and module properties which the module requires permanently should be created here.
     *
     * @return void
     */
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        // Properties
        $this->RegisterPropertyString('Host', '');
        $this->RegisterPropertyInteger('UpdateInterval', 0);
        $this->RegisterPropertyInteger('PauseAfterDays', 3);
        $this->RegisterPropertyBoolean('AdvancedMode', false);
        $this->RegisterPropertyBoolean('TimerCheck', false);
        $this->RegisterPropertyString('TimerOn', '{"hour": 15,"minute": 0,"second": 0}');
        $this->RegisterPropertyString('TimerOff', '{"hour": 23,"minute": 0,"second": 0}');

        // Attributes for Login
        $this->RegisterAttributeString('Token', '');
        $this->RegisterAttributeInteger('Validate', 0);

        // Attributes for internal state
        $this->RegisterAttributeString('Movies', '[]');
        $this->RegisterAttributeString('TimerConfig', '');
        $this->RegisterAttributeInteger('UnreachableSince', 0);
        $this->RegisterAttributeInteger('Effects', 5);

        // Timer for state synchronisation
        $this->RegisterTimer('UpdateTimer', 0, 'IPS_RequestAction($_IPS[\'TARGET\'], \'Update\', \'\');');

        // Presentations
        $mode = $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MODE, 'OPTIONS', 'Caption');

        // Variablen erzeugen
        $this->RegisterVariableBoolean('Switch', $this->Translate('Switch'), self::TWINKLY_PRESENTATION_SWITCH, 0);
        $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), $mode, 1);
        $this->RegisterVariableInteger('Color', $this->Translate('Color'), self::TWINKLY_PRESENTATION_COLOR, 2);
        $this->RegisterVariableInteger('Effect', $this->Translate('Effect'), $this->GetEffectPresentation(), 3);
        $this->RegisterVariableString('Movie', $this->Translate('Movie'), $this->GetMoviePresentation(), 4);
        $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), self::TWINKLY_PRESENTATION_BRIGHTNESS, 5);
        $this->RegisterVariableInteger('Saturation', $this->Translate('Saturation'), self::TWINKLY_PRESENTATION_SATURATION, 6);

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
     * The function is not called when exiting Symcon.
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
            foreach (['TimerOn' => $timer['on'], 'TimerOff' => $timer['off']] as $name => $value) {
                $this->ModifyFormElement($form['elements'], $name, function (array &$element) use ($value): void
                {
                    $element['value'] = $value;
                });
            }
        } else {
            $this->ModifyFormElement($form['elements'], 'TimerCheck', function (array &$element): void
            {
                $element['value'] = false;
            });
            foreach (['TimerNowOn', 'TimerOn', 'TimerNowOff', 'TimerOff'] as $name) {
                $this->ModifyFormElement($form['elements'], $name, function (array &$element): void
                {
                    $element['enabled'] = false;
                });
            }
        }
        // Device Name (alias)
        $this->ModifyFormElement($form['actions'], 'DeviceName', function (array &$element) use ($alias): void
        {
            $element['value'] = $alias;
        });
        // Extract version
        $ins = IPS_GetInstance($this->InstanceID);
        $mod = IPS_GetModule($ins['ModuleInfo']['ModuleID']);
        $lib = IPS_GetLibrary($mod['LibraryID']);
        $this->ModifyFormElement($form['actions'], 'Version', function (array &$element) use ($lib): void
        {
            $element['caption'] = sprintf('v%s.%d', $lib['Version'], $lib['Build']);
        });
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

        // Remove legacy profiles (migration to presentations)
        foreach (['Twinkly.Mode', 'Twinkly.ModeEx', 'Twinkly.Movie'] as $profile) {
            if (IPS_VariableProfileExists($profile)) {
                IPS_DeleteVariableProfile($profile);
            }
        }

        // IP Check
        $host = $this->ReadPropertyString('Host');
        $valid = filter_var($host, FILTER_VALIDATE_IP) !== false;
        if ($host === '') {
            $this->SetStatus(104);
        } elseif ($valid) {
            $this->SetStatus(102);
        } else {
            $this->SetStatus(201);
        }

        // Host may have changed, force a new login
        $this->WriteAttributeInteger('Validate', 0);

        // State synchronisation (apply also ends a paused synchronisation)
        $this->WriteAttributeInteger('UnreachableSince', 0);
        $this->SetTimerInterval('UpdateTimer', $valid ? $this->GetUpdateInterval() : 0);

        // Mode presentation (extended mode)
        $advanced = $this->ReadPropertyBoolean('AdvancedMode');
        if ($advanced) {
            $mode = $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MODE_EX, 'OPTIONS', 'Caption');
        } else {
            $mode = $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MODE, 'OPTIONS', 'Caption');
        }
        $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), $mode, 1);

        // Effects (presentation from last known number of effects)
        $this->RegisterVariableInteger('Effect', $this->Translate('Effect'), $this->GetEffectPresentation(), 3);

        // Movies (presentation from last known movie list)
        $this->RegisterVariableString('Movie', $this->Translate('Movie'), $this->GetMoviePresentation(), 4);

        // Timer (only with running kernel, device is only contacted on changed settings)
        if ($valid && IPS_GetKernelRunlevel() === KR_READY) {
            $this->SetTimer();
        }

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
            case 'Update':
                $this->Update();
                break;
            case 'Switch':
                if ($this->SetSwitch($value)) {
                    $this->SetValueBoolean($ident, $value);
                    $this->OnReachable();
                }
                break;
            case 'Mode':
                if ($this->SetMode($value)) {
                    $this->SetValueInteger($ident, $value);
                }
                break;
            case 'Color':
                if ($this->SetColor($value)) {
                    $this->SetValueInteger($ident, $value);
                    $this->OnReachable();
                }
                break;
            case 'Effect':
                if ($this->SetEffect($value)) {
                    $this->SetValueInteger($ident, $value);
                    $this->OnReachable();
                }
                break;
            case 'Movie':
                if ($this->SetMovie((string) $value)) {
                    $this->SetValueString($ident, (string) $value);
                    $this->OnReachable();
                }
                break;
            case 'Brightness':
                if ($this->SetBrightness($value)) {
                    $this->SetValueInteger($ident, $value);
                    $this->OnReachable();
                }
                break;
            case 'Saturation':
                if ($this->SetSaturation($value)) {
                    $this->SetValueInteger($ident, $value);
                    $this->OnReachable();
                }
                break;
            default:
                throw new Exception('Invalid Ident');
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWINKLY_Brightness($id);
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
     * TWINKLY_Saturation($id);
     *
     * @return string Saturation value or error message
     */
    public function Saturation(): string
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
     * TWINKLY_Color($id);
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
            return $this->Translate('Color: ') . sprintf('#%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2]) . ' (' . $value . ')';
        }
        return $this->Translate('Error occurred!');
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWINKLY_Effect($id);
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
        // Number of effects
        $json = $this->doEffects($host, $token);
        $this->LogDebug(__FUNCTION__, $json);
        if ($json !== false && isset($json['effects_number'])) {
            $this->UpdateEffects((int) $json['effects_number']);
        }
        // Request
        $json = $this->doEffect($host, $token);
        $this->LogDebug(__FUNCTION__, $json);
        // Sync effect
        if ($json !== false) {
            $this->SetValueInteger('Effect', $json['preset_id'] + 1);
            // Display value
            return $this->Translate('Current effect: ') . ($json['preset_id'] + 1);
        }
        return $this->Translate('Error occurred!');
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWINKLY_Movie($id);
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
        if ($json === false) {
            return $this->Translate('Error occurred!');
        }
        $movies = [];
        foreach ($json['movies'] ?? [] as $movie) {
            $movies[] = ['id' => (int) $movie['id'], 'name' => (string) $movie['name'], 'uid' => (string) $movie['unique_id']];
        }
        $this->UpdateMovies($movies);
        if (empty($movies)) {
            $this->SetValueString('Movie', '');
            return $this->Translate('No films uploaded!');
        }
        // Current movie
        $json = $this->doMovie($host, $token);
        $this->LogDebug(__FUNCTION__, $json);
        if ($json === false) {
            return $this->Translate('Error occurred!');
        }
        // Sync movie
        $uid = (string) $json['unique_id'];
        $this->SetValueString('Movie', $uid);
        // Display value
        $captions = $this->GetMovieCaptions($movies);
        return $this->Translate('Current movie: ') . ($captions[$uid] ?? $json['name']) . "\n" . $this->Translate('Available movies: ') . count($movies);
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWINKLY_Gestalt();
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
        if ($json === false) {
            return $this->Translate('Error occurred!');
        }

        return $this->PrettyPrint(self::TWINKLY_MAP_GESTALT, json_encode($json));
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWINKLY_Version();
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
        if ($json === false) {
            return $this->Translate('Error occurred!');
        }

        return $this->Translate('Firmware: ') . $json['version'];
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWINKLY_Network();
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
        if ($json === false) {
            return $this->Translate('Error occurred!');
        }

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
            $enc[$json['ap']['enc']] ?? (string) $json['ap']['enc'],
            $json['ap']['ssid_hidden'] ? 'true' : 'false',
            $json['ap']['max_connections']
        );
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TWINKLY_DeviceName();
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
     * @return bool True if the device accepted the command, otherwise false.
     */
    private function SetSwitch(bool $value): bool
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return false;
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
        return $this->doMode($host, $token, $body) !== false;
    }

    /**
     * Sets the device mode.
     *
     * @param int $value Mode value.
     *
     * @return bool True if the device accepted the command, otherwise false.
     */
    private function SetMode(int $value): bool
    {
        if ($this->GetValue('Switch') == false) { // device is off, mode is only stored and applied on switching on
            return true;
        }
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return false;
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
        return $this->doMode($host, $token, $body) !== false;
    }

    /**
     * Sets the color value.
     *
     * @param int $value Color value.
     *
     * @return bool True if the device accepted the command, otherwise false.
     */
    private function SetColor(int $value): bool
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return false;
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
        return $this->doColor($host, $token, $body) !== false;
    }

    /**
     * Sets the effect id.
     *
     * @param int $value Effect id.
     *
     * @return bool True if the device accepted the command, otherwise false.
     */
    private function SetEffect(int $value): bool
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return false;
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
        return $this->doEffect($host, $token, $body) !== false;
    }

    /**
     * Sets the movie by its unique id.
     *
     * The device renumbers its movie ids when movies are deleted or reordered,
     * therefore the stable unique id is mapped to the current id before sending.
     *
     * @param string $value Unique id of the movie.
     *
     * @return bool True if the movie was set, otherwise false.
     */
    private function SetMovie(string $value): bool
    {
        if ($value === '') {
            $this->LogDebug(__FUNCTION__, 'No movie to set!');
            return false;
        }

        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return false;
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Movie list
        $json = $this->doMovies($host, $token);
        if ($json === false) {
            $this->LogDebug(__FUNCTION__, 'Movie list not available!');
            return false;
        }
        $id = null;
        foreach ($json['movies'] ?? [] as $movie) {
            if ((string) $movie['unique_id'] === $value) {
                $id = (int) $movie['id'];
                break;
            }
        }
        if ($id === null) {
            $this->LogDebug(__FUNCTION__, 'Movie not found on device: ' . $value);
            return false;
        }
        // Debug
        $this->LogDebug(__FUNCTION__, 'Set movie ' . $value . ' with id: ' . $id);
        // Movie ID
        $body = [
            'id'  => $id,
        ];
        // Request
        return $this->doMovie($host, $token, $body) !== false;
    }

    /**
     * Stores the number of effects and updates the presentation of the effect variable if it has changed.
     *
     * @param int $count Number of effects on the device.
     *
     * @return void
     */
    private function UpdateEffects(int $count): void
    {
        if ($count < 1 || $count === $this->ReadAttributeInteger('Effects')) {
            return;
        }
        $this->WriteAttributeInteger('Effects', $count);
        $this->RegisterVariableInteger('Effect', $this->Translate('Effect'), $this->GetEffectPresentation(), 3);
    }

    /**
     * Builds the effect presentation from the stored number of effects (values are 1 based).
     *
     * @return array<string,mixed> Enumeration presentation.
     */
    private function GetEffectPresentation(): array
    {
        $caption = $this->Translate('Effect');
        $options = [];
        for ($i = 1; $i <= $this->ReadAttributeInteger('Effects'); $i++) {
            $options[] = [
                'Caption'     => $caption . ' ' . $i,
                'Color'       => -1,
                'IconActive'  => false,
                'IconValue'   => '',
                'Value'       => $i,
            ];
        }
        $presentation = self::TWINKLY_PRESENTATION_EFFECT;
        $presentation['OPTIONS'] = json_encode($options, JSON_UNESCAPED_UNICODE);
        return $presentation;
    }

    /**
     * Stores the movie list and updates the presentation of the movie variable if it has changed.
     *
     * @param array<int,array<string,mixed>> $movies List of movies (id, name, uid).
     *
     * @return void
     */
    private function UpdateMovies(array $movies): void
    {
        $encoded = json_encode($movies);
        if ($encoded === $this->ReadAttributeString('Movies')) {
            return;
        }
        $this->WriteAttributeString('Movies', $encoded);
        $this->RegisterVariableString('Movie', $this->Translate('Movie'), $this->GetMoviePresentation(), 4);
    }

    /**
     * Builds the movie presentation from the stored movie list.
     *
     * @return array<string,mixed> Enumeration presentation.
     */
    private function GetMoviePresentation(): array
    {
        $movies = json_decode($this->ReadAttributeString('Movies'), true);
        if (empty($movies)) {
            return $this->TranslatePresentation(self::TWINKLY_PRESENTATION_MOVIE, 'OPTIONS', 'Caption');
        }
        $options = [];
        foreach ($this->GetMovieCaptions($movies) as $uid => $caption) {
            $options[] = [
                'Caption'     => $caption,
                'Color'       => -1,
                'IconActive'  => false,
                'IconValue'   => '',
                'Value'       => (string) $uid,
            ];
        }
        $presentation = self::TWINKLY_PRESENTATION_MOVIE;
        $presentation['OPTIONS'] = json_encode($options, JSON_UNESCAPED_UNICODE);
        return $presentation;
    }

    /**
     * Builds the captions of all movies, duplicate names get the device id appended.
     *
     * @param array<int,array<string,mixed>> $movies List of movies (id, name, uid).
     *
     * @return array<string,string> Captions indexed by unique id.
     */
    private function GetMovieCaptions(array $movies): array
    {
        $counts = array_count_values(array_column($movies, 'name'));
        $captions = [];
        foreach ($movies as $movie) {
            $caption = $movie['name'];
            if ($counts[$movie['name']] > 1) {
                $caption = sprintf('%s (%d)', $movie['name'], $movie['id'] ?? 0);
            }
            $captions[(string) $movie['uid']] = $caption;
        }
        return $captions;
    }

    /**
     * Sets the brightness level.
     *
     * @param int $value Brightness value.
     *
     * @return bool True if the device accepted the command, otherwise false.
     */
    private function SetBrightness(int $value): bool
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return false;
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
        return $this->doBrightness($host, $token, $body) !== false;
    }

    /**
     * Sets the saturation level.
     *
     * @param int $value Saturation value.
     *
     * @return bool True if the device accepted the command, otherwise false.
     */
    private function SetSaturation(int $value): bool
    {
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return false;
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
        return $this->doSaturation($host, $token, $body) !== false;
    }

    /**
     * Validate the token and login to renew it.
     *
     * @return bool true if successful, otherwise false.
     */
    private function CheckLogin(): bool
    {
        $token = $this->ReadAttributeString('Token');
        $last = $this->ReadAttributeInteger('Validate');
        if ($token !== '' && (time() - $last) < self::TOKEN_LIFETIME) {
            return true;
        }
        return $this->RenewToken() !== null;
    }

    /**
     * Login to the device and store the new token (also called by TwinklyHelper if the device rejects a token).
     *
     * @return string|null New token if successful, otherwise null.
     */
    private function RenewToken(): ?string
    {
        // Invalidate the current token first
        $this->WriteAttributeInteger('Validate', 0);
        // Host
        $host = $this->ReadPropertyString('Host');
        // Debug
        $this->LogDebug(__FUNCTION__, 'Login to host: ' . $host);
        // Login
        $challange = $this->doLogin($host);
        // Check
        if ($challange === false) {
            $this->LogDebug(__FUNCTION__, 'Login failed!');
            return null;
        }
        // Validate
        $token = $challange['authentication_token'];
        $response = $challange['challenge-response'];
        // Check
        if ($this->doVerify($host, $token, $response) === false) {
            $this->LogDebug(__FUNCTION__, 'Verify failed!');
            return null;
        }
        // Token
        $this->WriteAttributeString('Token', $token);
        $this->WriteAttributeInteger('Validate', time());
        return $token;
    }

    /**
     * Synchronises all state variables with the device and reports the reachability as instance status.
     *
     * @return void
     */
    private function Update(): void
    {
        if ($this->CheckLogin() === false) {
            $this->OnUnreachable();
            return;
        }
        // Host
        $host = $this->ReadPropertyString('Host');
        // Token
        $token = $this->ReadAttributeString('Token');
        // Mode (also tells whether the device is on or off)
        $json = $this->doMode($host, $token);
        if ($json === false) {
            $this->OnUnreachable();
            return;
        }
        $this->OnReachable();
        $mode = (string) $json['mode'];
        $this->SetValueBoolean('Switch', $mode !== 'off');
        foreach (self::TWINKLY_MODES as [$index, $name]) {
            if ($name === $mode) {
                $this->SetValueInteger('Mode', $index);
                break;
            }
        }
        // All other values
        $this->Brightness();
        $this->Saturation();
        $this->Color();
        $this->Effect();
        $this->Movie();
    }

    /**
     * Device did not answer: report the error and pause the synchronisation after the configured days.
     *
     * Paused means checking only hourly, so the device is detected automatically when it is plugged in again.
     *
     * @return void
     */
    private function OnUnreachable(): void
    {
        $since = $this->ReadAttributeInteger('UnreachableSince');
        if ($since === 0) {
            $since = time();
            $this->WriteAttributeInteger('UnreachableSince', $since);
        }
        $days = $this->ReadPropertyInteger('PauseAfterDays');
        if ($days > 0 && (time() - $since) >= $days * 86400) {
            if ($this->GetStatus() !== self::STATUS_PAUSED) {
                $this->LogDebug(__FUNCTION__, 'Device not reachable since ' . date('Y-m-d H:i', $since) . ', synchronisation paused.');
                $this->SetTimerInterval('UpdateTimer', max(self::PAUSE_INTERVAL * 1000, $this->GetUpdateInterval()));
                $this->SetStatus(self::STATUS_PAUSED);
            }
            return;
        }
        $this->SetDeviceStatus(202);
    }

    /**
     * Device answered: end a paused synchronisation and report the instance as active.
     *
     * @return void
     */
    private function OnReachable(): void
    {
        if ($this->ReadAttributeInteger('UnreachableSince') !== 0) {
            $this->WriteAttributeInteger('UnreachableSince', 0);
            $this->SetTimerInterval('UpdateTimer', $this->GetUpdateInterval());
        }
        $this->SetDeviceStatus(102);
    }

    /**
     * Returns the configured update interval.
     *
     * @return int Interval in milliseconds, 0 if disabled.
     */
    private function GetUpdateInterval(): int
    {
        return max(0, $this->ReadPropertyInteger('UpdateInterval')) * 1000;
    }

    /**
     * Sets the instance status, but only if it has changed.
     *
     * @param int $status New status code.
     *
     * @return void
     */
    private function SetDeviceStatus(int $status): void
    {
        if ($this->GetStatus() !== $status) {
            $this->SetStatus($status);
        }
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
     * Sets timer information, but only if the timer settings have changed since the last transfer.
     *
     * @return void
     */
    private function SetTimer(): void
    {
        $timer = $this->ReadPropertyBoolean('TimerCheck');
        $config = json_encode([$timer, $this->ReadPropertyString('TimerOn'), $this->ReadPropertyString('TimerOff')]);
        $last = $this->ReadAttributeString('TimerConfig');
        if ($config === $last) {
            return;
        }
        // First run after update with disabled timer: keep a timer set via app untouched
        if ($last === '' && !$timer) {
            $this->WriteAttributeString('TimerConfig', $config);
            return;
        }
        if ($this->CheckLogin() === false) {
            $this->LogDebug(__FUNCTION__, 'Login error!');
            return;
        }
        // Timer
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
        if ($this->doTimer($host, $token, $body) !== false) {
            $this->WriteAttributeString('TimerConfig', $config);
        }
    }
}
