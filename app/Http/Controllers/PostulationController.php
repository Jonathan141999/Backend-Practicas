<?php

namespace App\Http\Controllers;

//use App\Models\Postulation;
use Illuminate\Http\Request;
use App\Models\Postulation as aPostulation;
use App\Http\Resources\Postulation as PostulationResource;
use App\Http\Resources\PostulationCollection;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use JWTAuth;


class PostulationController extends Controller
{
    private static $messages = [
        'required' => 'El campo :attribute es obligatorio',
        'numeric' => 'El parámetro ingresado en :attribute no es un número',
        'date' => 'El campo :attribute no es una fecha',
        'status' => 'El campo :attribute no es una cadena',
    ];
    public function index()
    {
        $user = Auth::user();
        $publication = Publication::where('publications.user_id', $user->id)
            ->selectRaw('postulations.id as postulation_id,publications.id,publications.name as publication_name,publications.hour,users.name,users.last_name,users.phone,users.email,postulations.type,postulations.status')
            ->join('postulations', 'postulations.publication_id', 'publications.id')
            ->join('users', 'users.id', 'postulations.user_id')
            ->orderBy('postulation_id')
            ->get();

        return  response()->json(["data" => $publication], 200);
    }
    public function show(aPostulation $apostulation)
    {
        $postulations = aPostulation::where('id', $apostulation->id)->first();
        $publications = Publication::where('id', $apostulation->publication_id)->first();
        $category = Category::select('name')->where('id', $publications->category_id)->first();
        $postulations->publications = $publications;
        $postulations->category = $category->name;
        return  response()->json(["data" => $postulations], 200);
    }


    public function detail($index)
    {
        $user = Auth::user();
        $publication = Publication::where('postulations.id', $index)
            ->selectRaw('postulations.id as postulation_id,publications.id,publications.name as publication_name,publications.hour,publications.details,users.name,users.last_name,users.phone,users.email,users.direction,postulations.type,postulations.languages,postulations.work_experience,postulations.career,postulations.status')
            ->join('postulations', 'postulations.publication_id', 'publications.id')
            ->join('users', 'users.id', 'postulations.user_id')
            ->first();
        return  response()->json($publication, 200);
    }

    public function requestsByUser()
    {
        $user = Auth::user();
        $requests = aPostulation::where('user_id', $user->id)->get();
        if (count($requests) >0 ){
        foreach ($requests as $request) {
            $publication = Publication::select('name')
                ->where("id", $request->publication_id)->first();
            $publication_list[] = array(
                "id" => $request->id,
                "languages" => $request->languages,
                "type" => $request->type,
                "work_experience" => $request->work_experience,
                "career" => $request->career,
                "status" => $request->status,
                "created_at" => $request->created_at,
                "user_id" => $request->user_id,
                "publication_id" => $request->publication_id,
                "publication_name" => $publication->name,
            );
        }
        return response()->json(["data" => $publication_list], 200);
    }else{
            return response()->json(['data' => $requests],200);
        }
    }

    public function store(Request $request)
    {
        $this->authorize('create', aPostulation::class);
        $request->validate([
            'languages' => 'required|string',
            'work_experience' => 'required|string',
            'career' => 'required|string',
            'type' => 'required|string',
        ], self::$messages);

        $request1 = new aPostulation($request->all());
        $request1->save();
        return response()->json(new PostulationResource($request1), 201);
    }

    public function update(Request $request,  aPostulation $apostulation)
    {
        $this->authorize('update', $apostulation);
        $request->validate([
            'language' => 'required|string',
            'work_experience' => 'required|string',
            'career' => 'required|string',
        ], self::$messages);
        $apostulation->update($request->all());
        return response()->json($apostulation, 200);
    }

    public function updatestatus($id_request, Request $request)
    {
        //$this->autorize('update', $apostulation);
        $data = aPostulation::where('id', $id_request)
            ->firstOrFail();
        $data->update([
            "status" => $request->get('status'),
        ]);
        return response()->json("Actualizacion Correcta", 200);
    }

    public function delete(Request $request, aPostulation $postulation)
    {
        $this->authorize('delete', $request);
        $request->delete();
        return response()->json(null, 204);
        //$postulation->delete();
        //return response()->json(null, 204);
    }
}
