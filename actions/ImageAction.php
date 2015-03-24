<?php
/**
 * Created by Kieren Eaton.
 * Date: 24.03.15
 */

namespace circulon\images\actions;

use circulon\images\models\Image;
use yii\base\Action;

class ImageAction extends Action
{

    public $models = [];

    /**
     * returns model image
     * @return null|image
     */
    public function run($id = false, $ref = false)
    {
      if (empty($id) || empty($ref)) return null;

      $modelClasses = [];
      foreach ($this->models as $aModel) {
        if (preg_match('@\\\\([\w]+)$@', $aModel, $matches)) {
            $modelClasses[] = $matches[1];
        }
        else {
          $modelClasses[] = $aModel;
        }
      }

      $exploded = explode('_', $ref);
      $size = isset($exploded[1]) ? $exploded[1] : false;
      $ref = isset($exploded[0]) ? $exploded[0] : $ref;

      // this SHOULD return a unique record
      $image = Image::find()
        ->where([
          'item_id' => $id,
          'url_alias' => $ref,
        ])
        ->andWhere(['in', 'model_name', $modelClasses])->one();

        if($image){
          header('Content-Type: image/png');
          echo $image->getContent($size);
        }
    }
}


