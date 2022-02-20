<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     * @return Response
     */
    public function index ()
    {
        //
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
            $data = $this->service->store($request->all());

            return new TransactionResource($data);
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param Transaction $transaction
     * @return Response
     */
    public function show (Transaction $transaction)
    {
        //
    }
}
