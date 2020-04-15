<?php
/* Copyright (c) 2020, VRAI Labs and/or its affiliates. All rights reserved.
 *
 * This software is licensed under the Apache License, Version 2.0 (the
 * "License") as published by the Apache Software Foundation.
 *
 * You may not use this file except in compliance with the License. You may
 * obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
namespace SuperTokens\Session\Exceptions;

use Exception;
use Throwable;

class SuperTokensGeneralException extends SuperTokensException
{

    /**
     * SuperTokensGeneralException constructor.
     * @param $anything
     * @param Throwable|null $previous
     */
    public function __construct($anything, Throwable $previous = null)
    {
        $message = "General error";
        if (is_string($anything)) {
            $message = $anything;
        } elseif ($anything instanceof Exception) {
            if (!isset($previous)) {
                $previous = $anything;
            } else {
                $message = $anything->getMessage();
            }
        }
        parent::__construct($message, $previous);
    }
}