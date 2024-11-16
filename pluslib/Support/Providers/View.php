<?php
namespace pluslib\Support\Providers;

use pluslib\Support\Facades\View as FacadesView;
use pluslib\Support\ServiceProvider;

class View extends ServiceProvider
{
  public function register()
  {
    //
  }

  public function boot()
  {
    FacadesView::setPathForAppViews(config('view.path', resources_path('views')));
    FacadesView::publishNamespace('pluslib', (__DIR__.'/../../../resources/views'));
  }
}