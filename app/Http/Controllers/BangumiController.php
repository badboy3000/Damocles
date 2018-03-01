<?php

namespace App\Http\Controllers;

use App\Models\Bangumi;
use App\Models\BangumiCollection;
use App\Models\MixinSearch;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BangumiController extends Controller
{
    public function create(Request $request)
    {
        $bangumi_id = Bangumi::insertGetId([
            'name' => $request->get('name'),
            'avatar' => $request->get('avatar'),
            'banner' => $request->get('banner'),
            'summary' => $request->get('summary'),
            'released_at' => $request->get('released_at'),
            'released_video_id' => $request->get('released_video_id') ?: 0,
            'season' => $request->get('season') ? $request->get('season') : 'null',
            'alias' => $request->get('alias') ? json_encode([
                'search' => $request->get('alias')
            ]) : 'null',
            'collection_id' => $request->get('collection_id'),
            'published_at' => $request->get('published_at') ?: 0,
            'others_site_video' => $request->get('others_site_video'),
            'deleted_at' => Carbon::now(),
            'count_score' => 0
        ]);

        if ($request->get('isCollection'))
        {
            DB::table('bangumis')->where('id', $bangumi_id)->update([
                'collection_id' => $bangumi_id
            ]);
        }

        $tags = [];
        foreach($request->get('tags') as $i => $tag_id)
        {
            array_push($tags, [
                'bangumi_id' => $bangumi_id,
                'tag_id' => $tag_id
            ]);
        }
        DB::table('bangumi_tag')->insert($tags);
    }

    public function release(Request $request)
    {
        $bangumi_id = $request->get('bangumi_id');
        $video_id = $request->get('video_id');

        $video = Video::find($video_id);
        if (is_null($video)) {
            return response()->json(['data' => ''], 403);
        }

        Bangumi::where('id', $bangumi_id)->update([
            'released_time' => time(),
            'released_video_id' => $video_id
        ]);

        $searchId = MixinSearch::whereRaw('type_id = ? and modal_id = ?', [2, $bangumi_id])
            ->pluck('id')
            ->first();

        if (!is_null($searchId))
        {
            MixinSearch::where('id', $searchId)->increment('score', 1);
            MixinSearch::where('id', $searchId)->update([
                'updated_at' => time()
            ]);
        }

        return response('success');
    }

    public function item(Request $request)
    {
        $id = $request->get('id');

        $bangumi = Bangumi::withTrashed()->find($id);
        $bangumi['alias'] = $bangumi['alias'] === 'null' ? '' : json_decode($bangumi['alias'])->search;
        $bangumi['tags'] = $bangumi->tags()->get()->pluck('id');
        $bangumi['season'] = $bangumi['season'] === 'null' ? '' : $bangumi['season'];
        $bangumi['published_at'] = $bangumi['published_at'] * 1000;
        $bangumi['isCollection'] = $bangumi['id'] === $bangumi['collection_id'];

        return $bangumi;
    }

    public function edit(Request $request)
    {
        $rollback = false;
        $bangumi_id = $request->get('id');
        DB::beginTransaction();

        $result = DB::table('bangumi_tag')->where('bangumi_id', $bangumi_id)->delete();
        if ($result === false)
        {
            $rollback = true;
        }

        $tags = [];
        foreach($request->get('tags') as $i => $tag_id)
        {
            array_push($tags, [
                'bangumi_id' => $bangumi_id,
                'tag_id' => $tag_id
            ]);
        }
        $result = DB::table('bangumi_tag')->insert($tags);
        if ( ! $result)
        {
            $rollback = true;
        }

        $bangumi = Bangumi::withTrashed()->where('id', $bangumi_id)->first();
        $arr = [
            'name' => $request->get('name'),
            'avatar' => $request->get('avatar'),
            'banner' => $request->get('banner'),
            'summary' => $request->get('summary'),
            'released_at' => $request->get('released_at'),
            'released_video_id' => $request->get('released_video_id'),
            'season' => $request->get('season') ? $request->get('season') : 'null',
            'alias' => $request->get('alias') ? json_encode([
                'search' => $request->get('alias')
            ]) : 'null',
            'collection_id' => $request->get('collection_id'),
            'published_at' => $request->get('published_at'),
            'others_site_video' => $request->get('others_site_video')
        ];

        $result = $bangumi->update($arr);
        if ($result === false)
        {
            $rollback = true;
        }

        if ($rollback)
        {
            DB::rollBack();
        }
        else
        {
            DB::commit();

            $searchId = MixinSearch::whereRaw('type_id = ? and modal_id = ?', [2, $bangumi_id])
                ->pluck('id')
                ->first();

            if (!is_null($searchId))
            {
                MixinSearch::where('id', $searchId)->update([
                    'title' => $request->get('name'),
                    'content' => $request->get('alias')
                ]);
            }
        }
    }

    public function delete(Request $request)
    {
        $hasDeleted = $request->get('isDeleted') ;
        $bangumi_id = $request->get('id');
        $bangumi = Bangumi::withTrashed()->find($bangumi_id);

        if ($hasDeleted)
        {
            $bangumi->restore();

            $now = time();

            MixinSearch::create([
                'title' => $bangumi->name,
                'content' => $bangumi->alias === 'null' ? '' : json_decode($bangumi->alias)->search,
                'type_id' => 2,
                'modal_id' => $bangumi_id,
                'url' => '/bangumi/' . $bangumi_id,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
        else
        {
            $bangumi->delete();

            MixinSearch::whereRaw('type_id = ? and modal_id = ?', [2, $bangumi_id])
                ->delete();
        }
    }

    public function list()
    {
        return Bangumi::withTrashed()->select('id', 'name', 'collection_id', 'deleted_at')->get();
    }

    public function collections()
    {
        $list = Bangumi::where('collection_id', '<>', '0')->select('id', 'collection_id', 'name')->get();
        $result = [];
        foreach ($list as $item)
        {
            if ($item['collection_id'] === $item['id'])
            {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function createCollection(Request $request)
    {
        return Bangumi::insertGetId([
            'name' => $request->get('name'),
            'summary' => ''
        ]);
    }
}
