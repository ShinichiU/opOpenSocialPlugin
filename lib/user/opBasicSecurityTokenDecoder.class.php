<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opBasicSecurityTokenDecoder
 *
 * @author Shogo Kawahara <kawahara@tejimaya.net>
 * @see    BasicSecurityTokenDecoder
 */
class opBasicSecurityTokenDecoder extends SecurityTokenDecoder {
  private $OWNER_INDEX = 0;
  private $VIEWER_INDEX = 1;
  private $APP_ID_INDEX = 2;
  private $CONTAINER_INDEX = 3;
  private $APP_URL_INDEX = 4;
  private $MODULE_ID_INDEX = 5;

  /**
   * 
   * @see BasicSecurityTokenDecoder::createToken()
   */
  public function createToken($stringToken)
  {
    if (empty($stringToken) && ! empty($_GET['authz']))
    {
      throw new GadgetException('INVALID_GADGET_TOKEN');
    }
    try
    {
      if (Shindig_Config::get('allow_plaintext_token') && count(explode(':', $stringToken)) == 6)
      {
        $tokens = explode(":", $stringToken);
        return new opBasicSecurityToken(null, null, urldecode($tokens[$this->OWNER_INDEX]), urldecode($tokens[$this->VIEWER_INDEX]), urldecode($tokens[$this->APP_ID_INDEX]), urldecode($tokens[$this->CONTAINER_INDEX]), urldecode($tokens[$this->APP_URL_INDEX]), urldecode($tokens[$this->MODULE_ID_INDEX]));
      }
      else
      {
        return opBasicSecurityToken::createFromToken($stringToken, Shindig_Config::get('token_max_age'));
      }
    }
    catch (Exception $e)
    {
      throw new GadgetException('INVALID_GADGET_TOKEN');
    }
  }
}
