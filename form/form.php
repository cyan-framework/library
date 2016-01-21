<?php
namespace Cyan\Library;

/**
 * Class Form
 * @package Cyan\Library
 * @since 1.0.0
 */
class Form
{
    /**
     * control name for fields
     *
     * @var String
     * @since 1.0.0
     */
    private $control_name = 'item';

    /**
     * Field Paths
     *
     * @var array
     * @since 1.0.0
     */
    private static $field_paths = [];

    /**
     * Group fields
     *
     * @var array
     * @since 1.0.0
     */
    private $fields = [];

    /**
     * Form Manifest
     *
     * @var \SimpleXMLElement
     * @since 1.0.0
     */
    private $xml;

    /**
     * Multiton Instance
     *
     * @var array
     * @since 1.0.0
     */
    private static $instance = [];

    /**
     * Singleton Instance
     *
     * @return Form
     *
     * @since 1.0.0
     */
    public static function getInstance() {
        if (func_num_args() == 0) {
            throw new FormException('Error: You need to send at least "form_identifier".');
        }
        $args = func_get_args();
        $id = array_shift($args);
        if (!isset(self::$instance[$id])) {
            $total_args = count($args);
            switch ($total_args)
            {
                case 0:
                    $instance = new self($id);
                    break;
                case 1:
                    $instance = new self($id, $args[0]);
                    break;
                case 2:
                    $instance = new self($id, $args[0], $args[1]);
                    break;
                case 3:
                    $instance = new self($id, $args[0], $args[1], $args[2]);
                    break;
                default:
                    $instance = call_user_func_array([new self], array_merge([$id],$args));
                    break;

            }
            self::$instance[$id] = $instance;
        }
        return self::$instance[$id];
    }

    /**
     * Form constructor.
     *
     * @param string $form_identifier
     * @param string $control_name
     *
     * @since 1.0.0
     */
    public function __construct($form_identifier, $control_name = null)
    {
        if (is_null($control_name)) {
            $control_name = 'item';
        }
        $this->control_name = $control_name;

        /** @var \Cyan $Cyan */
        $Cyan = \Cyan::initialize();

        $form_path = $Cyan->Finder->getPath($form_identifier,'.xml');
        if (!file_exists($form_path)) {
            throw new FormException(sprintf('Form "%s" not found.', $form_path));
        }

        /** @var \SimpleXMLElement xml */
        $this->xml = simplexml_load_file($form_path, __NAMESPACE__.'\XmlElement');

        foreach ($this->xml->children() as $node) {
            $this->setXML($node);
        }

        self::addFieldPath($Cyan->Finder->getPath('cyan:form.field'));

        $this->instanceElements();
    }

    /**
     * Instance Elements
     *
     * @since 1.0.0
     */
    private function instanceElements()
    {
        foreach($this->fields as $group_name => $element)
            $fields[$group_name] = $this->getChildsByParent($group_name);

        $this->fields = $fields;
    }

    /**
     * Set xml form
     *
     * @param $xml
     *
     * @since 1.0.0
     */
    private function setXML( $xml )
    {
        if ($xml instanceof XmlElement)
        {
            /** @var \Cyan $Cyan */
            $Cyan = \Cyan::initialize();

            $group_name = (string)$xml['name'];
            if (!empty($group_name)) {
                $this->fields[$group_name] = $xml;
            } else {
                $this->fields['_default'] = $xml;
            }
            if ($path_identifier = $xml->getAttribute( 'addpath' )) {
                self::addFieldPath(str_replace('/', DIRECTORY_SEPARATOR, $Cyan->Finder->getPath($path_identifier)));
            }
        }
    }

    /**
     * Get fields by group and parent
     *
     * @param string $group_name
     *
     * @return array $nodes ou false
     *
     * @since 1.0.0
     */
    private function getChildsByParent($group_name = '_default')
    {
        if (!isset($this->fields[$group_name])) {
            return false;
        }
        $results = [];

        foreach ($this->fields[$group_name] as $param)  {
            $fieldName = $param->getAttribute('name');
            $results[$fieldName] = $this->getNode($param, $this->control_name, $group_name);
        }

        return $results;
    }

    /**
     * Return FormField
     *
     * @param $node
     * @param string $control_name
     * @param string $group
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    private function getNode($node, $control_name = 'item', $group = '_default')
    {
        //get the type of the parameter
        $type = $node->getAttribute('type');

        $namespace = $node->getAttribute('cf-namespace');
        if (empty($namespace)) {
            $namespace = 'Cyan\Library';
        }
        $class_name = $this->loadField($type, $namespace);

        $options = [];
        if(isset($node->option) && count($node->option) > 0)
        {
            foreach($node->option as $option)
            {
                $option->addAttribute('name', (string)$option);
                $option_field = $this->loadField("option", $namespace);
                $option = new $option_field($option);
                $options[] = $option;
            }
        }

        return new $class_name($node, $this->control_name, $options);
    }

    /**
     * Load a field type
     *
     * @param $type
     * @param $namespace
     * @param bool $new
     *
     * @return bool|string
     *
     * @since 1.0.0
     */
    private function loadField( $type, $namespace, $new = false )
    {
        $signature = md5( $type  );

        if( (isset( $this->elements[$signature] ) && !is_a($this->elements[$signature], '__PHP_Incomplete_Class'))  && $new === false ) {
            return	$this->elements[$signature];
        }

        $element_class	=	sprintf('%s\FormField%s',$namespace,ucfirst(strtolower($type)));

        if( !class_exists( $element_class ) )
        {
            $dirs = self::addFieldPath();

            $file = sprintf('%s'.DIRECTORY_SEPARATOR.'%s.php',$type,$type);

            if ($element_path = FilesystemPath::find($dirs, $file)) {
                require_once $element_path;
            } else {
                $element_path = FilesystemPath::find($dirs, "null.php");
                require_once $element_path;
                $element_class = $namespace.'\FormFieldNull';
            }
        }

        if( !class_exists( $element_class ) ) {
            return false;
        }

        return $element_class;
    }

    /**
     * get a field
     *
     * @param $field_name
     * @param string $group_name
     *
     * @return array|bool
     *
     * @since 1.0.0
     */
    public function getField($field_name, $group_name = "_default")
    {
        if(is_array($this->fields[$group_name]))
        {
            if(array_key_exists($field_name, $this->fields[$group_name]))
                return $this->fields[$group_name][$field_name];
            else
                return [];
        }
        else
            return false;
    }

    /**
     * Bind data to form fields
     *
     * @param $values
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function bind($values)
    {
        foreach($this->fields as $fields)
        {
            foreach($fields as $field_name => $field) {
                if (is_array($values) && isset($values[$field_name])) {
                    $value =  $values[$field_name];
                } elseif (is_object($values) && property_exists($values,$field_name)) {
                    $value = $values->$field_name;
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
     *
     * @since 1.0.0
     */
    public function getGroups()
    {
        return array_keys($this->fields);
    }

    /**
     * get all fields from a group
     *
     * @param $group
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getFields($group)
    {
        return array_keys($this->fields[$group]);
    }

    /**
     * Check if required field are not empty
     *
     * @return bool|FormField
     *
     * @since 1.0.0
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
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function addFieldPath($path = null)
    {
        if (!is_null($path) && is_dir($path)) {
            self::$field_paths[] = $path;
        }

        return self::$field_paths;
    }
}