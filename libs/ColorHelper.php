<?php

/**
 * ColorHelper.php
 *
 * Part of the Trait-Libraray for IP-Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2022 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/**
 * Helper class for access satus variables.
 */
trait ColorHelper
{
    /**
     * Transform integer into rgb array.
     *
     * @param int $num Integer value of the color
     */
    protected function Int2RGB(int $num)
    {
        $rgb[0] = ($num & 0xFF0000) >> 16;
        $rgb[1] = ($num & 0x00FF00) >> 8;
        $rgb[2] = ($num & 0x0000FF);
        return $rgb;
    }

    /**
     * Transform rgb array into integer value.
     *
     * @param array $rgb Array (red,green,blue)
     */
    protected function RGB2Int(array $rgb)
    {
        $num = $rgb[0] << 16;
        $num += $rgb[1] << 8;
        $num += $rgb[2];
        return $num;
    }

    /**
     * Transform color string represantation into RGB array.
     *
     * @param string $str String value, e.g. FF88AA
     */
    protected function Str2RGB($str)
    {
        $str = preg_replace('~[^0-9a-f]~', '', $str);
        $rgb = str_split($str, 2);
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = intval($rgb[$i], 16);
        }
        return $rgb;
    }

    /**
     * Transform RGB values into hsl values (hue, saturation, and lightness).
     *
     * @param int $r Red (0..255)
     * @param int $g Green (0..255)
     * @param int $b Blue (0..255)
     */
    protected function RGB2HSL($r, $g, $b)
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $l = ($max + $min) / 2;
        $d = $max - $min;
        if ($d == 0) {
            $h = $s = 0;
        } else {
            $s = $d / (1 - abs(2 * $l - 1));
            switch ($max) {
                case $r:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;
                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;
                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
            }
        }
        return [round($h, 0), round($s * 100, 0), round($l * 100, 0)];
    }

    /**
     * Transform HSL values (hue, saturation, and lightness) into RGB (red, green, blue).
     *
     * @param int $h Hue (0..255)
     * @param int $s Saturation (0..255)
     * @param int $l Lightness (0..255)
     */
    protected function hsl2rgb($h, $s, $l)
    {
        $c = (1 - abs(2 * ($l / 100) - 1)) * $s / 100;
        $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
        $m = ($l / 100) - ($c / 2);
        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } elseif ($h < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } elseif ($h < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } elseif ($h < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } elseif ($h < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }
        return [floor(($r + $m) * 255), floor(($g + $m) * 255), floor(($b + $m) * 255)];
    }
}
