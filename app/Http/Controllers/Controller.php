<?php

namespace App\Http\Controllers;

use App\Models\TelegramBot;
use App\Traits\QueryListTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="PINSCHER SALES",
 *     version="2.0",
 *     description="Swagger OpenApi description",
 *     @OA\Contact(
 *          email="example@example.com"
 *      ),
 *    ),
 *    @OA\Components(
 *     @OA\SecurityScheme(
 *          securityScheme="bearerAuth",
 *          type="http",
 *          in="header",
 *          name="X-CSRF-TOKEN",
 *          description="ApiKey security",
 *          scheme="bearer",
 *          bearerFormat="JWT",
 *      ),
 *     ),
 *      security={{"bearerAuth": {}}},
 *      @OA\Server(
 *      url="{schema}://localhost",
 *      description="OpenApi parameters",
 *      @OA\ServerVariable(
 *          serverVariable="schema",
 *          enum={"https", "http"},
 *          default="http"
 *      )
 *   ),
 *   @OA\ExternalDocumentation(
 *     description="Find out more about Swagger",
 *     url="http://swagger.io"
 *   )
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use QueryListTrait;

    protected function respValidationError(Validator $validator): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'code'    => 7,
            'message' => 'Validation error',
            'fields'  => $validator->messages()->getMessages()
        ], 422);
    }

    protected function updateTelegram(Request $request, $userId)
    {
        $telegram = TelegramBot::where('username', $request->get('telegram'))->first();
        if ($telegram) {
            $telegram->update([
                'user_id' => $userId,
                'updated_at' => DB::raw('now()'),
            ]);
        }
    }
}
