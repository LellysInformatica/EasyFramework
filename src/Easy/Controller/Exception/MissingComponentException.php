<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 1.6
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Controller\Exception;

/**
 * Used when a component cannot be found.
 *
 * @package       Easy.Error
 */
class MissingComponentException extends ControllerException
{

    protected $_messageTemplate = 'Component class %s could not be found.';

}
