<?php
/**
 * Get parsed search engine preview.
 *
 * @package seopro
 * @subpackage processors
 */
class SeoSuitePreviewProcessor extends modProcessor
{
    protected $seosuite;

    public function __construct(modX &$modx, array $properties = array())
    {
        $modelPath = $modx->getOption(
                'seosuite.core_path',
                null,
                $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/seosuite/'
            ) . 'model/seosuite/';
        $modx->loadClass('SeoSuite', $modelPath, true, true);

        $this->seosuite = new SeoSuite($modx);

        parent::__construct($modx, $properties);
    }

    /**
     * @return mixed|string
     */
    public function process()
    {
        $this->modx->switchContext($this->getProperty('context', 'web'));
        $this->modx->resource = $this->modx->getObject('modResource', $this->getProperty('resource'));

        /* If no resource is set, when creating a resource, then temporarily create a new one so the getAliasPath method can be used. */
        if (!$this->modx->resource) {
            $this->modx->resource = $this->modx->newObject('modResource');
        }

        $alias = $this->modx->resource->getAliasPath($this->getProperty('alias'), [
            'content_type' => $this->getProperty('content_type'),
            'uri'          => $this->getProperty('uri'),
            'uri_override' => $this->getProperty('uri_override') === 'true' ? true : false,
            'context_key'  => $this->getProperty('context', 'web')
        ]);

        $title       = $this->getProperty('title');
        $description = $this->getProperty('description');
        if ($this->getProperty('use_default_meta') === 'true') {
            $title       = $this->modx->getOption('seosuite.meta.default_meta_title');
            $description = $this->modx->getOption('seosuite.meta.default_meta_description');
        }

        $fields   = json_decode($this->getProperty('fields'), true);
        $rendered = [
            'title'       => $this->seosuite->renderMetaValue($title, $fields),
            'description' => $this->seosuite->renderMetaValue($description, $fields),
            'alias'       => $alias
        ];

        return $this->outputArray([
            'output' => $rendered,
            'counts' => [
                'title'       => strlen($rendered['title']),
                'description' => strlen($rendered['description'])
            ]
        ],
        0
        );
    }
}

return 'SeoSuitePreviewProcessor';
