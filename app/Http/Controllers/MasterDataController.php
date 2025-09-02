<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use App\UseCases\MasterData\GetAllMasterDataAction;
use App\Http\Resources\MasterData\MasterDataResource;

class MasterDataController extends Controller
{
    /**
     * マスタデータ取得
     */
    public function getAllMasterData(GetAllMasterDataAction $action): JsonResponse
    {
        $masterData = $action();

        return response()->json(new MasterDataResource($masterData), Response::HTTP_OK);
    }
}
