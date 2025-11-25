# VigihDev Command Library

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-8892BF.svg)](https://www.php.net/)
[![Symfony Components](https://img.shields.io/badge/symfony-6.4-green.svg)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A powerful Symfony Console-based command library with enhanced features for process management, filesystem operations, and configuration handling.

## Features

- 🚀 Built on Symfony Console component
- 🔧 Process management capabilities
- 📁 Advanced filesystem operations
- ⚙️ Configuration management with YAML support
- 🔒 Encryption integration
- 🧪 Comprehensive testing suite
- 📦 PSR-4 autoloading compliant

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

```bash
composer require vigihdev/command
```

## Usage

```php

require_once __DIR__ . '/vendor/autoload.php';

use Vigihdev\Command\YourCommandClass;

```

## Development

Run tests:

```bash
composer test
```

Start development server:

```bash
composer dev
```

## Package Dependencies

### Core Dependencies

- `symfony/console` - Console component
- `symfony/process` - Process management
- `symfony/filesystem` - Filesystem operations
- `symfony/finder` - File finding utilities
- `symfony/yaml` - YAML parsing and dumping

### VigihDev Packages

- `vigihdev/symfony-bridge-config` - Configuration bridge
- `vigihdev/serializer` - Serialization utilities
- `vigihdev/encryption` - Encryption capabilities

### External Dependencies

- `guzzlehttp/guzzle` - HTTP client

## Project Structure

```
src/
├── Command/          # Command classes
├── Process/          # Process management
├── Filesystem/       # Filesystem utilities
└── Config/          # Configuration handlers
tests/               # Test suites
```

## Testing

```bash
# Run PHPUnit tests
composer test

# Or directly with PHPUnit
./vendor/bin/phpunit
```

## License

This library is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Author

**Vigih Dev**

- Email: vigihdev@gmail.com
- Role: Developer

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## Support

For support and questions, please contact vigihdev@gmail.com
