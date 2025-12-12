<?php

declare(strict_types=1);

namespace Vigihdev\Command\Enums;

/**
 * Enum ProjectEnum - Menyediakan jenis-jenis project yang didukung oleh sistem
 */
enum ProjectEnum: string
{
    case PROJECT = 'project';
    case NPM_PROJECT = 'npm-project';
    case WP_CLI_PROJECT = 'wp-cli-project';
}