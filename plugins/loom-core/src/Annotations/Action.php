<?php
/**
 * Action Annotation
 *
 * Marks a method as a state action that can modify state.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Action {

    public const MODE_CLIENT = 'client';
    public const MODE_SERVER = 'server';
    public const MODE_BACKGROUND = 'background';

    public $mode = self::MODE_SERVER;
    public $debounce = null;
    public $confirm = null;

    public function __construct(
        $mode = self::MODE_SERVER,
        $debounce = null,
        $confirm = null
    ) {
        $this->mode = $mode;
        $this->debounce = $debounce;
        $this->confirm = $confirm;
    }
}
