<?php
if (!defined('IN_CMS')) { exit(); }

/*
 * Form
 * 
 * The Form plugin is a third-party plugin that lets you create and display forms on your installation of Wolf CMS.
 * 
 * @package     Plugins
 * @subpackage  form
 * 
 * @author      Nic Wortel <nic.wortel@nth-root.nl>
 * @copyright   Nic Wortel, 2012
 * @version     0.1.2
 */

class FormField extends Record {
    const TABLE_NAME = 'form_field';
    
    public $id;
    public $label;
    public $slug;
    public $type;
    public $required;
    public $form_id;
    public $position;
    
    public function __construct() {
        if (!isset($this->options)) {
            $this->options = FormFieldOption::findByFieldId($this->id);
        }
    }
    
    public function beforeDelete() {
        if (!FormFieldOption::deleteByFieldId($this->id)) {
            return false;
        }
        else {
            return true;
        }
    }
    
    public function beforeSave() {
        $this->slug = Node::toSlug($this->label);
        
        return true;
    }
    
    public static function deleteByFormId($form_id) {
        $form_id = (int) $form_id;
        
        $form_fields = self::findByFormId($form_id);
        
        if (is_array($form_fields)) {
            foreach ($form_fields as $form_field) {
                if (!$form_field->delete()) {
                    return false;
                }
            }
        }
        elseif ($form_fields instanceof FormField) {
            if (!$form_fields->delete()) {
                return false;
            }
        }
        
        return true;
    }
    
    public static function findByFormId($id) {
        $id = (int) $id;
        
        return Record::findAllFrom('FormField', 'form_id = ' . $id . ' ORDER BY position ASC, id ASC');
    }
    
    public function getColumns() {
        return array(
            'id', 'label', 'slug',
            'type', 'required',
            'form_id', 'position'
        );
    }
    
    public static function typesArray() {
        return array(
            'text' => array(
                'label' => __('Text')
            ),
            'long_text' => array(
                'label' => __('Long text')
            ),
            'number' => array(
                'label' => __('Number')
            ),
            'postcode' => array(
                'label' => __('Postal code')
            ),
            'email_address' => array(
                'label' => __('Email address')
            ),
            'phone_number' => array(
                'label' => __('Phone number')
            ),
            'url' => array(
                'label' => __('URL')
            ),
            'dropdown' => array(
                'label' => __('Dropdown')
            ),
            'checkboxes' => array(
                'label' => __('Checkboxes')
            ),
            'radio_buttons' => array(
                'label' => __('Radio buttons')
            )
        );
    }
    
    
    public function validate($value = false) {
        use_helper('Validate');
        
        if (is_string($value) && trim($value) == '') {
            if ($this->required == 1) {
                return false;
            }
        }
        else {
        
            if ($this->type == 'number') {
                if (!Validate::numeric($value)) return false;
            }
            elseif ($this->type == 'postcode') {
                if (!Validate::postcode(strtoupper($value))) return false;
            }
            elseif ($this->type == 'email_address') {
                if (!Validate::email($value)) return false;
            }
            elseif ($this->type == 'phone_number') {
                if (!Validate::phone($value)) return false;
            }
            elseif ($this->type == 'url') {
                if (!Validate::url($value)) return false;
            }
            elseif ($this->type == 'dropdown') {
                $options = FormFieldOption::findByFieldId($this->id);
                foreach ($options as $option) {
                    if ($value == $option->label) return true;
                }
                return false;
            }
            elseif ($this->type == 'radio_buttons') {
                $options = FormFieldOption::findByFieldId($this->id);
                foreach ($options as $option) {
                    if ($value == $option->label) return true;
                }
                return false;
            }
            
        }
        
        return true;
    }
}