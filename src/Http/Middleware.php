<?php


namespace SuperTokens\Http;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use SuperTokens\Exceptions\SuperTokensGeneralException;
use SuperTokens\Exceptions\SuperTokensTokenTheftException;
use SuperTokens\Exceptions\SuperTokensTryRefreshTokenException;
use SuperTokens\Exceptions\SuperTokensUnauthorisedException;
use SuperTokens\Helpers\CookieAndHeader;
use SuperTokens\Helpers\HandshakeInfo;
use SuperTokens\SuperTokens;

class Middleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string $antiCsrfCheck
     * @return Response
     * @throws SuperTokensGeneralException
     * @throws SuperTokensTryRefreshTokenException
     * @throws SuperTokensUnauthorisedException
     * @throws SuperTokensTokenTheftException
     */
    public function handle(Request $request, Closure $next, string $antiCsrfCheck = null)
    {
        if (($request->isMethod('options')) || ($request->isMethod('trace'))) {
            return $next($request);
        }
        $session = null;
        $response = null;
        $refreshTokenPath = Config::get('supertokens.refreshTokenPath', HandshakeInfo::getInstance()->refreshTokenPath);
        if (
            (
                '/'.$request->path() === $refreshTokenPath
                ||
                '/'.$request->path() === $refreshTokenPath.'/'
                ||
                '/'.$request->path().'/' === $refreshTokenPath
            )
            &&
            $request->isMethod("post")
        ) {
            $session = SuperTokens::refreshSession($request, null);
            $request->merge(['supertokens' => $session]);
            $response = $next($request);
        } else {
            if (!isset($antiCsrfCheck)) {
                $antiCsrfCheckBoolean = !($request->isMethod('get'));
            } else {
                $antiCsrfCheckBoolean = strtolower($antiCsrfCheck) === "true";
            }
            $session = SuperTokens::getSession($request, null, $antiCsrfCheckBoolean);
            $request->merge(['supertokens' => $session]);
            $response = $next($request);
        }

        if ($session->removeCookies) {
            $handshakeInfo = HandshakeInfo::getInstance();
            CookieAndHeader::clearSessionFromCookie($response, $handshakeInfo->cookieDomain, $handshakeInfo->cookieSecure, $handshakeInfo->accessTokenPath, $handshakeInfo->refreshTokenPath, $handshakeInfo->sameSite);
        } else {
            if (count($session->newAccessTokenInfo) !== 0) {
                $accessToken = $session->newAccessTokenInfo;
                CookieAndHeader::attachAccessTokenToCookie($response, $accessToken['token'], $accessToken['expiry'], $accessToken['domain'], $accessToken['cookieSecure'], $accessToken['cookiePath'], $accessToken['sameSite']);
                CookieAndHeader::attachFrontTokenToHeader($response, $session->getUserId(), $accessToken['expiry'],  $accessToken['token']);
            }
            if (count($session->newRefreshTokenInfo) !== 0) {
                $refreshToken = $session->newRefreshTokenInfo;
                CookieAndHeader::attachRefreshTokenToCookie($response, $refreshToken['token'], $refreshToken['expiry'], $refreshToken['domain'], $refreshToken['cookieSecure'], $refreshToken['cookiePath'], $refreshToken['sameSite']);
            }
            if (count($session->newIdRefreshTokenInfo) !== 0) {
                $idRefreshToken = $session->newIdRefreshTokenInfo;
                CookieAndHeader::attachIdRefreshTokenToCookieAndHeader($response, $idRefreshToken['token'], $idRefreshToken['expiry'], $idRefreshToken['domain'], $idRefreshToken['cookieSecure'], $idRefreshToken['cookiePath'], $idRefreshToken['sameSite']);
            }
            if (isset($session->newAntiCsrfToken)) {
                CookieAndHeader::attachAntiCsrfHeader($response, $session->newAntiCsrfToken);
            }
        }
        return $response;
    }
}
