<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opBasicBlobCrypter
 *
 * @author Shogo Kawahara <kawahara@tejimaya.net>
 * @see    BasicBlobCrypter
 */
class opBasicBlobCrypter extends BlobCrypter {
  //FIXME make this compatible with the java's blobcrypter

  // Labels for key derivation
  private $CIPHER_KEY_LABEL = 0;
  private $HMAC_KEY_LABEL = 1;
  
  /** Key used for time stamp (in seconds) of data */
  public $TIMESTAMP_KEY = "t";
  
  /** minimum length of master key */
  public $MASTER_KEY_MIN_LEN = 16;
  
  /** allow three minutes for clock skew */
  private $CLOCK_SKEW_ALLOWANCE = 180;
  
  private $UTF8 = "UTF-8";
  
  protected $cipherKey;
  protected $hmacKey;
  protected $allowPlaintextToken;

  /**
   * @see BasicBlobCrypter::__construct()
   */
  public function __construct() {
    $this->cipherKey = Shindig_Config::get('token_cipher_key');
    $this->hmacKey = Shindig_Config::get('token_hmac_key');
    $this->allowPlaintextToken = Shindig_Config::get('allow_plaintext_token');
  }

  /**
   * @see BasicBlobCrypter::warp()
   */
  public function wrap(Array $in) {
    $encoded = $this->serializeAndTimestamp($in);
    $cipherText = opShindigCrypto::encrypt($this->cipherKey, $encoded);
    $hmac = Crypto::hmacSha1($this->hmacKey, $cipherText);
    $b64 = base64_encode($cipherText . $hmac);
    return $b64;
  }

  private function serializeAndTimestamp(Array $in) {
    $encoded = "";
    foreach ($in as $key => $val) {
      $encoded .= urlencode($key) . "=" . urlencode($val) . "&";
    }
    $encoded .= $this->TIMESTAMP_KEY . "=" . time();
    return $encoded;
  }

  /**
   * @see BasicBlobCrypter::unwrap();
   */
  public function unwrap($in, $maxAgeSec) {
    if ($this->allowPlaintextToken && count(explode(':', $in)) == 6) {
      $data = explode(":", $in);
      $out = array();
      $out['o'] = $data[0];
      $out['v'] = $data[1];
      $out['a'] = $data[2];
      $out['d'] = $data[3];
      $out['u'] = $data[4];
      $out['m'] = $data[5];
    } else {
      $bin = base64_decode($in);
      if (is_callable('mb_substr')) {
        $cipherText = mb_substr($bin, 0, - Crypto::$HMAC_SHA1_LEN, 'latin1');
        $hmac = mb_substr($bin, mb_strlen($cipherText, 'latin1'), Crypto::$HMAC_SHA1_LEN, 'latin1');
      } else {
        $cipherText = substr($bin, 0, - Crypto::$HMAC_SHA1_LEN);
        $hmac = substr($bin, strlen($cipherText));
      }
      Crypto::hmacSha1Verify($this->hmacKey, $cipherText, $hmac);
      $plain = base64_decode($cipherText);
      $plain = opShindigCrypto::decrypt($this->cipherKey, $cipherText);
      $out = $this->deserialize($plain);
      $this->checkTimestamp($out, $maxAgeSec);
    }
    return $out;
  }

  private function deserialize($plain) {
    $map = array();
    $items = split("[&=]", $plain);
    for ($i = 0; $i < count($items);) {
      $key = urldecode($items[$i ++]);
      $value = urldecode($items[$i ++]);
      $map[$key] = $value;
    }
    return $map;
  }

  private function checkTimestamp(Array $out, $maxAge) {
    $minTime = (int)$out[$this->TIMESTAMP_KEY] - $this->CLOCK_SKEW_ALLOWANCE;
    $maxTime = (int)$out[$this->TIMESTAMP_KEY] + $maxAge + $this->CLOCK_SKEW_ALLOWANCE;
    $now = time();
    if (! ($minTime < $now && $now < $maxTime)) {
      throw new BlobExpiredException("Security token expired");
    }
  }
}
