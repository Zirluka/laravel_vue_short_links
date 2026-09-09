<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class EmptyStringException extends Exception
{
    public function render(Request $request) {
        // Если это API
        if ($request->expectsJson()) {
            return response()->json([
                "error" => "Validation error",
                "message" => $this->getMessage()
            ], 422);
        }

        // Если веб версия (В данном кейсе не будет использовано, но все равно прописываю)
        return back()->withErrors(["text" => $this->getMessage()])->withInput();
    }
}
