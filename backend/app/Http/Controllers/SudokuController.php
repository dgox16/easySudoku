<?php

namespace App\Http\Controllers;

use App\Enums\SudokuDifficult;
use App\Http\Requests\backwardRequest;
use App\Http\Requests\NewGameRequest;
use App\Http\Requests\newMovementRequest;
use App\Models\Game;
use App\Models\Movement;
use App\Models\Sudoku;
use App\Services\SudokuGenerator;
use Illuminate\Http\JsonResponse;

class SudokuController extends Controller
{
    public function newGameSudoku(NewGameRequest $request): JsonResponse
    {
        $difficult = SudokuDifficult::from($request->difficult);

        $generator = new SudokuGenerator;
        $board = $generator->generate();
        $sudokuWithRemovedNumbers = $generator->removeNumbers($difficult);

        $newSudoku = Sudoku::create([
            'solution' => $board,
            'grid' => $sudokuWithRemovedNumbers,
            'difficult' => $difficult->value,
        ]);

        $game = Game::create(['sudoku_id' => $newSudoku->id]);

        Movement::create([
            'game_id' => $game->id,
            'current_grid' => $sudokuWithRemovedNumbers,
            'number_movement' => 1,
            'is_winning_movement' => false,
        ]);

        return response()->json([
            'game' => $game->id,
            'sudoku' => $newSudoku->grid,
        ]);
    }

    public function validateCurrentGrid(array $originalGrid, array $currentGrid): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($originalGrid[$row][$col] !== 0) {
                    if ($currentGrid[$row][$col] !== $originalGrid[$row][$col]) {
                        return false; // Si no coincide, retorna false
                    }
                }
            }
        }

        return true;
    }

    public function validateVictory(array $solutionGrid, array $currentGrid): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($currentGrid[$row][$col] !== $solutionGrid[$row][$col]) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getNumberMovement($gameId)
    {
        $movements = Movement::where('game_id', $gameId)->count();

        return $movements === 0 ? 1 : $movements + 1;
    }

    public function newMovement(newMovementRequest $request): JsonResponse
    {
        $game = Game::find($request->game_id);
        $isGood = $this->validateCurrentGrid($game->sudoku->grid, $request->current_grid);
        if ($isGood) {
            $finished = $this->validateVictory(
                $game->sudoku->solution, $request->current_grid
            );
            $game->timer_seconds = $request->timer;
            $game->finished = $finished;
            $game->save();
            $newMovement = Movement::create([
                'game_id' => $request->game_id,
                'current_grid' => $request->current_grid,
                'number_movement' => $this->getNumberMovement($request->game_id),
                'is_winning_movement' => $finished,
            ]);

            return response()->json(
                [
                    'game' => $newMovement->current_grid,
                    'is_winning_movement' => $finished,
                ]
            );
        }

        return response()->json([
            'error' => 'error',
        ]);
    }

    public function backwardMove(backwardRequest $request): JsonResponse
    {
        $lastMove = Movement::where('game_id', $request->game)->orderBy('number_movement', 'desc')->first();

        if (!$lastMove || $lastMove->number_movement == 1) {
            return response()->json(['message' => 'No hay movimientos para deshacer.'], 400);
        }

        $previousMove = Movement::where('game_id', $request->game)->where('number_movement', $lastMove->number_movement - 1)->first();

        $lastMove->delete();

        return response()->json([
            'game' => $previousMove->game_id,
            'sudoku' => $previousMove->current_grid,
        ]);
    }

    public function searchHint(array $solutionGrid, array $currentGrid)
    {
        $emptyCells = [];
        foreach ($currentGrid as $rowIndex => $row) {
            foreach ($row as $colIndex => $cell) {
                if ($cell === 0) {
                    $emptyCells[] = [$rowIndex, $colIndex];
                }
            }
        }

        $randomCell = $emptyCells[array_rand($emptyCells)];
        $row = $randomCell[0];
        $column = $randomCell[1];

        $hint = $solutionGrid[$row][$column];

        return [
            'row' => $row + 1,
            'column' => $column + 1,
            'hint' => $hint,
        ];
    }

    public function getHint(backwardRequest $request): JsonResponse
    {
        $lastMove = Movement::where('game_id', $request->game)->orderBy('number_movement', 'desc')->first();
        $currentGrid = $lastMove->current_grid;
        $solution = $lastMove->game->sudoku->solution;

        $hint = $this->searchHint($solution, $currentGrid);

        return response()->json($hint);
    }
}
