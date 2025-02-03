<?php

namespace Inovector\Mixpost\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Inovector\Mixpost\Concerns\Model\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;
    use HasUuid;

    public $table = 'mixpost_tags';

    protected $fillable = [
        'name',
        'hex_color'
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo('LumeSocial\Models\Organization', 'organization_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany('LumeSocial\Models\Post', 'post_tags')
            ->withTimestamps();
    }
}
