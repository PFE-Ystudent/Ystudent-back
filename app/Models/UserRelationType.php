<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRelationType extends Model
{
    use HasFactory;

    public $fillable = ['name'];

    public $incrementing = false;

    public static $contact = 1;
    public static $report = 2;
    public static $request = 3;
    public static $blocked = 4;
}
