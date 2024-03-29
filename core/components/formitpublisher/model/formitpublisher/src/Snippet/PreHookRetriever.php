<?php

namespace FormItPublisher\Snippet;

class PreHookRetriever extends Snippet
{
    /** @var \fiHooks */
    protected $hook;

    public function process(): bool
    {
        $this->hook = $this->sp['hook'];
        $resource = (object)[];

        $fipResourceFields = $this->modx->getOption(
            'fipResourceFields',
            $this->hook->formit->config,
            $this->modx->getOption('formFields', $this->hook->formit->config)
        );
        $fipTVFields = $this->modx->getOption('fipTVFields', $this->hook->formit->config, null);
        $fipCheckPermissions = $this->modx->getOption('fipCheckPermissions', $this->hook->formit->config, true);
        $fipResource = (int)$this->modx->getOption('fipResource', $this->hook->formit->config, 0);
        $fipResourceKey = $this->modx->getOption('fipResourceKey', $this->hook->formit->config, 'fipResource');
        if ($fipResourceKey) {
            $fipResource = (isset($_REQUEST[$fipResourceKey])) ? (int)$_REQUEST[$fipResourceKey] : 0;
        }
        if ($fipResource) {
            $resource = $this->modx->getObject('modResource', $fipResource);
        }
        if (!(array)$resource) {
            return true;
        }
        if ($fipCheckPermissions && !$this->checkPermissions($resource)) {
            return true;
        }

        $fields = $this->getFieldKeys($fipResourceFields);

        if (empty($fields)) {
            return true;
        }
        foreach ($fields as $k => $v) {
            $fields[$v] = $resource->get($k);
        }

        $tvs = $this->getFieldKeys($fipTVFields);
        foreach ($tvs as $k => $v) {
            $fields[$v] = $resource->getTVValue($k);
        }
        $fields[$fipResourceKey] = $resource->id;
        $this->hook->setValues($fields);
        return true;
    }

    private function checkPermissions($resource): bool
    {
        if (!$this->modx->user) {
            return false;
        }
        if (!($this->modx->user->hasSessionContext('mgr') ||
            $this->modx->user->hasSessionContext($this->modx->resource->context_key))
        ) {
            return false;
        }
        if (!$this->modx->hasPermission('save_document')) {
            return false;
        }
        if (!$resource->checkPolicy('save')) {
            return false;
        }

        return true;
    }
}
