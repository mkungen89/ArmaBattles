# Security Policy

## Code Scanning

This repository uses GitHub's default CodeQL setup for automated security scanning.

### CodeQL Configuration

CodeQL is configured to scan:
- PHP code
- JavaScript/TypeScript code
- GitHub Actions workflows

The default setup runs automatically on:
- Every push to the main branch
- Every pull request
- Weekly schedule (configurable in repository settings)

### Permissions

GitHub's default CodeQL setup uses minimal permissions:
- `contents: read` - To read repository code
- `security-events: write` - To upload scan results

No additional permissions are required or granted.

## Reporting a Vulnerability

If you discover a security vulnerability, please report it by:

1. **Do NOT** open a public issue
2. Contact the repository maintainers privately
3. Include detailed information about the vulnerability
4. Allow reasonable time for a fix before public disclosure

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| main    | :white_check_mark: |
| < main  | :x:                |

## Security Updates

Security updates are applied to the main branch as soon as possible after discovery.
