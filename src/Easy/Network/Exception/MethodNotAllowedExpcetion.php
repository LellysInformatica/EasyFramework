<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Network\Exception;

use Exception;
use HttpException;

/**
 * Represents an HTTP 405 error.
 */
class MethodNotAllowedHttpException extends HttpException
{

    /**
     * Constructor.
     *
     * @param array      $allow    An array of allowed methods
     * @param string     $message  The internal exception message
     * @param Exception $previous The previous exception
     * @param integer    $code     The internal exception code
     */
    public function __construct(array $allow, $message = null, Exception $previous = null, $code = 0)
    {
        $headers = array('Allow' => strtoupper(implode(', ', $allow)));

        parent::__construct(405, $message, $previous, $headers, $code);
    }

}