<?php
namespace pluslib\Router;

interface RouteParameterable
{
  public static function fromRoute($id): static;
}