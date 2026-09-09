<?php

namespace App\Http\Services;

use App\Exceptions\EmptyStringException;
use App\Models\Link;
use Exception;
use function PHPUnit\Framework\throwException;

class ShortLinkService {
    public function short_link($link): string {
        // Проверка на пустую строку
        if (blank($link)) {
            throw new EmptyStringException("Ссылка не должна быть пустой.");
        }

        $free_row = Link::where("original_url", null)->first();
        /*
            Сделать логику, что при отсутствии пустых полей, должны
            создаться 50 записей с готовыми short_link, но пустыми original_link
            После чего сделать запись
        */
        if (!$free_row) {}
    }

}
