<?php
/**
 * @var modX $modx
 * @var array $scriptProperties
 */
$fiPublisher = $modx->getService('formitpublisher', 'FormItPublisher', $modx->getOption('formitpublisher.core_path', null, $modx->getOption('core_path') . 'components/formitpublisher/') . 'model/formitpublisher/');
if (!($fiPublisher instanceof \FormItPublisher)) return '';

return (new \FormItPublisher\Snippet\PreHookRetriever($fiPublisher, $scriptProperties))->process();