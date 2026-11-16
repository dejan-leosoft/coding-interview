<?php

declare(strict_types=1);

namespace App\Domain\Logic;

use App\Domain\Logic\Task\MergeIntervals;

/**
 * Runs a task from src/Domain/Logic/Task with predefined arguments.
 */
final class Logic
{
    /**
     * Predefined cases per task identifier. Each case holds the arguments
     * passed to the task method and the expected result from the task docs.
     * The result must be identical to the expected value.
     *
     * @return array<string, array{run: callable, cases: list<array{arguments: list<mixed>, expected: mixed}>}>
     */
    private function tasks(): array
    {
        return [
            'merge-intervals' => [
                'run' => static fn (array $intervals): array => (new MergeIntervals())->merge($intervals),
                'cases' => [
                    ['arguments' => [[[5, 8], [1, 3], [3, 6]]], 'expected' => [[1, 8]]],
                    ['arguments' => [[]], 'expected' => []],
                    ['arguments' => [[[1, 3]]], 'expected' => [[1, 3]]],
                    ['arguments' => [[[1, 3], [3, 5]]], 'expected' => [[1, 5]]],
                    ['arguments' => [[[1, 2], [3, 4]]], 'expected' => [[1, 2], [3, 4]]],
                    ['arguments' => [[[1, 10], [2, 4]]], 'expected' => [[1, 10]]],
                    ['arguments' => [[[5, 7], [1, 2]]], 'expected' => [[1, 2], [5, 7]]],
                    ['arguments' => [[[1, 5], [1, 2]]], 'expected' => [[1, 5]]],
                    ['arguments' => [[[1, 4], [2, 5], [7, 9], [8, 8], [6, 6]]], 'expected' => [[1, 5], [6, 6], [7, 9]]],
                    ['arguments' => [[[2, 2], [2, 2]]], 'expected' => [[2, 2]]],
                    ['arguments' => [[[1, 1], [1, 3]]], 'expected' => [[1, 3]]],
                    ['arguments' => [[[-5, -1], [-2, 3]]], 'expected' => [[-5, 3]]],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function identifiers(): array
    {
        return array_keys($this->tasks());
    }

    /**
     * Calls the task once per predefined case and reports each outcome.
     *
     * @return list<array{arguments: list<mixed>, expected: mixed, result: mixed, error: ?string, passed: bool}>
     * @throws \InvalidArgumentException For an unknown identifier.
     */
    public function run(string $identifier): array
    {
        $task = $this->tasks()[$identifier]
            ?? throw new \InvalidArgumentException(sprintf('Unknown task "%s".', $identifier));

        $outcomes = [];
        foreach ($task['cases'] as $case) {
            $result = null;
            $error = null;
            try {
                $result = ($task['run'])(...$case['arguments']);
            } catch (\Throwable $exception) {
                $error = sprintf('%s: %s', $exception::class, $exception->getMessage());
            }

            $outcomes[] = [
                'arguments' => $case['arguments'],
                'expected' => $case['expected'],
                'result' => $result,
                'error' => $error,
                'passed' => $error === null && $result === $case['expected'],
            ];
        }

        return $outcomes;
    }
}
