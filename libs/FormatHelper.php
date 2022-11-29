<?php

/**
 * FormatHelper.php
 *
 * Part of the Trait-Libraray for IP-Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2021 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/**
 * Helper class for access satus variables.
 */
trait FormatHelper
{
    /**
     * pretty print json(array) data.
     *
     * @param array $map Json keys to translation
     * @param array|string $json Json data string
     * @param bool $associated Print values with no json representation
     * @param string $undefined Output for no data
     */
    private function PrettyPrint(?array $map, $json, bool $associated = false, string $undefined = 'undefined')
    {
        $ret = '';
        // check json data
        if (empty($json)) {
            return $ret;
        }
        $data = $json;
        // json to array?
        if (!is_array($json)) {
            $data = json_decode($json, true);
        }
        // check Json data
        if (empty($map)) {
            $pretty = json_encode($data, JSON_PRETTY_PRINT);
            $ret = str_replace(["{\n", '}', '    ', ',', '"'], '', $pretty);
        } else {
            foreach ($map as $entry) {
                if (array_key_exists($entry[0], $data)) {
                    $ret .= $this->Translate($entry[1]) . ': '; // Translate
                    switch ($entry[2]) {
                        case 0: // boolean (YES/MO)
                            $ret .= $this->Translate($data[$entry[0]] ? 'YES' : 'NO');
                            break;
                        case 1: // integer
                        case 2: // float
                        case 3: // string
                            $ret .= $data[$entry[0]];
                            break;
                        case 4: // date/time
                            if (!empty($data[$entry[0]])) {
                                $ret .= strftime('%a, %d.%b %Y, %H:%M', strtotime($data[$entry[0]]));
                            }
                            break;
                        case 5: // boolean (ON/OFF)
                            $ret .= $this->Translate($data[$entry[0]] ? 'ON' : 'OFF');
                            break;
                    }
                    if (isset($entry[3])) {
                        $ret .= $entry[3];
                    }
                    $ret .= "\n";
                } elseif ($associated) {
                    $ret .= $entry[1] . ': ' . $this->Translate($undefined) . "\n";
                }
            }
        }
        return $ret;
    }
}
