<?php

namespace app\widgets\webuploader;

use Yii;

class MultiImage extends WebUploader
{
	public function init()
	{
		parent::init();
	}
	public function run()
	{
		$this->registerClientScript();
		return $this->render('multi');
	}
	protected function registerClientScript()
	{
		$view = $this->getView();
		MultiImageAsset::register($view);
	}
}
