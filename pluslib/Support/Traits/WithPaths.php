<?php
namespace pluslib\Support\Traits;

trait WithPaths
{
  protected $base_path;
  protected $public_path;
  protected $resources_path;
  protected $config_path;
  protected $storage_path;
  
  // Public path
  protected function getPublicPath()
  {
    return $this->public_path ?? $this->basepath('public');
  }

  public function public_path($path)
  {
    return join_paths($this->getPublicPath(), $path);
  }

  public function withPublicPath($public_path){
    $this->public_path = $public_path;

    return $this;
  }

  // Resources path
  protected function getResourcesPath()
  {
    return $this->resources_path ?? $this->basepath('resources');
  }

  public function resources_path($path)
  {
    return join_paths($this->getResourcesPath(), $path);
  }


  public function withResourcesPath($resources_path)
  {
    $this->resources_path = $resources_path;

    return $this;
  }

  // Config path
  protected function getConfigPath()
  {
    return $this->config_path ?? $this->basepath('config');
  }

  public function config_path($path)
  {
    return join_paths($this->getConfigPath(), $path);
  }

  public function withConfigPath($config_path)
  {
    $this->config_path = $config_path;

    return $this;
  }

  // Storage path
  protected function getStoragePath()
  {
    return $this->storage_path ?? $this->basepath('storage');
  }

  public function storage_path($path)
  {
    return join_paths($this->getStoragePath(), $path);
  }

  public function withStoragePath($storage_path)
  {
    $this->storage_path = $storage_path;

    return $this;
  }

  // Base Path
  public function withBasePath($base_path)
  {
    $this->base_path = $base_path;

    return $this;
  }

  public function basepath($path = '')
  {
    return join_paths($this->base_path, $path);
  }
}