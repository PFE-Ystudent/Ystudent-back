<?php

namespace App\Http\Traits;

trait IndexTrait {

    function indexQuery($query, $validated) {
        return $query->skip(($validated['page'] - 1) * $validated['per_page'])
            ->limit($validated['per_page']);
    }
}
