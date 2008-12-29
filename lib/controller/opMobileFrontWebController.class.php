<?php

/**
 * opMobileFrontWebController
 *
 * @package    OpenPNE
 * @subpackage controller
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class opMobileFrontWebController extends sfFrontWebController
{
 /**
  * @see sfWebController
  */
  public function genUrl($parameters = array(), $absolute = false)
  {
    $isSid = false;

    if (is_string($parameters) && false !== ($sidPos = strpos($parameters, SID)))
    {
      $isSid = true;
      $paramHead = substr($parameters, 0, $sidPos);
      $paramFoot = substr($parameters, $sidPos + strlen(SID) + 1);
      $parameters = $paramHead.$paramFoot;
    }
    elseif (is_array($parameters) && in_array($parameters, session_name()))
    {
      $isSid = true;
      unset($parameters[session_name()]);
    }

    $url = parent::genUrl($parameters, $absolute);

    if ($isSid)
    {
      $fragment = '';
      if (false !== ($fragPos = strpos($url, '#')))
      {
        $fragment = substr($url, $fragPos);
        $url = substr($url, 0, $fragPos);
      }

      $url .= '?'.SID.$fragment;
    }

    return $url;
  }

 /**
  * @see sfWebController
  */
  public function redirect($url, $delay = 0, $statusCode = 302)
  {
    if (!$this->context->getRequest()->isCookie())
    {
      $url = $url.'?'.SID;
    }
    parent::redirect($url, $delay, $statusCode);
  }
}