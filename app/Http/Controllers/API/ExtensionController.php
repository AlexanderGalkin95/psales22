<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateExtensionAdminActionRequest;
use App\Models\GoogleExtension;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @deprecated
 */
class ExtensionController extends Controller
{
    /**
     * Block an active google extension
     *
     * @param ValidateExtensionAdminActionRequest $request
     * @param $extensionId
     * @return JsonResponse
     */
    public function block(ValidateExtensionAdminActionRequest $request, $extensionId): JsonResponse
    {
        $extension = GoogleExtension::find($extensionId);
        $extension->disable();
        return response()->json([
            'message' => 'Расширение был заблокировано успешно'
        ], 200);
    }

    /**
     * Unblock a blocked google extension
     *
     * @param ValidateExtensionAdminActionRequest $request
     * @param $extensionId
     * @return JsonResponse
     */
    public function unblock(ValidateExtensionAdminActionRequest $request, $extensionId): JsonResponse
    {
        DB::transaction(function () use ($extensionId) {
            $extension = GoogleExtension::find($extensionId);
            $extension->unblock();
        });
        return response()->json([
            'message' => 'Расширение был разблокировано успешно'
        ], 200);
    }

    /**
     * Remove duplicates of an existing main google extension
     *
     * @param ValidateExtensionAdminActionRequest $request
     * @param $extensionId
     * @return JsonResponse
     */
    public function removeDuplicates(ValidateExtensionAdminActionRequest $request, $extensionId): JsonResponse
    {
        $extension = GoogleExtension::find($extensionId);
        $extension->removeDuplicates();
        return response()->json([
            'message' => 'Дубликаты были удалены успешно'
        ], 200);
    }

    /**
     * Drop an existing google extension with all its related duplicates
     *
     * @param ValidateExtensionAdminActionRequest $request
     * @param $extensionId
     * @return JsonResponse
     */
    public function reset(ValidateExtensionAdminActionRequest $request, $extensionId): JsonResponse
    {
        DB::transaction(function () use ($extensionId) {
            $extension = GoogleExtension::find($extensionId);
            $extension->reset();
        });
        return response()->json([
            'message' => 'Сброс расширения прошёл успешно'
        ], 200);
    }

    /**
     * Delete an existing google extension
     *
     * @param ValidateExtensionAdminActionRequest $request
     * @param $extensionId
     * @return JsonResponse
     */
    public function delete(ValidateExtensionAdminActionRequest $request, $extensionId): JsonResponse
    {
        DB::transaction(function () use ($extensionId) {
            $extension = GoogleExtension::find($extensionId);
            $extension->delete();
        });

        return response()->json([
            'message' => 'Расширение был удалено успешно'
        ], 200);
    }

}
