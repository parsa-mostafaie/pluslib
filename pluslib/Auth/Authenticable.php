<?php
namespace pluslib\Auth;

interface Authenticable
{
  public function authenticate($credentials): bool;
  public function getAuthenticated($credentials): static|null;
}