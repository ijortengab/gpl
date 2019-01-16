<?php

namespace Gpl\Form;

class NodeFormManager
{
    protected $form_id;
    protected $form;
    protected $form_state;
    protected $current_field;
    protected $current_behaviour;

    /**
     *
     */
    public function __construct($form_id, &$form, &$form_state) {
        $this->form_id = $form_id;
        $this->form =& $form;
        $this->form_state =& $form_state;
    }

    /**
     *
     */
    public function __get($name) {
        $this->current_field = $name;
        return $this;
    }

    /**
     *
     */
    public function isValue($value)
    {
        $field = $this->current_field;
        $this->current_field = null;
        return (
            $this->isInitialize() && $this->$field->isOriginalValue($value) ||
            $this->isRebuild() && $this->$field->isCurrentValue($value)
        );
    }
    /**
     *
     */
    public function isFilled()
    {
        $field = $this->current_field;
        $this->current_field = null;
        return (
            $this->isInitialize() && $this->$field->isOriginalValueFilled() ||
            $this->isRebuild() && $this->$field->isCurrentValueFilled()
        );
    }

    /**
     *
     */
    public function isCurrentValue($value)
    {
        $this->modifyValue($value);
        $form_state = $this->form_state;
        $field = $this->current_field;
        $this->current_field = null;
        return (
            isset($form_state['input'][$field][LANGUAGE_NONE]) &&
            $form_state['input'][$field][LANGUAGE_NONE] == $value
        ) ||
        (
            isset($form_state['values'][$field][LANGUAGE_NONE][0]['target_id']) &&
            $form_state['values'][$field][LANGUAGE_NONE][0]['target_id'] == $value
        );
    }

    /**
     *
     */
    public function isOriginalValue($value)
    {
        $this->modifyValue($value);
        $form_state = $this->form_state;
        $field = $this->current_field;
        $this->current_field = null;
        return isset($form_state['node']->$field[LANGUAGE_NONE]) &&
        $form_state['node']->$field[LANGUAGE_NONE][0]['target_id'] == $value;
    }

    /**
     *
     */
    public function isCurrentValueFilled()
    {
        $form_state = $this->form_state;
        $field = $this->current_field;
        $this->current_field = null;
        return
            isset($form_state['input'][$field][LANGUAGE_NONE]) ||
            isset($form_state['values'][$field][LANGUAGE_NONE][0]['target_id']);
    }

    /**
     *
     */
    public function isOriginalValueFilled()
    {
        $form_state = $this->form_state;
        $field = $this->current_field;
        $this->current_field = null;
        return isset($form_state['node']->$field[LANGUAGE_NONE]);
    }

    /**
     *
     */
    public function setBehaviourValue($behaviour)
    {
        $this->current_behaviour = $behaviour;
    }

    /**
     *
     */
    public function resetBehaviourValue()
    {
        $this->current_behaviour = null;
    }

    /**
     *
     */
    public function modifyValue(&$value)
    {
        switch ($this->current_behaviour) {
            case 'taxonomy':
                $conditions = array('machine_name' => trim($value));
                $result = entity_load('taxonomy_term', false, $conditions);
                if (empty($result)) {
                    return;
                }
                $result = array_shift($result);
                $value = $result->tid;
                break;

            case '':
                // Do something.
                break;

            default:
                // Do something.
                break;
        }
        // return $this;
    }

    /**
     *
     */
    public function isRebuild()
    {
        return $this->form_state['rebuild'];
    }

    /**
     *
     */
    public function isInitialize()
    {
        return !$this->form_state['rebuild'];
    }

    /**
     *
     */
    public function show()
    {
        $this->form[$this->current_field]['#access'] = true;
        $this->current_field = null;
    }

    /**
     *
     */
    public function hide()
    {
        $this->form[$this->current_field]['#access'] = false;
        $this->current_field = null;
    }

    /**
     *
     */
    public function restore()
    {
        return $this->show();
        $this->form[$this->current_field] = $this->form_state['backup'][$this->current_field];
        unset($this->form_state['backup'][$this->current_field]);
        $this->current_field = null;
    }

    /**
     *
     */
    public function backup()
    {
        return $this->hide();
        $this->form_state['backup'][$this->current_field] = $this->form[$this->current_field];
        unset($this->form[$this->current_field]);
        $this->current_field = null;
    }

    /**
     *
     */
    public function addAjax($array)
    {
        $this->form[$this->current_field][LANGUAGE_NONE]['#ajax'] = $array;
        $this->current_field = null;
    }

    /**
     *
     */
    public function prependProperty($property, $value)
    {
        $field = $this->current_field;
        $this->current_field = null;
        if (!isset($this->form[$field][LANGUAGE_NONE][0]['#'.$property])) {
            $this->form[$field][LANGUAGE_NONE][0]['#'.$property] = array();
            array_unshift($this->form[$field][LANGUAGE_NONE][0]['#'.$property], $value);
        }
    }


}

