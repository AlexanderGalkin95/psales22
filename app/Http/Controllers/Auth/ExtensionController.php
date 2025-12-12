<?php

namespace App\Http\Controllers\Auth;

use App\Events\GoogleExtensionEvent;
use App\Exceptions\ExtensionBlockedException;
use App\Http\Controllers\Controller;
use App\Models\GoogleExtension;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @deprecated
 */
class ExtensionController extends Controller
{
    /**
     * @throws ExtensionBlockedException
     */
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'extension_id' => 'required|string',
            'fingerprint' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->respValidationError($validator);
        }

        $user = Auth::user();

        $extension = GoogleExtension::where('extension_id', '=', $request->get('extension_id'))->first();
        if ($extension === null) {
            $extension = GoogleExtension::saveNew($request);
        }

        if (!$extension->isExtensionBound()) {
            $extension->bindExtension($user->id);
        }

        $user_extension = $user->extension()->value('fingerprint');
        if ($extension->fingerprint !== $user_extension) {
            $extension->disable();
        }

        $data = [
            'extension_id' =>  $extension->id,
            'user_id' =>  $extension->user_id,
            'enabled' =>  true,
            'online' => true,
            'online_date' =>  DB::raw('now()'),
        ];

        $extension->activities()->create($data);

        event(new GoogleExtensionEvent($request->get('extension_id')));

        if ($extension->is_blocked) {
            $data['enabled'] = false;
            $data['online'] = false;
            $data['offline_date'] = DB::raw('now()');
            $extension->activities()->create($data);
            throw new ExtensionBlockedException('Расширение заблокировано',403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Расширение был успешно идендифицирован'
        ], 200 );
    }
}
