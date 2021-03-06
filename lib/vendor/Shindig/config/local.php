<?php
$webprefix = sfContext::getInstance()->getRequest()->getScriptName();
$shindigConfig = array(
  'debug' => false,
  'web_prefix' => $webprefix,
  'default_js_prefix' => $webprefix.'/gadgets/js/',
  'default_iframe_prefix' => $webprefix.'/gadgets/ifr?',

  'allow_plaintext_token' => false,
  'allow_anonymous_token' => false,

  'token_cipher_key' => SnsConfigPeer::get('shindig_token_cipher_key'),
  'token_hmac_key' => SnsConfigPeer::get('shindig_token_hmac_key'),
  'token_max_age' => SnsConfigPeer::get('shindig_token_max_age', 60*60),
  
  'base_path'      => sfConfig::get('sf_plugins_dir').'/opOpenSocialPlugin/lib/vendor/Shindig/',
  'features_path'  => sfConfig::get('sf_plugins_dir').'/opOpenSocialPlugin/lib/vendor/Shindig/features/',
  'container_path' => sfConfig::get('sf_app_cache_dir').'/plugins/opOpenSocialPlugin',

  'private_key_file' => sfConfig::get('sf_plugins_dir').'/opOpenSocialPlugin/certs/private.key', 
  'public_key_file'  => sfConfig::get('sf_plugins_dir').'/opOpenSocialPlugin/certs/public.crt',
  'private_key_phrase' => SnsConfigPeer::get('shindig_private_key_phrase'), 

  'remote_content'        => 'opBasicRemoteContent',
  'security_token_signer' => 'opBasicSecurityTokenDecoder',
  'security_token'        => 'opBasicSecurityToken',

  'person_service'   => 'opJsonDbOpensocialService',
  'activity_service' => 'opJsonDbOpensocialService',
  'app_data_service' => 'opJsonDbOpensocialService',
  'messages_service' => 'opJsonDbOpensocialService',

  'cache_time' => SnsConfigPeer::get('shindig_cache_time', 60*60),
  'cache_root' => sfConfig::get('sf_app_cache_dir').'/plugins/opOpenSocialPlugin',

  'curl_connection_timeout' => '15',
);
