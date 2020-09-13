<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class TodoListsController extends Controller
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
        $where['user_id'] = Auth::id();
        $lists = TodoList::where($where)->paginate(20);
        return $this->getResult($lists->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = Auth::id();
        $name = $request->input('name');
        $todoList = new TodoList();
        $todoList->name = $name;
        $todoList->user_id = $user_id;
        //$todoList->save();
        //return $todoList;
        return $this->getResult(TodoList::create(compact('name', 'user_id'))->toArray());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TodoList  $todoList
     * @return \Illuminate\Http\Response
     */
    public function show(TodoList $todoList)
    {
        if ($todoList->user_id != Auth::id())
            return response()->json(['error' => 'Unauthorized'], 401);

        return $this->getResult($todoList->toArray());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TodoList  $todoList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TodoList $todoList)
    {
        if ($todoList->user_id != Auth::id())
            return response()->json(['error' => 'Unauthorized'], 401);

        $todoList->name = $request->name;
        return $this->getResult($todoList->toArray(), $todoList->save());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TodoList  $todoList
     * @return \Illuminate\Http\Response
     */
    public function destroy(TodoList $todoList)
    {
        if ($todoList->user_id != Auth::id())
            return response()->json(['error' => 'Unauthorized'], 401);

        return $this->getResult($todoList->toArray(), $todoList->delete());
    }

    private function getResult(array $data = [], $success = true)
    {
        return response()->json([
            'result' => $data,
            'success' => $success
        ]);
    }
}
