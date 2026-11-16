# Coding tasks

The tasks cover standalone PHP algorithms, player registration, Doctrine transactions, events, and payment providers.

| Task | Description | Entry point |
| --- | --- | --- |
| [01 — Merge intervals](01-merge-intervals.md) | Merge overlapping and touching intervals. | `MergeIntervals::merge()` |
| [05 — Player registration review](05-create-player.md) | Review and refactor the controller that registers a player and dispatches a welcome-email event. | `PlayerController` (`POST /api/players`) |
| [03 — Payment providers](03-payment-provider.md) | Implement Codonix deposits and Coinbase withdrawals, and review the existing integration setup. | `CodonixPayment` and `CoinbasePayment` |

The merge intervals task is implemented in `src/Domain/Logic/Lib` as a plain PHP class. It has no HTTP routes or Symfony dependencies. The examples assume Composer's `vendor/autoload.php` has been loaded.

The task stub to implement lives in `src/Domain/Logic/Task`. Run it with `logic.php` in the repository root, passing only its identifier. `App\Domain\Logic\Logic` calls the task with predefined arguments (the examples from its task doc) and compares each result with the expected value.

```bash
php logic.php                       # list identifiers
php logic.php merge-intervals       # Task 01: Task\MergeIntervals::merge()
```

Each case prints `PASS` or `FAIL` with its arguments and result. The exit code is `0` when every case passes and `1` otherwise.

Task 03 starts from the existing Payment integration. Complete the Codonix deposit and Coinbase withdrawal providers, and comment on any changes you recommend to the setup.

Task 5 requires a configured Doctrine database and tables matching the entities. Migrations are not part of these exercises.
