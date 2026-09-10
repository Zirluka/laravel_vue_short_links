<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkRequest;
use App\Http\Services\ShortLinkService;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkController extends Controller
{

    // index
    public function getUrl($code) {
        $link = Link::where("short_code", $code)->first();
        if (!$link) {
            return response()->json([], 404);
        }

        return response()->json([
            "data" => $link->original_url
        ], 200);
    }


    // create
    public function shortLink(LinkRequest $request, ShortLinkService $shortLinkService) {
        // Если пользователь не авторизован, то создаем анонимную ссылку
        if (!Auth::check()) {
            // Используем сервис для создания анонимной ссылки
            try {
                $code = $shortLinkService->shortLink($request->validated("link"));

                return response()->json([
                    "status" => "success",
                    "code" => $code->short_code
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    "status" => "error",
                    "data" => $th->getMessage(),
                ], 500);
            }
        } else {

        }

    }

}
