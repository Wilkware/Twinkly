<?php

/**
 * FormatHelper.php
 *
 * Part of the Trait-Libraray for IP-Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2025 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/** @symcon-namespace */

namespace Wilkware\Twinkly;

/**
 * Helper class for formating and output json data.
 */
trait FormatHelper
{
    /**
     * Pretty print json(array) data.
     *
     * @param array<int,array{0:string,1:string,2:int,3:?string}> $map Json keys to translation.
     * @param string $json       Json data string.
     * @param bool   $associated Print values with no json representation.
     * @param string $undefined  Output for no data.
     *
     * @return string Pretty formated string.
     */
    private function PrettyPrint(?array $map, string $json, bool $associated = false, string $undefined = 'undefined'): string
    {
        $ret = '';
        // check json data
        if (empty($json)) {
            return $ret;
        }
        // json to array
        $data = json_decode($json, true);
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

    /**
     * Get HTML rgb formated color.
     *
     * @param int $color Color value or -1 for transparency
     *
     * @return string HTML coded color or empty string
     */
    private function GetColorFormatted(int $color): string
    {
        if ($color != '-1') {
            return '#' . sprintf('%06X', $color);
        } else {
            return '';
        }
    }

    /**
     * Converts a hex color string (#RRGGBB or RRGGBB) to an integer.
     * Returns -1 if the input is empty, null, or invalid.
     *
     * Examples:
     *   colorHexToInt('#FF00AA') → 16711850
     *   colorHexToInt('FF00AA')  → 16711850
     *   colorHexToInt('')        → -1
     *   colorHexToInt(null)      → -1
     *   colorHexToInt('#XYZ123') → -1
     *
     * @param string|null $hex Hex color string with or without leading '#'
     *
     * @return int RGB integer representation or -1 on error
     */
    private function GetColorUnformatted(?string $hex): int
    {
        // Empty or null input → return -1
        if (empty($hex)) {
            return -1;
        }
        // Remove leading '#' if present
        $hex = ltrim($hex, '#');
        // Validate hex format (must be 6 hex digits)
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            return -1;
        }
        // Convert to integer
        return hexdec($hex);
    }

    /**
     * Get media type
     *
     * @param string $ext File extention
     * @return string Media type data prefix
     */
    private function GetMediaType(string $ext): string
    {
        $type = '';
        switch ($ext) {
            case 'bmp':
                $type = 'data:image/bmp;base64,';
                break;
            case 'jpg':
            case 'jpeg':
                $type = 'data:image/jpeg;base64,';
                break;
            case 'gif':
                $type = 'data:image/gif;base64,';
                break;
            case 'png':
                $type = 'data:image/png;base64,';
                break;
            case 'ico':
                $type = 'data:image/x-icon;base64,';
                break;
            case 'webp':
                $type = 'data:image/webp;base64,';
                break;
        }
        return $type;
    }

    /**
     * Returns a media object as base 64 image.
     *
     * @param int $media Media ID
     *
     * @return string Base64 coded image data
     */
    private function GetBase64Image(int $media): string
    {
        if (!IPS_MediaExists($media)) {
            return '';
        }
        $image = IPS_GetMedia($media);
        if ($image['MediaType'] !== MEDIATYPE_IMAGE) {
            return '';
        }

        $file = explode('.', $image['MediaFile']);
        $mime = $this->GetMediaType(end($file));
        // Unsupported file type
        if (!$mime) {
            return '';
        }

        $content = @IPS_GetMediaContent($media);
        // Could not read media content
        if ($content === false || $content === '') {
            return '';
        }

        return $mime . $content;
    }
}