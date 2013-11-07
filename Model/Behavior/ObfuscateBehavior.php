<?php

App::uses('Obfuscate', 'Obfuscate.Utility');

class ObfuscateBehavior extends ModelBehavior {
    public $settings;
    protected $Obfuscate;

    public function setup(Model $Model, $settings=array()) {
        if (!isset($this->settings[$Model->alias])) {
            $this->settings[$Model->alias] = array(
                'salt'       => Configure::read('Security.salt'),
                'min_length' => 6,
                'alphabet'   => '',
                'fields'     => array('id')
            );
        }

        $this->settings[$Model->alias] = array_merge(
            $this->settings[$Model->alias], (array)$settings
        );

        $this->Obfuscate = new Obfuscate(
            $this->settings[$Model->alias]['salt'],
            $this->settings[$Model->alias]['min_length'],
            $this->settings[$Model->alias]['alphabet']
        );
    }

    public function beforeSave($Model, $options) {
        foreach ($this->settings[$Model->alias]['fields'] as $fieldName) {
            list($model, $field) = $this->_getModelField($Model->alias, $fieldName);
            if (array_key_exists($model, $Model->data)
                && array_key_exists($field, $Model->data[$model])
            ) {
                $value = $this->decode($Model->data[$model][$field]);
                if (!empty($value)) {
                    $Model->data[$model][$field] = $value;
                }
            }
        }

        return true;
    }

    protected function _getModelField($model, $field) {
        $parts = explode('.', $field);
        if (count($parts) == 2) {
            $model = $parts[0];
            $field = $parts[1];
        } else {
            $field = $parts[0];
        }
        return array($model, $field);
    }

    protected function _recursiveDecode(&$conditions, $modelName, $fieldName) {
        foreach($conditions as $queryField => &$value) {
            if (is_array($value)) {
                return $this->_recursiveDecode($value, $modelName, $fieldName);
            } else {
                list($model, $field) = $this->_getModelField($modelName, $fieldName);

                if (in_array(preg_replace('/[^\w\d\.]/', '', $queryField), array("{$model}.{$field}", $field))) {
                    if ($cleanValue = $this->decode($value)) {
                        $value = $cleanValue;
                    }
                }
            }
        }
    }

    public function getOID(Model $Model) {
        return $this->encode($Model->getID());
    }

    public function beforeFind($Model, $query) {
        // Loop through the fields in the settings
        foreach ($this->settings[$Model->alias]['fields'] as $field) {
            $this->_recursiveDecode($query['conditions'], $Model->alias, $field);
        }
        return $query;
    }

    protected function _fixPrimaryKey(&$Model) {
        if (false !== $Model->id) {
            if ($cleanValue = $this->decode($Model->id)) {
                $Model->id = $cleanValue;
            }
        }
    }

    public function afterFind(&$Model, $results, $primary) {
        if (count($results)) {
            $this->_fixPrimaryKey($Model);
            foreach ($this->settings[$Model->alias]['fields'] as $fieldName) {
                foreach($results as &$result) {
                    list($model, $field) = $this->_getModelField($Model->alias, $fieldName);
                    if (array_key_exists($field, $result[$model])) {
                        $value = $this->encode($result[$model][$field]);
                        if (!empty($value)) {
                            if ($field == $Model->primaryKey) {
                                $result[$model]["o{$Model->primaryKey}"] = $value;
                            } else {
                                $result[$model][$field] = $value;
                            }
                        }
                    }
                }
            }
        }

        return $results;
    }

    // helper to decode encoded id
    public function obfuscate($str) {
        return $this->Obfuscate->encrypt_hex($str);
    }

    public function deobfuscate($str) {
        return $this->Obfuscate->decrypt_hex($str);
    }

    // helper aliases
    public function encode($str) {
        return $this->obfuscate($str);
    }

    public function decode($str) {
        return $this->deobfuscate($str);
    }
}