<?php

namespace App\Models;

use App\Enums\SudokuDifficult;
use Illuminate\Database\Eloquent\Model;

/**
 * @property false|mixed|string $solution
 * @property false|mixed|string $grid
 * @property SudokuDifficult|mixed $difficult
 * @property mixed $id
 * @method static create(array $array)
 * @method static where(string $string, SudokuDifficult $difficult)
 */
class Sudoku extends Model
{

    protected $fillable = [
        'grid',
        'solution',
        'difficult',
    ];
    protected $casts = [
        'difficult' => SudokuDifficult::class,
        'grid' => 'array',
        'solution' => 'array',
    ];
}
