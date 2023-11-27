<?php

declare(strict_types=1);

// Generell funktions
require_once __DIR__ . '/../libs/_traits.php';

// CLASS TwinklyDiscovery
class TwinklyDiscovery extends IPSModule
{
    // Helper Traits
    use DebugHelper;

    // Discovery constant
    public const DISCOVERY_IP = '255.255.255.255';
    public const DISCOVERY_PORT = 5555;
    public const DISCOVERY_MSG = "\x01discover";
    public const DISCOVERY_TIMEOUT = 1;

    /**
     * Overrides the internal IPS_Create($id) function
     */
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        // Properties
        $this->RegisterPropertyInteger('TargetCategory', 0);
    }

    /**
     * Overrides the internal IPS_ApplyChanges($id) function
     */
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    /**
     * Internal function of the SDK.
     *
     * @access public
     */
    public function GetConfigurationForm()
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $devices = $this->DiscoverDevices();
        // Save location
        $location = $this->GetPathOfCategory($this->ReadPropertyInteger('TargetCategory'));
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
                            'location'      => $location,
                        ],
                    ],
                ];
            }
            $form['actions'][0]['values'] = $values;
        }
        return json_encode($form);
    }

    /**
     * Delivers all found devices.
     *
     * @return array configuration list all devices
     */
    private function DiscoverDevices()
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
        $this->SendDebug(__FUNCTION__, $data);
        // remove dublicates
        $data = array_unique($data, SORT_REGULAR);
        return $data;
    }

    /**
     * Returns the instance ID for a given device.
     *
     * @param string device IP adresss
     * @return array device instance id
     */
    private function GetTwinklyInstances($ip)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID('{A8ACEF24-02E6-A5A6-8409-64B16A8A3DC0}');
        foreach ($InstanceIDs as $id) {
            if (IPS_GetProperty($id, 'Host') == $ip) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * Returns the ascending list of category names for a given category id
     *
     * @param int $categoryId Category ID.
     * @return array List of reverse catergory names.
     */
    private function GetPathOfCategory(int $categoryId): array
    {
        if ($categoryId === 0) {
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
