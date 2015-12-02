<?php
namespace Cyan\Library;

/**
 * Class Form
 * @package Cyan\Library
 */
class Form
{
    use TraitsSingleton;

    /**
     * Controle name for field groups
     *
     * @var String
     */
    private $controlName = 'fields';

    /**
     * Field Paths
     *
     * @var array
     */
    private static $fieldPaths = [];

    /**
     * Group fields
     *
     * @var array
     */
    private $fields = [];

    /**
     * Form Manifest
     *
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * Form constructor.
     *
     * @param $form
     * @param array $data
     */
    public function __construct($formIdentifier = null, $controlName = "fields")
    {
        $this->controlName = $controlName;

        /** @var \Cyan $Cyan */
        $Cyan = \Cyan::initialize();

        $formPath = $Cyan->Finder->getPath($formIdentifier,'.xml');
        if (!file_exists($formPath)) {
            throw new FormException(sprintf('Form "%s" not found.', $formPath));
        }

        /** @var \SimpleXMLElement xml */
        $this->xml = simplexml_load_file($formPath, __NAMESPACE__.'\XmlElement');

        foreach ($this->xml->children() as $node) {
            $this->setXML($node);
        }

        self::addFieldPath($Cyan->Finder->getPath('cyan:form.field'));

        $this->instanceElements();
    }

    /**
     * Instance Elements
     */
    private function instanceElements()
    {
        foreach($this->fields as $groupName => $element)
            $fields[$groupName] = $this->getChildsByParent($groupName);

        $this->fields = $fields;
    }

    /**
     * Set xml form
     *
     * @param $xml
     */
    private function setXML( $xml )
    {
        if ($xml instanceof XmlElement)
        {
            /** @var \Cyan $Cyan */
            $Cyan = \Cyan::initialize();

            $group = (string)$xml['name'];
            if (!empty($group)) {
                $this->fields[$group] = $xml;
            } else {
                $this->fields['_default'] = $xml;
            }
            if ($pathIdentifier = $xml->getAttribute( 'addpath' )) {
                self::addFieldPath(str_replace('/', DIRECTORY_SEPARATOR, $Cyan->Finder->getPath($pathIdentifier)));
            }
        }
    }

    /**
     * Get fields by group and parent
     *
     * @param string $group
     * @param string $name
     * @return array $nodes ou false
     */
    private function getChildsByParent($group = '_default', $name = 'fields')
    {
        if (!isset($this->fields[$group])) {
            return false;
        }
        $results = [];

        foreach ($this->fields[$group] as $param)  {
            $fieldName = $param->getAttribute('name');
            $results[$fieldName] = $this->getNode($param, $this->controlName, $group);
        }

        return $results;
    }

    /**
     * Retorna um campo instanciado
     * @param simpleXML $node
     * @param string $control_name
     * @param string $group
     * @return NoixField $element ou false
     */
    private function getNode($node, $control_name = 'params', $group = '_default')
    {
        //get the type of the parameter
        $type = $node->getAttribute('type');

        $namespace = $node->getAttribute('namespace');
        if (empty($namespace)) {
            $namespace = 'Cyan\Library';
        }
        $className = $this->loadField($type, $namespace);

        $options = [];
        if(isset($node->option) && count($node->option) > 0)
        {
            foreach($node->option as $option)
            {
                $option->addAttribute('name', (string)$option);
                $optionField = $this->loadField("option", $namespace);
                $option = new $optionField($option);
                $options[] = $option;
            }
        }

        return new $className($node, $this->controlName, $options);
    }

    /**
     * Tenta carregar um campo de um determinado tipo
     * @param string $type
     * @param bool $new
     * @return NoixField ou false
     */
    private function loadField( $type, $namespace, $new = false )
    {
        $signature = md5( $type  );

        if( (isset( $this->_elements[$signature] ) && !is_a($this->_elements[$signature], '__PHP_Incomplete_Class'))  && $new === false ) {
            return	$this->_elements[$signature];
        }

        $elementClass	=	sprintf('%s\FormField%s',$namespace,ucfirst(strtolower($type)));

        if( !class_exists( $elementClass ) )
        {
            $dirs = self::addFieldPath();

            $file = sprintf('%s'.DIRECTORY_SEPARATOR.'%s.php',$type,$type);

            if ($elementFile = FilesystemPath::find($dirs, $file)) {
                require_once $elementFile;
            } else {
                $elementFile = FilesystemPath::find($dirs, "null.php");
                require_once $elementFile;
                $elementClass = $namespace.'\FormFieldNull';
            }
        }

        if( !class_exists( $elementClass ) ) {
            return false;
        }

        return $elementClass;
    }

    /**
     * @param $fieldName
     * @param string $groupName
     * @return FormField|bool
     */
    public function getField($fieldName, $groupName = "_default")
    {
        if(is_array($this->fields[$groupName]))
        {
            if(array_key_exists($fieldName, $this->fields[$groupName]))
                return $this->fields[$groupName][$fieldName];
            else
                return [];
        }
        else
            return false;
    }

    /**
     * Bind values
     *
     * @param $values
     */
    public function bind($values)
    {
        foreach($this->fields as $fields)
        {
            foreach($fields as $fieldName => $field) {
                if (is_array($values) && isset($values[$fieldName])) {
                    $value =  $values[$fieldName];
                } elseif (is_object($values) && property_exists($values,$fieldName)) {
                    $value = $values->$fieldName;
                } else {
                    $value = null;
                }
                $field->setValue( $value );
            }
        }

        return $this;
    }

    /**
     * List of groups fields
     *
     * @return array
     */
    public function getGroups()
    {
        return array_keys($this->fields);
    }

    /**
     *
     * @param $group
     * @return array
     */
    public function getFields($group)
    {
        return array_keys($this->fields[$group]);
    }

    /**
     * Check if required field are not empty
     *
     * @return bool|FormField
     */
    public function isValid()
    {
        foreach ($this->fields as $group) {
            /** @var FormField $field */
            foreach ($group as $field) {
                if ($field->getAttribute('required', false) && empty($field->getValue())) {
                    return $field;
                }
            }
        }

        return true;
    }

    /**
     * Add Field Path
     *
     * @param null $path
     * @return array
     */
    public static function addFieldPath($path = null)
    {
        if (!is_null($path) && is_dir($path)) {
            self::$fieldPaths[] = $path;
        }

        return self::$fieldPaths;
    }
}