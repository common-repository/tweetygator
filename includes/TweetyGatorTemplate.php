<?php
class TweetyGatorTemplate
{
    protected $_data;

    public function __get ($key)
    {
        $propertyName = $this->_getUnifiedPropertyName($key);

        if (!property_exists($this, $propertyName)) {
            return $this->_data[$key];
        } else {
            return $this->{$propertyName};
        }
    }

    public function  __set ($key,  $value)
    {
        $propertyName = $this->_getUnifiedPropertyName($key);

        if (!property_exists($this, $propertyName)) {
            $this->_data[$key] = $value;
        } else {
            $this->{$propertyName} = $value;
        }
    }

    public function __isset ($key)
    {
        $propertyName = $this->_getUnifiedPropertyName($key);

        if (property_exists($this, $propertyName)) {
            return true;
        } else {
            return (array_key_exists($key, $this->_data));
        }
    }

    protected function _getUnifiedPropertyName ($key)
    {
        $propertyName = '_' . $key;
        return $propertyName;
    }

    public function render ($templateName)
    {
        if (!file_exists($templateName)) {
            return '';
        }
        
        ob_start();
        try {
            // echo content within $this context
            include $templateName;
        }
        catch (Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ob_get_clean();
    }
}