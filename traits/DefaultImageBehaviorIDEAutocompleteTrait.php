<?php
namespace circulon\images\behaviors;

/**
 * @method \circulon\images\models\Image attachImage($newImage, $isMain = false)
 * @see \circulon\images\behaviors\ImageBehavior::attachImage
 *
 * @method void setMainImage(\circulon\images\models\Image $img)
 * @see \circulon\images\behaviors\ImageBehavior::setMainImage
 *
 * @methid bool clearImagesCache()
 * @see \circulon\images\behaviors\ImageBehavior::clearImagesCache
 *
 * @method \circulon\images\models\Image[] getImages($usePlaceholder = true)
 * @see \circulon\images\behaviors\ImageBehavior::getImages
 *
 * @method null|\circulon\images\models\Image|\circulon\images\models\Placeholder getImage($id = null, $usePlaceholder = true)
 * @see \circulon\images\behaviors\ImageBehavior::getImage
 *
 * @method void removeImages()
 * @see \circulon\images\behaviors\ImageBehavior::removeImages
 *
 * @method void removeImage(\circulon\images\models\Image $img)
 * @see \circulon\images\behaviors\ImageBehavior::removeImage
 */
trait DefaultImageBehaviorIDEAutocompleteTrait {

}
