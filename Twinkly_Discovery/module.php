<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/traits.php';  // General helper functions

// CLASS TwinklyDiscovery
class TwinklyDiscovery extends IPSModule
{
    // Helper Traits
    use DebugHelper;

    // Discovery constant
    const DISCOVERY_IP = '255.255.255.255';
    const DISCOVERY_PORT = 5555;
    const DISCOVERY_MSG = "\x01discover";

    /**
     * Overrides the internal IPS_Create($id) function
     */
    public function Create()
    {
        //Never delete this line!
        parent::Create();
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

        if (!empty($devices)) {
            foreach ($devices as $device) {
                $values[] = [
                    'instanceID' => $this->GetTwinklyInstances($device['host']),
                    'host'       => $device['host'],
                    'name'       => $device['name'],
                    'state'      => $device['state'],
                    'create'     => [
                        [
                            'moduleID'      => '{A8ACEF24-02E6-A5A6-8409-64B16A8A3DC0}',
                            'configuration' => [
                                'Host' => $device['host'], ], ],
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
        socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec'=>5, 'usec'=>0]);
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
}
