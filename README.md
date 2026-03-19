# LightningStrike

**LightningStrike** is a work-in-progress PHP framework focused on one core idea:
**keep things fast, simple, and lightweight — without sacrificing the essentials.**

---

## Status

This project is currently under active development. Features and design decisions may evolve as the framework matures.

---

## Philosophy

LightningStrike aims to capture the spirit of **old-school PHP development**, where building a website could be as straightforward as creating a file per page, while introducing a minimal layer of modern structure.

The goal is not to compete with large, full-featured frameworks, but to provide a **lean alternative** for developers who want to:

- Get a simple website up and running quickly
- Avoid unnecessary complexity
- Retain control over how things work under the hood

---

## Core Principles

### 1. Lightweight by Design
LightningStrike avoids unnecessary abstractions and heavy components. It is built to stay minimal and fast.

### 2. No ORM by Default
There is **no ORM included out of the box**. You are free to use raw queries, your preferred database layer, or plug in your own solution.

### 3. View-First Architecture
Instead of controllers returning views, LightningStrike is centered around a **view-first system**.

After a request passes through the pipeline (middleware, routing, etc.), it is handed off to a **view class**, which:

- Receives the request data
- Processes it
- Returns a response

This keeps the flow simple and direct, especially for traditional web applications.

### 4. Focus on Web, Not APIs
At least initially, LightningStrike supports:

- `GET`
- `POST`

This reflects its focus on **building websites**, not REST APIs.

This decision is based on a simple fact: HTML forms only support `GET` and `POST`

---

## Why LightningStrike?

Modern frameworks are powerful — but often **overkill** for small or straightforward projects.

LightningStrike exists for cases where you want:

- A minimal setup
- Clear and predictable behavior
- Just enough structure (routing, middleware)
- Without the overhead of a full-stack framework

It’s about striking a balance between the simplicity of classic PHP and the usefulness of modern tooling

---

## Vision

LightningStrike is being built with the intention of staying:

- Minimal
- Understandable
- Easy to extend

Future improvements will aim to enhance usability without compromising the framework’s lightweight nature.

---

## Summary

LightningStrike is for developers who want to:

- Move fast
- Keep things simple
- Build traditional websites without unnecessary complexity

If you’ve ever missed the simplicity of old PHP — but still want routing and middleware — this framework is for you.

---

*More details coming as the project evolves.*