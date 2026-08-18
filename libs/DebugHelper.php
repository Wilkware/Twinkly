<?php

/**
 * DebugHelper.php
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
 * Helper class for the debug output.
 */
trait DebugHelper
{
    /**
     * Adds functionality to serialize arrays and objects for debugging.
     *
     * @param string $msg       Title of the debug message.
     * @param mixed  $data      Data to be logged (array, object, scalar, etc.).
     * @param bool   $multiline Output arrays/objects in separate lines if true, otherwise as string.
     *
     * @return void
     */
    protected function LogDebug(string $msg, $data, bool $multiline = true): void
    {
        if (is_array($data) || is_object($data)) {
            if ($multiline) {
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $k=>$v) {
                            if (is_array($v)) {
                                $this->SendDebug($msg, '[' . $key . '][' . $k . '] => ' . $this->Stringify($v), 0);
                            }else {
                                $this->SendDebug($msg, '[' . $key . '][' . $k . '] => ' . $v, 0);
                            }
                        }
                    }
                    else {
                        $this->SendDebug($msg, '[' . $key . '] => ' . $this->Stringify($value), 0);
                    }
                }
            } else {
                $this->SendDebug($msg, $this->Stringify($data), 0);
            }
        } elseif (is_bool($data)) {
            $this->SendDebug($msg, $data ? 'true' : 'false', 0);
        } else {
            $this->SendDebug($msg, is_scalar($data) ? (string) $data : json_encode($data), 0);
        }
    }

    /**
     * Wrapper to print various object/variable types.
     *
     * @param mixed $var Variable to string.
     *
     * @return string Line based print message.
     */
    protected function Stringify($var): string
    {
        if (is_array($var) || is_object($var)) {
            $json = json_encode($var, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json !== false ? $json : '[unserializable]';
        }
        if (is_bool($var)) {
            return $var ? 'true' : 'false';
        }
        if ($var === null) {
            return 'null';
        }
        return (string) $var;
    }

    /**
     * Wrapper for default modul log messages
     *
     * @param string $msg  Title of the log message.
     * @param int    $type message typ (KL_DEBUG| KL_ERROR| KL_MESSAGE| KL_NOTIFY (default)| KL_WARNING).
     *
     * @return bool  Always true
     */
    protected function LogMessage($msg, $type = KL_NOTIFY): bool
    {
        return parent::LogMessage($msg, $type);
    }
}