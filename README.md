yii2-images
===========
Yii2-images is yii2 module that allows attachment of images to any model, you can also retrieve images in any sizes. 
Additionally you can set main (default) image of a group of images.

Module supports Imagick and GD libraries, you can set up it in module settings.

Installation
-------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

``
php composer.phar require --prefer-dist circulon/yii2-images "*"
``

or add

``
"circulon/yii2-images": "*"
``

to the require section of your `composer.json` file.

Run the migration
```
php yii migrate/up --migrationPath=@vendor/circulon/yii2-images/migrations
```

Setup
-----
add the module setup to your app config 
 
    'modules' => [
    	...
		'images' => [
        	'class' => 'circulon\images\Module',
            // be sure, that permissions ok 
            // if you cant avoid permission errors you have to create "images" folder in web root manually and set 777 permissions
            'imagesStorePath' => 'images/store', //path to origin images
            'imagesCachePath' => 'images/cache', //path to resized copies
            'graphicsLibrary' => 'GD', //but really its better to use 'Imagick' 
            'placeholderPath' => '@webroot/images/placeholder.png', // if you want to get placeholder when image not exists, string will be processed by Yii::getAlias
        ],
    ],

optionally add the url route to the UrlManager

  NOTE : you may need to add a sililar rule to your module/s that have attached actions 
    
    'components' => [
        ...
        'urlManager' => [
          'enablePrettyUrl' => true,
          'showScriptName' => false,
          'rules' => [
              ...
             
              '<controller:\w+>/<action:\w+>/<id:\d+>/<ref:[a-z0-9_-]+>' => '<controller>/<action>',
              
              ...
           ],
        ],
        ...
    ]

attach the behavior to your model/s 
 
 	public function behaviors()
  {
    	return [
        	'image' => [
            	'class' => 'circlulon\images\behaviors\ImageBehavior',
              'idAttribute' => 'id' // set the models id column , default : 'id'
          ]
      ];
  }
    
 
add the action to the required controllers
  
  NOTE : it is recommended to add a route to your url manager config 
	
	public function actions()
	{
    	return [
        	'image' => [
          		'class' => 'circulon\images\actions\ImageAction',
          		
              // all the models to be searched by this controller action.
              // Can be fully qualified namespace or alias
          		'models' => ['User']  
        ]
    ];
}
   
    

Usage 
-------------

```php
    $model = Model::findOne(12); //Model must have id
    
    //If an image is first it will be main image for this model
    $model->attachImage('../../image.png');
    
    //But if you need set another image as main, use second arg
    $model->attachImage('../../image2.png', true);
    
    //get all images
    $images = $model->getImages();
    foreach($images as $img){
        //retun url to full image
        echo $img->getUrl();
        
        //return url to proportionally resized image by width
        echo $img->getUrl('300x');
    
        //return url to proportionally resized image by height
        echo $img->getUrl('x300');
        
        //return url to resized and cropped (center) image by width and height
        echo $img->getUrl('200x300');
    }
    
    // get image model 
    $image = $model->getImage();
    
    if($image){
        //get path to resized image 
        echo $image->getPath('400x300');
        
        //path to original image
        $image->getPathToOrigin();
        
        //will remove this image and all cache files
        $model->removeImage($image);
        
        // get the content of the image
        $model->getContent();
    }

    

```

with an img tag 

```html
    <!-- create a thumbnail sized image with base64 encoding for fast display -->
    <img src="data:image/png;base64,<?= $user->getImage()->getContent('50x50', true) ?>" alt="">
```

Details
-------------
1. Get images
    ```php
    $model->getImage(); //returns main image for model (first added image or setted as main)
    
    $model->removeImages(); //returns array with images
    
    //If there is no images for model, above methods will return Placeholder image or null
    //If you want placeholder set up it in module configuration (see documentation)
    
    ```
2. Remove image/images
    ```php
    $model->removeImage($image); //you must to pass image (object)
    
    $model->removeImages(); //will remove all images of this model
    ```

3. Set main/default image
    ```php
    $model->attachImage($absolutePathToImage, true); //will attach image and make it main
    
    foreach($model->getImages() as $img){
        if($img->id == $ourId){
            $model->setMainImage($img);//will set current image main
        }
    }
    ```

4. Get image sizes
    ```php
    $image = $model->getImage();
    $sizes = $image->getSizesWhen('x500');
    
    // the url is relative to the current controller eg /site/image/2/876293878623-x500
    echo '<img width="'.$sizes['width'].'" height="'.$sizes['height'].'" src="'.$image->getUrl('x500').'" />';
    
    // using a different controller / module+controller 
    //  example generated url /product/image/2/876293878623-x500
    echo '<img width="'.$sizes['width'].'" height="'.$sizes['height'].'" src="'.$image->getUrl('x500','product').'" />';
    
    ```

5. Get original image
    ```php
    $img = $model->getImage();
    echo $img->getPathToOrigin();
    ```

6. Get raw image content or encoded content
    ```php
    $image = $model->getImage();
    $content = $model->getContent();
    
    // output base64 encoded thumbnail with bootstrap3 css
    echo '<img class="img-responsive img-rounded" src="data:image/png;base64,'.$user->getImage()->getContent('50x50', true).'" alt="">';