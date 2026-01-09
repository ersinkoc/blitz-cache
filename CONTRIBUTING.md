# Contributing to Blitz Cache

First off, thank you for considering contributing to Blitz Cache! It's people like you that make this project better.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How to Contribute](#how-to-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Documentation](#documentation)
- [Pull Request Process](#pull-request-process)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

### Our Pledge

We are committed to making participation in this project a harassment-free experience for everyone.

### Our Standards

Examples of behavior that contributes to creating a positive environment include:

- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

## Getting Started

### What You Can Help With

- 🐛 **Bug Reports** - Help us find and fix bugs
- 💡 **Feature Requests** - Suggest new features
- 📝 **Documentation** - Improve docs and examples
- 🧪 **Testing** - Write or improve tests
- 💻 **Code** - Fix bugs or implement features
- 🌐 **Translations** - Help translate Blitz Cache

### Ways to Contribute

1. **Report Bugs** - Use GitHub Issues
2. **Suggest Features** - Use GitHub Issues
3. **Submit Pull Requests** - Fix bugs or add features
4. **Improve Documentation** - Fix typos, add examples
5. **Write Tests** - Increase test coverage
6. **Review Pull Requests** - Help review others' work

## How to Contribute

### Reporting Bugs

We use GitHub Issues to track bugs. When creating a bug report, please include:

**Bug Report Template:**
```markdown
**Bug Description**
A clear and concise description of what the bug is.

**Steps to Reproduce**
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
- WordPress Version: [e.g. 6.4]
- PHP Version: [e.g. 8.1]
- Blitz Cache Version: [e.g. 1.0.0]
- Theme: [e.g. Astra]
- Active Plugins: [list]

**Additional Context**
Any other context about the problem.
```

### Suggesting Features

We use GitHub Issues for feature requests. When suggesting a feature, include:

**Feature Request Template:**
```markdown
**Feature Description**
A clear description of the feature you want.

**Problem It Solves**
What problem does this solve? Is your feature request related to a problem?

**Proposed Solution**
Describe what you want to happen.

**Alternatives**
Describe any alternative solutions you've considered.

**Additional Context**
Screenshots, mockups, or other context.
```

### Submitting Pull Requests

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Run tests: `composer test`
5. Commit changes: `git commit -m 'Add amazing feature'`
6. Push to branch: `git push origin feature/amazing-feature`
7. Open a Pull Request

## Development Setup

### Prerequisites

- PHP 8.0 or higher
- Composer
- Git
- WordPress 6.0+ for testing

### Installation

1. **Clone the repository:**
```bash
git clone https://github.com/ersinkoc/blitz-cache.git
cd blitz-cache
```

2. **Install dependencies:**
```bash
composer install
```

3. **Set up development environment:**
```bash
# Copy environment file
cp .env.example .env

# Install WordPress test suite
composer install-wp-tests
```

4. **Run tests:**
```bash
composer test
```

### Development Commands

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run code style checks
composer cs-check

# Fix code style issues
composer cs-fix

# Run static analysis
composer analyze

# Build for production
composer build
```

## Coding Standards

We follow WordPress Coding Standards and PSR-12.

### PHP Standards

- **WordPress Coding Standards** for WordPress-specific code
- **PSR-12** for general PHP code
- **PHPDoc** for documentation

### Code Style

- Use spaces, not tabs (4 spaces)
- Use meaningful variable names
- Keep functions small and focused
- Write self-documenting code
- Comment complex logic

### Example:

```php
/**
 * Get cached HTML for a specific key
 *
 * @param string $key Cache key
 * @return string|null Cached HTML or null if not found
 */
public function get_cached(string $key): ?string {
    $file = $this->cache_dir . $key . '.html';

    if (!file_exists($file)) {
        return null;
    }

    return file_get_contents($file);
}
```

### File Naming

- Class files: `class-blitz-cache-example.php`
- Test files: `ExampleTest.php` (in tests/ directory)
- Follow WordPress naming conventions

### Variable Naming

- **Classes:** `PascalCase` (e.g., `Blitz_Cache_Cache`)
- **Methods/Functions:** `snake_case` (e.g., `get_cache_key`)
- **Variables:** `snake_case` (e.g., `$cache_dir`)
- **Constants:** `UPPER_CASE` (e.g., `BLITZ_CACHE_VERSION`)

### Comments

- Use PHPDoc for functions and classes
- Explain WHY, not WHAT
- Keep comments up to date

```php
/**
 * Calculate cache hit ratio
 *
 * We use a simple calculation because it provides
 * the most accurate representation of cache effectiveness.
 *
 * @param int $hits Number of cache hits
 * @param int $misses Number of cache misses
 * @return float Hit ratio (0.0 to 1.0)
 */
private function calculate_hit_ratio(int $hits, int $misses): float {
    $total = $hits + $misses;
    return $total > 0 ? $hits / $total : 0.0;
}
```

## Testing

We require tests for all new features and bug fixes.

### Test Structure

```
tests/
├── Unit/          # Unit tests
├── Integration/   # Integration tests
└── bootstrap.php  # Test bootstrap
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
composer test tests/Unit/CacheTest.php

# Run tests with coverage report
composer test-coverage

# Generate HTML coverage report
composer test-coverage-html
```

### Writing Tests

1. **Unit Tests** - Test individual methods/classes
2. **Integration Tests** - Test how components work together

Example Unit Test:

```php
/**
 * Test that cache key generation works correctly
 */
public function test_cache_key_generation()
{
    $cache = new Blitz_Cache_Cache();
    $key = $cache->get_cache_key();

    // Assert key is MD5 hash (32 characters, hexadecimal)
    $this->assertEquals(32, strlen($key));
    $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $key);
}
```

### Test Coverage

We aim for 100% test coverage on all core functionality.

```bash
# Generate coverage report
composer test-coverage-html
open coverage/html/index.html
```

### Mocking

Use PHPUnit's built-in mocking:

```php
// Create mock object
$mock = $this->getMockBuilder('MyClass')
    ->onlyMethods(['methodName'])
    ->getMock();

