<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class Model extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
} 