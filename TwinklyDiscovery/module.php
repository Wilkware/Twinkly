<?php

declare(strict_types=1);

/** Generell funktions */
require_once __DIR__ . '/../libs/_traits.php';

/** Namespaced traits */
use Wilkware\Twinkly\DebugHelper;

/**
 * CLASS TwinklyDiscovery
 */
class TwinklyDiscovery extends IPSModuleStrict
{
    // -------------------------------------------------------------------------
    // Traits
    // -------------------------------------------------------------------------

    use DebugHelper;

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
        // Version check
        $version = (float) IPS_GetKernelVersion();
        // Save location
        $location = $this->GetPathOfCategory($this->ReadPropertyInteger('TargetCategory'));
        // Enable or disable "TargetCategory" for 6.x
        if ($version < 7) {
            $form['elements'][2]['visible'] = true;
        }
        // Build configuration list values
        if (!empty($devices)) {
            foreach ($devices as $device) {
                $values[] = [
                    'instanceID'    => $this->GetTwinklyInstances($device['host']),
                    'host'          => $device['host'],
                    'name'          => $device['name'],
                    'state'         => $device['state'],
                    'create'        => [
                        [
                            'moduleID'      => '{A8ACEF24-02E6-A5A6-8409-64B16A8A3DC0}',
                            'configuration' => ['Host' => $device['host']],
                            'location'      => ($version < 7) ? $location : [],
                        ],
                    ],
                ];
            }
            $form['actions'][0]['values'] = $values;
        }
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
        socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec'=>self::DISCOVERY_TIMEOUT, 'usec'=>0]);
        socket_sendto($sock, self::DISCOVERY_MSG, strlen(self::DISCOVERY_MSG), 0, self::DISCOVERY_IP, self::DISCOVERY_PORT);

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
     * Returns the instance ID for a given device.
     *
     * @param string $ip device IP adresss
     *
     * @return int device instance id
     */
    private function GetTwinklyInstances(string $ip): int
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID('{A8ACEF24-02E6-A5A6-8409-64B16A8A3DC0}');
        foreach ($InstanceIDs as $id) {
            if (IPS_GetProperty($id, 'Host') == $ip) {
                return $id;
            }
        }
        return 1;
    }

    /**
     * Returns the ascending list of category names for a given category id
     *
     * @param int $categoryId Category ID.
     *
     * @return array<int,string> List of category names from root to leaf
     */
    private function GetPathOfCategory(int $categoryId): array
    {
        if (!IPS_CategoryExists($categoryId)) {
            return [];
        }

        $path[] = IPS_GetName($categoryId);
        $parentId = IPS_GetObject($categoryId)['ParentID'];

        while ($parentId > 0) {
            $path[] = IPS_GetName($parentId);
            $parentId = IPS_GetObject($parentId)['ParentID'];
        }

        return array_reverse($path);
    }
}
