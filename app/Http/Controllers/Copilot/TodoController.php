<?php
namespace App\Http\Controllers\Copilot;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Todo;

class TodoController extends Controller {

    /**
     * @var Authenticatable|User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * @return Builder
     */
    /**
        * Display a listing of the todo.
        *
        * @return Response
        */
    public function index(Request $request)
    {
        $this->validatedRules([
            'advisor_request_id' => 'required|numeric|exists:advisor_request,id',
        ]);
        $advisor_request_id = $request->input( 'advisor_id' );
        return response()->json(Todo::where('advisor_request_id',$request->advisor_request_id)
                    ->where('user_id', $this->user->id)
                    ->orderBy('id', 'DESC')
                    ->get());
    }

    /**
        * Show the form for creating a new todo.
        *
        * @return Response
        */
    public function create(Request $request)
    {
        $this->validatedRules([
            'advisor_request_id' => 'required|numeric|exists:advisor_request,id',
            'name' => 'required|string|max:255',
        ]);
       try {
        $todo = new Todo();
        $todo->name = $request->name;
        $todo->advisor_request_id = $request->advisor_request_id;
        $todo->user_id = $this->user->id;
        $todo->save();
        return response()->json($todo);

       } catch (\Exception $exception) {
        \Log::error($exception->getMessage(), $exception->getTrace());
        throw new \Exception("Sorry, this todo can't be created! Please, try again later", 0, $exception);
    }
    }

    /**
        * Store a newly created todo in storage.
        *
        * @return Response
        */
    public function store()
    {
        //
    }

    /**
        * Display the specified todo.
        *
        * @param  int  $id
        * @return Response
        */
    public function show($id)
    {
    }

    /**
        * Show the form for editing the specified todo.
        *
        * @param  int  $id
        * @return Response
        */
    public function edit($id)
    {
        //
    }

    /**
        * Update the specified todo in storage.
        *
        * @param  int  $id
        * @return Response
        */
    public function update(Request $request)
    {
        $this->validatedRules([
            'name' => 'required|string|max:255',
            'id' => 'required',
        ]);
        try {

            $todo = Todo::find($request->id);

            if($todo){

                $todo->name = $request->name;
                $todo->user_id = $this->user->id;
                $todo->save();
                return response()->json($todo);
            }

        } catch (\Exception $exception) {
            \Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, this todo can't be created! Please, try again later", 0, $exception);
    }
}


    /**
        * Remove the specified todo from storage.
        *
        * @param  int  $id
        * @return Response
        */
    public function destroy(Request $request)
    {
        try {

            $todo = Todo::find($request->id);
            if ($todo){
                $todo->delete();
                return response()->json(['status' => true, "message"=> 'Deleted succesfully!']);
            }
            else
            return response()->json(['status' => false, "message"=> 'Record does not exist!']);

        } catch (\Exception $exception) {
            \Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, this todo can't be created! Please, try again later", 0, $exception);
    }
    }
    public function markAsCompleted(Request $request)
    {
        try {
            $todo = Todo::find($request->id);
            if($todo){
                $todo->completed = 1;
                $todo->save();
                return response()->json($todo);
            }

           } catch (\Exception $exception) {
            \Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, this todo can't be created! Please, try again later", 0, $exception);
        }
    }

}
