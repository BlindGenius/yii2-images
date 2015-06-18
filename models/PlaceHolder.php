<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 05.08.14
 * Time: 18:21
 *
 * TODO: check that placeholder is enable in module class
 * override methods
 */

namespace circulon\images\models;

use yii;
use \yii\base\Exception;

/**
 * TODO: check path to save and all image method for placeholder
 */


class Placeholder extends Image
{
    private $model_name = '';
    private $item_id = '';
    public $file_path = 'placeholder.png';
    public $url_alias = 'placeholder';

    public function __construct()
    {
        $this->file_path = basename(Yii::getAlias($this->getModule()->placeholderPath)) ;
    }

    public function getPathToOrigin()
    {

        $url = Yii::getAlias($this->getModule()->placeholderPath);
        if (!$url) {
            throw new Exception('Placeholder image must have path setting!!!');
        }
        return $url;
    }

    protected  function getSubDur(){
        return 'placeholder';
    }

    public function setMain($isMain = true){
        throw new Exception('You must not set placeholder as main image!!!');
    }

}

