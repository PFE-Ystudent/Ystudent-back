<?php

namespace App\Http\Traits;

trait RequestDefaultValuesTrait {

    protected function prepareForValidation(){
        if(method_exists( $this, 'defaults' ) ) {
            foreach ($this->defaults() as $key => $defaultValue) {
                if (!$this->has($key)) $this->merge([$key => $defaultValue]);
            }
        }
    } 
}