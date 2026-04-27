# Overview

## Current Goal
Build ThreadCore as a Laravel-based microsaas for AI thread orchestration, provider management, and API gateway usage.

## Current Scope
Laravel skeleton and the first admin/provider verification slice are now implemented. Gateway behavior, customer API keys, billing, and provider adapters remain upcoming work.

## Product Principles
- Keep the project English-first for app-facing assets and documentation.
- Support OpenRouter as the primary cloud provider while keeping the architecture provider-agnostic.
- Allow local providers such as Ollama when they are available.
- Keep token accounting and memory compaction as first-class product behavior.

## Current Status
The project began from a seed note, gained a clean documentation base, and now has a Laravel 12 application skeleton with seeded provider records.
