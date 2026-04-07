<?php namespace Pensoft\Cropper\Widgets;

use Backend\Classes\WidgetBase;
use Illuminate\Support\Arr;
use Pensoft\Cropper\Models\Cropper as CropperModel;

class Cropper extends WidgetBase
{
    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'Cropper';

    public function __construct($controller)
    {
        parent::__construct($controller, []);
        $this->checkPostback();
    }

    protected function checkPostback(): void
    {
    }

    public function prepareVars(): void
    {
        $model = $this->loadModel();

        $this->vars['src'] = $model->getSrc();
        $this->vars['path'] = $model->path;
        $this->vars['data'] = Arr::get($model->attributes, 'data', '');
        $this->vars['type'] = $model->type;
    }

    protected function loadAssets(): void
    {
        $this->addCss('css/imagecropper.css');
        $this->addCss('https://unpkg.com/cropperjs/dist/cropper.css');

        $this->addJs('https://unpkg.com/cropperjs/dist/cropper.js');
        $this->addJs('javascript/jquery-cropper.js');
        $this->addJs('javascript/imagecropper.js');
    }

    public function onLoadPopup(): string
    {
        if (post('error')) {
            throw new \Exception('Please save object and try again');
        }
        $this->prepareVars();
        return $this->makePartial('popup');
    }

    public function onUpdate(): void
    {
        $data = post('data');
        $model = $this->loadModel();
        if ($data) {
            $model->data = $data;
            $model->save();
        } else {
            $model->delete();
        }
    }

    protected function loadModel(): CropperModel
    {
        $path = post('path');
        $type = post('type');
        $model = CropperModel::where('type', $type)->where('path', $path)->first();

        if (!$model) {
            $model = new CropperModel(['type' => $type, 'path' => $path]);
            $model->save();  // Ensure the model is saved immediately upon creation
        }

        return $model;
    }
}