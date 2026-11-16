# Task 3 — Implement deposit and withdrawal providers

## Description

The Payment domain already has an integration setup with a shared interface,
a provider factory, separate provider lists, and controllers for deposits,
withdrawals, and callbacks.

Read the existing setup, understand how a request reaches a provider, and
complete both provider implementations:

| Provider | Class | Supported operation |
| --- | --- | --- |
| Codonix | `CodonixPayment` | Deposits only |
| Coinbase | `CoinbasePayment` | Withdrawals only |

For this exercise, simulate provider behaviour locally. No real payment service,
credentials, or money are required.

## Existing setup

Start with the code in [src/Domain/Payment](../src/Domain/Payment):

- [Payment](../src/Domain/Payment/Payment.php): the shared provider interface.
- [PaymentFactory](../src/Domain/Payment/PaymentFactory.php): provider selection.
- [provider.yaml](../src/Domain/Payment/Config/provider.yaml): separate deposit and withdrawal provider lists.
- [DepositController](../src/Domain/Payment/Controller/DepositController.php): `POST /api/payments/{provider}`.
- [WithdrawController](../src/Domain/Payment/Controller/WithdrawController.php): `POST /api/payments/{provider}/withdraw`.
- [CallbackController](../src/Domain/Payment/Controller/CallbackController.php): `POST /api/payments/{provider}/callback`.
- [CodonixPayment](../src/Domain/Payment/Provider/CodonixPayment.php) and [CoinbasePayment](../src/Domain/Payment/Provider/CoinbasePayment.php): the providers to complete.

Some behaviour is already implemented and some is a placeholder. Review what
is there before making changes. You do not need to implement `DemoPayment`.

## Your task

1. Implement the Codonix deposit flow through the existing integration.
2. Implement the Coinbase withdrawal flow through the existing integration.
3. Handle provider callbacks and signature verification where needed to complete
   each flow.
4. Review the setup and comment on anything you think should change. Explain
   the impact and your proposed improvement. You may make changes that are
   necessary for the two implementations, but explain why.

## Expected behaviour

- Codonix is available for deposits and cannot be used for withdrawals.
- Coinbase is available for withdrawals and cannot be used for deposits.
- Each supported request reaches the correct provider and returns a meaningful
  result with a reference and status.
- Amount and currency are validated, and invalid requests do not create or
  change a payment.
- Callbacks are verified before they change payment state. Repeated callbacks
  are safe, and conflicting updates are handled consistently.
- Unsupported operations, unknown providers, and provider failures produce
  appropriate JSON responses without exposing internal error details.

Use the current method contracts and request format as your starting point.
Where provider behaviour is not specified, choose a reasonable simulated
behaviour and document your assumptions.

## What to deliver

- Both provider implementations working within the existing setup.
- Brief notes explaining your choices and assumptions.
- Review comments covering problems or improvements you identified in the
  integration, including anything you chose to leave unchanged and why.

Be ready to explain how each flow works and what you would change before
connecting real providers.
