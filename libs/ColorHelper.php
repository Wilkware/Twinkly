<?php

/**
 * ColorHelper.php
 *
 * Part of the Trait-Libraray for IP-Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2020 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/** @symcon-namespace */

namespace Wilkware\Twinkly;

/**
 * Helper class for color handling.
 */
trait ColorHelper
{
    /**
     * Convert color integer value in RGB array
     *
     * @param int $num Color value as integer.
     *
     * @return array{0:int,1:int,2:int} RGB array.
     */
    private function int2rgb(int $num): array
    {
        $rgb[0] = ($num & 0xFF0000) >> 16;
        $rgb[1] = ($num & 0x00FF00) >> 8;
        $rgb[2] = ($num & 0x0000FF);
        return $rgb;
    }

    /**
     * Convert RGB array in color integer value.
     *
     * @param array{0:int,1:int,2:int} $rgb RGB array
     *
     * @return int Color value
     */
    private function rgb2int(array $rgb): int
    {
        $num = $rgb[0] << 16;
        $num += $rgb[1] << 8;
        $num += $rgb[2];
        return intval($num);
    }

    /**
     * Convert Hex color string in RGB array.
     *
     * @param string $str Hexadecimal color string
     *
     * @return array{0: int, 1: int, 2: int} RGB array
     */
    private function str2rgb(string $str): array
    {
        $str = preg_replace('~[^0-9a-f]~', '', $str);
        $rgb = str_split($str, 2);
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = intval($rgb[$i], 16);
        }
        return $rgb;
    }

    /**
     * Convert RGB values in HSL array.
     *
     * @param int $r Red value
     * @param int $g Green value
     * @param int $b Blue value
     *
     * @return array{0: int, 1: int, 2: int} HSL array
     */
    private function rgb2hsl(int $r, int $g, int $b): array
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
                    if ($b > $g) $h += 360;
                    break;
                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;
                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
                default:
                    $h = 0;
                    break;
            }
        }
        return [intval(round($h, 0)), intval(round($s * 100, 0)), intval(round($l * 100, 0))];
    }

    /**
     * Convert HSL values in RGB array.
     *
     * @param int $h Hue value
     * @param int $s Saturation value
     * @param int $l Lightness value
     *
     * @return array{0:int,1:int,2:int} RGB array
     */
    private function hsl2rgb(int $h, int $s, int $l): array
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
        return [intval(floor(($r + $m) * 255)), intval(floor(($g + $m) * 255)), intval(floor(($b + $m) * 255))];
    }
}
