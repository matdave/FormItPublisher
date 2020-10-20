<?php

namespace FormItPublisher\Snippet;

class HookPublisher extends Snippet
{

    /** @var \fiHooks */
    protected $hook;
    protected $values;

    public function process()
    {
        $this->hook = $this->sp['hook'];
        $this->values = $this->hook->getValues();
        $resource = (object)[];

        $fipResourceFields = $this->modx->getOption('fipResourceFields', $this->hook->formit->config, $this->modx->getOption('formFields', $this->hook->formit->config));
        $fipTVFields = $this->modx->getOption('fipTVFields', $this->hook->formit->config, null);
        $fipResourceDefaults = json_decode($this->modx->getOption('fipResourceDefaults', $this->hook->formit->config, '[]'), true);
        $fipCheckPermissions = $this->modx->getOption('fipCheckPermissions', $this->hook->formit->config, true);
        $fipResource = (int)$this->modx->getOption('fipResource', $this->hook->formit->config, 0);
        if($fipCheckPermissions && !$this->checkPermissions($fipResource)){
            $this->hook->addError('fiPublisher','Could not verify permissions.');
            return $this->hook->hasErrors();
        }
        
        $fields = $this->getProperties($fipResourceFields,$this->values);
        if(is_array($fipResourceDefaults) && !empty($fipResourceDefaults)){
            $fields = $fields + $fipResourceDefaults;
        }

        if(empty($fields)){
            $this->hook->addError('fiPublisher','No fields to create resource from.');
            return $this->hook->hasErrors();
        }
        
        $tvs = $this->getProperties($fipTVFields,$this->values);
        foreach($tvs as $k=>$v){
            $tv = $this->modx->getObject('modTemplateVar', array('name' => $k));
            if(!empty($tv)){
                $fields['tv' . $tv->get('id')] = $v;
            }
        }
        
        if($fipResource){
            $resource = $this->modx->getObject('modResource', $fipResource);    
        }

        if(!(array)$resource){
            $response = $this->modx->runProcessor('resource/create', $fields);
        }else{
            $fields['id'] = $fipResource;
            @unlink($this->modx->getOption('core_path') . 'cache/resource/web/resources/' . $fields['id'] . '.cache.php');
            $response = $this->modx->runProcessor('resource/update', $fields);
        }
        if ($response->isError()) {
            $errorMessage = null;
            if ($response->hasFieldErrors()) {
                $fieldErrors = $response->getAllErrors();
                $errorMessage = implode("\n", $fieldErrors);
            } else {
                $errorMessage = 'An error occurred: ' . $response->getMessage();
            }
            $this->hook->addError('fiPublisher',$errorMessage);
            return $this->hook->hasErrors();
        }else{
            $object = $response->getObject();
            $this->hook->setValue('resourceid', $object['id']);
            return true;
        }
        
        $this->hook->addError('fiPublisher','Unable to create resource.');
        return $this->hook->hasErrors();
    }

    private function checkPermissions($update = false){
        if (!$this->modx->user) return false;
        if (!($this->modx->user->hasSessionContext('mgr') || $this->modx->user->hasSessionContext($this->modx->resource->context_key))) return false;
        if (!$this->modx->hasPermission('save_document')) return false;
        if (!$update && !$this->modx->hasPermission('new_document')) return false;

        return true;
    }
}