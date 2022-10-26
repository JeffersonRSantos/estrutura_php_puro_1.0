<?php

namespace App;

class Providers
{
    public function globalModels($model)
    {
        if (!empty($model)) {
            $model = $this->convertToStudlyCaps($model);
            $namespace_model = $this->getNamespaceModels($model);
            if (class_exists($namespace_model)) {
                return (new $namespace_model);
            }
        }
    }

    /**
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function getNamespaceModels($model, $path = 'Models')
    {
        $namespace = 'App\\' . $path . '\\';
        return $namespace . $model;
    }

    /**
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }
}
