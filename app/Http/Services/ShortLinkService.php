<?php

namespace App\Http\Services;

use App\Exceptions\EmptyStringException;
use App\Models\Link;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use function PHPUnit\Framework\throwException;

class ShortLinkService {
    // Функция для записи ссылки
    public function shortLink($link, $password = null) {
        // Проверка на пустую строку
        if (blank($link)) {
            throw new EmptyStringException("Ссылка не должна быть пустой.");
        }

        $free_row = Link::where("original_url", null)->first();
        // Если мы не нашли доступное поле, создаем 50 пустых полей
        if (!$free_row) {
            $this->preallocateCodes();
            $free_row = Link::where("original_url", null)->first();
        }

        // Вставляем данные
        $free_row->user_id = Auth::user()->id ?? null;
        $free_row->original_url = $link;
        $free_row->password = $password;
        $free_row->expired_at = now()->addDays(7);
        $free_row->is_active = true;
        $free_row->created_at = now();
        $free_row->updated_at = now();
        $free_row->save();

        return $free_row;
    }

    // Функция создания заранее прописанных кодов
    public function preallocateCodes($count = 50) {
        $insert_data = [];
        // Берем все существующие коды
        $existingCodes = Link::pluck("short_code")->flip();

        // Цикл, которые прерывается, когда данные для вставки заполняются в нужном количестве
        while (count($insert_data) < $count) {
            // Генерируем код
            $code = Str::password(length: 6, symbols: false);

            // Проверяем, есть ли он в бд и в наших данных для вставки
            if (!$existingCodes->has($code) && !isset($insert_data[$code])) {
                $insert_data[$code] = [
                    "short_code" => $code
                ];
            }
        }

        // Массово вставляем через Eloquent-модель
        Link::insert(array_values($insert_data));
    }


}
