<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 1:21 PM
 */

namespace OpenFW\Constants;


interface SystemEvents
{
    const BEFORE_LOAD_EVENT = 'app.before';
    const AFTER_LOAD_EVENT = 'app.after';

    const BUNDLE_NOT_FOUND_EVENT = 'app.bundle.missing';
    const BUNDLE_ENVIRONMENT_CHECK_FAIL_EVENT = 'app.bundle.env.invalid';
    const BEFORE_BUNDLE_INIT = 'app.bundle.before';
    const AFTER_BUNDLE_INIT = 'app.bundle.after';

    const CONTROLLER_NOT_FOUND_EVENT = 'app.controller.missing';
    const BEFORE_CONTROLLER_CALL_EVENT = 'app.controller.before';
    const AFTER_CONTROLLER_CALL_EVENT = 'app.controller.after';

    const ON_RUNTIME_EXCEPTION_EVENT = 'app.runtime.exception';
} 