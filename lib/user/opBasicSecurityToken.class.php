<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opBasicSecurityToken
 *
 * @author Shogo Kawahara <kawahara@tejimaya.net>
 * @see          BasicSecurityToken
 */
class opBasicSecurityToken extends BasicSecurityToken {
  /**
   * @see BasicSecurityToken::createFromToken()
   */
  public static function createFromToken($token, $maxAge)
  {
    return new opBasicSecurityToken($token, $maxAge, null, null, null, null, null, null);
  }

  /**
   * @see BasicSecurityToken::createFromValues()
   */
  public static function createFromValues($owner, $viewer, $app, $domain, $appUrl, $moduleId)
  {
    return new opBasicSecurityToken(null, null, $owner, $viewer, $app, $domain, $appUrl, $moduleId);
  }

  protected function getCrypter() {
    return new opBasicBlobCrypter();
  }
}
