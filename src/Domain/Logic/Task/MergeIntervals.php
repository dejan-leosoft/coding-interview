<?php

declare(strict_types=1);

namespace App\Domain\Logic\Task;

final class MergeIntervals
{
    /**
     * Task 1: Merge overlapping or touching intervals, sorted by start time.
     * The caller's input is not modified. Empty input returns [].
     * Example: [[5, 8], [1, 3], [3, 6]] returns [[1, 8]].
     *
     * @param list<array{0: int, 1: int}> $intervals
     * @return list<array{0: int, 1: int}>
     * @throws \InvalidArgumentException For invalid endpoints or start > end.
     */
    public function merge(array $intervals): array
    {
        // TODO: edit return and write here your implementation
        return [[-1, -1]];
    }
}
