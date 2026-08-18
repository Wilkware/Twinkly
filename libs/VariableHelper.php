<?php

/**
 * VariableHelper.php
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
 * Helper class for access satus variables.
 */
trait VariableHelper
{
    /**
     * Update a boolean value.
     *
     * @param string $ident Ident of the boolean variable
     * @param bool   $value Value of the boolean variable
     *
     * @return void
     */
    protected function SetValueBoolean(string $ident, bool $value): void
    {
        $id = @$this->GetIDForIdent($ident);
        if (IPS_VariableExists($id)) {
            $this->SetValue($ident, $value);
        }
    }

    /**
     * Update a string value.
     *
     * @param string $ident Ident of the string variable
     * @param string $value Value of the string variable
     *
     * @return void
     */
    protected function SetValueString(string $ident, string $value): void
    {
        $id = @$this->GetIDForIdent($ident);
        if (IPS_VariableExists($id)) {
            $this->SetValue($ident, $value);
        }
    }

    /**
     * Update a integer value.
     *
     * @param string $ident Ident of the integer variable
     * @param int    $value Value of the integer variable
     *
     * @return void
     */
    protected function SetValueInteger(string $ident, int $value): void
    {
        $id = @$this->GetIDForIdent($ident);
        if (IPS_VariableExists($id)) {
            $this->SetValue($ident, $value);
        }
    }

    /**
     * Update a float value.
     *
     * @param string $ident Ident of the float variable
     * @param float  $value Value of the float variable
     *
     * @return void
     */
    protected function SetValueFloat(string $ident, float $value): void
    {
        $id = @$this->GetIDForIdent($ident);
        if (IPS_VariableExists($id)) {
            $this->SetValue($ident, $value);
        }
    }

    /**
     * Sets the variable inactive.
     *
     * @param string $ident Ident of the integer variable.
     * @param bool   $value Enable or disable value the variable.
     *
     * @return void
     */
    protected function SetVariableDisabled(string $ident, bool $value): void
    {
        $id = @$this->GetIDForIdent($ident);
        if (IPS_VariableExists($id)) {
            IPS_SetDisabled($id, $value);
        }
    }

    /**
     * Check if the identifier is a valid variable identifier
     *
     * @param string $ident Variable identifier
     * @param bool   $exist may exist variable
     *
     * @return string (correct) variable identifier
     */
    protected function GetVariableIdent(string $ident, bool $exist = false): string
    {
        // Replace not allowed chars
        $fixchar = ['/ä/', '/ö/', '/ü/', '/Ä/', '/Ö/', '/Ü/', '/ß/'];
        $replace = ['ae', 'oe', 'ue', 'AE', 'OE', 'UE', 'ss'];
        $ident = preg_replace($fixchar, $replace, $ident);

        // Replace spaces with underscores
        $ident = str_replace(' ', '_', $ident);

        // If the passed identifier is empty, simply set it to underscore
        if (empty($ident)) {
            $ident = '_';
        }

        // Allow only allowed characters
        $ident = preg_replace('/[^a-z0-9_]+/i', '', $ident);

        // If the identifier starts with a number, prepend an underscore
        //if (preg_match('/^[0-9]/', $ident)) {
        //    $ident = '_' . $ident;
        //}

        // If the identifier is already in use, append a number to make it unique
        if ($exist) {
            $counter = 1;
            $originalIdent = $ident;
            while (IPS_VariableExists(@$this->GetIDForIdent($ident))) {
                $ident = $originalIdent . '_' . $counter;
                $counter++;
            }
        }

        return $ident;
    }

    /**
     * Translate all specific values recursively inside a configuration array.
     *
     * @param array<string,mixed> $configuration Configuration structure
     * @param string              $index         Index of the configuration array to translate
     * @param string              $key           Key of the configuration array to translate
     *
     * @return array<string,mixed> Modified configuration array
     */
    protected function TranslatePresentation(array $configuration, ?string $index = null, ?string $key = null): array
    {
        // Case 1: JSON array of objects at a specific index (e.g. OPTIONS -> Caption)
        if ($index !== null && $index !== '' && $key !== null && $key !== '' && array_key_exists($index, $configuration)) {
            $template = json_decode($configuration[$index], true);
            if (is_array($template)) {
                foreach ($template as &$a) {
                    if (isset($a[$key])) {
                        $a[$key] = $this->Translate($a[$key]);
                    }
                }
                unset($a);
                $configuration[$index] = json_encode($template, JSON_UNESCAPED_UNICODE);
            }
        }

        // Case 2: all "normal" string values on the top level translate (e.g. PREFIX, SUFFIX)
        foreach ($configuration as $k => $v) {
            if ($k === $index) {
                continue; // was possibly already handled above as a JSON array
            }
            if (is_string($v) && $v !== '') {
                $configuration[$k] = $this->Translate($v);
            }
        }

        return $configuration;
    }
}