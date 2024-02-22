<?php

/**
 * ProfileHelper.php
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

/**
 * Helper class for create variable profiles.
 */
trait ProfileHelper
{
    /**
     * Create the profile for the given type with the passed name.
     *
     * @param string $name    Profil name.
     * @param string $vartype Type of the variable.
     */
    protected function RegisterProfileType($name, $vartype)
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, $vartype);
        } else {
            $profile = IPS_GetVariableProfile($name);
            if ($profile['ProfileType'] != $vartype) {
                throw new Exception('Variable profile type does not match for profile ' . $name);
            }
        }
    }

    /**
     * Create a profile for boolean values.
     *
     * @param string $name   Profil name.
     * @param string $icon   Icon to display.
     * @param string $prefix Variable prefix.
     * @param string $suffix Variable suffix.
     * @param array  $asso   Associations of the values.
     */
    protected function RegisterProfileBoolean($name, $icon, $prefix, $suffix, $asso = null)
    {
        $this->RegisterProfileType($name, VARIABLETYPE_BOOLEAN);

        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);

        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $this->Translate($ass[1]), $ass[2], $ass[3]);
            }
        }
    }

    /**
     * Create a profile for integer values.
     *
     * @param string $name     Profil name.
     * @param string $icon     Icon to display.
     * @param string $prefix   Variable prefix.
     * @param string $suffix   Variable suffix.
     * @param int    $minvalue Minimum value.
     * @param int    $maxvalue Maximum value.
     * @param int    $stepsize Increment.
     * @param array  $asso     Associations of the values.
     */
    protected function RegisterProfileInteger($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $asso = null)
    {
        $this->RegisterProfileType($name, VARIABLETYPE_INTEGER);

        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $stepsize);

        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $this->Translate($ass[1]), $ass[2], $ass[3]);
            }
        }
    }

    /**
     * Create a profile for float values.
     *
     * @param string $name     Profil name.
     * @param string $icon     Icon to display.
     * @param string $prefix   Variable prefix.
     * @param string $suffix   Variable suffix.
     * @param int    $minvalue Minimum value.
     * @param int    $maxvalue Maximum value.
     * @param int    $stepsize Increment.
     * @param int    $digits   Decimal places.
     * @param array  $asso     Associations of the values.
     */
    protected function RegisterProfileFloat($name, $icon, $prefix, $suffix, $minvalue, $maxvalue, $stepsize, $digits, $asso = null)
    {
        $this->RegisterProfileType($name, VARIABLETYPE_FLOAT);

        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);
        IPS_SetVariableProfileValues($name, $minvalue, $maxvalue, $stepsize);
        IPS_SetVariableProfileDigits($name, $digits);

        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $this->Translate($ass[1]), $ass[2], $ass[3]);
            }
        }
    }

    /**
     * Create a profile for string values.
     *
     * @param string $name   Profil name.
     * @param string $icon   Icon to display.
     * @param string $prefix Variable prefix.
     * @param string $suffix Variable suffix.
     * @param array  $asso     Associations of the values.
     */
    protected function RegisterProfileString($name, $icon, $prefix, $suffix, $asso)
    {
        $this->RegisterProfileType($name, VARIABLETYPE_STRING);

        IPS_SetVariableProfileIcon($name, $icon);
        IPS_SetVariableProfileText($name, $prefix, $suffix);

        if (($asso !== null) && (count($asso) !== 0)) {
            foreach ($asso as $ass) {
                IPS_SetVariableProfileAssociation($name, $ass[0], $this->Translate($ass[1]), $ass[2], $ass[3]);
            }
        }
    }

    /**
     * Returns the used profile name of a variable
     *
     * @param int $id Variable ID
     * @return string Empty, or name of the profile
     */
    protected function GetVariableProfile($id)
    {
        $variableProfileName = IPS_GetVariable($id)['VariableCustomProfile'];
        if ($variableProfileName == '') {
            $variableProfileName = IPS_GetVariable($id)['VariableProfile'];
        }
        return $variableProfileName;
    }
}
