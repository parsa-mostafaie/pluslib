<?php
namespace pluslib\Auth;

use pluslib\Collections\Arr;

trait AuthenticableModel
{
  public function authenticate($credentials): bool
  {
    return !!$this->getAuthenticated($credentials);
  }

  public function getAuthenticated($credentials): static|null
  {
    $first = $this->authQuery($credentials)->first();

    if ($first) {
      foreach ($this->getHashFields() as $field) {
        if (!isset($credentials[$field]) || !pass_verify($credentials[$field], $first->$field)) {
          return null;
        }
      }

      return $first;
    }

    return null;
  }

  public function authQuery($credentials)
  {
    return static::where(0)->orWhere(Arr::except($credentials, $this->getHashFields()));
  }

  public function getHashFields()
  {
    return ['password'];
  }
}