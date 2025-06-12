<?php

namespace App\Http\Controllers;

use App\Exceptions\CodeException;
use App\Models\Designs\PopupContent;
use App\Models\Designs\Banner;
use App\Models\Promotions\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class MainController extends Controller
{
     public function index(Request $request)
    {
        return view('web.index');
    }
}
?>
