<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * @var TransactionService
     */
    protected $service;

    /**
     * @param TransactionService $service
     */
    public function __construct (TransactionService $service)
    {
        $this->middleware("permission:transfer:list")->only(["index"]);
        $this->middleware("permission:transfer:store")->only("store");

        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return TransactionCollection|JsonResponse
     */
    public function index (Request $request)
    {
        try {
            $data = $this->service->setModelType($request->model_type)->setModelId($request->model_id)->index();
            return new TransactionCollection($data);

        } catch (\Exception $e) {
            return $this->error('There was a problem processing the data, please try again later.');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|TransactionResource
     */
    public function store (Request $request)
    {
        try {
            $data = $this->service->setModelType($request->model_type)->setModelId($request->model_id)->store($request->all());

            return new TransactionResource($data);
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        } catch (\BadMethodCallException $e) {
            return $this->error('Resource unavailable', 403);
        }
    }

}
