<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Changelog extends Model
{

    /**
     * @var string[]
     */
    protected $fillable = [
        'build_id',
        'locale',
        'content'
    ];

    /**
     * The related build
     *
     * @return BelongsTo
     */
    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class);
    }
}
