<?php

namespace FormItPublisher\Snippet;

abstract class Snippet
{
    /** @var \modX */
    protected $modx;

    /** @var \FormItPublisher */
    protected $fiPublisher;

    /** @var array */
    protected $sp = [];

    /** @var bool */
    protected $debug = false;

    private $tplCache = [];

    public function __construct(\FormItPublisher &$fiPublisher, array $sp = [])
    {
        $this->fiPublisher =& $fiPublisher;
        $this->modx =& $this->fiPublisher->modx;
        $this->sp = $sp;
        $this->debug = (bool)$this->getOption('debug', 0);
    }

    abstract public function process();

    protected function getOption($key, $default = null, $skipEmpty = false)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }

    protected function getChunk($tpl, $phs = [])
    {
        if (strpos($tpl, '@INLINE ') !== false) {
            $content = str_replace('@INLINE ', '', $tpl);

            /** @var \modChunk $chunk */
            $chunk = $this->modx->newObject('modChunk', array('name' => 'inline-' . uniqid()));
            $chunk->setCacheable(false);

            return $chunk->process($phs, $content);
        }

        return $this->modx->getChunk($tpl, $phs);
    }

    /**
     * @param  string $tplName
     * @param  array  $phs
     *
     * @return string
     */
    public function chunk($tplName, $phs = [])
    {
        if (!isset($this->tplCache[$tplName])) {
            $tpl = $this->modx->getOption($tplName, $this->sp, '');
            $this->tplCache[$tplName] = $tpl;
        } else {
            $tpl = $this->tplCache[$tplName];
        }

        if (!empty($tpl)) return $this->modx->getChunk($tpl, $phs);

        if ($this->debug) return "<strong>{$tplName}</strong><br><pre>" . print_r($phs, true) . "</pre>";

        return '';
    }

    /**
     * @param mixed $fields "key1==property1,key2==property2,property3" or array("key1"=>"property1", "key2"=>"property2")
     * @param array $values array("key1"=>"value1")
     */
    
    public function getProperties($fields = null, $values = array())
    {
        $properties = array();
        $fields =$this->getKeys($fields);
        if (!empty($fields)) {
            foreach ($fields as $k => $v) {
                $properties[$k] = $values[$v];
            }
            return $properties;
        } else {
            return $values;
        }
    } 

    /**
     * @param mixed $fields "key1==property1,key2==property2,property3" or array("key1"=>"property1", "key2"=>"property2")
     */
    
    public function getFieldKeys($fields = null)
    { 
        if (!is_array($fields)) {
            $fieldsNew = array();
            $fields = explode(',', $fields);
            foreach ($fields as $field) {
                $field = explode('==', $field);
                $fieldsNew[$field[0]] = ($field[1]) ? $field[1] : $field[0];
            }
            $fields = $fieldsNew;
        }
        return $fields;
    } 
}
