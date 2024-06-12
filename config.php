<?php
namespace pluslib;

defined('ABSPATH') || exit;

class Config
{
  public static bool $passwordHashDisable = false;
  public static string $uploadDirectory = 'uploads/';
  public static bool $passwordHash_SHA256 = true;
}