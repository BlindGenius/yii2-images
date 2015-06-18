<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 22.06.14
 * Time: 16:58
 */

namespace circulon\images\behaviors;

use circulon\images\models\Image;
use circulon\images\models\Placeholder;
use circulon\images\ModuleTrait;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\BaseFileHelper;
use yii\web\UploadedFile;

class ImageBehavior extends Behavior
{

    use ModuleTrait;

    /**
     * @var string
     */
    public $idAttribute = 'id';

    public $createAliasMethod = false;

    /**
     * @var ActiveRecord|null Model class, which will be used for storing image data in db, if not set default class(models/Image) will be used
     */
    public $modelClass = '\circulon\images\models\Image';

    /**
     *
     * Method copies image file to module store and creates db record.
     *
     * @param string|UploadedFile $newImage
     * @param bool $isMain
     * @return bool|Image
     * @throws \Exception
     */
    public function attachImage($newImage, $isMain = false)
    {
        if (!$this->owner->{$this->idAttribute}) {
            throw new \Exception($this->owner->classname().' must have an is when you attach image!');
        }

        $pictureFileName = '';

        if ($newImage instanceof UploadedFile) {
          $sourcePath = $newImage->tempName;
          $imageExt = $newImage->extension;
        } else {
          if(!preg_match('#http#', $newImage)){
              if (!file_exists($newImage)) {
                  throw new \Exception('File not exist! :'.$newImage);
              }
          } else {
              //nothing
          }
          $sourcePath = $newImage;
          $imageExt = pathinfo($newImage, PATHINFO_EXTENSION);
        }

        $pictureFileName = substr(sha1(microtime(true) . $sourcePath), 4, 12);
        $pictureFileName .= '.'.$imageExt;

        if (!file_exists($sourcePath)) {
            throw new \Exception('Source file doesnt exist! ' . $sourcePath . ' to ' . $newAbsolutePath);
        }

        $pictureSubDir = $this->getModule()->getModelSubDir($this->owner);
        $storePath = $this->getModule()->getStorePath($this->owner);

        $destPath = $storePath .
            DIRECTORY_SEPARATOR . $pictureSubDir .
            DIRECTORY_SEPARATOR . $pictureFileName;

        BaseFileHelper::createDirectory($storePath . DIRECTORY_SEPARATOR . $pictureSubDir,
            0775, true);

        if (!copy($sourcePath, $destPath)) {
            throw new \Exception('Failed to copy file from ' . $sourcePath . ' to ' . $destPath);
        }

        $image = new $this->modelClass;

        $image->item_id = $this->owner->{$this->idAttribute};
        $image->file_path = $pictureSubDir . '/' . $pictureFileName;
        $image->model_name = $this->getModule()->getShortClass($this->owner);

        $image->url_alias = $this->getAlias($image);

        if(!$image->save()){
            return false;
        }

        if (count($image->getErrors()) > 0) {

            $ar = array_shift($image->getErrors());

            unlink($newAbsolutePath);
            throw new \Exception(array_shift($ar));
        }
        $img = $this->owner->getImage();

        //If main image not exists
        if(
            is_object($img) && get_class($img)=='circulon\images\models\Placeholder'
            or
            $img == null
            or
            $isMain
        ){
            $this->setMainImage($image);
        }

        return $image;
    }

    /**
     * Sets main image of model
     * @param $img
     * @throws \Exception
     */
    public function setMainImage($img)
    {
        if ($this->owner->{$this->idAttribute} != $img->item_id) {
            throw new \Exception('Image must belong to this model');
        }
        $counter = 1;
        /* @var $img Image */
        $img->setMain(true);
        $img->url_alias = $this->getAliasString() . '-' . $counter;
        $img->save();


        $images = $this->owner->getImages();
        foreach ($images as $allImg) {

            if ($allImg->id == $img->id) {
                continue;
            } else {
                $counter++;
            }

            $allImg->setMain(false);
            $allImg->url_alias = $this->getAliasString() . '-' . $counter;
            $allImg->save();
        }

        $this->owner->clearImagesCache();
    }


    /**
     * Clear all images cache (and resized copies)
     * @return bool
     */
    public function clearImagesCache()
    {
        $cachePath = $this->getModule()->getCachePath();
        $subdir = $this->getModule()->getModelSubDir($this->owner);

        $dirToRemove = $cachePath . '/' . $subdir;

        if (preg_match('/' . preg_quote($cachePath, '/') . '/', $dirToRemove)) {
            BaseFileHelper::removeDirectory($dirToRemove);
            //exec('rm -rf ' . $dirToRemove);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns model images
     * First image alwats must be main image
     * @return array|yii\db\ActiveRecord[]
     */
    public function getImages()
    {
        $query = $this->imageQuery();
        $imageRecords = $query->all();
        if((empty($imageRecords)) && ($this->getModule()->getPlaceholder())) {
            $imageRecords[] = $this->getModule()->getPlaceholder();
        }

        return $imageRecords;
    }


    /**
     * returns main model image
     * @return array|null|ActiveRecord
     */
    public function getImage()
    {
        $query = $this->imageQuery();
        $query->andWhere(['is_main' => true]);
        $img = $query->one();

        if(!$img){
            return $this->getModule()->getPlaceholder();
        }

        return $img;
    }

    /**
     * Remove all model images
     */
    public function removeImages()
    {
        $images = $this->owner->getImages();
        if (count($images) < 1) {
            return true;
        } else {
            foreach ($images as $image) {
                $this->owner->removeImage($image);
            }
        }
    }


    /**
     *
     * removes concrete model's image
     * @param Image $img
     * @throws \Exception
     */
    public function removeImage(Image $img)
    {
        $img->clearCache();

        $storePath = $this->getModule()->getStorePath();

        $fileToRemove = $storePath . DIRECTORY_SEPARATOR . $img->file_path;
        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
        }
        $img->delete();
    }

    private function imageQuery()
    {
        $query = Image::find()
          ->where(
          [
            'item_id' => $this->owner->{$this->idAttribute},
            'model_name' => $this->getModule()->getShortClass($this->owner)
          ])
          ->orderBy(['is_main' => SORT_DESC]);

        return $query;
    }



    /** Make string part of image's url
     * @return string
     * @throws \Exception
     */
    private function getAliasString()
    {
        if ($this->createAliasMethod) {
            $string = $this->owner->{$this->createAliasMethod}();
            if (!is_string($string)) {
                throw new \Exception("Image's url must be string!");
            } else {
                return $string;
            }
        } else {
            return substr(sha1(microtime()), 3, 20);
        }
    }


    /**
     *
     * Обновить алиасы для картинок
     * Зачистить кэш
     */
    private function getAlias()
    {
        $aliasWords = $this->getAliasString();
        $imagesCount = count($this->owner->getImages());

        return $aliasWords . '-' . intval($imagesCount + 1);
    }




}


