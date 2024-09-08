<?php

/**
 * VariableHelper.php
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
trait VariableHelper
{
    /**
     * Update a boolean value.
     *
     * @param string $ident Ident of the boolean variable
     * @param bool   $value Value of the boolean variable
     */
    protected function SetValueBoolean(string $ident, bool $value)
    {
        $id = @$this->GetIDForIdent($ident);
        if ($id !== false) {
            SetValueBoolean($id, $value);
        }
    }

    /**
     * Update a string value.
     *
     * @param string $ident Ident of the string variable
     * @param string $value Value of the string variable
     */
    protected function SetValueString(string $ident, string $value)
    {
        $id = @$this->GetIDForIdent($ident);
        if ($id !== false) {
            SetValueString($id, $value);
        }
    }

    /**
     * Update a integer value.
     *
     * @param string $ident Ident of the integer variable
     * @param int    $value Value of the integer variable
     */
    protected function SetValueInteger(string $ident, int $value)
    {
        $id = @$this->GetIDForIdent($ident);
        if ($id !== false) {
            SetValueInteger($id, $value);
        }
    }

    /**
     * Update a float value.
     *
     * @param string $ident Ident of the float variable
     * @param float  $value Value of the float variable
     */
    protected function SetValueFloat(string $ident, float $value)
    {
        $id = @$this->GetIDForIdent($ident);
        if ($id !== false) {
            SetValueFloat($id, $value);
        }
    }

    /**
     * Sets the variable inactive.
     *
     * @param string $ident Ident of the integer variable
     * @param bool   $value Enable or disable value the variable
     */
    protected function SetVariableDisabled(string $ident, bool $value)
    {
        $id = @$this->GetIDForIdent($ident);
        if ($id !== false) {
            IPS_SetDisabled($id, $value);
        }
    }

    /**
     * Check if the identifier is a valid variable identifier
     *
     * @param string $ident Variable identifier
     * @param bool $exist may exist variable
     * @return string (correct) variable identifier
     */
    protected function GetVariableIdent(string $ident, bool $exist = false)
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
            while (@$this->GetIDForIdent($ident) !== false) {
                $ident = $originalIdent . '_' . $counter;
                $counter++;
            }
        }

        return $ident;
    }
}
