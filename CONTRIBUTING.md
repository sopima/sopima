# Contributing to Sopima

Thank you for your interest in contributing to Sopima! This document explains how you can help.

## Reporting Bugs

Please open a [GitHub Issue](https://github.com/sopima/sopima/issues) and include:

- PHP version and extensions (`php -v`, `php -m`)
- Sopima version (see CHANGELOG)
- Steps to reproduce
- Expected vs. actual behaviour
- Relevant log output if available

## Suggesting Features

Open an Issue with the label `enhancement`. Describe the use case, not just the solution.

## Submitting Code

1. Fork the repository
2. Create a branch: `git checkout -b feat/your-feature`
3. Make your changes
4. Test manually (no automated test suite yet)
5. Open a Pull Request against `main`

### Coding Conventions

- Plain PHP 8.2+, no frameworks (no Laravel, no Symfony)
- SQLite only via PDO — do not introduce MySQL support
- No new Composer dependencies without prior discussion
- All schema changes via numbered migration files in `database/migrations/`
- Prepared statements for all DB queries — no raw user input in SQL
- `htmlspecialchars()` for all output in views
- Conventional Commit messages: `feat:`, `fix:`, `docs:`, `chore:`

### What We Won't Accept

- MySQL or other database drivers
- Framework dependencies
- Breaking changes to the `.env` configuration format without a migration path
- Hardcoded tenant IDs, server names, or internal infrastructure references

## Development Setup

See [docs/INSTALL.md](docs/INSTALL.md) for setup instructions.

The quickest way to get started:

```bash
cp .env.example .env
# edit .env – set APP_URL and APP_SECRET
docker compose up -d
# open APP_URL/setup in your browser
```

## Questions

Open a GitHub Discussion or an Issue — we're happy to help.