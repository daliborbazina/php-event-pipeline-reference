# Design decisions

This repository intentionally stays small and focused.

## Sections

- [Goals](#goals)
- [Key decisions](#key-decisions)
- [Non-goals](#non-goals)
- [Trade-offs](#trade-offs)
- [Limitations](#limitations)
- [Failure modes](#failure-modes)
- [Delivery model](#delivery-model)
- [Summary](#summary)

---

## Goals

* demonstrate asynchronous processing in PHP systems
* keep system boundaries explicit and understandable
* provide an observable and verifiable result

The goal is not to simulate production complexity, but to clearly show how an event flows through the system.

---

## Key decisions

### No framework

The implementation avoids frameworks on purpose.

Reason:

* keep all dependencies visible
* avoid hidden abstractions
* make the execution flow easy to follow

---

### Explicit layering

The system is split into clear layers:

* event (domain)
* broker (transport)
* worker (processing)
* persistence (projection)

Reason:

* make responsibilities explicit
* reduce coupling between components

---

### SQLite as projection storage

SQLite is used instead of a full database.

Reason:

* zero setup
* deterministic behavior
* sufficient for demonstrating projections

---

### RabbitMQ as message broker

RabbitMQ is used for event transport.

Reason:

* widely used in production systems
* clear separation between producer and consumer
* supports asynchronous processing model

---

### CLI as entry point

All interactions happen via CLI commands.

Reason:

* avoids HTTP complexity
* keeps focus on backend architecture
* makes the system easy to run and inspect

---

## Non-goals

This repository intentionally avoids:

* becoming a full framework
* providing a complete test suite
* simulating a distributed production system
* implementing retries, DLQ, or advanced messaging patterns

These concerns are important in real systems but outside the scope of this reference.

---

## Trade-offs

- simplicity over production completeness
- at-least-once delivery instead of exactly-once guarantees
- SQLite over scalable storage
- CLI over HTTP interface
- no idempotency layer to keep processing logic minimal

---

## Limitations

- no retry handling
- no dead-letter queues
- no ordering guarantees

---

## Failure modes

- message loss if producer cannot reach the broker
- duplicate processing due to at-least-once delivery
- worker crash may interrupt processing mid-flow
- aggregate inconsistency if processing is interrupted between read and write

---

## Delivery model

The system operates under an at-least-once delivery model.

This means:
- messages can be delivered more than once
- processing must be idempotent in real systems
- no guarantees are made about exactly-once execution

This repository does not implement idempotency on purpose, to keep the example minimal.

---

## Summary

The design favors:

* clarity over completeness
* explicitness over abstraction
* observability over optimization

This makes the repository suitable as a learning and reference implementation.
