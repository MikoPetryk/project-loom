<?php
/**
 * State Annotation
 *
 * Marks a class as a reactive state container.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class State {

    public const PERSIST_NONE = 'none';
    public const PERSIST_SESSION = 'session';
    public const PERSIST_LOCAL = 'local';
    public const PERSIST_DATABASE = 'database';

    public const SCOPE_PAGE = 'page';
    public const SCOPE_USER = 'user';
    public const SCOPE_GLOBAL = 'global';

    public $persist = self::PERSIST_SESSION;
    public $sync = true;
    public $scope = self::SCOPE_USER;
    public $name = null;

    public function __construct(
        $persist = self::PERSIST_SESSION,
        $sync = true,
        $scope = self::SCOPE_USER,
        $name = null
    ) {
        $this->persist = $persist;
        $this->sync = $sync;
        $this->scope = $scope;
        $this->name = $name;
    }
}
