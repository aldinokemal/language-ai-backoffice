# Git Hooks

This directory contains Git hooks that are shared across the team.

## Available Hooks

### pre-commit

Automatically runs Laravel Pint to format PHP code before each commit. This ensures consistent code style across the team.

**What it does:**
- Detects staged PHP files
- Runs `./vendor/bin/pint --dirty` to format only modified files
- Re-stages the formatted files
- Prevents commit if Pint fails

## Installation

The hooks are automatically installed when you run:

```bash
composer install
```

Or manually install them with:

```bash
composer setup-hooks
```

## For Team Members

### First Time Setup

After cloning the repository:

1. Run `composer install` - this automatically installs the Git hooks
2. The pre-commit hook will now run automatically on every commit

### Manual Installation

If for some reason the hooks aren't installed automatically:

```bash
composer setup-hooks
```

### Disabling the Hook (Not Recommended)

If you need to bypass the hook for a specific commit (use sparingly):

```bash
git commit --no-verify -m "your message"
```

## Cross-Platform Compatibility

These hooks work on:
- ✅ macOS
- ✅ Linux
- ✅ Windows (via Git Bash)

## Troubleshooting

**Hook not running:**
```bash
composer setup-hooks
```

**Permission errors (macOS/Linux):**
```bash
chmod +x .githooks/pre-commit
composer setup-hooks
```

**Pint not found:**
```bash
composer install
```
