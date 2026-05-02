# Overview

## Current Goal
Build ThreadCore as a Laravel-based microsaas for AI thread orchestration, provider management, and API gateway usage.

## Current Scope
ThreadCore is now an operational Laravel 12 microsaas slice with admin resources, customer dashboards, API keys, provider/model registries, gateway endpoints, provider adapters, token/cost tracking, and AI-backed memory compaction. The remaining work is hardening, polish, production deployment preparation, and live provider verification.

## Product Principles
- Keep the project English-first for app-facing assets and documentation.
- Support OpenRouter as the primary cloud provider while keeping the architecture provider-agnostic.
- Allow local providers such as Ollama when they are available.
- Keep token accounting and memory compaction as first-class product behavior.

## Current Status
The project began from a seed note, gained a clean documentation base, and now has a working Laravel 12 application with CMS-backed public pages, admin and customer surfaces, resource management, gateway runtime behavior, and a project-specific root README replacing the default Laravel page.
