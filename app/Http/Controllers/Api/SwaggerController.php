<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SwaggerController extends Controller
{

    public function index(Request $request){
        $apis_token = $request->query('token');
        if($apis_token == config('apis.token')){
            $path = __DIR__ . '/swagger.json';
            $json = json_decode(file_get_contents($path));
            return response()->json($json);
        } else {
            return abort(403);
        }
    }

}
