<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Http\Resources\Publication as PublicationResource;
use App\Http\Resources\PublicationCollection;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PublicationController extends Controller
{
    private static $messages = [
        'required'=>'El campo :attribute es obligatorio',
        'exists'=>'El parámetro :attribute no corresponde a ningun registro',
        'integer'=>'El parámetro ingresado en :attribute no es un entero',
        'numeric'=>'El parámetro ingresado en :attribute no es un número',
        'string'=>'El campo :attribute tiene que ser un string',
        'image'=>'El campo :attribute no es una imagen',
    ];
    public function index()
    {
        $user = Auth::user();
        $publications = Publication::where('user_id',$user->id)->get();
        foreach ($publications as $publication) {
            $category = Category::select('name')
                ->where("id",$publication->category_id)->first();

            $publication_list[] = array(
                'id' => $publication->id,
                'name' => $publication->name,
                'email' => $publication->email,
                'hour' => $publication->hour,
                'location' => $publication->location,
                'type' => $publication->type,
                'phone' => $publication->phone,
                'image'=> $publication->image,
                'details'=>$publication->details,
                'created_at'=> $publication->created_at,
                'category' => $category->name,
                'user_name'=> $user->name.' '.$user->last_name,
            );
        }
        return response()->json(['data' => $publication_list],200);
    }

    public function forstudents(){

        $publications = Publication::all();
        foreach ($publications as $publication) {
            $category = Category::select('name')
                ->where("id",$publication->category_id)->first();
            $user = User::select('name','last_name')
                ->where('id',$publication->user_id)->first();

            $publication_list[] = array(
                'id' => $publication->id,
                'name' => $publication->name,
                'email' => $publication->email,
                'hour' => $publication->hour,
                'location' => $publication->location,
                'type' => $publication->type,
                'phone' => $publication->phone,
                'image'=> $publication->image,
                'details'=>$publication->details,
                'created_at'=> $publication->created_at,
                'category' => $category->name,
                'user_name'  => $user->name.' '.$user->last_name,
            );
        }
        return response()->json(['data' => $publication_list],200);
    }
    public function show(Publication $publication)
    {
        $publications = Publication::where('id', $publication->id)->first();
        $category = Category::select('name')
                ->where("id",$publication->category_id)->first();
        $user = User::select('name','last_name')
            ->where('id',$publication->user_id)->first();

        $publications->category = $category->name;
        $publications->user = $user->name.' '.$user->last_name;
        return  response()->json($publications,200);
    }
    public function searchPublication($category)
    {
        $publications = Publication::where("category_id",$category)->get();
        if (count($publications) >0 ){
            foreach ($publications as $publication) {
                $categoria = Category::select('name')
                    ->where("id",$publication->category_id)->first();
                $user = User::select('name','last_name')
                    ->where('id',$publication->user_id)->first();
    
                $publication_list[] = array(
                    'id' => $publication->id,
                    'name' => $publication->name,
                    'email' => $publication->email,
                    'hour' => $publication->hour,
                    'location' => $publication->location,
                    'type' => $publication->type,
                    'phone' => $publication->phone,
                    'image'=> $publication->image,
                    'details'=>$publication->details,
                    'created_at'=> $publication->created_at,
                    'category' => $categoria->name,
                    'user_name'  => $user->name.' '.$user->last_name,
                );
            }
            return response()->json(['data' => $publication_list],200);
        }else{
            return response()->json(['data' => $publications],200);
        }
        
    }
    public function image(Publication $publication)
    {
        return response()->download(public_path(Storage::url($publication->image)),
            $publication->name);
    }
    public function store(Request $request)
    {
        $this->authorize('create', Publication::class);
        $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string',
            'hour' => 'required|string',
            'type' => 'required|string',
            'details' => 'required|string',
            'image' => 'required|image',
            'category_id' => 'required|exists:categories,id',
        ],self::$messages);
        //$publication = Publication::create($request->all());
        $publication = new Publication($request->all());
        $path = $request->image->store('public/publications');
        $publication->image = 'publications/' . basename($path);
        $publication->setAttribute('name', $request->get('name'));
        //$publication->image = $path;
        $publication->save();
        return response()->json(new PublicationResource($publication), 201);
    }
    public function update(Request $request,  Publication $publication)
    {
        $this->authorize('update',$publication);
        $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'phone' => 'required|string',
            'hour' => 'required|string',
            'details' => 'required|string',
        ],self::$messages);
        $publication->update($request->all());
        return response()->json($publication, 200);
    }
    public function delete(Request $request, Publication $publication )
    {
        $publication->delete();
        return response()->json(null, 204);
    }

}
