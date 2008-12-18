<?php

class HomeWidget extends BaseHomeWidget
{
  public function save(PropelPDO $con = null)
  {
    if (!$this->getSortOrder())
    {
      $maxSortOrder = 0;

      $widgets = HomeWidgetPeer::retrieveByType($this->getType());
      $finalWidget = array_pop($widgets);
      if ($finalWidget)
      {
        $maxSortOrder = $finalWidget->getSortOrder();
      }

      $this->setSortOrder($maxSortOrder + 10);
    }

    return parent::save($con);
  }

  public function getComponentModule()
  {
    $list = sfConfig::get('op_widget_list');

    if (empty($list[$this->getName()]))
    {
      return false;
    }

    return $list[$this->getName()]['component'][0];
  }

  public function getComponentAction()
  {
    $list = sfConfig::get('op_widget_list');

    if (empty($list[$this->getName()]))
    {
      return false;
    }

    return $list[$this->getName()]['component'][1];
  }

  public function isEnabled()
  {
    $list = sfConfig::get('op_widget_list');
    if (empty($list[$this->getName()]))
    {
      return false;
    }

    return true;
  }

  public function getConfig($name)
  {
    $result = null;

    $config = HomeWidgetConfigPeer::retrieveByWidgetIdAndName($this->getId(), $name);
    if ($config)
    {
      $result = $config->getValue();
    }

    return $result;
  }
}