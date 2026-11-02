<?php

declare(strict_types=1);

/** General functions */
require_once __DIR__ . '/../libs/_traits.php';

/** Namespaced traits */
use Wilkware\Twinkly\DebugHelper;
use Wilkware\Twinkly\FormHelper;

/**
 * CLASS TwinklyDiscovery
 */
class TwinklyDiscovery extends IPSModuleStrict
{
    // -------------------------------------------------------------------------
    // Traits
    // -------------------------------------------------------------------------

    use DebugHelper;
    use FormHelper;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    /** @var string Discovery IP */
    private const DISCOVERY_IP = '255.255.255.255';

    /** @var int Discovery Port */
    private const DISCOVERY_PORT = 5555;

    /** @var string Discovery Message */
    private const DISCOVERY_MSG = "\x01discover";

    /** @var int Discovery Timeout */
    private const DISCOVERY_TIMEOUT = 1;

    /** @var string Module GUID of Twinkly Device */
    private const DEVICE_GUID = '{A8ACEF24-02E6-A5A6-8409-64B16A8A3DC0}';

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
        // Properties (TargetCategory is obsolete since Symcon 7, kept for existing configurations)
        $this->RegisterPropertyInteger('TargetCategory', 0);
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
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $devices = $this->DiscoverDevices();
        $instances = $this->GetTwinklyInstances();
        // Build configuration list values
        $values = [];
        foreach ($devices as $device) {
            $values[] = [
                'instanceID'    => $instances[$device['host']] ?? 0,
                'host'          => $device['host'],
                'name'          => $device['name'],
                'state'         => $device['state'],
                'create'        => [
                    'moduleID'      => self::DEVICE_GUID,
                    'configuration' => ['Host' => $device['host']],
                ],
            ];
            unset($instances[$device['host']]);
        }
        // Existing instances whose device was not found (e.g. switched off)
        foreach ($instances as $host => $id) {
            $values[] = [
                'instanceID'    => $id,
                'host'          => $host,
                'name'          => IPS_GetName($id),
                'state'         => $this->Translate('Not found'),
            ];
        }
        $this->ModifyFormElement($form['actions'], 'Configurator', function (array &$element) use ($values): void
        {
            $element['values'] = $values;
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
    }

    /**
     * Delivers all found devices.
     *
     * @return array<int,mixed> configuration list all devices
     */
    private function DiscoverDevices(): array
    {
        // Format Response
        $format =
            'C4IP/' .    # Get the first 2 bytes
            'A2State/' . # Get the next 2 byte
            'A*Name';    # Get the next bytes

        // Create UDP Broadcast Socket
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            $this->LogMessage($this->Translate('Device search failed: ') . socket_strerror(socket_last_error()), KL_ERROR);
            return [];
        }
        socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec'=>self::DISCOVERY_TIMEOUT, 'usec'=>0]);
        if (socket_sendto($sock, self::DISCOVERY_MSG, strlen(self::DISCOVERY_MSG), 0, self::DISCOVERY_IP, self::DISCOVERY_PORT) === false) {
            $this->LogMessage($this->Translate('Device search failed: ') . socket_strerror(socket_last_error($sock)), KL_ERROR);
            socket_close($sock);
            return [];
        }

        // Collect all devices
        $data = [];
        while (true) {
            $ret = @socket_recvfrom($sock, $buf, 50, 0, $ip, $port);
            if ($ret === false) {
                break;
            }
            $array = unpack($format, $buf);
            $data[] = ['name' => $array['Name'], 'state' => $array['State'], 'host' => $array['IP4'] . '.' . $array['IP3'] . '.' . $array['IP2'] . '.' . $array['IP1']];
        }
        socket_close($sock);
        $this->LogDebug(__FUNCTION__, $data);
        // remove dublicates
        $data = array_unique($data, SORT_REGULAR);
        return $data;
    }

    /**
     * Returns all existing Twinkly device instances.
     *
     * @return array<string,int> Instance IDs indexed by device IP address
     */
    private function GetTwinklyInstances(): array
    {
        $instances = [];
        foreach (IPS_GetInstanceListByModuleID(self::DEVICE_GUID) as $id) {
            $instances[(string) IPS_GetProperty($id, 'Host')] = $id;
        }
        return $instances;
    }
}
