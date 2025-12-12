<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Logs\LogEvent;
use Illuminate\Http\Request;


class LogController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function events(Request $request)
    {
        $request->validate([
            'limit' => 'integer',
            'offset' => 'integer',
        ]);

        // костыль из-за кастомной пагинации
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $perPage = $limit;
        $page = $offset/$limit + 1;

        $result = LogEvent::query()
            ->with(['initiator', 'target'])
            ->latest('id')
            ->paginate(perPage: $perPage, page: $page);

        return $result->through(function (LogEvent $model) {
            return [
                'id' => $model->id,
                'type_name' => $model->typeName(),
                'description' => $model->description,
                'user_name' => $model->initiator?->name,
                'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
            ];
        });

    }


}
