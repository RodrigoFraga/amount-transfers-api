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
     * @param $model_id
     * @return $this
     */
    public function setModelId ($model_id)
    {
        $this->model_id = $model_id;

        return $this;
    }

    /**
     * @param $model_type
     * @return $this
     */
    public function setModelType ($model_type)
    {
        $this->model_type = $model_type;

        return $this;
    }
}
