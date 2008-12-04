<?php

require_once 'Archive/Tar.php';

class opPluginArchiveTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->namespace        = 'opPlugin';
    $this->name             = 'archive';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [opPlugin:archive|INFO] task creates the plugin archive.
Call it with:

  [php symfony opPlugin:archive name|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $pluginName = $arguments['name'];
    $packagePath = sfConfig::get('sf_plugins_dir').'/'.$pluginName;
    if (!is_readable($packagePath.'/package.xml'))
    {
      throw new sfException(sprintf('Plugin "%s" dosen\'t have a definition file.', $pluginName));
    }

    $infoXml = simplexml_load_file($packagePath.'/package.xml');
    $filename = sprintf('%s-%s.tgz', (string)$infoXml->name, (string)$infoXml->version->release);
    $dirPath = sfConfig::get('sf_plugins_dir').'/'.$pluginName;

    $tar = new Archive_Tar(sfConfig::get('sf_cache_dir').'/'.$filename, true);
    foreach ($infoXml->contents->dir->file as $file)
    {
      $attributes = $file->attributes();
      $name = (string)$attributes['name'];
      $tar->addString($pluginName.'/'.$name, file_get_contents($dirPath.'/'.$name));
    }
    $tar->addString($pluginName.'/package.xml', file_get_contents($dirPath.'/package.xml'));
  }
}