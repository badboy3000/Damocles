<?php

namespace App\Http\Controllers;

use App\Http\Services\SearchService;
use App\Models\Bangumi;
use App\Models\CartoonRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class CartoonRoleController extends Controller
{
    public function show(Request $request)
    {
        return CartoonRole::find($request->get('id'));
    }

    public function create(Request $request)
    {
        $bangumiId = $request->get('bangumi_id');
        $time = Carbon::now();

        $id =  CartoonRole::insertGetId([
            'bangumi_id' => $bangumiId,
            'avatar' => $request->get('avatar'),
            'name' => $request->get('name'),
            'intro' => $request->get('intro'),
            'alias' => $request->get('alias'),
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $searchService = new SearchService();
        $searchService->create(
            $id,
            $request->get('name') . ',' . $request->get('alias'),
            'role'
        );

        Redis::DEL('cartoon_role_trending_ids');
        Redis::DEL('bangumi_'.$bangumiId.'_cartoon_role_ids');

        $job = (new \App\Jobs\Push\Baidu('role/' . $id));
        dispatch($job);

        return $id;
    }

    public function list(Request $request)
    {
        $take = $request->get('take') ?: 10;
        $minId = $request->get('minId') ?: 0;

        $list = CartoonRole::orderBy('id', 'DESC')
            ->when($minId, function ($query) use ($minId) {
                return $query->where('id', '<' ,$minId);
            })
            ->take($take)
            ->get();

        if ($minId)
        {
            return $list;
        }

        $total = CartoonRole::count();

        return [
            'list' => $list,
            'role' => CartoonRole::select('id', 'name', 'bangumi_id')->get(),
            'bangumi' => Bangumi::all(),
            'total' => $total
        ];
    }

    public function edit(Request $request)
    {
        $id = $request->get('id');

        CartoonRole::where('id', $id)->update([
            'bangumi_id' => $request->get('bangumi_id'),
            'avatar' => $request->get('avatar'),
            'name' => $request->get('name'),
            'intro' => $request->get('intro'),
            'alias' => $request->get('alias')
        ]);

        $searchService = new SearchService();
        $searchService->update(
            $id,
            $request->get('name') . ',' . $request->get('alias'),
            'role'
        );

        $job = (new \App\Jobs\Push\Baidu('role/' . $id, 'update'));
        dispatch($job);
    }
}
