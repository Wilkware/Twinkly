<?php

/**
 * FormHelper.php
 *
 * Part of the Trait-Library for Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2026 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/** @symcon-namespace */

namespace Wilkware\Twinkly;

/**
 * Helper class for access form definitions.
 */
trait FormHelper
{
    /**
     * Recursively searches a form items array for an element with a
     * matching "name" property and applies a callback to it.
     *
     * Works for any element type (Label, Button, List, PopupAlert, etc.),
     * as long as the element defines a "name" key. Descends into nested
     * "items" arrays (e.g. RowLayout, ColumnLayout, ExpansionPanel) so the
     * target element does not need to be addressed by a fixed, fragile
     * array index.
     *
     * @param array<mixed> $items    Reference to the array of form items to search (modified in place).
     * @param string       $name     Exact value of the "name" property to find.
     * @param callable     $callback Callback invoked with a reference to the matching element.
     *                               Signature: function (array &$element): void
     *
     * @return bool True if a matching element was found and modified, false otherwise.
     */
    private function ModifyFormElement(array &$items, string $name, callable $callback): bool
    {
        foreach ($items as &$item) {
            if (isset($item['name']) && $item['name'] === $name) {
                $callback($item);
                return true;
            }

            if (isset($item['items']) && is_array($item['items'])) {
                if ($this->ModifyFormElement($item['items'], $name, $callback)) {
                    return true;
                }
            }
        }

        return false;
    }
}