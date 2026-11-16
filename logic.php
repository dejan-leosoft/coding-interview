<?php

/**
 * Command-line runner for the tasks in src/Domain/Logic/Task.
 * Each task runs with the predefined arguments defined in App\Domain\Logic\Logic.
 *
 * Usage: php logic.php <identifier>
 * Run without arguments to list the identifiers.
 */

declare(strict_types=1);

use App\Domain\Logic\Logic;

if (PHP_SAPI !== 'cli') {
    return;
}

require __DIR__ . '/vendor/autoload.php';

$logic = new Logic();
$identifier = $argv[1] ?? null;

if ($identifier === null || in_array($identifier, ['-h', '--help', 'help'], true)) {
    echo 'Usage: php logic.php <identifier>' . PHP_EOL . PHP_EOL . 'Identifiers:' . PHP_EOL;
    foreach ($logic->identifiers() as $name) {
        echo '  ' . $name . PHP_EOL;
    }
    exit(0);
}

if (!in_array($identifier, $logic->identifiers(), true)) {
    fwrite(STDERR, sprintf('Unknown identifier "%s". Available: %s%s', $identifier, implode(', ', $logic->identifiers()), PHP_EOL));
    exit(1);
}

$json = static fn (mixed $value): string => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

$outcomes = $logic->run($identifier);
$passed = 0;

foreach ($outcomes as $outcome) {
    $arguments = implode(', ', array_map($json, $outcome['arguments']));
    if ($outcome['passed']) {
        ++$passed;
        printf('PASS  (%s) => %s%s', $arguments, $json($outcome['result']), PHP_EOL);
    } elseif ($outcome['error'] !== null) {
        printf('FAIL  (%s) => %s, expected %s%s', $arguments, $outcome['error'], $json($outcome['expected']), PHP_EOL);
    } else {
        printf('FAIL  (%s) => %s, expected %s%s', $arguments, $json($outcome['result']), $json($outcome['expected']), PHP_EOL);
    }
}

printf('%s%d/%d passed%s', PHP_EOL, $passed, count($outcomes), PHP_EOL);
exit($passed === count($outcomes) ? 0 : 1);
