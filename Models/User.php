<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class User implements Model {
    use GenericModel;

    public function __construct(
        private string $username,
        private string $email,
        private ?int $id = null,
        private ?string $email_confirmed_at = null,
        private ?string $created_at = null,
        private ?string $updated_at = null,
    ) {}

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getEmailConfirmedAt(): ?string {
        return $this->email_confirmed_at;
    }

    public function setEmailConfirmedAt(string $email_confirmed_at): void {
        $this->email_confirmed_at = $email_confirmed_at;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }

    public function setUpdatedAt(string $updated_at): void {
        $this->updated_at = $updated_at;
    }
}
