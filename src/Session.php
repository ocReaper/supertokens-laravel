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
namespace SuperTokens\Session;

use ArrayObject;
use Illuminate\Http\Response;
use SuperTokens\Session\Exceptions\SuperTokensException;
use SuperTokens\Session\Exceptions\SuperTokensGeneralException;
use SuperTokens\Session\Exceptions\SuperTokensUnauthorizedException;
use SuperTokens\Session\Helpers\CookieAndHeader;
use SuperTokens\Session\Helpers\HandshakeInfo;

class Session
{
    /**
     * @var
     */
    private $sessionHandle;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var array | null
     */
    private $userDataInJWT;

    /**
     * @var Response
     */
    private $response;

    /**
     * SuperTokens constructor.
     * @param $sessionHandle
     * @param $userId
     * @param array | null $userDataInJWT
     * @param $response
     */
    public function __construct($sessionHandle, $userId, $userDataInJWT, $response)
    {
        $this->sessionHandle = $sessionHandle;
        $this->userId = $userId;
        $this->userDataInJWT = $userDataInJWT;
        $this->response = $response;
    }

    /**
     * @throws SuperTokensGeneralException
     * @throws SuperTokensException
     */
    public function revokeSession()
    {
        if (SessionHandlingFunctions::revokeSessionUsingSessionHandle($this->sessionHandle)) {
            $handshakeInfo = HandshakeInfo::getInstance();
            CookieAndHeader::clearSessionFromCookie($this->response, $handshakeInfo->cookieDomain, $handshakeInfo->cookieSecure, $handshakeInfo->accessTokenPath, $handshakeInfo->refreshTokenPath);
        }
    }

    /**
     * @return array | null
     * @throws SuperTokensException
     * @throws SuperTokensGeneralException
     * @throws SuperTokensUnauthorizedException
     */
    public function getSessionData()
    {
        try {
            return SessionHandlingFunctions::getSessionData($this->sessionHandle);
        } catch (SuperTokensUnauthorizedException $e) {
            $handshakeInfo = HandshakeInfo::getInstance();
            CookieAndHeader::clearSessionFromCookie($this->response, $handshakeInfo->cookieDomain, $handshakeInfo->cookieSecure, $handshakeInfo->accessTokenPath, $handshakeInfo->refreshTokenPath);
            throw $e;
        }
    }

    /**
     * @param array | null $newSessionData
     * @throws SuperTokensGeneralException
     * @throws SuperTokensUnauthorizedException
     * @throws SuperTokensException
     */
    public function updateSessionInfo($newSessionData)
    {
        try {
            SessionHandlingFunctions::updateSessionData($this->sessionHandle, $newSessionData);
        } catch (SuperTokensUnauthorizedException $e) {
            $handshakeInfo = HandshakeInfo::getInstance();
            CookieAndHeader::clearSessionFromCookie($this->response, $handshakeInfo->cookieDomain, $handshakeInfo->cookieSecure, $handshakeInfo->accessTokenPath, $handshakeInfo->refreshTokenPath);
            throw $e;
        }
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return array | null
     */
    public function getJWTPayload()
    {
        return $this->userDataInJWT;
    }
}