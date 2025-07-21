<?php

namespace App\Http\Traits;

trait IndexTrait {

    function indexQuery($query, $validated) {
        return $query->clone()->skip(($validated['page'] - 1) * $validated['per_page'])
            ->limit($validated['per_page']);
    }
}
