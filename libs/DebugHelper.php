<?php

/**
 * DebugHelper.php
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
 * Helper class for the debug output.
 */
trait DebugHelper
{
    /**
     * Adds functionality to serialize arrays and objects.
     *
     * @param string $msg    Title of the debug message.
     * @param mixed  $data   Data output.
     * @param int    $format Output format.
     */
    protected function SendDebug($msg, $data, $format = 0)
    {
        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $this->SendDebug($msg . ':' . $key, $value, 1);
            }
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->SendDebug($msg . ':' . $key, $value, 0);
            }
        } elseif (is_bool($data)) {
            parent::SendDebug($msg, ($data ? 'TRUE' : 'FALSE'), 0);
        } else {
            parent::SendDebug($msg, $data, $format);
        }
    }

    /**
     * Wrapper for default modul log messages
     *
     * @param string $msg  Title of the log message.
     * @param int    $type message typ (KL_DEBUG| KL_ERROR| KL_MESSAGE| KL_NOTIFY (default)| KL_WARNING).
     */
    protected function LogMessage($msg, $type = KL_NOTIFY)
    {
        parent::LogMessage($msg, $type);
    }
}
