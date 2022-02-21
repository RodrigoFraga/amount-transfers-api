<?php

namespace App\Services;

trait ServiceTrait
{
    /**
     * @return mixed
     */
    abstract function model ();

    /**
     * @var
     */
    protected $model_id;

    /**
     * @var
     */
    protected $model_type;

    /**
     * @param $modelId
     * @return $this
     */
    public function setModelId ($modelId)
    {
        $this->model_id = $modelId;

        return $this;
    }

    /**
     * @param $modelType
     * @return $this
     */
    public function setModelType ($modelType)
    {
        $this->model_type = $modelType;

        return $this;
    }
}
