<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/traits.php';  // General helper functions

// CLASS TwinklyDiscovery
class TwinklyDiscovery extends IPSModule
{
    use DebugHelper;

    // Discovery constant
    const DISCOVERY_IP = '255.255.255.255';
    const DISCOVERY_PORT = 5555;
    const DISCOVERY_MSG = "\x01discover";

    public function Create()
    {
        //Never delete this line!
        parent::Create();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetConfigurationForm()
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $devices = $this->DiscoverDevices();

        if (!empty($devices)) {
            foreach ($devices as $device) {
                $values[] = [
                    'instanceID' => $this->GetTwinklyInstances($device['ip']),
                    'host'       => $host,
                    'name'       => $device['name'],
                    'state'      => $device['state'],
                    'create'     => [
                        [
                            'moduleID'      => '{A8ACEF24-02E6-A5A6-8409-64B16A8A3DC0}',
                            'configuration' => [
                                'Host' => $host, ], ],
                    ],
                ];
            }
        }
        $Form['actions'][0]['values'] = $values;
        return json_encode($Form);
    }

    private function GetTwinklyInstances($ip)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID('{6EFF1F3C-DF5F-43F7-DF44-F87EFF149566}');
        foreach ($InstanceIDs as $id) {
            if (IPS_GetProperty($id, 'Host') == $ip) {
                return $id;
            }
        }
        return 0;
    }
}
