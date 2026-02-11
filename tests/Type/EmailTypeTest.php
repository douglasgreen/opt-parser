<?php

declare(strict_types=1);

namespace Tests\Unit\Type;

use DouglasGreen\OptParser\Exception\ValidationException;
use DouglasGreen\OptParser\Type\EmailType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailType::class)]
#[Small]
final class EmailTypeTest extends TestCase
{
    public static function validEmailProvider(): iterable
    {
        yield 'simple gmail' => ['user@gmail.com'];
        yield 'with subdomain' => ['user@mail.example.com'];
        yield 'with plus' => ['user+tag@example.com'];
        yield 'with dots' => ['first.last@example.co.uk'];
    }

    public static function invalidEmailProvider(): iterable
    {
        yield 'missing at' => ['userexample.com'];
        yield 'missing domain' => ['user@'];
        yield 'missing user' => ['@example.com'];
        yield 'spaces' => ['user name@example.com'];
        yield 'empty string' => [''];
    }

    public function test_it_returns_type_name(): void
    {
        // Arrange
        $type = new EmailType();

        // Act
        $name = $type->getName();

        // Assert
        $this->assertSame('EMAIL', $name);
    }

    #[DataProvider('validEmailProvider')]
    public function test_it_validates_email_addresses(string $email): void
    {
        // Arrange
        $type = new EmailType();

        // Act
        $result = $type->validate($email);

        // Assert
        $this->assertSame($email, $result);
    }

    #[DataProvider('invalidEmailProvider')]
    public function test_it_rejects_invalid_emails(string $invalidEmail): void
    {
        // Arrange
        $type = new EmailType();

        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid email: ' . $invalidEmail);

        // Act
        $type->validate($invalidEmail);
    }
}
