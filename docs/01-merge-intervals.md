# Task 1 — Merge intervals

## Description

Given a list of integer intervals, merge every group of intervals that overlap or touch into a single interval and return the result sorted by start. Use plain PHP without Symfony or a database.

Implement [Task\MergeIntervals::merge()](../src/Domain/Logic/Task/MergeIntervals.php) and check it with:

```bash
php logic.php merge-intervals
```

## Definitions

- An interval `[start, end]` is **closed**: it contains both endpoints and every integer between them.
- Two intervals **overlap** when they share at least one point, for example `[1, 5]` and `[4, 8]`.
- Two intervals **touch** when one ends exactly where the other starts, for example `[1, 3]` and `[3, 5]`. Touching intervals are merged.
- Intervals that are only next to each other do **not** touch: `[1, 2]` and `[3, 4]` stay separate, because no point is shared.
- Merging can chain: `[1, 4]`, `[2, 5]` and `[5, 6]` all become `[1, 6]`, even though `[1, 4]` and `[5, 6]` don't overlap directly.

## Input arguments

```php
public function merge(array $intervals): array
```

`$intervals` is a list of pairs `[start, end]`:

| Rule | Detail |
| --- | --- |
| Pair shape | Each interval is a list with exactly two elements, at keys `0` and `1`. |
| Endpoint type | Both endpoints are PHP `int`. Floats (`1.0`) and numeric strings (`'1'`) are invalid. |
| Order | `start <= end`. Zero-length intervals such as `[2, 2]` are allowed. |
| Range | Any integer, including negatives, `PHP_INT_MIN` and `PHP_INT_MAX`. |
| List | May be empty, unsorted, contain duplicates, or contain intervals nested inside others. Its keys are ignored. |

Do not change the caller's array. Sorting a local copy is fine.

## Output

Return a list of merged `[start, end]` pairs:

- sorted by `start`, ascending;
- with no two intervals that overlap or touch;
- covering exactly the same points as the input, no more and no less;
- re-indexed from `0`, with each pair also a list `[start, end]`.

```php
$intervals = new App\Domain\Logic\Task\MergeIntervals();
$result = $intervals->merge([[5, 8], [1, 3], [3, 6]]);
// [[1, 8]]: [1, 3] touches [3, 6], which overlaps [5, 8].
```

## Examples and edge cases

| Input | Output | Why |
| --- | --- | --- |
| `[]` | `[]` | Empty input |
| `[[1, 3]]` | `[[1, 3]]` | Single interval |
| `[[1, 3], [3, 5]]` | `[[1, 5]]` | Touching at `3` |
| `[[1, 2], [3, 4]]` | `[[1, 2], [3, 4]]` | Adjacent but not touching |
| `[[1, 10], [2, 4]]` | `[[1, 10]]` | Nested |
| `[[5, 7], [1, 2]]` | `[[1, 2], [5, 7]]` | Unsorted input, sorted output |
| `[[1, 5], [1, 2]]` | `[[1, 5]]` | Same start |
| `[[1, 4], [2, 5], [7, 9], [8, 8], [6, 6]]` | `[[1, 5], [6, 6], [7, 9]]` | Chained merge; `[6, 6]` stays alone |
| `[[2, 2], [2, 2]]` | `[[2, 2]]` | Duplicates |
| `[[1, 1], [1, 3]]` | `[[1, 3]]` | Zero-length interval touching another |
| `[[-5, -1], [-2, 3]]` | `[[-5, 3]]` | Negative endpoints |

## Errors

Throw `InvalidArgumentException` if any interval is invalid. Validate every interval before returning: one bad interval fails the whole call, even when the others are valid.

| Input | Reason |
| --- | --- |
| `[[5, 1]]` | Start greater than end |
| `[[1]]`, `[[1, 2, 3]]` | Not exactly two endpoints |
| `[['start' => 1, 'end' => 3]]` | Pair is not a list `[start, end]` |
| `[[1.0, 2]]`, `[['1', 2]]` | Endpoint is not an `int` |
| `[5]` | Interval is not an array |
| `[[1, 2], [3, 'x']]` | One invalid interval among valid ones |

## Expectations

- Aim for `O(n log n)` time, where `n` is the number of intervals. Comparing every pair of intervals (`O(n²)`) is not expected.
- Keep the method free of side effects: no output, no global state.
