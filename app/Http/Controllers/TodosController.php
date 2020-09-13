<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $list = $request->list_id ? $request->list_id : 0;

        if ($list && !$this->userAuth($list)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $where = [];
        $ids = array();

        if ($list) {
            $ids[] = $list;
        } else {
            $list_ids = TodoList::select('id')->whereUserId(Auth::id())->get();
            foreach ($list_ids as $list_id) {
                $ids[] = $list_id->id;
            }

        }

        $filter = $request->filter ? $request->filter : 'ALL';
        switch ($filter) {
            case 'TODO':
                $where['completed'] = 0;
                break;
            case 'COMPLETED':
                $where['completed'] = 1;
                break;
            default:
                break;
        }
        $data = Todo::select(['id', 'todo', 'list_id', 'completed'])
        ->where($where)
        ->whereIn('list_id', $ids)
            ->orderBy('id', 'desc')
            ->paginate(20);
        return $this->getResult($data->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only(['list_id', 'todo', 'completed']);
        $data['list_id'] = $data['list_id'] ? $data['list_id'] : 1;
        if (!$this->userAuth($data['list_id'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data['user_id'] = Auth::id();

        return $this->getResult(Todo::create($data)->toArray());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(Todo $id)
    {
        if (!$this->userAuth($id->list_id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->getResult($id->toArray());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
        if (!$this->userAuth($request->list_id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $todo->todo = $request->todo;
        $todo->completed = $request->completed;
        $todo->list_id = $request->list_id;
        return $this->getResult($todo->toArray(), $todo->save());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {

        if (!$this->userAuth($todo->list_id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->getResult($todo->toArray(), $todo->delete());
    }

    private function getResult(array $data = [], $success = true)
    {
        return response()->json([
            'result' => $data,
            'success' => $success
        ]);
    }

    private function userAuth($listId)
    {
        $where['user_id'] = Auth::id();
        $where['id'] = $listId;
        return TodoList::where($where)->exists();
    }
}
