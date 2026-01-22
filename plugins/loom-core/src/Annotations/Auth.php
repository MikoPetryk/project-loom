<?php
/**
 * Auth Annotation
 *
 * Requires authentication for a route or action.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Auth {

    public $capability = null;
    public $redirect = '/login';

    public function __construct(
        $capability = null,
        $redirect = '/login'
    ) {
        $this->capability = $capability;
        $this->redirect = $redirect;
    }
}
