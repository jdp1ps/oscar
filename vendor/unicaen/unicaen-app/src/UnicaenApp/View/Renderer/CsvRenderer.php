<?php

namespace UnicaenApp\View\Renderer;

use Zend\Stdlib\ArrayUtils;
use Zend\View\Exception;
use UnicaenApp\View\Model\CsvModel;
use Zend\View\Model\ModelInterface as Model;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\ResolverInterface as Resolver;

/**
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class CsvRenderer implements Renderer
{
    /**
     * Whether or not to merge child models with no capture-to value set
     * @var bool
     */
    protected $mergeUnnamedChildren = false;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     *
     * @return mixed
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     *
     * @param  Resolver $resolver
     * @return Renderer
     */
    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Set flag indicating whether or not to merge unnamed children
     *
     * @param  bool $mergeUnnamedChildren
     * @return JsonRenderer
     */
    public function setMergeUnnamedChildren($mergeUnnamedChildren)
    {
        $this->mergeUnnamedChildren = (bool) $mergeUnnamedChildren;
        return $this;
    }

    /**
     * Should we merge unnamed children?
     *
     * @return bool
     */
    public function mergeUnnamedChildren()
    {
        return $this->mergeUnnamedChildren;
    }

    /**
     * Renders values as Csv
     *
     * @todo   Determine what use case exists for accepting both $nameOrModel and $values
     * @param  string|Model $nameOrModel The script/resource process, or a view model
     * @param  null|array|\ArrayAccess $values Values to use during rendering
     * @throws Exception\DomainException
     * @return string The script output.
     */
    public function render($nameOrModel, $values = null)
    {
        // use case 1: View Models
        // Serialize variables in view model
        if ($nameOrModel instanceof Model) {
            if ($nameOrModel instanceof CsvModel) {
                $children = $this->recurseModel($nameOrModel, false);
                $this->injectChildren($nameOrModel, $children);
                $values = $nameOrModel->serialize();
            } else {
                $values = $this->recurseModel($nameOrModel);
                $values = $this->renderFromVariables($values);
            }

            return $values;
        }

        // use case 2: $nameOrModel is populated, $values is not
        // Serialize $nameOrModel
        if (null === $values) {
            if ($nameOrModel instanceof Traversable) {
                $nameOrModel = ArrayUtils::iteratorToArray($nameOrModel);
                $return = $this->renderFromVariables($nameOrModel);
            } else {
                $return = $this->renderFromVariables(get_object_vars($nameOrModel));
            }

            return $return;
        }

        // use case 3: Both $nameOrModel and $values are populated
        throw new Exception\DomainException(sprintf(
            '%s: Do not know how to handle operation when both $nameOrModel and $values are populated',
            __METHOD__
        ));
    }

    /**
     * 
     * @param array $variables
     */
    protected function renderFromVariables( $variables )
    {
        $header     = null;
        $data       = [];
        $delimiter  = ';';
        $enclosure  = '"';
        if (isset($variables['header']))    $header = $variables['header'];
        if (isset($variables['data']))      $header = $variables['data'];
        if (isset($variables['delimiter'])) $header = $variables['delimiter'];
        if (isset($variables['enclosure'])) $header = $variables['enclosure'];

        return \UnicaenApp\Util::arrayToCsv($data, $header, $delimiter, $enclosure);
    }

    /**
     * Retrieve values from a model and recurse its children to build a data structure
     *
     * @param  Model $model
     * @param  bool $mergeWithVariables Whether or not to merge children with
     *         the variables of the $model
     * @return array
     */
    protected function recurseModel(Model $model, $mergeWithVariables = true)
    {
        $values = array();
        if ($mergeWithVariables) {
            $values = $model->getVariables();
        }

        if ($values instanceof Traversable) {
            $values = ArrayUtils::iteratorToArray($values);
        }

        if (!$model->hasChildren()) {
            return $values;
        }

        $mergeChildren = $this->mergeUnnamedChildren();
        foreach ($model as $child) {
            $captureTo = $child->captureTo();
            if (!$captureTo && !$mergeChildren) {
                // We don't want to do anything with this child
                continue;
            }

            $childValues = $this->recurseModel($child);
            if ($captureTo) {
                // Capturing to a specific key
                // TODO please complete if append is true. must change old
                // value to array and append to array?
                $values[$captureTo] = $childValues;
            } elseif ($mergeChildren) {
                // Merging values with parent
                $values = array_replace_recursive($values, $childValues);
            }
        }
        return $values;
    }

    /**
     * Inject discovered child model values into parent model
     *
     * @param  Model $model
     * @param  array $children
     */
    protected function injectChildren(Model $model, array $children)
    {
        foreach ($children as $child => $value) {
            // TODO detect collisions and decide whether to append and/or aggregate?
            $model->setVariable($child, $value);
        }
    }
}
