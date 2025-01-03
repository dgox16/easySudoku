<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static create(array $array)
 * @method static find(mixed $game_id)
 */
class Game extends Model
{
    protected $fillable = [
        'sudoku_id',
        'finished',
        'timer_seconds'
    ];

    public function sudoku(): BelongsTo
    {
        return $this->belongsTo(Sudoku::class);
    }
}
