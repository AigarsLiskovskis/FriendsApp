<?php

namespace App\Validation;

use App\Exceptions\FormValidationException;


class ArticleFormValidator
{
    private array $data;
    public array $errors = [];
    private array $rules;

    public function __construct(array $data, array $rules = []) //'key' = required
    {
        $this->data = $data;
        $this->rules = $rules;
    }


    public function passes()
    {
        foreach ($this->rules as $key => $rules) {


            foreach ($rules as $rule) {
                //"required"
                //"min:3"
                [$name, $attribute] = explode(':', $rule);

                //var_dump($name, $attribute);

                $ruleName = 'validate' . ucfirst($name); //validateMin;
                //check if method exists();
                $this->{$ruleName}($key, $attribute);

//                $ruleName = 'validate' . ucfirst($rule); //validateRequired;
//                $this->{$ruleName}($key);
            }
        }
//        foreach ($this->data as $key => $value) {
//            if (empty(trim($value))) {
//                $this->errors[$key][] = "{$key} field is required.";

        if (count($this->errors) > 0) {
            throw new FormValidationException();
        }
    }


    private function validateRequired(string $key): void
    {
        if (empty(trim($this->data[$key]))) {
            $this->errors[$key][] = "{$key} field is required.";
        }
    }

    private function validateMin(string $key, int $attribute)
    {
        if (strlen($this->data[$key]) < $attribute) {
            $this->errors[$key][] = "{$key} must be at least {$attribute} characters.";
        }
    }


    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

}