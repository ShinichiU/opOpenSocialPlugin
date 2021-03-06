<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * application components
 *
 * @package    OpenPNE
 * @subpackage opOpenSocialPlugin
 * @author     Shogo Kawahara <kawahara@tejimaya.net>
 */
class applicationComponents extends sfComponents
{
  public function executeGadget()
  {
    $memberApp = $this->memberApp;
    $modId     = $memberApp->getId();
    $this->mid  = $modId;
    $app = $memberApp->getApplication();
    $this->title = $app->getTitle();
    $ownerId = $memberApp->getMemberId();
    $appId   = $app->getId();
    $url     = $app->getUrl();
    $culture = $this->getUser()->getCulture();
    $culture = split("_",$culture);
    $this->aid       = $appId;
    $this->height    = $app->getHeight() ? $app->getHeight() : 200;
    $this->scrolling = $app->getScrolling();

    $viewerId = $this->getUser()->getMemberId();

    $this->isViewer = false;
    if ($ownerId == $viewerId)
    {
      $this->isViewer = true;
    }

    $this->hasSetting = true;
    if ($memberApp->getIsGadget() && !$app->hasSetting())
    {
      $this->hasSetting = false;
    }

    $isUseOuterShindig = SnsConfigPeer::get('is_use_outer_shindig');
    $isDevEnvironment = null;
    if ($isUseOuterShindig)
    {
      $isDevEnvironment = false;
    }

    $opOpenSocialContainerConfig = new opOpenSocialContainerConfig();
    $containerName = $opOpenSocialContainerConfig->getContainerName();

    $securityToken = opBasicSecurityToken::createFromValues(
      $ownerId,  // owner
      $viewerId, // viewer
      $appId,    // app id
      $containerName,  // domain key
      urlencode($url), // app url
      $modId    // mod id
    );

    $getParams = array(
      'synd'      => $containerName,
      'container' => $containerName,
      'owner'     => $ownerId,
      'viewer'    => $viewerId,
      'aid'       => $appId,
      'mid'       => $modId,
      'country'   => isset($culture[1]) ? $culture[1] : 'US',
      'lang'      => $culture[0],
      'view'      => $this->view,
      'parent'    => $this->getRequest()->getUri(),
      'st'        => base64_encode($securityToken->toSerialForm()),
      'url'       => $url,
    );

    $app_settings = MemberApplicationSettingPeer::getSettingsByMemberApplicationId($modId);

    $userpref_param_prefix = Shindig_Config::get('userpref_param_prefix','up_');
    foreach ($app_settings as $app_setting)
    {
      $getParams[$userpref_param_prefix.$app_setting->getName()] = $app_setting->getValue();
    }
    if ($isUseOuterShindig)
    {
      $this->iframeUrl = SnsConfigPeer::get('shindig_url').'gadgets/ifr?'.http_build_query($getParams).'#rpctoken='.rand(0,getrandmax());
    }
    else
    {
      $this->iframeUrl = sfContext::getInstance()->getController()->genUrl('gadgets/ifr').'?'.http_build_query($getParams).'#rpctoken='.rand(0,getrandmax());
    }
  }

  public function executeRenderHomeApplications()
  {
  }

  public function executeRenderHomeGadgetApplication()
  {
    $url = $this->gadget->getConfig('url');
    if (!$url)
    {
      return null;
    }

    try
    {
      $application = ApplicationPeer::addApplication($url);
      if (!$application)
      {
        return null;
      }
    }
    catch (Exception $e)
    {
      return null;
    }

    $applicationId = $application->getId();
    $memberId      = $this->getUser()->getMember()->getId();
    $criteria = new Criteria();
    $criteria->add(MemberApplicationPeer::IS_GADGET, true);
    $memberApplication = MemberApplicationPeer::retrieveByApplicationIdAndMemberId($applicationId, $memberId, $criteria);

    if (!$memberApplication)
    {
      $memberApplication = new MemberApplication();
      $memberApplication->setApplication($application);
      $memberApplication->setMemberId($memberId);
      $memberApplication->setIsDispOther(true);
      $memberApplication->setIsDispHome(true);
      $memberApplication->setIsGadget(true);
      $memberApplication->save();
    }

    $this->memberApplication = $memberApplication;
  }
}
