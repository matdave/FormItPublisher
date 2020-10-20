<?php

class FormItPublisher
{
    
    /**
     * @var modX|null $modx
     */
    public $modx = null;
    /**
     * @var array
     */
    public $config = array();
    public $namespace = 'formitpublisher';

    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;
        $corePath = $this->modx->getOption('bluehook.core_path', $config, $this->modx->getOption('core_path') . 'components/bluehook/');
        $this->config = array_merge(array(
            'namespace' => $this->namespace,
            'basePath' => $this->modx->getOption('base_path'),
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'snippetPath' => $corePath . 'elements/snippets/',
            'pluginPath' => $corePath . 'elements/plugin/',
        ), $config);
        $this->modx->addPackage($this->namespace, $this->config['modelPath']);
    }
}