// Expect method to be called once
$mock->expects($this->once())
    ->method('methodName')
    ->with($expectedArgument)
    ->willReturn($expectedValue);
```

## Documentation

Documentation is as important as code.

### What to Document

- All public methods and properties
- Complex algorithms
- Integration points
- Configuration options
- Hooks and filters

### Where to Document

1. **Code Comments** - PHPDoc in source code
2. **README** - Project overview
3. **Wiki** - Detailed guides
4. **Inline Comments** - Complex logic

### Example PHPDoc

```php
/**
 * Purge cache for a specific URL
 *
 * This method deletes the cached file for a given URL and also
 * handles mobile cache variants if enabled. The purge is
 * propagated to Cloudflare if configured.
 *
 * @param string $url URL to purge
 * @return void
 *
 * @hook blitz_cache_after_purge_url
 *     Fires after a URL is purged
 *     @param string $url The purged URL
 */
public function purge_url(string $url): void {
    $key = md5($url);
    $this->cache->delete($key);

    // Delete mobile variant if enabled
    if ($this->options['mobile_cache']) {
        $this->cache->delete(md5($url . '|mobile'));
    }

    // Propagate to Cloudflare
    if ($this->cloudflare) {
        $this->cloudflare->purge_urls([$url]);
    }

    do_action('blitz_cache_after_purge_url', $url);
}
```

## Pull Request Process

### Before Submitting

1. ✅ Run all tests: `composer test`
2. ✅ Check code style: `composer cs-check`
3. ✅ Update documentation
4. ✅ Update CHANGELOG.md
5. ✅ Write clear commit messages

### Commit Messages

Use [Conventional Commits](https://www.conventionalcommits.org/):

```
type(scope): subject

body

footer
```

Types:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes
- `refactor:` - Code refactoring
- `test:` - Adding/updating tests
- `chore:` - Maintenance tasks

Examples:

```bash
feat(cache): add GZIP compression support

Implement GZIP compression for cached files to reduce
bandwidth usage by up to 80%.

Closes #123

fix(purge): resolve issue with mobile cache not purging

When mobile cache was enabled, the mobile variant was not
being purged correctly. This fix ensures both desktop and
mobile caches are purged together.

docs(readme): update installation instructions

Add composer installation method to README
```

### Pull Request Template

When submitting a PR, please fill out:

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
How has this been tested?

## Checklist
- [ ] My code follows the coding standards
- [ ] I have performed a self-review of my code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes

## Screenshots
If applicable
```

### Review Process

1. **Automated Checks** - CI/CD runs tests and code style checks
2. **Code Review** - Maintainers review code
3. **Testing** - Changes are tested in staging
4. **Merge** - Approved changes are merged

### Review Criteria

- Code quality and standards compliance
- Test coverage
- Documentation
- Performance impact
- Security considerations
- Backward compatibility

## Release Process

1. **Feature freeze** - Decide which features go into release
2. **Beta testing** - Release beta for testing
3. **Bug fixes** - Fix any issues found
4. **Release candidate** - Final testing
5. **Release** - Tag and deploy
6. **Changelog** - Update CHANGELOG.md
7. **Announcement** - Post to blog/social media

## Questions?

If you have questions, feel free to:

- Open a GitHub Discussion
- Ask in the WordPress.org support forum
- Email: support@blitzcache.com

## Recognition

Contributors will be recognized in:

- README.md contributors section
- CHANGELOG.md release notes
- GitHub contributors page

Thank you for contributing to Blitz Cache! 🚀
